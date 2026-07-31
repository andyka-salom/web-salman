<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

// Models
use App\Models\ActionItem;
use App\Models\Company;
use App\Models\EntityFunction;

class ActionItemMonitoringController extends Controller
{
    /**
     * Menampilkan halaman dashboard monitoring action items.
     * Termasuk statistik global, per fungsi, dan per perusahaan.
     */
    public function index(Request $request)
    {
        // 1. Ambil Data Master untuk Dropdown Filter
        $companies = Company::orderBy('name')->get(['id', 'name']);
        $entityFunctions = EntityFunction::orderBy('name')->get(['id', 'name']);

        // 2. Ambil Parameter Filter dari Request
        $filterCompany = $request->input('company_id');
        $filterFunction = $request->input('entity_function_id');
        $filterStatus = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 3. Base Query untuk Statistik (Join ke User untuk filter Company/Entity)
        $query = ActionItem::query()
            ->join('users', 'action_items.responsible_id', '=', 'users.id')
            ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
            ->leftJoin('entity_functions', 'users.entity_function_id', '=', 'entity_functions.id');

        // Terapkan Filter
        if ($filterCompany) {
            $query->where('users.company_id', $filterCompany);
        }
        if ($filterFunction) {
            $query->where('users.entity_function_id', $filterFunction);
        }
        if ($filterStatus) {
            $query->where('action_items.status', $filterStatus);
        }
        if ($startDate && $endDate) {
            // Filter berdasarkan tanggal pembuatan action item
            $query->whereBetween('action_items.created_at', [$startDate, $endDate]);
        }

        // 4. Hitung Statistik Utama (Cards)
        // Menggunakan clone() agar query dasar tidak berubah untuk query berikutnya
        $stats = $query->clone()->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN action_items.status = "'.ActionItem::STATUS_DO.'" THEN 1 ELSE 0 END) as status_do,
            SUM(CASE WHEN action_items.status = "'.ActionItem::STATUS_IN_PROGRESS.'" THEN 1 ELSE 0 END) as status_progress,
            SUM(CASE WHEN action_items.status = "'.ActionItem::STATUS_COMPLETED.'" THEN 1 ELSE 0 END) as status_completed,
            SUM(CASE WHEN action_items.status = "'.ActionItem::STATUS_CANT_DO.'" THEN 1 ELSE 0 END) as status_cant_do,
            SUM(CASE WHEN action_items.is_overdue = 1 AND action_items.status NOT IN ("'.ActionItem::STATUS_COMPLETED.'", "'.ActionItem::STATUS_CANT_DO.'") THEN 1 ELSE 0 END) as overdue
        ')->first();

        // 5. Statistik Grouping by Entity Function (PIC Dept)
        $byEntity = $query->clone()
            ->selectRaw('
                entity_functions.name as entity_name,
                COUNT(*) as total,
                SUM(CASE WHEN action_items.status = "'.ActionItem::STATUS_COMPLETED.'" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN action_items.is_overdue = 1 AND action_items.status NOT IN ("'.ActionItem::STATUS_COMPLETED.'", "'.ActionItem::STATUS_CANT_DO.'") THEN 1 ELSE 0 END) as overdue
            ')
            ->groupBy('entity_functions.name')
            ->orderByDesc('total')
            ->get();

        // 6. Statistik Grouping by Company
        $byCompany = $query->clone()
            ->selectRaw('
                companies.name as company_name,
                COUNT(*) as total,
                SUM(CASE WHEN action_items.status = "'.ActionItem::STATUS_COMPLETED.'" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN action_items.is_overdue = 1 AND action_items.status NOT IN ("'.ActionItem::STATUS_COMPLETED.'", "'.ActionItem::STATUS_CANT_DO.'") THEN 1 ELSE 0 END) as overdue
            ')
            ->groupBy('companies.name')
            ->orderByDesc('total')
            ->get();

        return view('cermat.action_monitoring.index', compact(
            'companies',
            'entityFunctions',
            'stats',
            'byEntity',
            'byCompany'
        ));
    }

    /**
     * Menyediakan data JSON untuk DataTables (Tab Detail Daftar).
     * Termasuk data foto dan detail untuk Modal.
     */
    public function data(Request $request)
    {
        // 1. Eager Load Relasi yang dibutuhkan
        // 'photos' diload agar kita bisa mengirim URL foto ke modal popup
        $query = ActionItem::with([
                'responsible.company',
                'responsible.entityFunction',
                'cermatReport',
                'photos'
            ])
            ->select('action_items.*');

        // 2. Terapkan Filter (Sama dengan Index, tapi via Eloquent Relationship)
        if ($request->company_id) {
            $query->whereHas('responsible', fn($q) => $q->where('company_id', $request->company_id));
        }
        if ($request->entity_function_id) {
            $query->whereHas('responsible', fn($q) => $q->where('entity_function_id', $request->entity_function_id));
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        // Filter tambahan jika ingin filter by status spesifik via parameter URL
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->editColumn('created_at', fn($item) => $item->created_at->format('d M Y'))

            // Kolom Target Date dengan indikator Overdue
            ->editColumn('target_date', function($item) {
                $date = $item->current_target_date ? $item->current_target_date->format('d M Y') : '-';

                // Cek flag overdue dari model
                if ($item->is_overdue && !in_array($item->status, [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO])) {
                    return '<span class="text-danger fw-bold" title="Melewati batas waktu">'.$date.' <i class="bi bi-exclamation-circle-fill"></i></span>';
                }
                return $date;
            })

            // Kolom Relasi (Menggunakan Null Coalescing Operator)
            ->addColumn('responsible_name', fn($item) => $item->responsible->name ?? '-')
            ->addColumn('entity_name', fn($item) => $item->responsible->entityFunction->name ?? '-')
            ->addColumn('company_name', fn($item) => $item->responsible->company->name ?? '-')

            // Kolom Status Badge
            ->editColumn('status', function($item) {
                $colors = [
                    ActionItem::STATUS_DO => 'secondary',
                    ActionItem::STATUS_IN_PROGRESS => 'info',
                    ActionItem::STATUS_COMPLETED => 'success',
                    ActionItem::STATUS_CANT_DO => 'danger'
                ];

                $label = ucwords(str_replace('_', ' ', $item->status));
                $color = $colors[$item->status] ?? 'secondary';

                return '<span class="badge bg-'.$color.'">'.$label.'</span>';
            })

            // Kolom Indikator Jumlah Foto
            ->addColumn('photos_count', function($item) {
                $count = $item->photos->count();
                if($count > 0) {
                    return '<span class="badge bg-light text-dark border"><i class="bi bi-camera-fill text-primary"></i> '.$count.'</span>';
                }
                return '-';
            })

            ->addColumn('action', function($item) {
                $photoData = $item->photos->map(fn($p) => \Illuminate\Support\Facades\Storage::disk('public')->url($p->file_path))->toArray();

                $modalData = [
                    'description' => $item->description,
                    'completion_notes' => $item->completion_notes ?? '-',
                    'photos' => $photoData,
                    'report_url' => route('cermat.reports.show', $item->cermat_report_id),
                    'report_number' => $item->cermatReport->report_number ?? 'N/A'
                ];

                $jsonData = htmlspecialchars(json_encode($modalData), ENT_QUOTES, 'UTF-8');

                // Icon Eye SVG
                $eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>';

                // Icon Arrow Up Right SVG
                $arrowIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A.5.5 0 0 0 1 3.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V7.864a.5.5 0 0 0-1 0V13.5H2V4h5.136a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>';

                return '
                <div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-sm btn-primary btn-view-detail" data-json="'.$jsonData.'" title="Lihat Detail & Bukti">
                        '.$eyeIcon.'
                    </button>
                    <a href="'.route('cermat.reports.show', $item->cermat_report_id).'" class="btn btn-sm btn-outline-secondary" target="_blank" title="Buka Laporan Asli">
                        '.$arrowIcon.'
                    </a>
                </div>';
            })
            ->rawColumns(['target_date', 'status', 'photos_count', 'action'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $filters = [
            'company_id' => $request->input('company_id'),
            'entity_function_id' => $request->input('entity_function_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'status' => $request->input('status'),
        ];

        // Generate filename dengan timestamp
        $timestamp = now()->format('Y-m-d_His');
        $filename = "Action_Items_Export_{$timestamp}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ActionItemsExport($filters),
            $filename
        );
    }
}
