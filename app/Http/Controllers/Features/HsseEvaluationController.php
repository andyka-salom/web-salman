<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Models\HsseCriteria;
use App\Models\HsseEvaluation;
use App\Models\HsseEvaluationScore;
use App\Models\Company;
use App\Models\Vessel;
use App\Models\CrewMember;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class HsseEvaluationController extends Controller
{
    use AuthorizesRequests;

    private function canManage(): bool
    {
        return Auth::user()->hasAnyRole(['super-admin', 'hsse', 'user']);
    }

    // =========================================================
    // DASHBOARD (view)
    // =========================================================
   public function dashboard(Request $request)
    {
        $this->authorize('manage hsse evaluation');

        $user = Auth::user();

        $companies = $this->canManage()
            ? Company::orderBy('name')->get()
            : collect([]);

        $evaluatedVesselIds = HsseEvaluation::where('status', 'submitted')
            ->when(!$this->canManage(), fn($q) => $q->where('company_id', $user->company_id))
            ->distinct()->pluck('vessel_id');

        $vessels = Vessel::whereIn('id', $evaluatedVesselIds)->orderBy('name')->get();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HSSE On Board Evaluation'],
        ];

        return view('features.hsse-evaluation.dashboard', compact('companies', 'vessels', 'breadcrumbs'));
    }

    // =========================================================
    // DASHBOARD DATA (AJAX)
    // =========================================================
    public function dashboardData(Request $request)
    {
        try {
            $this->authorize('manage hsse evaluation');
            $user  = Auth::user();
            $query = HsseEvaluation::with(['company', 'vessel'])->where('status', 'submitted');

            if (!$this->canManage()) {
                $query->where('company_id', $user->company_id);
            } else {
                if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
            }

            if ($request->filled('vessel_id'))  $query->where('vessel_id',  $request->vessel_id);
            if ($request->filled('start_date')) $query->where('evaluated_date', '>=', $request->start_date);
            if ($request->filled('end_date'))   $query->where('evaluated_date', '<=', $request->end_date);

            $evaluations = $query->get();

            $totalEvaluations = $evaluations
                ->groupBy(fn($e) => $e->vessel_id . '_' . Carbon::parse($e->evaluated_date)->toDateString())
                ->count();

            $uniqueVessels = $evaluations->unique('vessel_id')->count();
            $totalVessels  = $this->canManage()
                ? Vessel::count()
                : Vessel::where('company_id', $user->company_id)->count();

            $uniqueCrewFiltered = $evaluations
                ->groupBy(fn($e) => $e->crew_member_id ?? ('manual_' . $e->id))
                ->count();

            // ── Total kru aktif (sesuai filter company) ──────────────────
            $totalActiveCrew = CrewMember::where('is_active', true)
                ->when(!$this->canManage(), fn($q) => $q->where('company_id', $user->company_id))
                ->when(
                    $this->canManage() && $request->filled('company_id'),
                    fn($q) => $q->where('company_id', $request->company_id)
                )
                ->count();

            $baik   = $evaluations->where('score_category', 'baik')->count();
            $cukup  = $evaluations->where('score_category', 'cukup')->count();
            $kurang = $evaluations->where('score_category', 'kurang')->count();

            $vesselGroups = $evaluations->groupBy('vessel_id')->map(function ($group) {
                $vessel         = $group->first()->vessel;
                $uniqueSessions = $group->pluck('evaluated_date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->unique()->count();
                $uniqueCrew = $group->groupBy(fn($e) => $e->crew_member_id ?? ('m_' . $e->id))->count();
                return [
                    'vessel_name' => $vessel ? $vessel->name : '-',
                    'frequency'   => $uniqueSessions,
                    'unique_crew' => $uniqueCrew,
                    'baik'        => $group->where('score_category', 'baik')->count(),
                    'cukup'       => $group->where('score_category', 'cukup')->count(),
                    'kurang'      => $group->where('score_category', 'kurang')->count(),
                ];
            })->values();

            $evaluationIds = $evaluations->pluck('id');
            $criteria      = HsseCriteria::where('is_active', true)->orderBy('order_no')->get();
            $allScores     = $evaluationIds->isNotEmpty()
                ? HsseEvaluationScore::whereIn('evaluation_id', $evaluationIds)->get()->groupBy('criteria_id')
                : collect();

            $criteriaRows = $criteria->map(fn($c) => [
                'criteria_id'  => $c->id,
                'order_no'     => $c->order_no,
                'aspect'       => $c->aspect,
                'total_scores' => ($scores = $allScores->get($c->id, collect()))->count(),
                'baik'         => $scores->where('score', 3)->count(),
                'cukup'        => $scores->where('score', 2)->count(),
                'kurang'       => $scores->where('score', 1)->count(),
                'avg_score'    => $scores->count() > 0 ? round($scores->avg('score'), 2) : null,
            ]);

            // ── Vessel status list: semua kapal aktif + flag sudah/belum dievaluasi ──
            $evaluatedVesselIds = $evaluations->pluck('vessel_id')->unique()->toArray();

            $allActiveVessels = Vessel::where('is_active', true)
                ->when(!$this->canManage(), fn($q) => $q->where('company_id', $user->company_id))
                ->when(
                    $this->canManage() && $request->filled('company_id'),
                    fn($q) => $q->where('company_id', $request->company_id)
                )
                // Jika filter vessel_id aktif, hanya tampilkan kapal itu
                ->when($request->filled('vessel_id'), fn($q) => $q->where('id', $request->vessel_id))
                ->orderBy('name')
                ->get(['id', 'name']);

            $vesselStatusList = $allActiveVessels->map(fn($v) => [
                'vessel_name'  => $v->name,
                'is_evaluated' => in_array($v->id, $evaluatedVesselIds),
            ])->values();

            return response()->json([
                'summary' => [
                    'total_evaluations' => $totalEvaluations,
                    'unique_vessels'    => $uniqueVessels,
                    'total_vessels'     => $totalVessels,
                    'unique_crew'       => $uniqueCrewFiltered,
                    'total_active_crew' => $totalActiveCrew,
                    'baik'              => $baik,
                    'cukup'             => $cukup,
                    'kurang'            => $kurang,
                ],
                'vessel_rows'        => $vesselGroups,
                'criteria_rows'      => $criteriaRows,
                'vessel_status_list' => $vesselStatusList,
            ]);

        } catch (AuthorizationException $e) {
            return response()->json(['error' => true, 'message' => 'Akses ditolak.'], 403);
        } catch (\Throwable $e) {
            \Log::error('[HSSE dashboardData] ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // TOP 10 ASSESSOR DATA (AJAX)
    // =========================================================
    public function top10AssessorData(Request $request)
    {
        try {
            $this->authorize('manage hsse evaluation');
            $user  = Auth::user();
            $query = HsseEvaluation::query()
                ->where('status', 'submitted')
                ->select('assessor_name', 'assessor_position')
                ->selectRaw('COUNT(*) as total_evaluations')
                ->selectRaw('SUM(CASE WHEN score_category = "baik"   THEN 1 ELSE 0 END) as total_baik')
                ->selectRaw('SUM(CASE WHEN score_category = "cukup"  THEN 1 ELSE 0 END) as total_cukup')
                ->selectRaw('SUM(CASE WHEN score_category = "kurang" THEN 1 ELSE 0 END) as total_kurang');

            if (!$this->canManage()) {
                $query->where('company_id', $user->company_id);
            } else {
                if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
                if ($request->filled('vessel_id'))  $query->where('vessel_id',  $request->vessel_id);
            }

            if ($request->filled('start_date')) $query->where('evaluated_date', '>=', $request->start_date);
            if ($request->filled('end_date'))   $query->where('evaluated_date', '<=', $request->end_date);

            $rows = $query->groupBy('assessor_name', 'assessor_position')
                ->orderByDesc('total_evaluations')->limit(10)->get()
                ->map(function ($r) {
                    $total = max((int) $r->total_evaluations, 1);
                    return [
                        'assessor_name'     => $r->assessor_name     ?? '-',
                        'assessor_position' => $r->assessor_position ?? '-',
                        'total_evaluations' => (int) $r->total_evaluations,
                        'total_baik'        => (int) $r->total_baik,
                        'total_cukup'       => (int) $r->total_cukup,
                        'total_kurang'      => (int) $r->total_kurang,
                        'pct_baik'          => round((int) $r->total_baik / $total * 100),
                    ];
                });

            return response()->json(['assessors' => $rows]);

        } catch (AuthorizationException $e) {
            return response()->json(['error' => true, 'message' => 'Akses ditolak.'], 403);
        } catch (\Throwable $e) {
            \Log::error('[HSSE top10AssessorData] ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // INDEX
    // =========================================================
    public function index(Request $request)
    {
        $this->authorize('manage hsse evaluation');
        $user = Auth::user();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HSSE Evaluation', 'url' => route('hsse-evaluation.dashboard')],
            ['label' => 'All Evaluations'],
        ];

        if ($request->ajax()) {
            try {
                $query = HsseEvaluation::with(['vessel', 'company'])
                    ->select('hsse_evaluations.*');

                if (!$this->canManage()) {
                    $query->where('hsse_evaluations.company_id', $user->company_id);
                } else {
                    if ($request->filled('company_id')) $query->where('hsse_evaluations.company_id', $request->company_id);
                }

                if ($request->filled('vessel_id'))      $query->where('hsse_evaluations.vessel_id',      $request->vessel_id);
                if ($request->filled('status'))         $query->where('hsse_evaluations.status',         $request->status);
                if ($request->filled('score_category')) $query->where('hsse_evaluations.score_category', $request->score_category);
                if ($request->filled('start_date'))     $query->where('hsse_evaluations.evaluated_date', '>=', $request->start_date);
                if ($request->filled('end_date'))       $query->where('hsse_evaluations.evaluated_date', '<=', $request->end_date);

                $canManage = $this->canManage();

                return DataTables::eloquent($query)
                    ->addIndexColumn()
                    ->editColumn('evaluated_date', function ($r) {
                        return Carbon::parse($r->evaluated_date)->format('d M Y');
                    })
                    ->addColumn('vessel_name',       function ($r) { return $r->vessel  ? $r->vessel->name  : '-'; })
                    ->addColumn('company_name',      function ($r) { return $r->company ? $r->company->name : '-'; })
                    ->addColumn('assessor_name_col', function ($r) { return $r->assessor_name  ?? '-'; })
                    ->addColumn('crew_position',     function ($r) { return $r->crew_position  ?? '';  })
                    ->addColumn('score_badge', function ($r) {
                        if (!$r->score_category) return '<span class="text-muted small">—</span>';
                        $map   = ['baik' => 'badge-baik', 'cukup' => 'badge-cukup', 'kurang' => 'badge-kurang'];
                        $cls   = $map[$r->score_category] ?? 'badge-draft';
                        $label = ucfirst($r->score_category);
                        $score = $r->total_score ?? '?';
                        return "<span class=\"score-badge {$cls}\"><span class=\"score-num\">{$score}</span> {$label}</span>";
                    })
                    ->addColumn('status_badge', function ($r) {
                        return $r->status === 'submitted'
                            ? '<span class="status-chip chip-submitted">Submitted</span>'
                            : '<span class="status-chip chip-draft">Draft</span>';
                    })
                    ->addColumn('action', function ($r) use ($canManage) {
                        return view('features.hsse-evaluation.partials.actions', ['r' => $r, 'canManage' => $canManage])->render();
                    })
                    ->orderColumn('vessel_name', function ($q, $order) {
                        $q->leftJoin('vessels as v_sort', 'v_sort.id', '=', 'hsse_evaluations.vessel_id')
                          ->orderBy('v_sort.name', $order);
                    })
                    ->orderColumn('company_name', function ($q, $order) {
                        $q->leftJoin('companies as c_sort', 'c_sort.id', '=', 'hsse_evaluations.company_id')
                          ->orderBy('c_sort.name', $order);
                    })
                    ->filterColumn('vessel_name', function ($q, $kw) {
                        $q->whereHas('vessel',  function ($q2) use ($kw) { $q2->where('name', 'like', "%{$kw}%"); });
                    })
                    ->filterColumn('company_name', function ($q, $kw) {
                        $q->whereHas('company', function ($q2) use ($kw) { $q2->where('name', 'like', "%{$kw}%"); });
                    })
                    ->filterColumn('assessor_name_col', function ($q, $kw) {
                        $q->where('hsse_evaluations.assessor_name', 'like', "%{$kw}%");
                    })
                    ->rawColumns(['score_badge', 'status_badge', 'action'])
                    ->make(true);

            } catch (\Throwable $e) {
                \Log::error('[HSSE index AJAX] ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                return response()->json([
                    'draw'            => intval($request->input('draw')),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                    'error'           => $e->getMessage(),
                ], 500);
            }
        }

        $companies = $this->canManage() ? Company::orderBy('name')->get() : collect([]);
        $vessels   = $this->canManage()
            ? Vessel::orderBy('name')->get()
            : Vessel::where('company_id', $user->company_id)->orderBy('name')->get();
        $canManage = $this->canManage();

        return view('features.hsse-evaluation.index', compact('companies', 'vessels', 'breadcrumbs', 'canManage'));
    }

    // =========================================================
    // CREATE
    // =========================================================
    public function create()
    {
        $this->authorize('manage hsse evaluation');
        if (!$this->canManage()) abort(403);

        $criteria    = HsseCriteria::where('is_active', true)->orderBy('order_no')->get();
        $companies   = Company::orderBy('name')->get();
        $userCompany = null;
        $vessels     = Vessel::orderBy('name')->get();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HSSE Evaluation', 'url' => route('hsse-evaluation.dashboard')],
            ['label' => 'New Evaluation'],
        ];

        return view('features.hsse-evaluation.create', compact('criteria', 'companies', 'userCompany', 'vessels', 'breadcrumbs'));
    }

    // =========================================================
    // STORE — selalu kembalikan JSON
    // =========================================================
    public function store(Request $request)
    {
        try {
            $this->authorize('manage hsse evaluation');
            if (!$this->canManage()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }

            $user     = Auth::user();
            $criteria = HsseCriteria::where('is_active', true)->orderBy('order_no')->get();

            $rules = [
                'company_id'         => 'required|exists:companies,id',
                'vessel_id'          => 'required|exists:vessels,id',
                'crew_name'          => 'required|string|max:255',
                'crew_position'      => 'nullable|string|max:255',
                'crew_member_id'     => 'nullable|exists:crew_members,id',
                'evaluated_date'     => 'required|date',
                'notes'              => 'nullable|string',
                'assessor_name'      => 'required|string|max:255',
                'assessor_position'  => 'nullable|string|max:255',
                'assessor_signature' => 'nullable|image|max:2048',
                'action'             => 'required|in:draft,submit',
            ];

            foreach ($criteria as $c) {
                $rules["scores.{$c->id}"]     = 'required|integer|min:1|max:3';
                $rules["keterangan.{$c->id}"] = 'nullable|string|max:500';
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            $signaturePath = null;
            if ($request->hasFile('assessor_signature')) {
                $signaturePath = $request->file('assessor_signature')
                    ->store('hsse-evaluations/signatures', 'public');
            }

            $totalScore = array_sum($request->input('scores', []));
            $category   = $this->calcCategory($totalScore);
            $status     = $request->input('action') === 'submit' ? 'submitted' : 'draft';

            DB::transaction(function () use ($request, $validated, $signaturePath, $totalScore, $category, $status, $user, $criteria) {
                $evaluation = HsseEvaluation::create([
                    'company_id'              => $validated['company_id'],
                    'vessel_id'               => $validated['vessel_id'],
                    'crew_member_id'          => $validated['crew_member_id'] ?? null,
                    'crew_name'               => $validated['crew_name'],
                    'crew_position'           => $validated['crew_position'] ?? null,
                    'evaluated_date'          => $validated['evaluated_date'],
                    'total_score'             => $totalScore,
                    'score_category'          => $category,
                    'notes'                   => $validated['notes'] ?? null,
                    'assessor_id'             => $user->id,
                    'assessor_name'           => $validated['assessor_name'],
                    'assessor_position'       => $validated['assessor_position'] ?? null,
                    'assessor_signature_path' => $signaturePath,
                    'status'                  => $status,
                    'submitted_at'            => $status === 'submitted' ? now() : null,
                ]);

                foreach ($criteria as $c) {
                    HsseEvaluationScore::create([
                        'evaluation_id' => $evaluation->id,
                        'criteria_id'   => $c->id,
                        'score'         => $request->input("scores.{$c->id}"),
                        'keterangan'    => $request->input("keterangan.{$c->id}"),
                    ]);
                }
            });

            return response()->json([
                'success'  => true,
                'message'  => $status === 'submitted'
                    ? 'Evaluasi berhasil disimpan & disubmit!'
                    : 'Draft evaluasi berhasil disimpan.',
                'redirect' => route('hsse-evaluation.index'),
            ]);

        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);

        } catch (\Throwable $e) {
            \Log::error('[HSSE store] ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // SHOW
    // =========================================================
    public function show(HsseEvaluation $hsseEvaluation)
    {
        $this->authorizeAccess($hsseEvaluation);
        $hsseEvaluation->load(['company', 'vessel', 'assessor', 'scores.criteria']);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HSSE Evaluation', 'url' => route('hsse-evaluation.dashboard')],
            ['label' => $hsseEvaluation->crew_name],
        ];

        return view('features.hsse-evaluation.show', compact('hsseEvaluation', 'breadcrumbs'));
    }

    // =========================================================
    // EDIT
    // =========================================================
    public function edit(HsseEvaluation $hsseEvaluation)
    {
        $this->authorize('manage hsse evaluation');
        if (!$this->canManage()) abort(403);
        $this->authorizeAccess($hsseEvaluation);

        $criteria = HsseCriteria::where('is_active', true)->orderBy('order_no')->get();
        $hsseEvaluation->load(['scores', 'company', 'vessel']);

        $scoreMap  = $hsseEvaluation->scores->keyBy('criteria_id');
        $companies = Company::orderBy('name')->get();
        $vessels   = Vessel::orderBy('name')->get();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'HSSE Evaluation', 'url' => route('hsse-evaluation.dashboard')],
            ['label' => 'Edit: ' . $hsseEvaluation->crew_name],
        ];

        $evaluation = $hsseEvaluation;

        return view('features.hsse-evaluation.edit', compact(
            'hsseEvaluation', 'evaluation', 'criteria', 'scoreMap', 'companies', 'vessels', 'breadcrumbs'
        ));
    }

    // =========================================================
    // UPDATE — selalu kembalikan JSON
    // =========================================================
    public function update(Request $request, HsseEvaluation $hsseEvaluation)
    {
        try {
            $this->authorize('manage hsse evaluation');
            if (!$this->canManage()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            $this->authorizeAccess($hsseEvaluation);

            $criteria = HsseCriteria::where('is_active', true)->orderBy('order_no')->get();

            $rules = [
                'vessel_id'          => 'required|exists:vessels,id',
                'crew_name'          => 'required|string|max:255',
                'crew_position'      => 'nullable|string|max:255',
                'evaluated_date'     => 'required|date',
                'notes'              => 'nullable|string',
                'assessor_name'      => 'required|string|max:255',
                'assessor_position'  => 'nullable|string|max:255',
                'assessor_signature' => 'nullable|image|max:2048',
                'action'             => 'required|in:draft,submit',
            ];
            foreach ($criteria as $c) {
                $rules["scores.{$c->id}"]     = 'required|integer|min:1|max:3';
                $rules["keterangan.{$c->id}"] = 'nullable|string|max:500';
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $validated     = $validator->validated();
            $signaturePath = $hsseEvaluation->assessor_signature_path;

            if ($request->hasFile('assessor_signature')) {
                if ($signaturePath) Storage::disk('public')->delete($signaturePath);
                $signaturePath = $request->file('assessor_signature')
                    ->store('hsse-evaluations/signatures', 'public');
            }

            $totalScore = array_sum($request->input('scores', []));
            $category   = $this->calcCategory($totalScore);
            $status     = $request->input('action') === 'submit' ? 'submitted' : 'draft';

            DB::transaction(function () use ($request, $validated, $hsseEvaluation, $signaturePath, $totalScore, $category, $status, $criteria) {
                $hsseEvaluation->update([
                    'vessel_id'               => $validated['vessel_id'],
                    'crew_name'               => $validated['crew_name'],
                    'crew_position'           => $validated['crew_position'] ?? null,
                    'evaluated_date'          => $validated['evaluated_date'],
                    'total_score'             => $totalScore,
                    'score_category'          => $category,
                    'notes'                   => $validated['notes'] ?? null,
                    'assessor_name'           => $validated['assessor_name'],
                    'assessor_position'       => $validated['assessor_position'] ?? null,
                    'assessor_signature_path' => $signaturePath,
                    'status'                  => $status,
                    'submitted_at'            => $status === 'submitted'
                        ? ($hsseEvaluation->submitted_at ?? now()) : null,
                ]);

                foreach ($criteria as $c) {
                    HsseEvaluationScore::updateOrCreate(
                        ['evaluation_id' => $hsseEvaluation->id, 'criteria_id' => $c->id],
                        [
                            'score'      => $request->input("scores.{$c->id}"),
                            'keterangan' => $request->input("keterangan.{$c->id}"),
                        ]
                    );
                }
            });

            return response()->json([
                'success'  => true,
                'message'  => 'Evaluasi berhasil diperbarui!',
                'redirect' => route('hsse-evaluation.index'),
            ]);

        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        } catch (\Throwable $e) {
            \Log::error('[HSSE update] ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // DESTROY — selalu kembalikan JSON
    // =========================================================
    public function destroy(HsseEvaluation $hsseEvaluation)
    {
        try {
            $this->authorize('manage hsse evaluation');
            if (!$this->canManage()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            }
            $this->authorizeAccess($hsseEvaluation);

            DB::transaction(function () use ($hsseEvaluation) {
                if ($hsseEvaluation->assessor_signature_path) {
                    Storage::disk('public')->delete($hsseEvaluation->assessor_signature_path);
                }
                $hsseEvaluation->scores()->delete();
                $hsseEvaluation->delete();
            });

            return response()->json(['success' => true, 'message' => 'Evaluasi dihapus permanen.']);

        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        } catch (\Throwable $e) {
            \Log::error('[HSSE destroy] ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // EXPORT EXCEL
    // =========================================================
    public function exportExcel(Request $request)
    {
        $this->authorize('manage hsse evaluation');

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end   = Carbon::parse($request->end_date)->endOfDay();

        if ($start->diffInDays($end) > 7) {
            return response()->json(['success' => false, 'message' => 'Rentang tanggal maksimal 7 hari.'], 422);
        }

        $user  = Auth::user();
        $query = HsseEvaluation::with(['company', 'vessel', 'scores.criteria'])
            ->whereBetween('evaluated_date', [$start, $end])
            ->orderBy('evaluated_date')->orderBy('created_at');

        if (!$this->canManage()) {
            $query->where('company_id', $user->company_id);
        } else {
            if ($request->filled('company_id')) $query->where('company_id', $request->company_id);
            if ($request->filled('vessel_id'))  $query->where('vessel_id',  $request->vessel_id);
            if ($request->filled('status'))     $query->where('status',     $request->status);
        }

        $rows     = $query->get();
        $criteria = HsseCriteria::where('is_active', true)->orderBy('order_no')->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('HSSE Evaluations');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ];

        $sheet->mergeCells('A1:R1');
        $sheet->setCellValue('A1', 'HSSE Marine Onboard Evaluation Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->mergeCells('A2:R2');
        $sheet->setCellValue('A2',
            'Periode: ' . $start->format('d M Y') . ' s/d ' . $end->format('d M Y') .
            '   |   Diekspor: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['No', 'Tanggal', 'Perusahaan', 'Kapal', 'Nama Kru', 'Jabatan', 'Assessor', 'Jabatan Assessor'];
        foreach ($criteria as $c) { $headers[] = 'K' . $c->order_no . ': ' . $c->aspect; }
        $headers = array_merge($headers, ['Total Score', 'Kategori', 'Status', 'Catatan']);

        $col = 1;
        foreach ($headers as $h) { $sheet->setCellValueByColumnAndRow($col++, 3, $h); }
        $lastCol       = $col - 1;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);
        $sheet->getStyle('A3:' . $lastColLetter . '3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(36);

        $rowNum = 4;
        $catMap = ['baik' => '32CD32', 'cukup' => 'FFA500', 'kurang' => 'FF4444'];

        foreach ($rows as $i => $ev) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $i + 1);
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, Carbon::parse($ev->evaluated_date)->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->company->name ?? '-');
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->vessel->name  ?? '-');
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->crew_name);
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->crew_position ?? '-');
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->assessor_name);
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->assessor_position ?? '-');

            foreach ($criteria as $c) {
                $score = $ev->scores->firstWhere('criteria_id', $c->id);
                $val   = $score ? $score->score : '-';
                $sheet->setCellValueByColumnAndRow($col, $rowNum, $val);
                if (is_numeric($val)) {
                    $color     = match ((int) $val) { 1 => 'FFDDDD', 2 => 'FFF3CD', 3 => 'D4EDDA', default => 'FFFFFF' };
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getStyle($colLetter . $rowNum)->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }
                $col++;
            }

            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->total_score ?? '-');
            $cat = $ev->score_category ?? '-';
            $sheet->setCellValueByColumnAndRow($col, $rowNum, ucfirst($cat));
            if (isset($catMap[$cat])) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->getStyle($colLetter . $rowNum)->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $catMap[$cat]]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, ucfirst($ev->status ?? '-'));
            $sheet->setCellValueByColumnAndRow($col++, $rowNum, $ev->notes ?? '');

            $bg = $rowNum % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle('A' . $rowNum . ':' . $lastColLetter . $rowNum)->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($rowNum)->setRowHeight(18);
            $rowNum++;
        }

        $sheet->mergeCells('A' . $rowNum . ':D' . $rowNum);
        $sheet->setCellValue('A' . $rowNum, 'Total Evaluasi: ' . $rows->count());
        $sheet->getStyle('A' . $rowNum . ':' . $lastColLetter . $rowNum)->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A5F']]],
        ]);

        $widths = [5, 12, 22, 20, 22, 18, 22, 18];
        foreach ($criteria as $c) { $widths[] = 28; }
        $widths = array_merge($widths, [10, 12, 12, 30]);
        foreach ($widths as $i => $w) { $sheet->getColumnDimensionByColumn($i + 1)->setWidth($w); }
        $sheet->freezePane('E4');

        $filename = 'HSSE_Evaluation_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        if (ob_get_length()) { ob_end_clean(); }

        return response()->streamDownload(
            function () use ($writer) { $writer->save('php://output'); },
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'max-age=0',
                'Pragma'              => 'public',
            ]
        );
    }

    // =========================================================
    // AJAX — Vessels by Company
    // =========================================================
    public function getVesselsByCompany(Request $request)
    {
        if (!$this->canManage()) return response()->json([], 403);
        $request->validate(['company_id' => 'required|exists:companies,id']);
        return response()->json(
            Vessel::where('company_id', $request->company_id)->orderBy('name')->get(['id', 'name'])
        );
    }

    // =========================================================
    // AJAX — Crew by Vessel
    // =========================================================
    public function getCrewByVessel(Request $request)
    {
        if (!$this->canManage()) return response()->json([], 403);
        $request->validate(['vessel_id' => 'required|exists:vessels,id']);

        $crew = CrewMember::where('is_active', true)
            ->whereHas('vesselAssignments', function ($q) use ($request) {
                $q->where('vessel_id', $request->vessel_id)
                  ->where('is_active', true)
                  ->whereNull('unassigned_at');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'position']);

        return response()->json($crew);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================
    private function calcCategory(int $total): string
    {
        if ($total <= 8)  return 'kurang';
        if ($total <= 11) return 'cukup';
        return 'baik';
    }

    private function authorizeAccess($model): bool
    {
        if ($this->canManage()) return true;

        if ((string) $model->company_id !== (string) Auth::user()->company_id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        return true;
    }
}