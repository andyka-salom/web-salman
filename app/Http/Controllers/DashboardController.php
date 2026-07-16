<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CermatReport;
use App\Models\ActionItem;
use App\Models\ActionCategory;
use App\Models\Company;
use App\Models\EntityFunction;
use App\Models\CampaignSalman;
use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function home()
{
    $user = auth()->user();
    return view('home');
}
    /**
     * Menampilkan halaman Dashboard Analytics (View).
     */
    public function analytics()
    {
        $user = Auth::user();
        $companies = [];

        // Hanya Super Admin atau HSSE yang butuh dropdown filter perusahaan
        if ($user->hasRole(['super-admin', 'hsse'])) {
            $companies = Company::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('dashboard.cermat', compact('companies'));
    }

    /**
     * API Endpoint untuk mengambil data statistik (AJAX Request).
     */
    public function getAnalyticsData(Request $request)
    {
        $user = Auth::user();

        // 1. Filter Tanggal (Default: Awal Tahun s/d Hari Ini)
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfYear();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // 2. Filter Perusahaan (Role-Based Logic)
        $companyId = null;

        if ($user->hasRole(['super-admin', 'hsse'])) {
            // Admin bisa memilih perusahaan dari dropdown. Jika kosong = Semua.
            $companyId = $request->input('company_id');
        } elseif ($user->hasRole('koordinator')) {
            // Koordinator melihat semua data di perusahaannya sendiri
            $companyId = $user->company_id;
        } else {
            // User biasa hanya melihat perusahaannya sendiri (biasanya juga dibatasi per-user di query laporan)
            $companyId = $user->company_id;
        }

        // 3. Base Query Laporan
        // Mengambil laporan aktif (bukan draft) dalam rentang tanggal
        $baseQuery = CermatReport::query()
            ->whereBetween('report_datetime', [$startDate, $endDate])
            ->where('status', '!=', CermatReport::STATUS_DRAFT);

        // Terapkan filter perusahaan pada Base Query
        if (!empty($companyId)) {
            $baseQuery->whereHas('reporter', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        // Jika User Biasa (bukan Admin/Koordinator/HSSE), mungkin ingin membatasi ke laporannya sendiri?
        // Namun biasanya Dashboard Analytics bersifat global per-perusahaan agar user aware.
        // Jika ingin dibatasi per-user, uncomment baris di bawah:
        // if (!$user->hasAnyRole(['super-admin', 'hsse', 'koordinator', 'manager'])) {
        //    $baseQuery->where('reporter_id', $user->id);
        // }


        // --- AGREGASI DATA ---

        $data = [];

        // A. KPI Stats (Card Atas)
        $data['kpis'] = $this->calculateKPIs($baseQuery, $startDate, $endDate, $companyId);

        // B. Charts Data
        $data['trendData'] = $this->getTrendData($startDate, $endDate, $companyId);
        $data['classificationData'] = $this->getClassificationData($baseQuery);
        $data['actionStatusData'] = $this->getActionStatusData($startDate, $endDate, $companyId);

        // C. Secondary Chart (Ranking)
        // Jika Super Admin melihat "Semua Perusahaan", tampilkan Ranking Perusahaan.
        // Jika Filter Perusahaan aktif (atau user non-admin), tampilkan Ranking Area/Lokasi.
        if (($user->hasRole('super-admin') || $user->hasRole('hsse')) && empty($companyId)) {
            $data['secondaryChartData'] = $this->getTopCompaniesData($startDate, $endDate);
            $data['secondaryChartType'] = 'company';
        } else {
            $data['secondaryChartData'] = $this->getTopAreasData($baseQuery);
            $data['secondaryChartType'] = 'area';
        }

        // D. Tables Data
        $data['clsrViolations'] = $this->getClsrViolations($baseQuery);
        $data['userActionItems'] = $this->getUserActionItems($user->id, $companyId);

        return response()->json($data);
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Menghitung Key Performance Indicators (Total, Closure Rate, Overdue).
     */
    private function calculateKPIs($baseQuery, $start, $end, $companyId)
    {
        // 1. Total Laporan Masuk
        $totalReports = (clone $baseQuery)->count();

        // 2. Closure Rate (Laporan Selesai / Total Laporan)
        // Status Selesai = CLOSED atau NO ACTION REQUIRED
        $closedCount = (clone $baseQuery)
            ->whereIn('status', [
                CermatReport::STATUS_CLOSED,
                CermatReport::STATUS_NO_ACTION_REQUIRED
            ])
            ->count();

        $closureRate = $totalReports > 0 ? round(($closedCount / $totalReports) * 100, 1) : 0;

        // 3. Overdue Actions (Tindakan Terlambat)
        // Query Action Item terpisah karena tidak selalu 1-to-1 dengan report di baseQuery
        $actionQuery = ActionItem::whereHas('cermatReport', function($q) use ($start, $end, $companyId) {
            $q->whereBetween('report_datetime', [$start, $end]);
            // Filter Perusahaan pada Report -> Reporter
            if (!empty($companyId)) {
                $q->whereHas('reporter', fn($sq) => $sq->where('company_id', $companyId));
            }
        });

        // Definisi Overdue:
        // Status bukan (Completed/Cant Do) DAN (Target Date < Sekarang ATAU Flag Overdue True)
        $overdueCount = (clone $actionQuery)
            ->whereNotIn('status', [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO])
            ->where(function($q) {
                $q->where('target_date', '<', Carbon::now())
                  ->orWhere('is_overdue', true);
            })->count();

        // 4. Close Out Progress (Completed Actions / Total Actions)
        $totalActions = (clone $actionQuery)->count();
        $completedActions = (clone $actionQuery)->where('status', ActionItem::STATUS_COMPLETED)->count();
        $progress = $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 1) : 0;

        return [
            'totalReports' => number_format($totalReports, 0, ',', '.'),
            'closureRate' => $closureRate . '%',
            'overdueActions' => number_format($overdueCount, 0, ',', '.'),
            'closeOutProgress' => $progress . '%' // Opsional, bisa dipakai di UI
        ];
    }

    /**
     * Data Tren Laporan per Bulan (Line Chart).
     */
    private function getTrendData($start, $end, $companyId)
    {
        // Query grouping by YYYY-MM
        $query = CermatReport::select(
            DB::raw("DATE_FORMAT(report_datetime, '%Y-%m') as period"),
            DB::raw('count(*) as total')
        )
        ->whereBetween('report_datetime', [$start, $end])
        ->where('status', '!=', CermatReport::STATUS_DRAFT);

        if (!empty($companyId)) {
            $query->whereHas('reporter', fn($q) => $q->where('company_id', $companyId));
        }

        $results = $query->groupBy('period')->orderBy('period')->get()->pluck('total', 'period');

        // Isi bulan yang kosong dengan 0 (Filling Gaps)
        $period = CarbonPeriod::create($start, '1 month', $end);
        $labels = [];
        $data = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y'); // Contoh: Des 2024
            $data[] = $results[$key] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Data Klasifikasi Temuan (Pie/Donut Chart).
     * Kolom: classification (Unsafe Act, Unsafe Condition, dll)
     */
    private function getClassificationData($baseQuery)
    {
        $stats = (clone $baseQuery)
            ->select('classification', DB::raw('count(*) as total'))
            ->whereNotNull('classification') // Pastikan tidak null
            ->groupBy('classification')
            ->get()
            ->pluck('total', 'classification');

        // Urutan label agar konsisten warnanya di Chart.js
        $possibleKeys = ['unsafe_action', 'unsafe_condition', 'near_miss', 'incident', 'positive_aspect'];

        // Ambil keys dinamis juga jika ada custom classification
        $allKeys = array_unique(array_merge($possibleKeys, $stats->keys()->toArray()));

        $finalLabels = [];
        $finalData = [];

        foreach($allKeys as $type) {
            // Kita hanya ambil label jika ada datanya ATAU jika dia masuk dalam possibleKeys (agar urutan tetap)
            if (isset($stats[$type]) || in_array($type, $possibleKeys)) {
                $finalLabels[] = ucwords(str_replace('_', ' ', $type));
                $finalData[] = (int) ($stats[$type] ?? 0);
            }
        }

        return ['labels' => $finalLabels, 'data' => $finalData];
    }

    /**
     * Data Status Tindakan (Bar Chart).
     * Relasi: CermatReport -> ActionItems
     */
    private function getActionStatusData($start, $end, $companyId)
    {
        $query = ActionItem::whereHas('cermatReport', function($q) use ($start, $end, $companyId) {
            $q->whereBetween('report_datetime', [$start, $end]);
            if (!empty($companyId)) {
                $q->whereHas('reporter', fn($sq) => $sq->where('company_id', $companyId));
            }
        });

        $completed = (clone $query)->where('status', ActionItem::STATUS_COMPLETED)->count();
        $inProgress = (clone $query)->where('status', ActionItem::STATUS_IN_PROGRESS)->count();
        $todo = (clone $query)->where('status', ActionItem::STATUS_DO)->count();
        $cantDo = (clone $query)->where('status', ActionItem::STATUS_CANT_DO)->count();

        // Logic Overdue
        $overdue = (clone $query)
            ->whereNotIn('status', [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO])
            ->where(function($q) {
                $q->where('target_date', '<', Carbon::now())->orWhere('is_overdue', true);
            })->count();

        return [
            'labels' => ['Completed', 'In Progress', 'To Do', 'Can\'t Do', 'Overdue'],
            'data' => [$completed, $inProgress, $todo, $cantDo, $overdue]
        ];
    }

    /**
     * Top 10 Perusahaan dengan Laporan Terbanyak (Horizontal Bar).
     */
    private function getTopCompaniesData($start, $end)
    {
        $data = CermatReport::join('users', 'cermat_reports.reporter_id', '=', 'users.id')
            ->join('companies', 'users.company_id', '=', 'companies.id')
            ->whereBetween('cermat_reports.report_datetime', [$start, $end])
            ->where('cermat_reports.status', '!=', CermatReport::STATUS_DRAFT)
            ->select('companies.name', DB::raw('count(*) as total'))
            ->groupBy('companies.id', 'companies.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('total')->toArray()
        ];
    }

    /**
     * Top 10 Area/Lokasi dengan Temuan Terbanyak (Horizontal Bar).
     */
    private function getTopAreasData($baseQuery)
    {
        $data = (clone $baseQuery)
            ->join('areas', 'cermat_reports.area_id', '=', 'areas.id')
            ->select('areas.name', DB::raw('count(*) as total'))
            ->groupBy('areas.id', 'areas.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('total')->toArray()
        ];
    }

    /**
     * Tabel Pelanggaran Terbanyak (Top 5).
     * Menggunakan model Pelanggaran terbaru (sequence_number, name).
     */
    private function getClsrViolations($baseQuery)
    {
        return (clone $baseQuery)
            ->whereNotNull('pelanggaran_id')
            ->join('pelanggaran', 'cermat_reports.pelanggaran_id', '=', 'pelanggaran.id')
            ->select(
                'pelanggaran.sequence_number',
                'pelanggaran.name',
                DB::raw('count(*) as count')
            )
            ->groupBy('pelanggaran.id', 'pelanggaran.sequence_number', 'pelanggaran.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }

    /**
     * Tabel Action Item Pending milik User (My Tasks).
     */
    private function getUserActionItems($userId, $companyId)
    {
        // Ambil Action Item dimana user ini adalah Responsible Person
        // Filter: Status belum Completed dan belum Cant Do
        $query = ActionItem::with(['cermatReport', 'actionCategory'])
            ->where('responsible_id', $userId)
            ->whereNotIn('status', [ActionItem::STATUS_COMPLETED, ActionItem::STATUS_CANT_DO]);

        if (!empty($companyId)) {
            // Optional: Filter task berdasarkan asal laporan perusahaan
            $query->whereHas('cermatReport.reporter', fn($q) => $q->where('company_id', $companyId));
        }

        $items = $query->orderBy('target_date', 'asc')->limit(10)->get();

        // Format data untuk JSON
        return $items->map(function($item) {
            $isOverdue = $item->is_overdue || ($item->target_date && Carbon::parse($item->target_date)->isPast());

            return [
                'report_number' => $item->cermatReport->report_number ?? '-',
                'category_code' => $item->actionCategory->code ?? '-',
                'category_name' => $item->actionCategory->name ?? '-',
                'duration_range' => $item->actionCategory->duration_range ?? '-',
                'description' => $item->description,
                'target_date' => $item->target_date ? Carbon::parse($item->target_date)->format('d M Y') : '-',
                'is_overdue' => $isOverdue
            ];
        });
    }
        public function campaignSalman()
    {

        $user = Auth::user();

        // Data untuk filter dropdown (hanya admin & hsse yang butuh list company)
        $companies = $user->hasAnyRole(['super-admin', 'hsse'])
            ? Company::orderBy('name')->get()
            : collect([]);

        // Entity Functions untuk dropdown (untuk Super Admin/HSSE atau user PHM tanpa entity function)
        $entityFunctions = collect([]);

        // Jika Super Admin/HSSE, bisa pilih entity function
        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            $entityFunctions = EntityFunction::active()->orderBy('name')->get();
        }
        // Jika user PHM tanpa entity function, tampilkan list entity function
        elseif ($user->company_id) {
            $userCompany = Company::find($user->company_id);
            if ($userCompany && $userCompany->slug === 'pertamina-hulu-mahakam' && !$user->entity_function_id) {
                $entityFunctions = EntityFunction::active()->orderBy('name')->get();
            }
        }

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.analytics')],
            ['label' => 'Dashboard Campaign Salman']
        ];

        return view('dashboard.campaign-salman', compact('companies', 'entityFunctions', 'breadcrumbs'));
    }

    /**
     * Get Campaign Salman Analytics Data
     */
    public function getCampaignSalmanData(Request $request)
    {

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'company_id' => 'nullable|exists:companies,id',
            'entity_function_id' => 'nullable|exists:entity_functions,id',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Base Query with Authorization Logic
        $query = CampaignSalman::with(['company', 'creator.entityFunction'])
            ->whereBetween('tanggal', [$startDate, $endDate]);

        // Apply authorization filters
        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            // Super Admin & HSSE can filter by company
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            // Filter by entity function if selected
            if ($request->filled('entity_function_id')) {
                $query->whereHas('creator', function($q) use ($request) {
                    $q->where('entity_function_id', $request->entity_function_id);
                });
            }
        } elseif ($user->company_id) {
            $userCompany = Company::find($user->company_id);

            if ($userCompany && $userCompany->slug === 'pertamina-hulu-mahakam') {
                // Filter by company
                $query->where('company_id', $user->company_id);

                // User PHM dengan entity function: filter by entity function
                if ($user->entity_function_id) {
                    $query->whereHas('creator', function($q) use ($user) {
                        $q->where('entity_function_id', $user->entity_function_id);
                    });
                }
                // User PHM tanpa entity function: bisa filter manual
                elseif ($request->filled('entity_function_id')) {
                    $query->whereHas('creator', function($q) use ($request) {
                        $q->where('entity_function_id', $request->entity_function_id);
                    });
                }
            } else {
                $query->where('company_id', $user->company_id);
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        // Get all data
        $campaigns = $query->get();

        // KPI Calculations
        $kpis = [
            'totalCampaigns' => $campaigns->count(),
            'totalParticipants' => $campaigns->sum('jumlah_peserta'),
            'avgParticipants' => $campaigns->count() > 0
                ? number_format($campaigns->avg('jumlah_peserta'), 0)
                : '0',
            'totalDocuments' => $campaigns->sum(function($c) {
                return count($c->dokumentasi ?? []) + count($c->daftar_hadir ?? []);
            }),
        ];

        // Trend Data (Monthly)
        $trendData = $this->getCampaignTrendData($campaigns, $startDate, $endDate);

        // Top Locations
        $topLocations = $this->getTopLocations($campaigns);

        // Top Speakers
        $topSpeakers = $this->getTopSpeakers($campaigns);

        // Company Distribution (untuk super admin/hsse tanpa filter company)
        $companyDistribution = null;
        if ($user->hasAnyRole(['super-admin', 'hsse']) && !$request->filled('company_id')) {
            $companyDistribution = $this->getCompanyDistribution($campaigns);
        }

        // Entity Function Distribution
        $entityFunctionDistribution = null;
        $showEntityFunctionChart = false;

        // Tampilkan chart Entity Function jika:
        // 1. Super Admin/HSSE dan memilih company PHM (atau tidak memilih company tapi ada data PHM)
        // 2. User PHM tanpa entity function
        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            if ($request->filled('company_id')) {
                $selectedCompany = Company::find($request->company_id);
                if ($selectedCompany && $selectedCompany->slug === 'pertamina-hulu-mahakam') {
                    $showEntityFunctionChart = true;
                }
            } elseif (!$request->filled('entity_function_id')) {
                // Cek apakah ada data dari PHM
                $hasPHMData = $campaigns->filter(function($c) {
                    return $c->company && $c->company->slug === 'pertamina-hulu-mahakam';
                })->count() > 0;

                if ($hasPHMData) {
                    $showEntityFunctionChart = true;
                }
            }
        } elseif ($user->company_id) {
            $userCompany = Company::find($user->company_id);
            if ($userCompany && $userCompany->slug === 'pertamina-hulu-mahakam' && !$user->entity_function_id && !$request->filled('entity_function_id')) {
                $showEntityFunctionChart = true;
            }
        }

        if ($showEntityFunctionChart) {
            $entityFunctionDistribution = $this->getEntityFunctionDistribution($campaigns);
        }

        // Recent Campaigns
        $recentCampaigns = $campaigns->sortByDesc('tanggal')->take(10)->map(function($campaign) {
            return [
                'id' => $campaign->id,
                'tanggal' => $campaign->tanggal->format('d M Y'),
                'tema' => $campaign->tema,
                'lokasi' => $campaign->lokasi,
                'peserta' => $campaign->jumlah_peserta,
                'pemateri' => $campaign->pemateri,
                'company_name' => $campaign->company ? $campaign->company->name : '-',
                'entity_function_name' => $campaign->creator && $campaign->creator->entityFunction
                    ? $campaign->creator->entityFunction->name
                    : '-',
            ];
        })->values();

        // Participant Statistics by Month
        $participantStats = $this->getParticipantStatistics($campaigns, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'kpis' => $kpis,
            'trendData' => $trendData,
            'topLocations' => $topLocations,
            'topSpeakers' => $topSpeakers,
            'companyDistribution' => $companyDistribution,
            'entityFunctionDistribution' => $entityFunctionDistribution,
            'recentCampaigns' => $recentCampaigns,
            'participantStats' => $participantStats,
        ]);
    }

    /**
     * Get Campaign Trend Data
     */
    private function getCampaignTrendData($campaigns, $startDate, $endDate)
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        $trendData = [];
        foreach ($months as $month) {
            $count = $campaigns->filter(function($c) use ($month) {
                return $c->tanggal->format('Y-m') === $month;
            })->count();

            $trendData[] = $count;
        }

        return [
            'labels' => array_map(function($m) {
                return Carbon::parse($m . '-01')->format('M Y');
            }, $months),
            'data' => $trendData
        ];
    }

    /**
     * Get Top Locations
     */
    private function getTopLocations($campaigns)
    {
        $locations = $campaigns->groupBy('lokasi')->map(function($group) {
            return $group->count();
        })->sortDesc()->take(10);

        return [
            'labels' => $locations->keys()->toArray(),
            'data' => $locations->values()->toArray()
        ];
    }

    /**
     * Get Top Speakers
     */
    private function getTopSpeakers($campaigns)
    {
        $speakers = $campaigns->groupBy('pemateri')->map(function($group) {
            return $group->count();
        })->sortDesc()->take(10);

        return [
            'labels' => $speakers->keys()->toArray(),
            'data' => $speakers->values()->toArray()
        ];
    }

    /**
     * Get Company Distribution
     */
    private function getCompanyDistribution($campaigns)
    {
        $companies = $campaigns->groupBy('company.name')->map(function($group) {
            return $group->count();
        })->sortDesc()->take(10);

        return [
            'labels' => $companies->keys()->toArray(),
            'data' => $companies->values()->toArray()
        ];
    }

    /**
     * Get Entity Function Distribution
     */
    private function getEntityFunctionDistribution($campaigns)
    {
        $functions = $campaigns->groupBy(function($campaign) {
            return $campaign->creator && $campaign->creator->entityFunction
                ? $campaign->creator->entityFunction->name
                : 'Tidak Ada Fungsi';
        })->map(function($group) {
            return $group->count();
        })->sortDesc();

        return [
            'labels' => $functions->keys()->toArray(),
            'data' => $functions->values()->toArray()
        ];
    }

    /**
     * Get Participant Statistics
     */
    private function getParticipantStatistics($campaigns, $startDate, $endDate)
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        $participantData = [];
        foreach ($months as $month) {
            $total = $campaigns->filter(function($c) use ($month) {
                return $c->tanggal->format('Y-m') === $month;
            })->sum('jumlah_peserta');

            $participantData[] = $total;
        }

        return [
            'labels' => array_map(function($m) {
                return Carbon::parse($m . '-01')->format('M Y');
            }, $months),
            'data' => $participantData
        ];
    }
}
