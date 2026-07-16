<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\UnsafeCondition;
use App\Http\Requests\Master\UnsafeConditionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class UnsafeConditionController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'unsafe_conditions';
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Display a listing of unsafe conditions.
     */
    public function index(Request $request)
    {
        $this->authorize('manage unsafe conditions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Conditions']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        return view('master.unsafe-conditions.index', compact('breadcrumbs'));
    }

    /**
     * Get DataTables data
     */
    protected function getDatatablesData(Request $request)
    {
        $query = UnsafeCondition::query()->select([
            'id', 'code', 'description', 'usage_count', 'is_active', 'created_at'
        ]);

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', function($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->editColumn('usage_count', function($row) {
                return '<span class="badge badge-info">' . number_format($row->usage_count) . '</span>';
            })
            ->addColumn('action', function($unsafeCondition) {
                return view('master.unsafe-conditions.partials.actions', compact('unsafeCondition'))->render();
            })
            ->rawColumns(['status', 'usage_count', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('manage unsafe conditions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Conditions', 'url' => route('unsafe-conditions.index')],
            ['label' => 'Create']
        ];

        return view('master.unsafe-conditions.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnsafeConditionRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['usage_count'] = 0; // Default

            $unsafeCondition = UnsafeCondition::create($data);

            // Clear cache (if lists are cached in future)
            $this->clearCache();

            DB::commit();

            Log::info('Unsafe Condition created', ['id' => $unsafeCondition->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Condition created successfully',
                'redirect' => route('unsafe-conditions.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unsafe Condition creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UnsafeCondition $unsafeCondition)
    {
        $this->authorize('manage unsafe conditions');
        // Cache the specific record details
        $data = Cache::remember(
            "{$this->cacheKey}.{$unsafeCondition->id}",
            $this->cacheDuration,
            function() use ($unsafeCondition) {
                return $unsafeCondition;
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Conditions', 'url' => route('unsafe-conditions.index')],
            ['label' => $data->code]
        ];

        return view('master.unsafe-conditions.show', [
            'unsafeCondition' => $data,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnsafeCondition $unsafeCondition)
    {
        $this->authorize('manage unsafe conditions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Conditions', 'url' => route('unsafe-conditions.index')],
            ['label' => 'Edit ' . $unsafeCondition->code]
        ];

        return view('master.unsafe-conditions.create', compact('unsafeCondition', 'breadcrumbs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnsafeConditionRequest $request, UnsafeCondition $unsafeCondition)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $unsafeCondition->update($data);

            // Clear specific cache
            $this->clearCache($unsafeCondition->id);

            DB::commit();

            Log::info('Unsafe Condition updated', ['id' => $unsafeCondition->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Condition updated successfully',
                'redirect' => route('unsafe-conditions.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unsafe Condition update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnsafeCondition $unsafeCondition)
    {
        try {
            DB::beginTransaction();

            // Optional: Check usage logic could go here
            if ($unsafeCondition->usage_count > 0) {
                 // Logic to prevent delete or just allow soft delete
            }

            $unsafeCondition->delete();

            // Clear specific cache
            $this->clearCache($unsafeCondition->id);

            DB::commit();

            Log::info('Unsafe Condition deleted', ['id' => $unsafeCondition->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Condition deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unsafe Condition deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle status.
     */
    public function toggleStatus(UnsafeCondition $unsafeCondition)
    {
        try {
            $unsafeCondition->update(['is_active' => !$unsafeCondition->is_active]);

            // Clear specific cache
            $this->clearCache($unsafeCondition->id);

            Log::info('Unsafe Condition status toggled', ['id' => $unsafeCondition->id, 'status' => $unsafeCondition->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'is_active' => $unsafeCondition->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    /**
     * Helper to clear cache
     */
    protected function clearCache($id = null)
    {
        if ($id) {
            Cache::forget("{$this->cacheKey}.{$id}");
        }
    }
}
