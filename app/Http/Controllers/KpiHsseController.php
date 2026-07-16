<?php

namespace App\Http\Controllers;

use App\Exports\KpiReportExport;
use App\Models\Company;
use App\Models\KpiItem;
use App\Models\KpiPeriod;
use App\Models\KpiReport;
use App\Models\KpiReportDetail;
use App\Models\KpiReportDetailEvidence;
use App\Models\KpiReportItemReview;
use App\Models\KpiReportStatusLog;
use App\Models\KpiReportVessel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * KpiHsseController — Production v11
 *
 * Perubahan v11:
 *  - Fix: Lagging item No.6 & 7 (As Reported) sekarang WAJIB direview
 *         oleh HSSE (Accept / Reject) meski tidak ada score.
 *  - Fix: updateDecision() — $reviewableDetails mencakup lagging 1–7
 *         agar auto-update status laporan memperhitungkan item 6-7.
 *  - Fix: review() bulk — scoredDetails juga diperluas ke lagging 1-7.
 *  - Tetap: item No.8 (Manhours, As Reported) tidak perlu review.
 *  - Tetap: Leading As Reported tidak perlu review.
 */
class KpiHsseController extends Controller
{
    private const LEADING_AVG_ITEMS = [2, 3, 4, 5, 6, 10, 11, 12, 14, 15];

    /**
     * Item lagging No.1–7 berbagi satu pool lampiran di anchor (item No.1).
     * Item No.8+ memiliki lampiran sendiri.
     */
    private const LAG_SHARED_ITEMS = [1, 2, 3, 4, 5, 6, 7];

    /**
     * Item lagging yang WAJIB direview HSSE (scored maupun As Reported No.6-7).
     * Item No.8 (Manhours) As Reported tidak perlu review.
     */
    private const LAG_REVIEWABLE_ITEMS = [1, 2, 3, 4, 5, 6, 7, 8];

    // ═══════════════════════════════════════════════════════════════
    // AUTH HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function auth()
    {
        return Auth::user();
    }

    private function isSuperAdmin(): bool
    {
        return $this->auth()->hasRole('super-admin');
    }

    private function isHsse(): bool
    {
        return $this->auth()->hasAnyRole(['hsse', 'super-admin']);
    }

    private function isKoord(): bool
    {
        return $this->auth()->hasRole('koordinator');
    }

    private function authorizeReport(KpiReport $r): void
    {
        if ($this->isKoord() && ! $this->isSuperAdmin()) {
            abort_unless((bool) $this->auth()->company_id, 403, 'Akun belum terhubung ke perusahaan.');
            abort_unless($r->company_id === $this->auth()->company_id, 403, 'Akses ditolak.');
        }
    }

    private function kpiItemsGrouped()
    {
        return KpiItem::where('is_active', true)
            ->orderBy('section')
            ->orderBy('item_no')
            ->get()
            ->groupBy('section');
    }

    private function getAvailableMonths(?string $companyId): \Illuminate\Support\Collection
    {
        if (! $companyId) {
            return collect();
        }

        $usedKeys = KpiReport::where('company_id', $companyId)
            ->join('kpi_periods', 'kpi_reports.kpi_period_id', '=', 'kpi_periods.id')
            ->selectRaw("CONCAT(kpi_periods.period_month, '-', kpi_periods.period_year) as mk")
            ->pluck('mk')
            ->toArray();

        $monthNames = [
            1 => 'Januari',   2 => 'Februari', 3 => 'Maret',
            4 => 'April',     5 => 'Mei',       6 => 'Juni',
            7 => 'Juli',      8 => 'Agustus',   9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $result = collect();
        $now    = now();

        for ($offset = 12; $offset >= -3; $offset--) {
            $d   = $now->copy()->subMonths($offset);
            $m   = (int) $d->format('n');
            $y   = (int) $d->format('Y');
            $key = "$m-$y";

            // Sekalian drop down periode yg muncul 2024 dan 2025 dihapus aja gpp mas, biar orang tidak salah pilih lagi
            if ($y < 2026) {
                continue;
            }

            if (in_array($key, $usedKeys)) {
                continue;
            }

            $result->push([
                'value' => $key,
                'label' => strtoupper(($monthNames[$m] ?? $m) . ' ' . $y),
            ]);
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════════

    public function dashboard(Request $request): View
    {
        $user   = $this->auth();
        $isHsse = $this->isHsse();

        $baseQuery = KpiReport::query();
        if (! $isHsse) {
            $baseQuery->where('company_id', $user->company_id);
        }

        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'draft'     => (clone $baseQuery)->where('status', 'draft')->count(),
            'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
            'validated' => (clone $baseQuery)->where('status', 'validated')->count(),
            'rejected'  => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        $chartQuery = KpiReport::where('status', 'validated')
            ->join('kpi_periods', 'kpi_reports.kpi_period_id', '=', 'kpi_periods.id')
            ->join('companies',   'kpi_reports.company_id',    '=', 'companies.id')
            ->selectRaw('
                companies.id   as company_id,
                companies.name as company_name,
                kpi_periods.period_year  as yr,
                kpi_periods.period_month as mo,
                kpi_reports.total_score,
                kpi_reports.total_score_lagging,
                kpi_reports.total_score_leading
            ');

        if (! $isHsse) {
            $chartQuery->where('kpi_reports.company_id', $user->company_id);
        }
        if ($request->filled('period_year')) {
            $chartQuery->where('kpi_periods.period_year', $request->period_year);
        }
        if ($request->filled('period_month')) {
            $chartQuery->where('kpi_periods.period_month', $request->period_month);
        }

        $chartData = $chartQuery
            ->orderBy('kpi_periods.period_year')
            ->orderBy('kpi_periods.period_month')
            ->get();

        $laggingItems       = KpiItem::where('section', 'lagging')->where('is_active', true)->orderBy('item_no')->get();
        $laggingPyramidData = [];
        foreach ($laggingItems as $item) {
            $q = KpiReportDetail::whereHas('kpiReport', function ($q) use ($isHsse, $user) {
                $q->where('status', 'validated');
                if (! $isHsse) {
                    $q->where('company_id', $user->company_id);
                }
            })->where('kpi_item_id', $item->id)->whereNotNull('actual_count');

            $laggingPyramidData[] = [
                'item_no'      => $item->item_no,
                'name'         => $item->name,
                'is_scored'    => $item->is_scored,
                'total_actual' => (int) $q->sum('actual_count'),
                'avg_score'    => round((float) $q->avg('score'), 2),
            ];
        }

        $leadingItems = KpiItem::where('section', 'leading')->where('is_active', true)->orderBy('item_no')->get();

        $trendQuery = KpiReport::where('status', 'validated')
            ->join('kpi_periods', 'kpi_reports.kpi_period_id', '=', 'kpi_periods.id')
            ->selectRaw('
                kpi_periods.period_year  as yr,
                kpi_periods.period_month as mo,
                COUNT(*) as cnt,
                AVG(kpi_reports.total_score)         as avg_score,
                AVG(kpi_reports.total_score_lagging) as avg_lagging,
                AVG(kpi_reports.total_score_leading) as avg_leading
            ');

        if (! $isHsse) {
            $trendQuery->where('kpi_reports.company_id', $user->company_id);
        }

        $trend = $trendQuery
            ->groupBy('kpi_periods.period_year', 'kpi_periods.period_month')
            ->orderByRaw('kpi_periods.period_year DESC, kpi_periods.period_month DESC')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        $topCompanies = collect();
        if ($isHsse) {
            $topCompanies = KpiReport::where('status', 'validated')
                ->join('companies', 'kpi_reports.company_id', '=', 'companies.id')
                ->selectRaw('
                    companies.id,
                    companies.name,
                    COUNT(*) as report_count,
                    AVG(kpi_reports.total_score)  as avg_score,
                    MAX(kpi_reports.total_score)  as max_score,
                    MIN(kpi_reports.total_score)  as min_score
                ')
                ->groupBy('companies.id', 'companies.name')
                ->orderByDesc('avg_score')
                ->limit(10)
                ->get();
        }

        $recentReports = (clone $baseQuery)
            ->with(['company', 'kpiPeriod', 'vessels'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $periods   = KpiPeriod::where('period_year', '>=', 2026)->orderByDesc('period_year')->orderByDesc('period_month')->get();
        $companies = $isHsse ? Company::orderBy('name')->get() : collect();

        return view('kpi-hsse.dashboard', compact(
            'user', 'isHsse', 'stats', 'chartData',
            'laggingPyramidData', 'leadingItems', 'trend',
            'topCompanies', 'recentReports', 'periods', 'companies'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // INDEX
    // ═══════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $user    = $this->auth();
        $isHsse  = $this->isHsse();
        $isSA    = $this->isSuperAdmin();
        $isKoord = $this->isKoord();

        $query = KpiReport::with(['company', 'kpiPeriod', 'vessels', 'createdBy'])
            ->withCount(['details as evidences_count' => fn ($q) => $q->whereHas('evidences')]);

        if ($isKoord && ! $isSA) {
            $query->where('company_id', $user->company_id);
        } elseif ($isHsse && ! $isSA) {
            $query->whereIn('status', ['submitted', 'validated', 'rejected']);
        }

        if ($request->filled('company_id') && $isHsse) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('period')) {
            [$m, $y] = explode('-', $request->period);
            $query->whereHas('kpiPeriod', fn ($q) => $q->where('period_month', $m)->where('period_year', $y));
        }

        $reports            = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();
        $periods            = KpiPeriod::where('period_year', '>=', 2026)->orderByDesc('period_year')->orderByDesc('period_month')->get();
        $companies          = $isHsse ? Company::orderBy('name')->get() : collect();
        $pendingReviewCount = $isHsse ? KpiReport::where('status', 'submitted')->count() : 0;

        return view('kpi-hsse.index', compact(
            'user', 'isHsse', 'isSA', 'isKoord',
            'reports', 'periods', 'companies', 'pendingReviewCount'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // CREATE
    // ═══════════════════════════════════════════════════════════════

    public function create(): View|RedirectResponse
    {
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        $user = $this->auth();

        if (! $user->company_id) {
            return redirect()->route('kpi-hsse.index')
                ->with('error', 'Akun Anda belum terhubung ke perusahaan. Hubungi administrator.');
        }

        return view('kpi-hsse.form', [
            'mode'            => 'create',
            'kpiReport'       => null,
            'user'            => $user,
            'isKoord'         => $this->isKoord(),
            'isHsse'          => $this->isHsse(),
            'isSA'            => $this->isSuperAdmin(),
            'availableMonths' => $this->getAvailableMonths($user->company_id),
            'kpiItems'        => collect(),
            'existingDetails' => collect(),
            'lagAnchorDetail' => null,
            'lagAnchorId'     => '',
            'lagEvCount'      => 0,
            'canEditKoord'    => false,
            'canEditHsse'     => false,
            'canReview'       => false,
            'allItems'        => collect(),
            'totalItems'      => 0,
            'rejCnt'          => 0,
            'mCnt'            => 0,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STORE
    // ═══════════════════════════════════════════════════════════════

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        $user = $this->auth();

        if (! $user->company_id) {
            return back()->with('error', 'Akun Anda belum terhubung ke perusahaan.');
        }

        $request->validate([
            'period'                      => 'required|string',
            'vessels'                     => 'required|array|min:1',
            'vessels.*.vessel_name'       => 'required|string|max:500',
            'vessels.*.vessel_count'      => 'nullable|string|max:200',
            'vessels.*.contract_number'   => 'nullable|string|max:150',
            'vessels.*.contract_end_date' => 'nullable|date',
        ]);

        [$periodMonth, $periodYear] = explode('-', $request->period);

        $existing = KpiReport::where('company_id', $user->company_id)
            ->whereHas('kpiPeriod', fn ($q) =>
                $q->where('period_month', $periodMonth)->where('period_year', $periodYear))
            ->first();

        if ($existing) {
            return back()->with('error', 'Laporan untuk periode ini sudah ada.')->withInput();
        }

        try {
            DB::beginTransaction();

            $period = KpiPeriod::firstOrCreate(
                ['period_month' => $periodMonth, 'period_year' => $periodYear],
                ['is_active' => true]
            );

            $kpiReport = KpiReport::create([
                'company_id'    => $user->company_id,
                'kpi_period_id' => $period->id,
                'status'        => 'draft',
                'created_by'    => $user->id,
            ]);

            foreach ($request->vessels as $idx => $v) {
                if (empty(trim($v['vessel_name'] ?? ''))) {
                    continue;
                }
                KpiReportVessel::create([
                    'kpi_report_id'     => $kpiReport->id,
                    'vessel_name'       => $v['vessel_name'],
                    'vessel_count'      => $v['vessel_count'] ?? null,
                    'contract_number'   => $v['contract_number'] ?? null,
                    'contract_end_date' => ! empty($v['contract_end_date']) ? $v['contract_end_date'] : null,
                    'sort_order'        => $idx,
                ]);
            }

            $this->backfillDetails($kpiReport);

            KpiReportStatusLog::create([
                'kpi_report_id' => $kpiReport->id,
                'from_status'   => null,
                'to_status'     => 'draft',
                'acted_by'      => $user->id,
                'acted_at'      => now(),
            ]);

            DB::commit();

            return redirect()->route('kpi-hsse.edit', $kpiReport)
                ->with('success', '✅ Draft KPI berhasil dibuat. Silakan isi data KPI dan upload lampiran.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KPI Store Error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SHOW — mode='show'
    // ═══════════════════════════════════════════════════════════════

    public function show(KpiReport $kpiReport): View
    {
        $this->authorizeReport($kpiReport);

        $user    = $this->auth();
        $isHsse  = $this->isHsse();
        $isSA    = $this->isSuperAdmin();
        $isKoord = $this->isKoord();

        $kpiReport->load([
            'company',
            'kpiPeriod',
            'vessels',
            'details.kpiItem',
            'details.evidences',
            'details.reviews',
            'statusLogs.actedBy',
            'submittedBy',
            'validatedBy',
            'createdBy',
        ]);

        $kpiItems        = $this->kpiItemsGrouped();
        $existingDetails = $kpiReport->details->keyBy('kpi_item_id');

        $lagAnchorItem   = $kpiItems->get('lagging', collect())->firstWhere('item_no', 1);
        $lagAnchorDetail = $lagAnchorItem ? ($existingDetails[$lagAnchorItem->id] ?? null) : null;
        $lagEvCount      = $lagAnchorDetail ? $lagAnchorDetail->evidences->count() : 0;
        $lagAnchorId     = $lagAnchorDetail?->id ?? '';

        $canReview = $isHsse && $kpiReport->status === 'submitted';

        $allItems = collect();
        foreach (['lagging', 'leading'] as $sec) {
            foreach ($kpiItems->get($sec, collect()) as $item) {
                $det       = $existingDetails[$item->id] ?? null;
                $nameParts = explode("\n", $item->name, 2);
                $allItems->push([
                    'sec'    => $sec,
                    'no'     => $item->item_no,
                    'name'   => trim($nameParts[0]),
                    'target' => isset($nameParts[1]) ? trim($nameParts[1]) : '',
                    'guide'  => $item->guidance_label ?? ($item->guidance ?? ''),
                    'unit'   => $item->unit_label ?? ($item->unit ?? '∑'),
                    'bobot'  => (float) $item->bobot,
                    'scored' => (bool) $item->is_scored,
                    'det'    => $det,
                    'kId'    => $det?->kpi_item_id ?? $item->id,
                    'dId'    => $det?->id ?? '',
                ]);
            }
        }

        $totalItems   = $allItems->count();
        $rejCnt       = $kpiReport->details->where('review_status', 'rejected')->count();
        $mCnt         = $kpiReport->details->where('is_marked', true)->count();
        $canEditKoord = ($isKoord || $isSA);
        $canEditHsse  = $isHsse && in_array($kpiReport->status, ['submitted', 'validated']);

        return view('kpi-hsse.form', compact(
            'kpiReport', 'kpiItems', 'existingDetails',
            'user', 'isHsse', 'isSA', 'isKoord',
            'lagAnchorDetail', 'lagEvCount', 'lagAnchorId',
            'canEditKoord', 'canEditHsse', 'canReview',
            'allItems', 'totalItems', 'rejCnt', 'mCnt'
        ) + ['mode' => 'show', 'availableMonths' => $this->getAvailableMonths($kpiReport->company_id)]);
    }

    // ═══════════════════════════════════════════════════════════════
    // EDIT — mode='edit'
    // ═══════════════════════════════════════════════════════════════

    public function edit(KpiReport $kpiReport): View
    {
        $this->authorizeReport($kpiReport);

        $user    = $this->auth();
        $isHsse  = $this->isHsse();
        $isSA    = $this->isSuperAdmin();
        $isKoord = $this->isKoord();

        // status apapun bisa dirubah

        $kpiReport->load(['vessels', 'details.kpiItem', 'details.evidences', 'details.reviews']);
        $this->backfillDetails($kpiReport);
        $kpiReport->load([
            'vessels', 'details.kpiItem', 'details.evidences',
            'details.reviews', 'statusLogs.actedBy',
            'submittedBy', 'createdBy', 'company', 'kpiPeriod',
        ]);

        $kpiItems        = $this->kpiItemsGrouped();
        $existingDetails = $kpiReport->details->keyBy('kpi_item_id');

        $lagAnchorItem   = $kpiItems->get('lagging', collect())->firstWhere('item_no', 1);
        $lagAnchorDetail = $lagAnchorItem ? ($existingDetails[$lagAnchorItem->id] ?? null) : null;
        $lagEvCount      = $lagAnchorDetail ? $lagAnchorDetail->evidences->count() : 0;
        $lagAnchorId     = $lagAnchorDetail?->id ?? '';

        $canEditKoord = ($isKoord || $isSA);
        $canEditHsse  = $isHsse && in_array($kpiReport->status, ['submitted', 'validated']);
        $canReview    = $isHsse && $kpiReport->status === 'submitted';

        $allItems = collect();
        foreach (['lagging', 'leading'] as $sec) {
            foreach ($kpiItems->get($sec, collect()) as $item) {
                $det = $existingDetails[$item->id] ?? null;
                $allItems->push(['det' => $det]);
            }
        }
        $totalItems = $allItems->count();
        $rejCnt     = $kpiReport->details->where('review_status', 'rejected')->count();
        $mCnt       = $kpiReport->details->where('is_marked', true)->count();

        return view('kpi-hsse.form', compact(
            'kpiReport', 'kpiItems', 'existingDetails',
            'user', 'isHsse', 'isSA', 'isKoord',
            'lagAnchorDetail', 'lagEvCount', 'lagAnchorId',
            'canEditKoord', 'canEditHsse', 'canReview',
            'allItems', 'totalItems', 'rejCnt', 'mCnt'
        ) + [
            'mode'            => 'edit',
            'availableMonths' => $this->getAvailableMonths($kpiReport->company_id),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // UPDATE — AJAX auto-save per baris
    // ═══════════════════════════════════════════════════════════════

    public function update(Request $request, KpiReport $kpiReport): JsonResponse
    {
        $this->authorizeReport($kpiReport);

        // status apapun bisa dirubah

        $request->validate([
            'kpi_item_id'  => 'required|uuid|exists:kpi_items,id',
            'actual_count' => 'nullable|numeric|min:0',
            'keterangan'   => 'nullable|string|max:2000',
        ]);

        try {
            $raw         = $request->input('actual_count');
            $actualCount = ($raw !== null && $raw !== '' && is_numeric($raw)) ? (float) $raw : null;

            $detail = KpiReportDetail::updateOrCreate(
                [
                    'kpi_report_id' => $kpiReport->id,
                    'kpi_item_id'   => $request->kpi_item_id,
                ],
                [
                    'actual_count' => $actualCount,
                    'keterangan'   => ($request->keterangan !== '' && $request->keterangan !== null)
                        ? $request->keterangan
                        : null,
                ]
            );

            return response()->json([
                'success'   => true,
                'detail_id' => $detail->id,
            ]);
        } catch (\Exception $e) {
            Log::error('KPI Update Row', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SAVE DRAFT — PUT /{kpiReport}/draft
    // ═══════════════════════════════════════════════════════════════

    public function saveDraft(Request $request, KpiReport $kpiReport): RedirectResponse
    {
        $this->authorizeReport($kpiReport);

        if ($this->isSuperAdmin()) {
            return back()->with('success', '💾 Draft tersimpan oleh Super Admin.');
        }

        // status apapun bisa dirubah

        if ($this->isHsse() && ! in_array($kpiReport->status, ['submitted', 'validated'])) {
            return back()->with('error', 'Tidak ada data yang perlu disimpan pada status ini.');
        }

        return back()->with('success', '💾 Progres tersimpan. Semua perubahan sudah ter-autosave.');
    }

    // ═══════════════════════════════════════════════════════════════
    // UPDATE DECISION — POST /{kpiReport}/decision (AJAX inline review)
    //
    // v11: $reviewableDetails diperluas ke lagging No.1–7 agar
    //      auto-update status laporan memperhitungkan item 6 & 7.
    // ═══════════════════════════════════════════════════════════════

    public function updateDecision(Request $request, KpiReport $kpiReport): JsonResponse
    {
        abort_unless($this->isHsse(), 403, 'Akses ditolak.');

        $allowedStatuses = $this->isSuperAdmin()
            ? ['draft', 'submitted', 'validated', 'rejected']
            : ['submitted', 'validated'];

        if (! in_array($kpiReport->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan belum disubmit. Review hanya bisa dilakukan setelah koordinator submit laporan.',
            ], 422);
        }

        $request->validate([
            'detail_id'    => 'required|uuid|exists:kpi_report_details,id',
            'decision'     => 'required|in:approved,rejected',
            'hsse_catatan' => 'nullable|string|max:1000',
        ]);

        if ($request->decision === 'rejected' && empty(trim($request->hsse_catatan ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan wajib diisi jika item ditolak.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $detail = KpiReportDetail::where('id', $request->detail_id)
                ->where('kpi_report_id', $kpiReport->id)
                ->firstOrFail();

            $round = $kpiReport->statusLogs()->where('to_status', 'submitted')->count();

            KpiReportItemReview::create([
                'kpi_report_detail_id' => $detail->id,
                'reviewed_by'          => Auth::id(),
                'decision'             => $request->decision,
                'comment'              => $request->hsse_catatan ?? null,
                'submission_round'     => $round,
                'reviewed_at'          => now(),
            ]);

            $detail->update([
                'review_status' => $request->decision,
                'hsse_catatan'  => $request->hsse_catatan ?? null,
            ]);

            $totals = $this->recalcScore($kpiReport);

            // ── v11: reviewable = is_scored + lagging No.1–7 (As Reported yg wajib review) ──
            $reviewableDetails = $kpiReport->details()
                ->whereHas('kpiItem', function ($q) {
                    $q->where('is_scored', true)
                      ->orWhere(function ($q2) {
                          $q2->where('section', 'lagging')
                             ->whereIn('item_no', self::LAG_REVIEWABLE_ITEMS);
                      });
                })
                ->get();

            $reviewedAll = $reviewableDetails->every(
                fn ($d) => in_array($d->review_status, ['approved', 'rejected'])
            );
            $hasRejected = $reviewableDetails->contains(
                fn ($d) => $d->review_status === 'rejected'
            );

            if ($reviewedAll) {
                $newStatus = $hasRejected ? 'rejected' : 'validated';

                if ($kpiReport->status !== $newStatus) {
                    $kpiReport->update([
                        'status'       => $newStatus,
                        'validated_by' => Auth::id(),
                        'validated_at' => $newStatus === 'validated' ? now() : null,
                    ]);

                    KpiReportStatusLog::create([
                        'kpi_report_id' => $kpiReport->id,
                        'from_status'   => $kpiReport->getOriginal('status'),
                        'to_status'     => $newStatus,
                        'comment'       => 'Auto-update setelah semua item direview inline.',
                        'acted_by'      => Auth::id(),
                        'acted_at'      => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'detail_id'    => $detail->id,
                'decision'     => $request->decision,
                'totals'       => $totals,
                'all_reviewed' => $reviewedAll,
                'new_status'   => $reviewedAll ? ($hasRejected ? 'rejected' : 'validated') : $kpiReport->status,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Data item tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KPI Decision Update Error', [
                'detail_id' => $request->detail_id,
                'decision'  => $request->decision,
                'err'       => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan keputusan: ' . $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // UPDATE SCORE — HSSE AJAX
    // ═══════════════════════════════════════════════════════════════

    public function updateScore(Request $request, KpiReport $kpiReport): JsonResponse
    {
        abort_unless($this->isHsse(), 403);

        $allowedStatuses = ['submitted', 'validated'];
        if ($this->isSuperAdmin()) {
            $allowedStatuses = ['draft', 'submitted', 'validated', 'rejected'];
        }

        if (! in_array($kpiReport->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Nilai hanya bisa diisi saat laporan berstatus Submitted atau Validated.',
            ], 422);
        }

        $request->validate([
            'kpi_item_id' => 'required|uuid|exists:kpi_items,id',
            'nilai'       => 'required|numeric|min:0|max:100',
        ]);

        $kpiItem = KpiItem::findOrFail($request->kpi_item_id);

        if (! $kpiItem->is_scored) {
            return response()->json(['success' => false, 'message' => 'Item As Reported.'], 422);
        }

        $nilai  = round((float) $request->nilai, 2);
        $score  = round($nilai * (float) $kpiItem->bobot, 4);
        $detail = KpiReportDetail::updateOrCreate(
            ['kpi_report_id' => $kpiReport->id, 'kpi_item_id' => $kpiItem->id],
            ['nilai' => $nilai, 'score' => $score]
        );

        $totals = $this->recalcScore($kpiReport);

        return response()->json([
            'success' => true,
            'detail'  => ['id' => $detail->id, 'nilai' => $nilai, 'score' => $score],
            'totals'  => $totals,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // UPDATE PERIOD — Koordinator AJAX
    // ═══════════════════════════════════════════════════════════════

    public function updatePeriod(Request $request, KpiReport $kpiReport): JsonResponse
    {
        $this->authorizeReport($kpiReport);
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        if (!$kpiReport->canBeEdited()) {
            return response()->json(['success' => false, 'message' => 'Laporan tidak dapat diedit pada status ini.'], 403);
        }

        $request->validate([
            'period' => 'required|string',
        ]);

        [$m, $y] = explode('-', $request->period);

        // Cek apakah laporan lain sudah ada untuk periode ini
        $existing = KpiReport::where('company_id', $kpiReport->company_id)
            ->where('id', '!=', $kpiReport->id)
            ->whereHas('kpiPeriod', fn($q) => $q->where('period_month', $m)->where('period_year', $y))
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Laporan untuk periode tersebut sudah ada.'], 422);
        }

        try {
            DB::beginTransaction();

            $period = KpiPeriod::firstOrCreate(
                ['period_month' => $m, 'period_year' => $y],
                ['is_active' => true]
            );

            $kpiReport->update(['kpi_period_id' => $period->id]);

            DB::commit();

            return response()->json([
                'success' => true,
                'label'   => $period->label,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // UPDATE CATATAN HSSE
    // ═══════════════════════════════════════════════════════════════

    public function updateCatatan(Request $request, KpiReport $kpiReport): JsonResponse
    {
        abort_unless($this->isHsse(), 403);

        $request->validate([
            'detail_id'    => 'required|uuid|exists:kpi_report_details,id',
            'hsse_catatan' => 'nullable|string|max:1000',
        ]);

        $detail = KpiReportDetail::where('id', $request->detail_id)
            ->where('kpi_report_id', $kpiReport->id)
            ->firstOrFail();

        $detail->update(['hsse_catatan' => $request->hsse_catatan]);

        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════
    // TOGGLE MARK — HSSE AJAX
    // ═══════════════════════════════════════════════════════════════

    public function toggleMark(Request $request, KpiReport $kpiReport): JsonResponse
    {
        abort_unless($this->isHsse(), 403);

        $request->validate([
            'detail_id' => 'required|uuid|exists:kpi_report_details,id',
            'is_marked' => 'required|boolean',
            'mark_note' => 'nullable|string|max:500',
        ]);

        $detail = KpiReportDetail::where('id', $request->detail_id)
            ->where('kpi_report_id', $kpiReport->id)
            ->firstOrFail();

        $detail->update([
            'is_marked' => $request->boolean('is_marked'),
            'mark_note' => $request->boolean('is_marked') ? $request->mark_note : null,
        ]);

        return response()->json([
            'success'   => true,
            'is_marked' => $detail->is_marked,
            'mark_note' => $detail->mark_note,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // VESSELS
    // ═══════════════════════════════════════════════════════════════

    public function storeVessel(Request $request, KpiReport $kpiReport): JsonResponse
    {
        $this->authorizeReport($kpiReport);
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        $request->validate([
            'vessel_name'       => 'required|string|max:500',
            'vessel_count'      => 'nullable|string|max:200',
            'contract_number'   => 'nullable|string|max:150',
            'contract_end_date' => 'nullable|date',
        ]);

        $v = KpiReportVessel::create([
            'kpi_report_id'     => $kpiReport->id,
            'vessel_name'       => $request->vessel_name,
            'vessel_count'      => $request->vessel_count,
            'contract_number'   => $request->contract_number,
            'contract_end_date' => $request->contract_end_date ?? null,
            'sort_order'        => $kpiReport->vessels()->count(),
        ]);

        return response()->json(['success' => true, 'vessel' => $v]);
    }

    public function updateVessel(Request $request, KpiReport $kpiReport, KpiReportVessel $vessel): JsonResponse
    {
        $this->authorizeReport($kpiReport);

        $request->validate([
            'vessel_name'       => 'required|string|max:500',
            'vessel_count'      => 'nullable|string|max:200',
            'contract_number'   => 'nullable|string|max:150',
            'contract_end_date' => 'nullable|date',
        ]);

        $vessel->update($request->only(['vessel_name', 'vessel_count', 'contract_number', 'contract_end_date']));

        return response()->json(['success' => true, 'vessel' => $vessel->fresh()]);
    }

    public function destroyVessel(KpiReport $kpiReport, KpiReportVessel $vessel): JsonResponse
    {
        $this->authorizeReport($kpiReport);
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        if ($kpiReport->vessels()->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Minimal 1 kapal/unit.'], 422);
        }

        $vessel->delete();

        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════
    // EVIDENCE UPLOAD — v10 (unchanged)
    // ═══════════════════════════════════════════════════════════════

    public function uploadEvidence(Request $request, KpiReport $kpiReport): JsonResponse
    {
        $this->authorizeReport($kpiReport);
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        if (! in_array($kpiReport->status, ['draft', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat upload pada status ini.'], 403);
        }

        $request->validate([
            'kpi_report_detail_id' => 'required|uuid|exists:kpi_report_details,id',
            'file'                 => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
            'caption'              => 'nullable|string|max:500',
        ]);

        try {
            $detail = KpiReportDetail::where('id', $request->kpi_report_detail_id)
                ->where('kpi_report_id', $kpiReport->id)
                ->with('kpiItem')
                ->firstOrFail();

            $targetDetail = $detail;

            if (
                $detail->kpiItem &&
                $detail->kpiItem->section === 'lagging' &&
                in_array($detail->kpiItem->item_no, self::LAG_SHARED_ITEMS) &&
                $detail->kpiItem->item_no !== 1
            ) {
                $anchorItem = KpiItem::where('section', 'lagging')
                    ->where('item_no', 1)
                    ->where('is_active', true)
                    ->first();

                if ($anchorItem) {
                    $targetDetail = KpiReportDetail::firstOrCreate(
                        ['kpi_report_id' => $kpiReport->id, 'kpi_item_id' => $anchorItem->id]
                    );
                }
            }

            $file = $request->file('file');
            $path = $file->store('kpi-evidences/' . date('Y/m'), 'public');

            $ev = KpiReportDetailEvidence::create([
                'kpi_report_detail_id' => $targetDetail->id,
                'file_path'            => $path,
                'file_name'            => $file->getClientOriginalName(),
                'file_type'            => $file->getMimeType(),
                'file_size'            => $file->getSize(),
                'caption'              => $request->caption,
                'sort_order'           => $targetDetail->evidences()->count(),
                'uploaded_by'          => Auth::id(),
            ]);

            return response()->json([
                'success'          => true,
                'anchor_detail_id' => $targetDetail->id,
                'evidence'         => [
                    'id'      => $ev->id,
                    'url'     => Storage::url($path),
                    'name'    => $ev->file_name,
                    'size'    => $this->formatFileSize($ev->file_size),
                    'type'    => $file->getMimeType(),
                    'caption' => $ev->caption,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('KPI Evidence Upload', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function deleteEvidence(KpiReport $kpiReport, KpiReportDetailEvidence $evidence): JsonResponse
    {
        $this->authorizeReport($kpiReport);
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        if (! in_array($kpiReport->status, ['draft', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat hapus pada status ini.'], 403);
        }

        $belongsToReport = KpiReportDetail::where('id', $evidence->kpi_report_detail_id)
            ->where('kpi_report_id', $kpiReport->id)
            ->exists();

        if (! $belongsToReport) {
            return response()->json(['success' => false, 'message' => 'Lampiran tidak ditemukan.'], 404);
        }

        try {
            Storage::disk('public')->delete($evidence->file_path);
            $evidence->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('KPI Evidence Delete', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus lampiran.'], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SUBMIT — v11: validasi lampiran & data
    // ═══════════════════════════════════════════════════════════════

    public function submit(Request $request, KpiReport $kpiReport): RedirectResponse
    {
        $this->authorizeReport($kpiReport);
        abort_unless($this->isKoord() || $this->isSuperAdmin(), 403);

        if (! in_array($kpiReport->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Laporan tidak dapat disubmit pada status ini.');
        }

        $kpiReport->load(['details.kpiItem', 'details.evidences']);

        $missingCount    = [];
        $missingEvidence = [];

        $anchorItem = KpiItem::where('section', 'lagging')
            ->where('item_no', 1)
            ->where('is_active', true)
            ->first();

        $anchorDetail = null;
        if ($anchorItem) {
            $anchorDetail = $kpiReport->details
                ->first(fn ($d) => $d->kpi_item_id === $anchorItem->id);

            if (! $anchorDetail) {
                $anchorDetail = KpiReportDetail::where('kpi_report_id', $kpiReport->id)
                    ->where('kpi_item_id', $anchorItem->id)
                    ->with('evidences')
                    ->first();
            }
        }

        $anchorEvCount = 0;
        if ($anchorDetail) {
            if ($anchorDetail->relationLoaded('evidences')) {
                $anchorEvCount = $anchorDetail->evidences->count();
            } else {
                $anchorEvCount = $anchorDetail->evidences()->count();
            }
        }

        $sharedEvChecked = false;

        foreach ($kpiReport->details as $detail) {
            $item = $detail->kpiItem;
            if (! $item || ! $item->is_active) {
                continue;
            }

            // Validasi ∑/% — hanya untuk scored items
            if ($item->is_scored && $detail->actual_count === null) {
                $missingCount[] = "No.{$item->item_no} ({$this->shortName($item->name)})";
            }
            // Lagging As-Reported (6-7) juga wajib isi actual_count sebagai laporan
            if (! $item->is_scored && $item->section === 'lagging'
                && in_array((int) $item->item_no, [6, 7])
                && $detail->actual_count === null) {
                $missingCount[] = "No.{$item->item_no} ({$this->shortName($item->name)})";
            }

            // Validasi lampiran
            if (! $item->is_scored && $item->section === 'leading') {
                continue; // Leading As-Reported bebas lampiran
            }

            if ($item->section === 'lagging') {
                if (in_array($item->item_no, self::LAG_SHARED_ITEMS)) {
                    if (! $sharedEvChecked) {
                        $sharedEvChecked = true;
                        if ($anchorEvCount === 0) {
                            $missingEvidence[] = 'Lagging No.1–7: wajib minimal 1 lampiran bersama (upload di baris No.1).';
                        }
                    }
                } else {
                    // Item No.8+ (Manhours dll): lampiran sendiri
                    if ($detail->evidences->count() === 0) {
                        $missingEvidence[] = "No.{$item->item_no} ({$this->shortName($item->name)}): wajib lampiran.";
                    }
                }
            } else {
                // Leading scored: lampiran masing-masing
                if ($detail->evidences->count() === 0) {
                    $missingEvidence[] = "No.{$item->item_no} ({$this->shortName($item->name)}): wajib lampiran.";
                }
            }
        }

        if (! empty($missingCount)) {
            $show = array_slice($missingCount, 0, 4);
            $more = count($missingCount) - count($show);
            return back()->with(
                'error',
                '∑/% belum diisi: ' . implode(', ', $show) . ($more > 0 ? " (+{$more} lainnya)" : '')
            );
        }

        if (! empty($missingEvidence)) {
            $show = array_slice($missingEvidence, 0, 3);
            $more = count($missingEvidence) - count($show);
            return back()->with(
                'error',
                'Lampiran belum lengkap: ' . implode('; ', $show) . ($more > 0 ? " (+{$more} lainnya)" : '')
            );
        }

        return $this->doSubmit($kpiReport, $request->comment);
    }

    // ═══════════════════════════════════════════════════════════════
    // REVIEW — HSSE bulk (via form POST)
    //
    // v11: scoredDetails diperluas ke lagging No.1–7
    // ═══════════════════════════════════════════════════════════════

    public function review(Request $request, KpiReport $kpiReport): RedirectResponse
    {
        abort_unless($this->isHsse(), 403);

        if ($kpiReport->status !== 'submitted') {
            return back()->with('error', 'Laporan belum disubmit atau sudah diproses.');
        }

        $request->validate([
            'general_comment'   => 'nullable|string|max:1000',
            'items'             => 'required|array',
            'items.*.detail_id' => 'required|uuid|exists:kpi_report_details,id',
            'items.*.decision'  => 'required|in:approved,rejected',
            'items.*.comment'   => 'nullable|string|max:1000',
        ]);

        $errors = [];
        foreach ($request->items as $idx => $item) {
            if ($item['decision'] === 'rejected' && empty(trim($item['comment'] ?? ''))) {
                $errors["items.{$idx}.comment"] = 'Catatan wajib diisi jika item ditolak.';
            }
        }
        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        try {
            DB::beginTransaction();

            $round         = $kpiReport->statusLogs()->where('to_status', 'submitted')->count();
            $rejectedCount = 0;

            foreach ($request->items as $ir) {
                $detail = KpiReportDetail::where('id', $ir['detail_id'])
                    ->where('kpi_report_id', $kpiReport->id)
                    ->firstOrFail();

                KpiReportItemReview::create([
                    'kpi_report_detail_id' => $detail->id,
                    'reviewed_by'          => Auth::id(),
                    'decision'             => $ir['decision'],
                    'comment'              => $ir['comment'] ?? null,
                    'submission_round'     => $round,
                    'reviewed_at'          => now(),
                ]);

                $detail->update([
                    'review_status' => $ir['decision'],
                    'hsse_catatan'  => $ir['comment'] ?? null,
                ]);

                if ($ir['decision'] === 'rejected') {
                    $rejectedCount++;
                }
            }

            $this->recalcScore($kpiReport);

            $finalStatus = $rejectedCount > 0 ? 'rejected' : 'validated';

            $kpiReport->update([
                'status'       => $finalStatus,
                'validated_by' => Auth::id(),
                'validated_at' => $finalStatus === 'validated' ? now() : null,
            ]);

            KpiReportStatusLog::create([
                'kpi_report_id' => $kpiReport->id,
                'from_status'   => 'submitted',
                'to_status'     => $finalStatus,
                'comment'       => $request->general_comment,
                'acted_by'      => Auth::id(),
                'acted_at'      => now(),
            ]);

            DB::commit();

            $msg = $finalStatus === 'validated'
                ? '✅ Laporan berhasil divalidasi.'
                : "{$rejectedCount} item ditolak. Laporan dikembalikan ke koordinator.";

            return redirect()->route('kpi-hsse.show', $kpiReport)->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('KPI Review Error', ['err' => $e->getMessage()]);
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT EXCEL
    // ═══════════════════════════════════════════════════════════════

    public function export(KpiReport $kpiReport)
    {
        $this->authorizeReport($kpiReport);

        $kpiReport->load([
            'company', 'kpiPeriod', 'vessels',
            'details.kpiItem', 'details.evidences',
            'details.reviews' => fn ($q) => $q->latest('reviewed_at'),
            'validatedBy',
        ]);

        $filename = 'KPI-HSSE-'
            . str_replace(' ', '-', $kpiReport->company->name ?? 'Company')
            . '-' . ($kpiReport->kpiPeriod->label ?? 'Period')
            . '.xlsx';

        return Excel::download(new KpiReportExport($kpiReport), $filename);
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT PDF
    // ═══════════════════════════════════════════════════════════════

    public function exportPdf(KpiReport $kpiReport)
    {
        $this->authorizeReport($kpiReport);

        $kpiReport->load([
            'company',
            'kpiPeriod',
            'vessels',
            'details.kpiItem',
            'details.evidences',
            'details.reviews' => fn ($q) => $q->latest('reviewed_at'),
            'statusLogs.actedBy',
            'submittedBy',
            'validatedBy',
            'createdBy',
        ]);

        $kpiItems        = $this->kpiItemsGrouped();
        $existingDetails = $kpiReport->details->keyBy('kpi_item_id');

        $anchorItem   = $kpiItems->get('lagging', collect())->firstWhere('item_no', 1);
        $anchorDetail = $anchorItem ? ($existingDetails[$anchorItem->id] ?? null) : null;

        $filename = 'KPI-HSSE-'
            . str_replace(' ', '-', $kpiReport->company->name ?? 'Company')
            . '-' . ($kpiReport->kpiPeriod->label ?? 'Period')
            . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kpi-hsse.export-pdf', [
            'kpiReport'       => $kpiReport,
            'kpiItems'        => $kpiItems,
            'existingDetails' => $existingDetails,
            'anchorDetail'    => $anchorDetail,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    // ═══════════════════════════════════════════════════════════════
    // DESTROY
    // ═══════════════════════════════════════════════════════════════

    public function destroy(KpiReport $kpiReport): RedirectResponse
    {
        $isSA = $this->isSuperAdmin();
        $this->authorizeReport($kpiReport);

        if (! $isSA) {
            abort_unless($this->isKoord(), 403);
            if ($kpiReport->status !== 'draft') {
                return back()->with('error', 'Koordinator hanya dapat menghapus laporan Draft.');
            }
        }

        try {
            foreach ($kpiReport->details as $d) {
                foreach ($d->evidences as $ev) {
                    Storage::disk('public')->delete($ev->file_path);
                }
            }
            $kpiReport->delete();
            return redirect()->route('kpi-hsse.index')->with('success', 'Laporan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // STORE PERIOD (utility)
    // ═══════════════════════════════════════════════════════════════

    public function storePeriod(Request $request): JsonResponse
    {
        abort_unless($this->isHsse(), 403);

        $request->validate([
            'period_month'     => 'required|integer|min:1|max:12',
            'period_year'      => 'required|integer|min:2020|max:2099',
            'submission_start' => 'nullable|date',
            'submission_end'   => 'nullable|date|after_or_equal:submission_start',
            'is_active'        => 'boolean',
        ]);

        $period = KpiPeriod::updateOrCreate(
            ['period_month' => $request->period_month, 'period_year' => $request->period_year],
            [
                'submission_start' => $request->submission_start,
                'submission_end'   => $request->submission_end,
                'is_active'        => $request->boolean('is_active', true),
            ]
        );

        return response()->json(['success' => true, 'period' => ['id' => $period->id, 'label' => $period->label]]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function doSubmit(KpiReport $report, ?string $comment): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $prev = $report->status;

            if ($prev === 'rejected') {
                $report->details()
                    ->where('review_status', 'rejected')
                    ->update(['review_status' => null, 'hsse_catatan' => null]);
            }

            $report->update([
                'status'       => 'submitted',
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
            ]);

            KpiReportStatusLog::create([
                'kpi_report_id' => $report->id,
                'from_status'   => $prev,
                'to_status'     => 'submitted',
                'comment'       => $comment,
                'acted_by'      => Auth::id(),
                'acted_at'      => now(),
            ]);

            DB::commit();

            return redirect()->route('kpi-hsse.show', $report)
                ->with('success', '🚀 Laporan berhasil disubmit. Menunggu penilaian HSSE.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal submit: ' . $e->getMessage());
        }
    }

    private function backfillDetails(KpiReport $kpiReport): void
    {
        $allItems        = KpiItem::where('is_active', true)->get();
        $existingItemIds = $kpiReport->details->pluck('kpi_item_id')->toArray();

        foreach ($allItems->whereNotIn('id', $existingItemIds) as $kpiItem) {
            KpiReportDetail::create([
                'kpi_report_id' => $kpiReport->id,
                'kpi_item_id'   => $kpiItem->id,
            ]);
        }
    }

    private function recalcScore(KpiReport $report): array
    {
        $details      = KpiReportDetail::where('kpi_report_id', $report->id)->with('kpiItem')->get();
        $lag          = 0;
        $leadSum      = 0;
        $leadAvgVals  = [];
        $leadAvgBobot = 0;

        foreach ($details as $d) {
            $item = $d->kpiItem;
            if (! $item || ! $item->is_scored || $d->score === null) {
                continue;
            }

            if ($item->section === 'lagging') {
                $lag += (float) $d->score;
            } else {
                if (in_array($item->item_no, self::LEADING_AVG_ITEMS)) {
                    $leadAvgVals[]  = (float) ($d->nilai ?? 0);
                    $leadAvgBobot  += (float) $item->bobot;
                } else {
                    $leadSum += (float) $d->score;
                }
            }
        }

        $leadAvg = 0;
        if (! empty($leadAvgVals) && $leadAvgBobot > 0) {
            $leadAvg = round(array_sum($leadAvgVals) / count($leadAvgVals) * $leadAvgBobot, 4);
        }

        $lag   = round($lag, 4);
        $lead  = round($leadSum + $leadAvg, 4);
        $total = round($lag + $lead, 4);

        $report->update([
            'total_score_lagging' => $lag,
            'total_score_leading' => $lead,
            'total_score'         => $total,
        ]);

        return ['lagging' => $lag, 'leading' => $lead, 'total' => $total];
    }

    private function shortName(string $name): string
    {
        return trim(explode("\n", $name)[0]);
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1_048_576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1_048_576, 1) . ' MB';
    }
}