<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Http\Requests\Master\AreaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yajra\DataTables\Facades\DataTables;

class AreaController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'areas';
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Display a listing of areas
     */
    public function index(Request $request)
    {
        $this->authorize('manage areas');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Areas']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        // Get companies for filter (Cached)
        $companies = $this->getCompaniesList();

        return view('master.areas.index', compact('breadcrumbs', 'companies'));
    }

    /**
     * Get DataTables data
     */
    protected function getDatatablesData(Request $request)
    {
        $query = Area::with('company')->select(['id', 'code', 'name', 'company_id', 'is_active', 'created_at']);

        // Filter by Status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        // Filter by Company
        if ($request->filled('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('company_name', function($row) {
                return $row->company ? $row->company->name : '<span class="text-muted">- No Company -</span>';
            })
            ->addColumn('status', function($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function($area) {
                return view('master.areas.partials.actions', compact('area'))->render();
            })
            ->rawColumns(['company_name', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new area
     */
    public function create()
    {
        $this->authorize('manage areas');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Areas', 'url' => route('areas.index')],
            ['label' => 'Create']
        ];

        // Get companies for dropdown (Cached)
        $companies = $this->getCompaniesList();

        return view('master.areas.create', compact('breadcrumbs', 'companies'));
    }

    /**
     * Store a newly created area
     */
    public function store(AreaRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $area = Area::create($data);

            // Clear cache
            $this->clearAreaCache();

            DB::commit();

            Log::info('Area created', ['area_id' => $area->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Area created successfully',
                'redirect' => route('areas.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Area creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create area: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified area
     */
    public function show(Area $area)
    {
        $this->authorize('manage areas');
        // Cache area details
        $areaData = Cache::remember(
            "{$this->cacheKey}.{$area->id}",
            $this->cacheDuration,
            function() use ($area) {
                return $area->load('company');
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Areas', 'url' => route('areas.index')],
            ['label' => $area->code]
        ];

        return view('master.areas.show', [
            'area' => $areaData,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Show the form for editing the specified area
     */
    public function edit(Area $area)
    {
        $this->authorize('manage areas');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Areas', 'url' => route('areas.index')],
            ['label' => 'Edit ' . $area->code]
        ];

        // Get companies for dropdown (Cached)
        $companies = $this->getCompaniesList();

        return view('master.areas.create', compact('area', 'breadcrumbs', 'companies'));
    }

    /**
     * Update the specified area
     */
    public function update(AreaRequest $request, Area $area)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $area->update($data);

            // Clear cache
            $this->clearAreaCache($area->id);

            DB::commit();

            Log::info('Area updated', ['area_id' => $area->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Area updated successfully',
                'redirect' => route('areas.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Area update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update area: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified area
     */
    public function destroy(Area $area)
    {
        try {
            DB::beginTransaction();

            $area->delete();

            // Clear cache
            $this->clearAreaCache($area->id);

            DB::commit();

            Log::info('Area deleted', ['area_id' => $area->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Area deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Area deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle area status
     */
    public function toggleStatus(Area $area)
    {
        try {
            $area->update(['is_active' => !$area->is_active]);

            $this->clearAreaCache($area->id);

            Log::info('Area status toggled', ['area_id' => $area->id, 'status' => $area->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'is_active' => $area->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error updating status'], 500);
        }
    }

    /**
     * Get companies list for dropdowns with caching
     */
    protected function getCompaniesList()
    {
        return Cache::remember('areas.companies_list', $this->cacheDuration, function() {
            return Company::select('id', 'name')->active()->orderBy('name')->get();
        });
    }

    /**
     * Clear area cache
     */
    protected function clearAreaCache($areaId = null)
    {
        // If you had stats cache, you would clear it here like:
        // Cache::forget("{$this->cacheKey}.stats");

        if ($areaId) {
            Cache::forget("{$this->cacheKey}.{$areaId}");
        }
    }
}
