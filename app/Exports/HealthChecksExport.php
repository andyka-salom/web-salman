<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class HealthChecksExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle
{
    protected $healthChecks;
    protected $rowNumber = 0;

    public function __construct($healthChecks)
    {
        // Pastikan Collection yang diterima sudah di-load dengan relasi yang diperlukan (crewMember.vessel.company)
        $this->healthChecks = $healthChecks;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->healthChecks;
    }

    /**
     * Header kolom Excel sesuai permintaan
     */
    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama',
            'Perusahaan',
            'Posisi/Pekerjaan',
            'Area Kerja (Kapal)',
            'Keluhan Kesehatan',
            'Obat-obatan yang dikonsumsi',
            'Suhu (°C)',
            'Frekuensi Nadi (bpm)',
            'Tekanan Darah (mmHg)',
            'Frekuensi Nafas (x/min)',
            'Gula Darah (mg/dL)',
            'NAPZA',
            'Romberg Test',
            'Saturasi Oksigen (%)',
            'Kelelahan',
            'Keterangan',
        ];
    }

    /**
     * Mapping data untuk setiap row
     */
    public function map($healthCheck): array
    {
        $this->rowNumber++;

        $crew = $healthCheck->crewMember;
        $vessel = $crew->vessel ?? null;
        $companyName = $vessel && $vessel->company ? ($vessel->company->name ?? '-') : '-';
        $vesselName = $vessel ? ($vessel->name ?? '-') : '-';


        return [
            $this->rowNumber,
            $crew->nik ?? '-',
            $crew->name ?? '-',
            $companyName,
            $crew->position ?? '-',
            $vesselName, // Area Kerja diisi Nama Kapal
            $healthCheck->illness_complaints ?? '-',
            $healthCheck->medications_consumed ?? '-',
            $healthCheck->temperature ?? '-',
            $healthCheck->pulse_rate ?? '-',
            $healthCheck->blood_pressure ?? '-',
            $healthCheck->respiratory_rate ?? '-',
            $healthCheck->blood_sugar_level ?? '-',
            $this->formatNapzaResult($healthCheck->napza_test_result),
            $this->formatRombergResult($healthCheck->romberg_test_result),
            $healthCheck->oxygen_saturation ?? '-',
            $healthCheck->fatigue_level ?? '-',
            $healthCheck->remarks ?? '-',
        ];
    }

    /**
     * Styling untuk Excel (bersih dan profesional)
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->healthChecks->count() + 1;
        $lastColumn = 'R';

        // Style untuk header (Dark Gray, White Text, Bold)
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F4F4F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ]);

        // Style untuk data
        $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'BBBBBB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Text wrap untuk kolom keluhan, obat, dan keterangan
        $sheet->getStyle('G2:H' . $lastRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('R2:R' . $lastRow)->getAlignment()->setWrapText(true);

        // Center align untuk kolom numerik/tes
        $centerColumns = ['A', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q'];
        foreach ($centerColumns as $col) {
            $sheet->getStyle($col . '2:' . $col . $lastRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Freeze pane
        $sheet->freezePane('D2');

        return [];
    }

    /**
     * Lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // NIK
            'C' => 25,  // Nama
            'D' => 20,  // Perusahaan
            'E' => 20,  // Posisi
            'F' => 20,  // Area Kerja (Kapal)
            'G' => 30,  // Keluhan
            'H' => 30,  // Obat
            'I' => 10,  // Suhu
            'J' => 12,  // Nadi
            'K' => 15,  // TD
            'L' => 12,  // RR
            'M' => 15,  // GDS
            'N' => 12,  // NAPZA
            'O' => 15,  // Romberg
            'P' => 12,  // SpO2
            'Q' => 15,  // Kelelahan
            'R' => 35,  // Keterangan
        ];
    }

    /**
     * Title sheet
     */
    public function title(): string
    {
        return 'Laporan Kesehatan Kru';
    }

    /**
     * Format hasil tes NAPZA
     */
    private function formatNapzaResult($result)
    {
        switch ($result) {
            case 'negative':
                return 'Negatif';
            case 'non-negative':
                return 'Non-Negatif';
            case 'not_tested':
            default:
                return 'Tidak Dites';
        }
    }

    /**
     * Format hasil tes Romberg
     */
    private function formatRombergResult($result)
    {
        switch ($result) {
            case 'negative':
                return 'Negatif';
            case 'positive':
                return 'Positif';
            case 'not_tested':
            default:
                return 'Tidak Dites';
        }
    }
}
