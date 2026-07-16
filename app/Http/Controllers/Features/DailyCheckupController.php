<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Exports\HealthChecksExport;
use App\Http\Requests\Features\StoreDailyCheckupRequest;
use App\Http\Requests\Features\StoreBulkDailyCheckupRequest;
use App\Http\Requests\Features\VerifyCheckupRequest;
use App\Http\Requests\Features\ValidateCheckupRequest;
use App\Models\CrewMember;
use App\Models\HealthCheck;
use App\Models\Vessel;
use App\Models\Company;
use App\Services\HealthCheckService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DailyCheckupController extends Controller
{
    use AuthorizesRequests;

    protected $healthCheckService;

    public function __construct(HealthCheckService $healthCheckService)
    {
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Main router - Redirect based on role
     */
    public function index(Request $request)
    {
        $this->authorize('manage daily checkup');
        $user = Auth::user();

        if ($user->hasRole('crew-kapal')) {
            return $this->crewDashboard();
        }

        if ($user->hasRole('koordinator')) {
            return $this->coordinatorDashboard($request);
        }

        if ($user->hasRole('nurse') || $user->hasRole('super-admin')) {
            return $this->adminNurseDashboard($request);
        }

        abort(403, 'Unauthorized access');
    }

    /**
     * Dashboard for Crew - Show today's checkup form
     */
    private function crewDashboard()
    {
        $this->authorize('manage daily checkup');
        $user = Auth::user();

        // Get vessel coordinated by this user
        $vessel = Vessel::where('coordinator_id', $user->id)->first();

        if (!$vessel) {
            return view('daily-checkup.crew.no-vessel');
        }

        $today = today();

        // Get active crew members assigned to this vessel via vessel_assignments
        $crewMembers = CrewMember::whereHas('vesselAssignments', function($q) use ($vessel) {
                $q->where('vessel_id', $vessel->id)
                  ->where('is_active', true)
                  ->whereNull('unassigned_at');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get existing checkups for today AT THIS VESSEL
        $existingCheckups = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', $today)
            ->pluck('crew_member_id')
            ->toArray();

        $stats = [
            'total_crew' => $crewMembers->count(),
            'completed' => count($existingCheckups),
            'pending' => $crewMembers->count() - count($existingCheckups),
        ];

        return view('daily-checkup.crew.index', compact(
            'vessel',
            'crewMembers',
            'existingCheckups',
            'stats',
            'today'
        ));
    }

    /**
     * Dashboard for Coordinator - Show all vessels in their company
     */
    private function coordinatorDashboard(Request $request)
    {
        $this->authorize('manage daily checkup');
        $user = Auth::user();
        $companyId = $user->company_id;

        if (!$companyId) {
            abort(403, 'No company assigned');
        }

        $selectedDate = $request->input('date', today()->format('Y-m-d'));

        // Get vessels in coordinator's company with crew count
        $vessels = Vessel::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['company', 'coordinator'])
            ->withCount([
                'vesselAssignments as current_crew_members_count' => function($q) {
                    $q->where('is_active', true)
                      ->whereNull('unassigned_at');
                }
            ])
            ->get();

        $vesselStats = [];
        foreach ($vessels as $vessel) {
            $stats = $this->healthCheckService->getVesselCheckupStats($vessel->id, $selectedDate);
            $vesselStats[$vessel->id] = $stats;
        }

        // BARU: Hitung total untuk all vessels
        $allStats = $this->healthCheckService->getDailyStats($selectedDate, $companyId);

        return view('daily-checkup.coordinator.index', compact(
            'vessels',
            'vesselStats',
            'selectedDate',
            'allStats'
        ));
    }

    /**
     * Dashboard for Admin/Nurse - Table view with filters
     */
    private function adminNurseDashboard(Request $request)
    {
        $this->authorize('manage daily checkup');

        // Check if filter is applied
        $hasFilter = $request->filled('company_id') ||
                     $request->filled('vessel_id') ||
                     ($request->filled('status') && $request->status !== 'all') ||
                     ($request->filled('health_status') && $request->health_status !== 'all');

        $selectedDate = $request->input('date', today()->format('Y-m-d'));
        $companyId = $request->input('company_id');
        $vesselId = $request->input('vessel_id');
        $statusFilter = $request->input('status', 'all');
        $healthFilter = $request->input('health_status', 'all');

        // Initialize defaults
        $healthChecks = collect();
        $stats = [
            'total' => 0,
            'pending' => 0,
            'reviewed' => 0,
            'validated' => 0,
            'warnings' => 0,
            'critical' => 0,
            'follow_ups' => 0,
        ];

        // Only fetch data if filters are applied
        if ($hasFilter) {
            $query = HealthCheck::query()
                ->whereDate('check_date', $selectedDate)
                ->with([
                    'crewMember' => function($q) {
                        $q->withTrashed();
                    },
                    'vessel' => function($q) {
                        $q->withTrashed()->with('company');
                    },
                    'reporter',
                    'verifier',
                    'validator'
                ]);

            // Support "all" vessels within a company
            if ($companyId) {
                $query->whereHas('vessel', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                });
            }

            // Only filter by specific vessel if not "all"
            if ($vesselId && $vesselId !== 'all') {
                $query->where('vessel_id', $vesselId);
            }

            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            if ($healthFilter === 'warning') {
                $query->where('has_health_issue', true);
            } elseif ($healthFilter === 'normal') {
                $query->where('has_health_issue', false);
            }

            $healthChecks = $query->latest('checked_at')->paginate(100)->withQueryString();

            // Pass null for vessel_id when showing all vessels
            $statsVesselId = ($vesselId && $vesselId !== 'all') ? $vesselId : null;
            $stats = $this->healthCheckService->getDailyStats($selectedDate, $companyId, $statsVesselId);
        }

        $companies = Company::active()->orderBy('name')->get();
        $vessels = $companyId
            ? Vessel::where('company_id', $companyId)->active()->orderBy('name')->get()
            : Vessel::active()->orderBy('name')->get();

        return view('daily-checkup.admin.index', compact(
            'healthChecks',
            'companies',
            'vessels',
            'selectedDate',
            'companyId',
            'vesselId',
            'statusFilter',
            'healthFilter',
            'stats',
            'hasFilter'
        ));
    }

    /**
     * Store single crew checkup
     */
    public function store(StoreDailyCheckupRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            $vessel = Vessel::findOrFail($validated['vessel_id']);

            // Check if crew is currently assigned to the vessel via vessel_assignments
            $isAssigned = $vessel->vesselAssignments()
                ->where('crew_member_id', $validated['crew_member_id'])
                ->where('is_active', true)
                ->whereNull('unassigned_at')
                ->exists();

            if (!$isAssigned) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member is not assigned to this vessel.'
                ], 422);
            }

            // Check for duplicate - PER VESSEL
            $exists = HealthCheck::where('crew_member_id', $validated['crew_member_id'])
                ->where('vessel_id', $validated['vessel_id'])
                ->whereDate('check_date', $validated['check_date'])
                ->exists();

            if ($exists) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Crew ini sudah melakukan pemeriksaan di kapal ini pada tanggal tersebut.'
                ], 422);
            }

            $healthCheck = $this->healthCheckService->createHealthCheck($validated, $user);

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pemeriksaan berhasil disimpan.',
                    'data' => $healthCheck
                ]);
            }

            return back()->with('success', 'Data pemeriksaan berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Checkup Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Store bulk crew checkups
     */
    public function storeBulk(StoreBulkDailyCheckupRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            $result = $this->healthCheckService->createBulkHealthChecks($validated, $user);

            DB::commit();

            $message = "Berhasil menyimpan {$result['success']} pemeriksaan.";
            if ($result['skipped'] > 0) {
                $message .= " {$result['skipped']} crew sudah diperiksa.";
            }
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} gagal disimpan.";
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'result' => $result
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Bulk Checkup Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Verify checkups (Coordinator only)
     * UPDATED: Support verifying all vessels in a company
     */
    public function verify(VerifyCheckupRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            // BARU: Support verifying all vessels
            $vesselId = $validated['vessel_id'];
            $companyId = $vesselId === 'all' ? $user->company_id : null;

            $result = $this->healthCheckService->verifyHealthChecks(
                $vesselId,
                $validated['check_date'],
                $validated['verification_notes'] ?? null,
                $user,
                $companyId
            );

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                if ($result['count'] === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data pending untuk diverifikasi.'
                    ], 404);
                }

                $vesselText = $vesselId === 'all' ? 'semua kapal' : 'vessel ini';
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil memverifikasi {$result['count']} pemeriksaan dari {$vesselText}.",
                    'count' => $result['count']
                ]);
            }

            if ($result['count'] === 0) {
                return back()->with('warning', 'Tidak ada data pending untuk diverifikasi.');
            }

            return back()->with('success', "Berhasil memverifikasi {$result['count']} pemeriksaan.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verify Checkup Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memverifikasi data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal memverifikasi data.');
        }
    }

    /**
     * Validate checkups (Nurse/Admin only)
     * UPDATED: Support validating all vessels in a company, bisa validasi pending/reviewed
     */
    public function validate(ValidateCheckupRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            // Support "all" vessels
            if ($validated['vessel_id'] === 'all') {
                if (!isset($validated['company_id']) || !$validated['company_id']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Company ID required when validating all vessels.'
                    ], 422);
                }

                $result = $this->healthCheckService->validateHealthChecks(
                    $validated['vessel_id'],
                    $validated['check_date'],
                    $validated['validation_notes'] ?? null,
                    $user,
                    $validated['company_id']
                );
            } else {
                $result = $this->healthCheckService->validateHealthChecks(
                    $validated['vessel_id'],
                    $validated['check_date'],
                    $validated['validation_notes'] ?? null,
                    $user
                );
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                if ($result['count'] === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data untuk divalidasi.'
                    ], 404);
                }

                $vesselText = $validated['vessel_id'] === 'all' ? 'semua kapal' : 'vessel ini';
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil memvalidasi {$result['count']} pemeriksaan dari {$vesselText}.",
                    'count' => $result['count']
                ]);
            }

            if ($result['count'] === 0) {
                return back()->with('warning', 'Tidak ada data untuk divalidasi.');
            }

            return back()->with('success', "Berhasil memvalidasi {$result['count']} pemeriksaan.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Validate Checkup Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memvalidasi data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal memvalidasi data.');
        }
    }

    /**
     * Show vessel checkup details
     */
    public function showVessel(Vessel $vessel, Request $request)
    {
        $this->authorize('manage daily checkup');
        $user = Auth::user();

        if ($user->hasRole('koordinator') && $user->company_id !== $vessel->company_id) {
            abort(403, 'Unauthorized access');
        }

        $selectedDate = $request->input('date', today()->format('Y-m-d'));
        $filter = $request->input('filter', 'all');

        $healthChecks = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', $selectedDate)
            ->with([
                'crewMember' => function($q) {
                    $q->withTrashed();
                },
                'reporter',
                'verifier',
                'validator'
            ])
            ->orderBy('checked_at')
            ->get();

        if ($filter === 'warnings') {
            $healthChecks = $healthChecks->filter(fn($check) => $check->hasAbnormalVitals());
        }

        $stats = $this->healthCheckService->getVesselCheckupStats($vessel->id, $selectedDate);

        return view('daily-checkup.vessel-detail', compact(
            'vessel',
            'healthChecks',
            'selectedDate',
            'filter',
            'stats'
        ));
    }

    /**
     * Show single checkup detail (AJAX)
     */
    public function showCheckup(HealthCheck $healthCheck)
    {
        try {
            // Load relationships with soft-deleted records
            $healthCheck->load([
                'crewMember' => function($q) {
                    $q->withTrashed();
                },
                'vessel' => function($q) {
                    $q->withTrashed()->with(['company' => function($q) {
                        $q->withTrashed();
                    }]);
                },
                'reporter',
                'verifier',
                'validator'
            ]);

            // Ensure we have crew data (use snapshot if crew is deleted)
            if (!$healthCheck->crewMember) {
                // Create a temporary object from snapshot data
                $healthCheck->crewMember = (object)[
                    'name' => $healthCheck->crew_name_snapshot ?? 'Unknown',
                    'position' => $healthCheck->crew_position_snapshot ?? 'Unknown',
                    'vessel' => $healthCheck->vessel ?? (object)['name' => 'N/A']
                ];
            } else {
                // Ensure vessel relationship exists
                if (!$healthCheck->crewMember->vessel) {
                    $healthCheck->crewMember->vessel = $healthCheck->vessel ?? (object)['name' => 'N/A'];
                }
            }

            return response()->json($healthCheck);
        } catch (\Exception $e) {
            Log::error('Show Checkup Detail Error', [
                'health_check_id' => $healthCheck->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show coordinator input form for specific vessel
     */
    public function coordinatorInput(Vessel $vessel)
    {
        $this->authorize('manage daily checkup');
        $user = Auth::user();

        if ($user->hasRole('koordinator') && $user->company_id !== $vessel->company_id) {
            abort(403, 'Unauthorized access to this vessel');
        }

        $today = today();

        // Get active crew members assigned to this vessel
        $crewMembers = CrewMember::whereHas('vesselAssignments', function($q) use ($vessel) {
                $q->where('vessel_id', $vessel->id)
                  ->where('is_active', true)
                  ->whereNull('unassigned_at');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get existing checkups for today AT THIS VESSEL
        $existingCheckups = HealthCheck::where('vessel_id', $vessel->id)
            ->whereDate('check_date', $today)
            ->pluck('crew_member_id')
            ->toArray();

        $stats = [
            'total_crew' => $crewMembers->count(),
            'completed' => count($existingCheckups),
            'pending' => $crewMembers->count() - count($existingCheckups),
        ];

        return view('daily-checkup.coordinator.input', compact(
            'vessel',
            'crewMembers',
            'existingCheckups',
            'stats',
            'today'
        ));
    }

    /**
     * Store single crew checkup by coordinator (auto-verified)
     */
    public function coordinatorStore(StoreDailyCheckupRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            $vessel = Vessel::findOrFail($validated['vessel_id']);

            // Check access
            if ($user->hasRole('koordinator') && $user->company_id !== $vessel->company_id) {
                abort(403, 'Unauthorized');
            }

            // Check if crew is assigned via vessel_assignments
            $isAssigned = $vessel->vesselAssignments()
                ->where('crew_member_id', $validated['crew_member_id'])
                ->where('is_active', true)
                ->whereNull('unassigned_at')
                ->exists();

            if (!$isAssigned) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member is not assigned to this vessel.'
                ], 422);
            }

            // Check duplicate - PER VESSEL
            $exists = HealthCheck::where('crew_member_id', $validated['crew_member_id'])
                ->where('vessel_id', $validated['vessel_id'])
                ->whereDate('check_date', $validated['check_date'])
                ->exists();

            if ($exists) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Crew ini sudah melakukan pemeriksaan di kapal ini pada tanggal tersebut.'
                ], 422);
            }

            $healthCheck = $this->healthCheckService->createHealthCheck($validated, $user);

            // Auto-verify for coordinator
            if ($user->hasRole('koordinator')) {
                $healthCheck->update([
                    'status' => 'reviewed',
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                    'verification_notes' => 'Auto-verified by coordinator',
                ]);
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pemeriksaan berhasil disimpan dan diverifikasi.',
                    'data' => $healthCheck
                ]);
            }

            return back()->with('success', 'Data pemeriksaan berhasil disimpan dan diverifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Coordinator Store Checkup Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Store bulk crew checkups by coordinator (auto-verified)
     */
    public function coordinatorStoreBulk(StoreBulkDailyCheckupRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = Auth::user();

            $vessel = Vessel::findOrFail($validated['vessel_id']);

            if ($user->hasRole('koordinator') && $user->company_id !== $vessel->company_id) {
                abort(403, 'Unauthorized');
            }

            $result = $this->healthCheckService->createBulkHealthChecks($validated, $user);

            // Auto-verify all created checkups for coordinator
            if ($user->hasRole('koordinator') && $result['success'] > 0) {
                HealthCheck::where('vessel_id', $validated['vessel_id'])
                    ->whereDate('check_date', $validated['check_date'])
                    ->where('reported_by', $user->id)
                    ->where('status', 'pending')
                    ->whereNull('verified_by')
                    ->update([
                        'status' => 'reviewed',
                        'verified_by' => $user->id,
                        'verified_at' => now(),
                        'verification_notes' => 'Auto-verified by coordinator',
                    ]);
            }

            DB::commit();

            $message = "Berhasil menyimpan dan memverifikasi {$result['success']} pemeriksaan.";
            if ($result['skipped'] > 0) {
                $message .= " {$result['skipped']} crew sudah diperiksa.";
            }
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} gagal disimpan.";
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'result' => $result
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Coordinator Store Bulk Checkup Error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'vessel_id' => 'nullable|exists:vessels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $user = Auth::user();

            // Build query
            $query = HealthCheck::whereBetween('check_date', [
                Carbon::parse($request->start_date),
                Carbon::parse($request->end_date)
            ]);

            // If vessel_id is provided, filter by that vessel
            if ($request->filled('vessel_id')) {
                $query->where('vessel_id', $request->vessel_id);
                $vessel = Vessel::findOrFail($request->vessel_id);
                $vesselName = $vessel->name;
            } else {
                // For super-admin, allow export all vessels
                if (!($user->hasRole('super-admin') || $user->hasRole('nurse'))) {
                    abort(403, 'Unauthorized to export all vessels');
                }
                $vesselName = 'All_Vessels';
            }

            $query->with([
                'crewMember' => function($q) {
                    $q->withTrashed();
                },
                'vessel',
                'reporter',
                'verifier',
                'validator'
            ])->orderBy('check_date')->orderBy('checked_at');

            $healthChecks = $query->get();

            if ($healthChecks->isEmpty()) {
                return back()->with('warning', 'Tidak ada data untuk diekspor.');
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $fileName = 'DCU_' . $vesselName . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';

            return Excel::download(new HealthChecksExport($healthChecks), $fileName);

        } catch (\Exception $e) {
            Log::error('Export Excel Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengekspor data.');
        }
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'vessel_id' => 'nullable|exists:vessels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $user = Auth::user();
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Check date range (max 31 days)
            if ($startDate->diffInDays($endDate) > 31) {
                return back()->with('warning', 'Maksimal rentang tanggal 31 hari untuk PDF.');
            }

            // Build query
            $query = HealthCheck::whereBetween('check_date', [$startDate, $endDate]);

            // If vessel_id is provided, filter by that vessel
            if ($request->filled('vessel_id')) {
                $query->where('vessel_id', $request->vessel_id);
                $vessel = Vessel::with('company')->findOrFail($request->vessel_id);
                $vesselName = $vessel->name;
                $companyName = $vessel->company->name ?? 'N/A';
            } else {
                // For super-admin, allow export all vessels
                if (!($user->hasRole('super-admin') || $user->hasRole('nurse'))) {
                    abort(403, 'Unauthorized to export all vessels');
                }
                $vesselName = 'All Vessels';
                $companyName = 'All Companies';
            }

            $healthChecks = $query->with([
                'crewMember' => function($q) {
                    $q->withTrashed();
                },
                'vessel.company',
                'reporter',
                'verifier',
                'validator'
            ])->orderBy('check_date')->orderBy('checked_at')->get();

            if ($healthChecks->isEmpty()) {
                return back()->with('warning', 'Tidak ada data untuk diekspor.');
            }

            $groupedByDate = $healthChecks->groupBy(fn($item) => $item->check_date->format('Y-m-d'));

            $data = [
                'groupedByDate' => $groupedByDate,
                'vesselName' => $vesselName,
                'companyName' => $companyName,
                'startDate' => $startDate->translatedFormat('d F Y'),
                'endDate' => $endDate->translatedFormat('d F Y'),
                'exportedBy' => Auth::user()->name,
                'exportedAt' => now()->translatedFormat('d F Y H:i'),
            ];

            $pdf = Pdf::loadView('daily-checkup.export-pdf', $data)
                ->setPaper('a4', 'landscape');

            $fileName = 'DCU_' . $vesselName . '_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.pdf';

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            Log::error('Export PDF Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal membuat PDF.');
        }
    }

    /**
     * Delete a health check
     */
    public function destroy(HealthCheck $healthCheck)
    {
        $user = Auth::user();

        if ($user->hasRole('crew-kapal') && !$healthCheck->check_date->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya dapat menghapus pemeriksaan hari ini.'
            ], 403);
        }

        try {
            // Use snapshot accessor for crew name
            $crewName = $healthCheck->crew_name ?? 'Unknown';
            $healthCheck->delete();

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pemeriksaan untuk {$crewName} berhasil dihapus."
                ]);
            }

            return back()->with('success', "Pemeriksaan untuk {$crewName} berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Delete Checkup Error', [
                'health_check_id' => $healthCheck->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
