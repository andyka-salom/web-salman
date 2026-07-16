<?php

namespace App\Exports;

use App\Models\KpiItem;
use App\Models\KpiReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KpiReportExport implements WithMultipleSheets
{
    public function __construct(private KpiReport $report) {}

    public function sheets(): array
    {
        return [new KpiReportSheet($this->report)];
    }
}

class KpiReportSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    private array $rows = [];

    public function __construct(private KpiReport $report) {}

    public function title(): string { return 'KPI HSSE'; }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // NO
            'B' => 30,  // ITEM KPI
            'C' => 28,  // PANDUAN
            'D' => 10,  // ∑ JUMLAH
            'E' => 35,  // KETERANGAN
            'F' => 10,  // NILAI
            'G' => 8,   // BOBOT
            'H' => 8,   // SCORE
            'I' => 22,  // CATATAN VERIFIKASI
            'J' => 12,  // STATUS
        ];
    }

    public function array(): array
    {
        $r      = $this->report;
        $period = strtoupper($r->kpiPeriod->label ?? '-');

        // Row 1 – judul
        $this->rows[] = ['KEY PERFORMANCE INDICATOR (KPI) HSSE - KONTRAKTOR','','','','','','','','',''];
        // Row 2 – perusahaan + periode
        $this->rows[] = [$r->company->name ?? '-','','','','','','','','BULAN : '.$period,''];
        // Row 3 – vessels header
        $this->rows[] = ['NO','NOMER KONTRAK','DURASI AKHIR KONTRAK','∑ KAPAL','NAMA KAPAL / UNIT LOGISTIK / TRANSPORT','','','','',''];

        // Vessel rows
        $vessels     = $r->vessels->sortBy('sort_order');
        $vesselCount = $vessels->count();
        foreach ($vessels as $vi => $vessel) {
            $this->rows[] = [
                $vi + 1,
                $vessel->contract_number ?? '—',
                $vessel->contract_end_date ? $vessel->contract_end_date->format('d M Y') : '—',
                $vi === 0 ? $vesselCount : '',
                $vessel->vessel_name,
                '', '', '', '', '',
            ];
        }

        // Blank row
        $this->rows[] = ['','','','','','','','','',''];

        // Column header
        $this->rows[] = [
            'NO','ITEM KPI','PANDUAN PENGISIAN','∑ JUMLAH','KETERANGAN IMPLEMENTASI',
            'NILAI','BOBOT','SCORE','CATATAN VERIFIKASI','STATUS REVIEW',
        ];

        // Items
        $allItems   = KpiItem::where('is_active', true)->orderBy('section')->orderBy('item_no')->get();
        $details    = $r->details->keyBy('kpi_item_id');
        $curSection = 'lagging';

        foreach ($allItems as $item) {
            // Section header
            if ($item->section === 'leading' && $curSection === 'lagging') {
                $curSection = 'leading';
                $this->rows[] = ['SECTION 2 – LEADING INDICATOR (BOBOT : 60)','','','','','','','','',''];
            }
            if ($item->section === 'lagging' && $curSection === 'lagging' && !isset($lagDone)) {
                $lagDone      = true;
                $this->rows[] = ['SECTION 1 – LAGGING INDICATOR (BOBOT : 40)','','','','','','','','',''];
            }

            $detail    = $details[$item->id] ?? null;
            $rs        = $detail?->review_status;
            $lastRev   = $detail?->reviews->sortByDesc('reviewed_at')->first();
            $catatan   = '';
            if ($rs === 'approved') $catatan = 'Disetujui';
            elseif ($rs === 'rejected') $catatan = 'Ditolak' . ($lastRev?->comment ? ': '.$lastRev->comment : '');

            $nameParts = explode("\n", $item->name, 2);

            $this->rows[] = [
                $item->item_no,
                $nameParts[0] . (!empty($nameParts[1]) ? "\n".$nameParts[1] : ''),
                $item->guidance,
                $detail?->actual_count ?? '',
                $detail?->keterangan   ?? '',
                $item->is_scored ? ($detail?->nilai !== null ? number_format((float)$detail->nilai, 1) : '—') : 'A/R',
                $item->is_scored ? number_format((float)$item->bobot * 100, 1) : '—',
                $item->is_scored ? ($detail?->score !== null ? number_format((float)$detail->score, 2) : '—') : 'A/R',
                $catatan,
                $rs ? ucfirst($rs) : '—',
            ];
        }

        // Total row
        $this->rows[] = [
            'TOTAL SCORE',
            '',
            '',
            '',
            '',
            '',
            'LAGGING: '.number_format((float)$r->total_score_lagging, 2),
            'TOTAL: '.number_format((float)$r->total_score, 2),
            'LEADING: '.number_format((float)$r->total_score_leading, 2),
            '',
        ];

        // Info rows
        $this->rows[] = ['','','','','','','','','',''];
        $this->rows[] = ['Dicetak', now()->format('d M Y, H:i'),'','','','','','','',''];
        $this->rows[] = ['Status', ucfirst($r->status),'','','','','','','',''];
        if ($r->validatedBy) {
            $this->rows[] = ['Divalidasi oleh', $r->validatedBy->name,'pada',$r->validated_at?->format('d M Y'),'','','','','',''];
        }

        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $ws    = $e->sheet->getDelegate();
                $rows  = $this->rows;
                $total = count($rows);

                // ── Styles ──────────────────────────────────────────
                $dark  = '1F3864';
                $mid   = '2F5496';
                $lag   = 'FEF3C7';
                $lagT  = '78350F';
                $lead  = 'DBEAFE';
                $leadT = '1E3A8A';

                // Row 1: judul
                $ws->mergeCells('A1:J1');
                $this->applyStyle($ws,'A1:J1',$dark,'FFFFFF',true,14,true,'center');

                // Row 2: perusahaan
                $ws->mergeCells('A2:H2');
                $ws->mergeCells('I2:J2');
                $this->applyStyle($ws,'A2:J2',$mid,'FFFFFF',true,11);

                // Row 3: vessel label
                $this->applyStyle($ws,'A3:J3','D6E4F7','1F3864',true,9,false,'center');

                // Vessel data rows (4 to 3+vesselCount)
                $vesselCount = $this->report->vessels->count();
                $vStart = 4; $vEnd = 3 + $vesselCount;
                for ($r = $vStart; $r <= $vEnd; $r++) {
                    $ws->getStyle("A{$r}:J{$r}")->getFont()->setSize(10);
                }

                // Blank + col header
                $colHeaderRow = $vEnd + 2;
                $this->applyStyle($ws,"A{$colHeaderRow}:J{$colHeaderRow}",$dark,'FFFFFF',true,9,false,'center');
                $ws->getRowDimension($colHeaderRow)->setRowHeight(30);

                // KPI item rows
                $dataStart = $colHeaderRow + 1;
                $dataEnd   = $total - 5; // approximate
                $rowNum    = $dataStart;

                foreach ($rows as $idx => $row) {
                    $excelRow = $idx + 1;
                    $cell0    = $row[0] ?? '';

                    // Section lagging
                    if (is_string($cell0) && str_contains($cell0, 'SECTION 1')) {
                        $ws->mergeCells("A{$excelRow}:J{$excelRow}");
                        $this->applyStyle($ws,"A{$excelRow}:J{$excelRow}",$lag,$lagT,true,10);
                    }
                    // Section leading
                    if (is_string($cell0) && str_contains($cell0, 'SECTION 2')) {
                        $ws->mergeCells("A{$excelRow}:J{$excelRow}");
                        $this->applyStyle($ws,"A{$excelRow}:J{$excelRow}",$lead,$leadT,true,10);
                    }
                    // Total
                    if (is_string($cell0) && str_contains($cell0, 'TOTAL SCORE')) {
                        $this->applyStyle($ws,"A{$excelRow}:J{$excelRow}",$dark,'FFFFFF',true,10);
                    }
                    // Wrap text for item name & keterangan
                    $ws->getStyle("B{$excelRow}")->getAlignment()->setWrapText(true);
                    $ws->getStyle("C{$excelRow}")->getAlignment()->setWrapText(true);
                    $ws->getStyle("E{$excelRow}")->getAlignment()->setWrapText(true);
                    $ws->getStyle("I{$excelRow}")->getAlignment()->setWrapText(true);

                    // Review status coloring
                    $rs = $row[9] ?? '';
                    if ($rs === 'Rejected') {
                        $ws->getStyle("A{$excelRow}:J{$excelRow}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF5F5');
                    } elseif ($rs === 'Approved') {
                        $ws->getStyle("A{$excelRow}:J{$excelRow}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');
                    }
                }

                // Border for entire table
                $ws->getStyle("A1:J{$total}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('B0B0B0');

                // Score column – center
                $ws->getStyle("F1:J{$total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Freeze header
                $ws->freezePane("A{$dataStart}");

                // Set row heights for data rows
                for ($i = $dataStart; $i <= $total; $i++) {
                    $ws->getRowDimension($i)->setRowHeight(40);
                }

                // Auto-filter on column header row
                $ws->setAutoFilter("A{$colHeaderRow}:J{$colHeaderRow}");
            },
        ];
    }

    private function applyStyle(
        Worksheet $ws, string $range, string $bgRgb, string $fgRgb,
        bool $bold = false, int $size = 10, bool $merge = false, string $hAlign = 'left'
    ): void {
        $style = $ws->getStyle($range);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgRgb);
        $style->getFont()->setColor(new Color($fgRgb))->setBold($bold)->setSize($size)->setName('Arial');
        $style->getAlignment()->setHorizontal(
            $hAlign === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT
        )->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
    }
}
