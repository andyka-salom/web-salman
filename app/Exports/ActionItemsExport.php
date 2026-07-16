<?php

namespace App\Exports;

use App\Models\ActionItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Request;

class ActionItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query data yang akan diekspor
     */
    public function collection()
    {
        $query = ActionItem::with([
            'responsible.company',
            'responsible.entityFunction',
            'cermatReport',
            'photos'
        ])->select('action_items.*');

        // Terapkan Filter
        if (!empty($this->filters['company_id'])) {
            $query->whereHas('responsible', fn($q) => $q->where('company_id', $this->filters['company_id']));
        }
        if (!empty($this->filters['entity_function_id'])) {
            $query->whereHas('responsible', fn($q) => $q->where('entity_function_id', $this->filters['entity_function_id']));
        }
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('created_at', [$this->filters['start_date'], $this->filters['end_date']]);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('current_target_date', 'asc')->get();
    }

    /**
     * Header kolom Excel
     */
    public function headings(): array
    {
        return [
            'No',
            'No. Laporan CERMAT',
            'Deskripsi Tindakan',
            'PIC (Penanggung Jawab)',
            'Fungsi / Departemen',
            'Perusahaan',
            'Tanggal Pembuatan',
            'Target Penyelesaian',
            'Status',
            'Jumlah Perpanjangan',
            'Overdue',
            'Catatan Penyelesaian',
            'Jumlah Foto Bukti',
            'Tanggal Selesai'
        ];
    }

    /**
     * Mapping data ke kolom
     */
    public function map($actionItem): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $statusLabel = match($actionItem->status) {
            ActionItem::STATUS_DO => 'To Do',
            ActionItem::STATUS_IN_PROGRESS => 'In Progress',
            ActionItem::STATUS_COMPLETED => 'Completed',
            ActionItem::STATUS_CANT_DO => 'Cannot Do',
            default => 'Unknown'
        };

        $isOverdue = $actionItem->is_overdue &&
                     !in_array($actionItem->status, [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO]);

        return [
            $rowNumber,
            $actionItem->cermatReport->report_number ?? '-',
            $actionItem->description ?? '-',
            $actionItem->responsible->name ?? '-',
            $actionItem->responsible->entityFunction->name ?? '-',
            $actionItem->responsible->company->name ?? '-',
            $actionItem->created_at ? $actionItem->created_at->format('d M Y H:i') : '-',
            $actionItem->current_target_date ? $actionItem->current_target_date->format('d M Y') : '-',
            $statusLabel,
            $actionItem->extension_count ?? 0,
            $isOverdue ? 'YA' : 'TIDAK',
            $actionItem->completion_notes ?? '-',
            $actionItem->photos->count(),
            $actionItem->completed_at ? $actionItem->completed_at->format('d M Y H:i') : '-'
        ];
    }

    /**
     * Styling untuk Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Style header
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0d6efd']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A1:N1');

        // Freeze header row
        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 20,  // No. Laporan
            'C' => 40,  // Deskripsi
            'D' => 25,  // PIC
            'E' => 20,  // Fungsi
            'F' => 20,  // Perusahaan
            'G' => 18,  // Tgl Pembuatan
            'H' => 15,  // Target
            'I' => 12,  // Status
            'J' => 12,  // Perpanjangan
            'K' => 10,  // Overdue
            'L' => 35,  // Catatan
            'M' => 12,  // Foto
            'N' => 18   // Tgl Selesai
        ];
    }
}
