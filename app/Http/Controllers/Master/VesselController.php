<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vessel;
use App\Models\CrewMember;
use App\Models\VesselAssignment;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Master\VesselRequest;

class VesselController extends Controller
{
    use AuthorizesRequests;

    protected $cacheKey = 'vessels';
    protected $cacheDuration = 3600;

    /**
     * Display a listing of vessels with statistics
     */
    public function index(Request $request)
    {
        $this->authorize('manage vessels');

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Vessels & Crew']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request, $user, $isSuperAdmin);
        }

        $stats = $this->getVesselStats($user, $isSuperAdmin);

        if ($isSuperAdmin) {
            $companies = Company::active()->orderBy('name')->get();
        } else {
            $companies = [];
        }

        return view('master.vessels.index', compact('breadcrumbs', 'stats', 'companies'));
    }

    /**
     * Get DataTables data for vessels list
     */
    protected function getDatatablesData(Request $request, $user, $isSuperAdmin)
    {
        $query = Vessel::with(['company', 'coordinator'])
            ->withCount(['activeAssignments as current_crew_count'])
            ->select([
                'id',
                'company_id',
                'coordinator_id',
                'name',
                'type',
                'valid_until',
                'is_active',
                'created_at'
            ]);

        // Filter by company for non-super-admin
        if (!$isSuperAdmin) {
            $query->where('company_id', $user->company_id);
        } else {
            if ($request->filled('company') && $request->company !== '') {
                $query->where('company_id', $request->company);
            }
        }

        // Global search
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->search($search);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        // Type filter
        if ($request->filled('type') && $request->type !== '') {
            $query->byType($request->type);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('company_name', function($vessel) {
                return $vessel->company ? $vessel->company->name : '-';
            })
            ->addColumn('coordinator_name', function($vessel) {
                return $vessel->coordinator ? $vessel->coordinator->name : '-';
            })
            ->addColumn('type_badge', function($vessel) {
                $colors = [
                    'AHT/S' => 'primary',
                    'Tug Boat' => 'warning',
                    'Mooring Boat' => 'info',
                    'Utility Boat' => 'success',
                    'Crew Boat' => 'dark',
                    'Small Marine' => 'secondary',
                    'LCT' => 'primary',
                    'Barge' => 'danger',
                    'Other' => 'secondary'
                ];
                $color = $colors[$vessel->type] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . $vessel->type . '</span>';
            })
            ->addColumn('validity_status', function($vessel) {
                if (!$vessel->valid_until) {
                    return '<span class="badge badge-light-success">Permanent</span>';
                }

                $days = $vessel->getDaysUntilExpiry();

                if ($days < 0) return '<span class="badge badge-light-danger">Expired</span>';
                if ($days <= 30) return '<span class="badge badge-light-warning">Expiring in '.$days.' days</span>';
                return '<span class="badge badge-light-success">Valid ('.date('d M Y', strtotime($vessel->valid_until)).')</span>';
            })
            ->addColumn('status', function($vessel) {
                $checked = $vessel->is_active ? 'checked' : '';
                return '
                    <div class="form-check form-switch form-check-custom form-check-solid justify-content-center">
                        <input class="form-check-input toggle-status" type="checkbox"
                            data-url="'.route('vessels.toggle-status', $vessel->id).'" '.$checked.'>
                    </div>';
            })
            ->addColumn('crew_count', function($vessel) {
                $count = $vessel->current_crew_count ?? 0;
                $color = $count > 0 ? 'primary' : 'secondary';
                return '<span class="badge badge-' . $color . '">' . $count . ' crew' . ($count != 1 ? 's' : '') . '</span>';
            })
            ->addColumn('action', function($vessel) {
                return view('master.vessels.partials.actions', compact('vessel'))->render();
            })
            ->rawColumns(['type_badge', 'validity_status', 'status', 'crew_count', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new vessel
     */
    public function create()
    {
        $this->authorize('manage vessels');
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Vessels & Crew', 'url' => route('vessels.index')],
            ['label' => 'Create Vessel']
        ];

        if ($isSuperAdmin) {
            $companies = Company::active()->orderBy('name')->get();
        } else {
            $companies = Company::where('id', $user->company_id)->get();
        }

        $coordinatorQuery = User::active();
        if (!$isSuperAdmin) {
            $coordinatorQuery->where('company_id', $user->company_id);
        }
        $coordinators = $coordinatorQuery->orderBy('name')->get();

        $vesselTypes = $this->getVesselTypes();

        return view('master.vessels.create', compact('breadcrumbs', 'companies', 'coordinators', 'vesselTypes'));
    }

    /**
     * Store a newly created vessel
     */
    public function store(VesselRequest $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        if (!$isSuperAdmin && $request->company_id != $user->company_id) {
            abort(403, 'Unauthorized company assignment');
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            if (!$isSuperAdmin) {
                $data['company_id'] = $user->company_id;
            }

            $data['is_active'] = $request->has('is_active') ? true : false;

            $vessel = Vessel::create($data);

            $this->clearVesselCache();

            DB::commit();

            Log::info('Vessel created', [
                'vessel_id' => $vessel->id,
                'user_id' => auth()->id(),
                'company_id' => $vessel->company_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vessel created successfully',
                'redirect' => route('vessels.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vessel creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create vessel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified vessel with crew details
     */
    public function show(Vessel $vessel)
    {
        $this->authorize('manage vessels');

        $user = auth()->user();
        if (!$user->hasRole('super-admin') && $vessel->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this vessel.');
        }

        // Load fresh data without cache
        $vessel->load([
            'company',
            'coordinator',
            'activeAssignments' => function($q) {
                $q->with(['crewMember' => function($cm) {
                    $cm->withTrashed();
                }, 'assignedBy'])
                ->orderBy('assigned_at', 'desc');
            }
        ]);

        // Get current crew members with assignment data
        $currentCrewMembers = $vessel->activeAssignments->map(function($assignment) {
            $crew = $assignment->crewMember;
            if ($crew) {
                $crew->currentVesselAssignment = $assignment;
                $crew->assignment_date = $assignment->assigned_at;
                $crew->assignment_notes = $assignment->notes;
                $crew->assigned_by_user = $assignment->assignedBy;
            }
            return $crew;
        })->filter()->sortBy('name')->values();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Vessels & Crew', 'url' => route('vessels.index')],
            ['label' => $vessel->name]
        ];

        $positions = $this->getPositions();

        // Get available crew members
        $availableCrewQuery = CrewMember::active()
            ->whereDoesntHave('vesselAssignments', function($q) {
                $q->where('is_active', true)
                  ->whereNull('unassigned_at');
            });

        if (!$user->hasRole('super-admin')) {
            $availableCrewQuery->where('created_by', $user->id);
        }

        $availableCrew = $availableCrewQuery->orderBy('name')->get();

        // Get crew statistics by position
        $crewByPosition = $currentCrewMembers->groupBy('position')->map(function($group) {
            return $group->count();
        });

        return view('master.vessels.show', [
            'vessel' => $vessel,
            'currentCrewMembers' => $currentCrewMembers,
            'breadcrumbs' => $breadcrumbs,
            'positions' => $positions,
            'availableCrew' => $availableCrew,
            'crewByPosition' => $crewByPosition
        ]);
    }

    /**
     * Show the form for editing the vessel
     */
    public function edit(Vessel $vessel)
    {
        $this->authorize('manage vessels');

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        if (!$isSuperAdmin && $vessel->company_id !== $user->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Vessels & Crew', 'url' => route('vessels.index')],
            ['label' => 'Edit ' . $vessel->name]
        ];

        if ($isSuperAdmin) {
            $companies = Company::active()->orderBy('name')->get();
            $coordinators = User::active()->where('company_id', $vessel->company_id)->orderBy('name')->get();
        } else {
            $companies = Company::where('id', $user->company_id)->get();
            $coordinators = User::active()->where('company_id', $user->company_id)->orderBy('name')->get();
        }

        $vesselTypes = $this->getVesselTypes();

        return view('master.vessels.create', compact('vessel', 'breadcrumbs', 'companies', 'coordinators', 'vesselTypes'));
    }

    /**
     * Update the specified vessel
     */
    public function update(VesselRequest $request, Vessel $vessel)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        if (!$isSuperAdmin && $vessel->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            if (!$isSuperAdmin) {
                unset($data['company_id']);
            }

            $data['is_active'] = $request->has('is_active') ? true : false;

            $vessel->update($data);

            $this->clearVesselCache($vessel->id);

            DB::commit();

            Log::info('Vessel updated', [
                'vessel_id' => $vessel->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vessel updated successfully',
                'redirect' => route('vessels.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vessel update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vessel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified vessel
     */
    public function destroy(Vessel $vessel)
    {
        $user = auth()->user();
        if (!$user->hasRole('super-admin') && $vessel->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Check if vessel has active crew assignments
            if ($vessel->activeAssignments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete vessel with active crew assignments. Please unassign all crew members first.'
                ], 422);
            }

            $vesselName = $vessel->name;
            $vessel->delete();

            $this->clearVesselCache($vessel->id);

            DB::commit();

            Log::info('Vessel deleted', [
                'vessel_id' => $vessel->id,
                'vessel_name' => $vesselName,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vessel deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vessel deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vessel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle vessel active status
     */
    public function toggleStatus(Vessel $vessel)
    {
        $user = auth()->user();
        if (!$user->hasRole('super-admin') && $vessel->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $newStatus = !$vessel->is_active;
            $vessel->update(['is_active' => $newStatus]);

            $this->clearVesselCache($vessel->id);

            Log::info('Vessel status toggled', [
                'vessel_id' => $vessel->id,
                'new_status' => $newStatus,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vessel status updated successfully',
                'is_active' => $vessel->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    // ========================================
    // CREW ASSIGNMENT METHODS
    // ========================================

    /**
     * Assign crew member to vessel
     */
    public function assignCrew(Request $request, Vessel $vessel)
    {
        $user = auth()->user();
        if (!$user->hasRole('super-admin') && $vessel->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'crew_member_id' => 'required|exists:crew_members,id',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $crewMember = CrewMember::findOrFail($request->crew_member_id);

            // Check if crew already assigned to any vessel
            if ($crewMember->isAssignedToVessel()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member is already assigned to another vessel. Please unassign or use transfer instead.'
                ], 422);
            }

            // Assign crew to vessel
            $assignment = $vessel->assignCrewMember(
                $request->crew_member_id,
                auth()->id(),
                $request->notes
            );

            $this->clearVesselCache($vessel->id);

            DB::commit();

            Log::info('Crew assigned to vessel', [
                'assignment_id' => $assignment->id,
                'crew_id' => $request->crew_member_id,
                'vessel_id' => $vessel->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member assigned successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign crew member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unassign crew member from vessel
     */
    public function unassignCrew(Request $request, Vessel $vessel, CrewMember $crewMember)
    {
        $user = auth()->user();
        if (!$user->hasRole('super-admin') && $vessel->company_id !== $user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $result = $vessel->unassignCrewMember($crewMember->id, $request->notes);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Crew member is not assigned to this vessel'
                ], 422);
            }

            $this->clearVesselCache($vessel->id);

            DB::commit();

            Log::info('Crew unassigned from vessel', [
                'crew_id' => $crewMember->id,
                'vessel_id' => $vessel->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member unassigned successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew unassignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unassign crew member: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get vessel and crew statistics
     */
    protected function getVesselStats($user, $isSuperAdmin)
    {
        $scopeKey = $isSuperAdmin ? 'global' : 'company_' . $user->company_id;
        $cacheKey = "{$this->cacheKey}.stats.{$scopeKey}";

        return Cache::remember($cacheKey, $this->cacheDuration, function() use ($user, $isSuperAdmin) {
            $vesselQuery = Vessel::query();
            $crewQuery = CrewMember::query();
            $assignmentQuery = VesselAssignment::query();

            if (!$isSuperAdmin) {
                $vesselQuery->where('company_id', $user->company_id);
                $crewQuery->where('created_by', $user->id);
                $assignmentQuery->whereHas('vessel', function($q) use ($user) {
                    $q->where('company_id', $user->company_id);
                });
            }

            return [
                'total_vessels' => (clone $vesselQuery)->count(),
                'active_vessels' => (clone $vesselQuery)->active()->count(),
                'inactive_vessels' => (clone $vesselQuery)->inactive()->count(),
                'expiring_soon' => (clone $vesselQuery)->active()->expiringSoon(30)->count(),
                'expired' => (clone $vesselQuery)->expired()->count(),

                'total_crew' => (clone $crewQuery)->count(),
                'active_crew' => (clone $crewQuery)->active()->count(),
                'assigned_crew' => (clone $assignmentQuery)->active()->count(),
                'unassigned_crew' => (clone $crewQuery)->active()->unassigned()->count(),
            ];
        });
    }

    /**
     * Get vessel types
     */
    protected function getVesselTypes()
    {
        return [
            'AHT/S' => 'AHT/S',
            'Tug Boat' => 'Tug Boat',
            'Mooring Boat' => 'Mooring Boat',
            'Utility Boat' => 'Utility Boat',
            'Crew Boat' => 'Crew Boat',
            'Small Marine' => 'Small Marine',
            'LCT' => 'LCT',
            'Barge' => 'Barge',
            'Other' => 'Other',
        ];
    }

    /**
     * Get crew positions
     */
    protected function getPositions()
    {
        return [
            'Captain' => 'Captain',
            'Chief Officer' => 'Chief Officer',
            'Second Officer' => 'Second Officer',
            'Third Officer' => 'Third Officer',
            'Chief Engineer' => 'Chief Engineer',
            'Second Engineer' => 'Second Engineer',
            'Third Engineer' => 'Third Engineer',
            'Bosun' => 'Bosun',
            'AB (Able Seaman)' => 'AB (Able Seaman)',
            'OS (Ordinary Seaman)' => 'OS (Ordinary Seaman)',
            'Oiler' => 'Oiler',
            'Fitter' => 'Fitter',
            'Cook' => 'Cook',
            'Steward' => 'Steward',
            'Operator' => 'Operator',
            'Driver' => 'Driver',
            'Rigger' => 'Rigger',
            'Helper' => 'Helper',
            'Others' => 'Others',
        ];
    }

    /**
     * Clear vessel cache
     */
    protected function clearVesselCache($vesselId = null)
    {
        Cache::forget("{$this->cacheKey}.stats.global");

        if (auth()->check()) {
            Cache::forget("{$this->cacheKey}.stats.company_" . auth()->user()->company_id);
        }

        if ($vesselId) {
            Cache::forget("{$this->cacheKey}.{$vesselId}");
        }
    }
}
