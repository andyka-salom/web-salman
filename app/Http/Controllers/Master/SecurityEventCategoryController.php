<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SecurityEventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Master\SecurityEventCategoryRequest;

class SecurityEventCategoryController extends Controller
{
    protected $cacheKey = 'security_event_categories';
    protected $cacheDuration = 3600; // 1 hour
    use AuthorizesRequests;
    /**
     * Display a listing of security event categories
     */
    public function index(Request $request)
    {
        $this->authorize('manage security event categories');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Security Event Categories']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        // Get statistics for dashboard cards
        $stats = $this->getCategoryStats();

        return view('master.security-event-categories.index', compact('breadcrumbs', 'stats'));
    }

    /**
     * Get DataTables data with caching
     */
    protected function getDatatablesData(Request $request)
    {
        $query = SecurityEventCategory::select([
            'id',
            'code',
            'name',
            'description',
            'is_active',
            'created_at'
        ]);

        // Apply search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', function($category) {
                $badge = $category->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
                return $badge;
            })
            ->addColumn('description_short', function($category) {
                return $category->description
                    ? \Illuminate\Support\Str::limit($category->description, 50)
                    : '-';
            })
            ->addColumn('action', function($category) {
                return view('master.security-event-categories.partials.actions', compact('category'))->render();
            })
            ->filterColumn('code', function($query, $keyword) {
                $query->where('code', 'like', "%{$keyword}%");
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $this->authorize('manage security event categories');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Security Event Categories', 'url' => route('security-event-categories.index')],
            ['label' => 'Create']
        ];

        return view('master.security-event-categories.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created category
     */
    public function store(SecurityEventCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? true : false;

            $category = SecurityEventCategory::create($data);

            // Clear cache
            $this->clearCategoryCache();

            DB::commit();

            Log::info('manage Security Event Category created', [
                'category_id' => $category->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security Event Category created successfully',
                'redirect' => route('security-event-categories.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Security Event Category creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show(SecurityEventCategory $securityEventCategory)
    {
        $this->authorize('manage security event categories');
        // Cache category details
        $categoryData = Cache::remember(
            "{$this->cacheKey}.{$securityEventCategory->id}",
            $this->cacheDuration,
            function() use ($securityEventCategory) {
                return $securityEventCategory;
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Security Event Categories', 'url' => route('security-event-categories.index')],
            ['label' => $securityEventCategory->name]
        ];

        return view('master.security-event-categories.show', [
            'category' => $categoryData,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(SecurityEventCategory $securityEventCategory)
    {
        $this->authorize('manage security event categories');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Security Event Categories', 'url' => route('security-event-categories.index')],
            ['label' => 'Edit ' . $securityEventCategory->name]
        ];

        return view('master.security-event-categories.create', [
            'category' => $securityEventCategory,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(SecurityEventCategoryRequest $request, SecurityEventCategory $securityEventCategory)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? true : false;

            $securityEventCategory->update($data);

            // Clear cache
            $this->clearCategoryCache($securityEventCategory->id);

            DB::commit();

            Log::info('Security Event Category updated', [
                'category_id' => $securityEventCategory->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security Event Category updated successfully',
                'redirect' => route('security-event-categories.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Security Event Category update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category (Soft Delete)
     */
    public function destroy(SecurityEventCategory $securityEventCategory)
    {
        try {
            DB::beginTransaction();

            $securityEventCategory->delete();

            // Clear cache
            $this->clearCategoryCache($securityEventCategory->id);

            DB::commit();

            Log::info('Security Event Category deleted', [
                'category_id' => $securityEventCategory->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security Event Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Security Event Category deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted category
     */
    public function restore($id)
    {
        try {
            $category = SecurityEventCategory::withTrashed()->findOrFail($id);
            $category->restore();

            $this->clearCategoryCache();

            Log::info('Security Event Category restored', [
                'category_id' => $category->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Security Event Category restored successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Security Event Category restoration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(SecurityEventCategory $securityEventCategory)
    {
        try {
            $securityEventCategory->update(['is_active' => !$securityEventCategory->is_active]);

            $this->clearCategoryCache($securityEventCategory->id);

            return response()->json([
                'success' => true,
                'message' => 'Category status updated successfully',
                'is_active' => $securityEventCategory->is_active
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
     * Get category statistics with caching
     */
    protected function getCategoryStats()
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, function() {
            return [
                'total' => SecurityEventCategory::count(),
                'active' => SecurityEventCategory::where('is_active', true)->count(),
                'inactive' => SecurityEventCategory::where('is_active', false)->count(),
            ];
        });
    }

    /**
     * Clear category cache
     */
    protected function clearCategoryCache($categoryId = null)
    {
        Cache::forget("{$this->cacheKey}.stats");

        if ($categoryId) {
            Cache::forget("{$this->cacheKey}.{$categoryId}");
        }
    }
}
