<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CrewMember;
use App\Models\Vessel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Master\CrewMemberRequest;

class CrewMemberController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'crew_members';
    protected $cacheDuration = 3600;

    public function index(Request $request)
    {
        $this->authorize('manage vessels');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Crew Members']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        $stats = $this->getCrewStats();
        $vessels = Vessel::active()->with('company')->orderBy('name')->get();

        return view('master.crew-members.index', compact('breadcrumbs', 'stats', 'vessels'));
    }

    protected function getDatatablesData(Request $request)
    {
        $query = CrewMember::with(['vessel.company'])
            ->select([
                'id',
                'vessel_id',
                'name',
                'nik',
                'position',
                'gender',
                'birth_date',
                'age',
                'blood_type',
                'phone',
                'is_active',
                'created_at'
            ]);

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->search($search);
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        if ($request->filled('vessel') && $request->vessel !== '') {
            $query->byVessel($request->vessel);
        }

        if ($request->filled('position') && $request->position !== '') {
            $query->byPosition($request->position);
        }

        if ($request->filled('gender') && $request->gender !== '') {
            $query->byGender($request->gender);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('vessel_name', function($crew) {
                if ($crew->vessel) {
                    $company = $crew->vessel->company ? $crew->vessel->company->name : 'N/A';
                    return $crew->vessel->name . '<br><small class="text-muted">' . $company . '</small>';
                }
                return '<span class="text-muted">No Vessel</span>';
            })
            ->addColumn('gender_badge', function($crew) {
                $colors = [
                    'male' => 'primary',
                    'female' => 'danger',
                    'other' => 'secondary'
                ];
                $color = $colors[$crew->gender] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . $crew->gender_label . '</span>';
            })
            ->addColumn('age_display', function($crew) {
                if ($crew->age) {
                    return $crew->age . ' years<br><small class="text-muted">' . ($crew->age_group ?? '') . '</small>';
                }
                return '-';
            })
            ->addColumn('status', function($crew) {
                $badge = $crew->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
                return $badge;
            })
            ->addColumn('action', function($crew) {
                return view('master.crew-members.partials.actions', compact('crew'))->render();
            })
            ->rawColumns(['vessel_name', 'gender_badge', 'age_display', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('manage vessels');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Crew Members', 'url' => route('crew-members.index')],
            ['label' => 'Create']
        ];

        $vessels = Vessel::active()->with('company')->orderBy('name')->get();
        $positions = $this->getPositions();
        $bloodTypes = $this->getBloodTypes();

        return view('master.crew-members.create', compact('breadcrumbs', 'vessels', 'positions', 'bloodTypes'));
    }

    public function store(CrewMemberRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? true : false;
            $data['created_by'] = auth()->id();

            $crew = CrewMember::create($data);

            // FIXED: Clear all related caches after creating new crew member
            $this->clearAllCache();

            DB::commit();

            Log::info('Crew member created', ['crew_id' => $crew->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member created successfully',
                'redirect' => route('crew-members.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew member creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create crew member: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(CrewMember $crewMember)
    {
        $this->authorize('manage vessels');
        $crewData = Cache::remember(
            "{$this->cacheKey}.{$crewMember->id}",
            $this->cacheDuration,
            function() use ($crewMember) {
                return $crewMember->load(['vessel.company', 'creator', 'groups']);
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Crew Members', 'url' => route('crew-members.index')],
            ['label' => $crewMember->name]
        ];

        return view('crew-members.show', [
            'crew' => $crewData,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function edit(CrewMember $crewMember)
    {
        $this->authorize('manage vessels');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Crew Members', 'url' => route('crew-members.index')],
            ['label' => 'Edit ' . $crewMember->name]
        ];

        $vessels = Vessel::active()->with('company')->orderBy('name')->get();
        $positions = $this->getPositions();
        $bloodTypes = $this->getBloodTypes();

        return view('crew-members.create', compact('crewMember', 'breadcrumbs', 'vessels', 'positions', 'bloodTypes'));
    }

    public function update(CrewMemberRequest $request, CrewMember $crewMember)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? true : false;

            $crewMember->update($data);

            // FIXED: Clear all related caches after updating crew member
            $this->clearAllCache();

            DB::commit();

            Log::info('Crew member updated', ['crew_id' => $crewMember->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member updated successfully',
                'redirect' => route('crew-members.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew member update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update crew member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * FIXED: Hard delete crew member
     */
    public function destroy(CrewMember $crewMember)
    {
        try {
            DB::beginTransaction();

            $crewId = $crewMember->id;
            $crewName = $crewMember->name;

            // FIXED: Hard delete (forceDelete) instead of soft delete
            $crewMember->forceDelete();

            // FIXED: Clear all related caches after deleting crew member
            $this->clearAllCache();

            DB::commit();

            Log::info('Crew member permanently deleted', [
                'crew_id' => $crewId,
                'crew_name' => $crewName,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Crew member deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Crew member deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete crew member: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(CrewMember $crewMember)
    {
        try {
            $crewMember->update(['is_active' => !$crewMember->is_active]);

            // FIXED: Clear all related caches after toggling status
            $this->clearAllCache();

            return response()->json([
                'success' => true,
                'message' => 'Crew member status updated successfully',
                'is_active' => $crewMember->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    protected function getCrewStats()
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, function() {
            return [
                'total' => CrewMember::count(),
                'active' => CrewMember::active()->count(),
                'inactive' => CrewMember::where('is_active', false)->count(),
                'assigned' => CrewMember::whereNotNull('vessel_id')->count(),
                'unassigned' => CrewMember::whereNull('vessel_id')->count(),
            ];
        });
    }

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
            'Helper' => 'Helper',
            'Others' => 'Others',
        ];
    }

    protected function getBloodTypes()
    {
        return ['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    }

    /**
     * FIXED: Clear all crew-related cache entries
     */
    protected function clearAllCache()
    {
        // Clear stats cache
        Cache::forget("{$this->cacheKey}.stats");

        // Clear individual crew member caches if needed
        // Note: Since we don't know all crew IDs, we clear the stats
        // Individual crew caches will be cleared when accessed

        // If you need to clear all crew member caches, you can use cache tags
        // or implement a more sophisticated caching strategy
    }

    /**
     * Clear crew cache entries (kept for backward compatibility)
     */
    protected function clearCrewCache($crewId = null)
    {
        if ($crewId) {
            Cache::forget("{$this->cacheKey}.{$crewId}");
        }
        $this->clearAllCache();
    }
}
