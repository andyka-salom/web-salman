<?php

namespace App\Exports;

use App\Models\CermatReport;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CermatReportsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $user;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $this->endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();
        $this->user = Auth::user();
    }

    public function query()
    {
        // Base Query dengan Eager Loading agar performa cepat
        $query = CermatReport::query()
            ->with([
                'reporter.company',
                'reporter.entityFunction',
                'area',
                'pelanggaran',
                'unsafeActs',
                'unsafeConditions',
                'actionItems',
                'lineSupervisor',
                'hsseOfficer',
                'closeOutPerformer'
            ])
            ->whereBetween('report_datetime', [$this->startDate, $this->endDate])
            ->where('status', '!=', CermatReport::STATUS_DRAFT); // Biasanya draft tidak di-export

        // --- LOGIKA FILTER BERDASARKAN ROLE ---

        if ($this->user->hasAnyRole(['super-admin', 'hsse', 'rses'])) {
            // 1. Super Admin, HSSE & RSES:
            // Melihat SEMUA data (Tanpa filter tambahan)
        }
        elseif ($this->user->hasRole('koordinator')) {
            // 2. Koordinator:
            // Hanya melihat data dari PERUSAHAAN-nya sendiri
            $query->whereHas('reporter', function ($q) {
                $q->where('company_id', $this->user->company_id);
            });
        }
        else {
            // 3. Fallback (User Biasa/Manager):
            // Hanya melihat laporannya sendiri (untuk keamanan jika endpoint ditembak user lain)
            $query->where('reporter_id', $this->user->id);
        }

        return $query->orderBy('report_datetime', 'desc');
    }

    public function map($report): array
    {
        // Helper untuk menggabungkan array relation menjadi string koma
        $unsafeActs = $report->unsafeActs->pluck('description')->implode(', ');
        $unsafeConditions = $report->unsafeConditions->pluck('description')->implode(', ');

        // Status Action Items (Selesai/Total)
        $totalActions = $report->actionItems->count();
        $completedActions = $report->actionItems->where('status', 'completed')->count();
        $actionProgress = $totalActions > 0 ? "{$completedActions}/{$totalActions}" : '-';

        return [
            $report->report_number,
            $report->report_datetime ? Carbon::parse($report->report_datetime)->format('d-m-Y H:i') : '-',
            $report->reporter->name ?? '-',
            $report->reporter->company->name ?? '-', // Penting untuk Admin melihat asal PT
            $report->area->name ?? '-',
            $report->location_details ?? '-',
            $report->details ?? '-',
            $report->classification ?? '-',
            $unsafeActs ?: '-',
            $unsafeConditions ?: '-',
            $report->immediate_action_taken ?? '-',
            str_replace('_', ' ', strtoupper($report->status)), // Format Status Global
            $report->supervisor_status ?? '-',
            $report->hsse_status ?? '-',
            $report->lineSupervisor->name ?? '-',
            $report->supervisor_approved_at ? Carbon::parse($report->supervisor_approved_at)->format('d-m-Y H:i') : '-',
            $report->hsseOfficer->name ?? '-',
            $report->hsse_reviewed_at ? Carbon::parse($report->hsse_reviewed_at)->format('d-m-Y H:i') : '-',
            $actionProgress, // Kolom Progress Tindakan
            $report->close_out_at ? Carbon::parse($report->close_out_at)->format('d-m-Y H:i') : '-',
            $report->closeOutPerformer->name ?? '-',
            $report->stop_card_issued ? 'Ya' : 'Tidak',
            $report->requires_feedback ? 'Ya' : 'Tidak',
        ];
    }

    public function headings(): array
    {
        return [
            'No. Laporan',
            'Waktu Kejadian',
            'Pelapor',
            'Perusahaan',
            'Area',
            'Lokasi Spesifik',
            'Uraian Kejadian',
            'Klasifikasi',
            'Unsafe Acts',
            'Unsafe Conditions',
            'Tindakan Langsung',
            'Status Laporan',
            'Status Supervisor',
            'Status HSSE',
            'Supervisor',
            'Tanggal Approved Supervisor',
            'HSSE Officer',
            'Tanggal Review HSSE',
            'Progress Action',
            'Tanggal Close Out',
            'Penutup',
            'Stop Card Issued',
            'Requires Feedback'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header (Baris 1) menjadi Bold
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
