<?php

namespace App\Http\Controllers\Master;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Models\UnsafeAct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Master\UnsafeActRequest;

class UnsafeActController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'unsafe_acts';
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Display a listing of unsafe acts
     */
    public function index(Request $request)
    {
        $this->authorize('manage unsafe acts');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Acts']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        // Get statistics for dashboard cards
        $stats = $this->getUnsafeActStats();

        // Get most used unsafe acts for quick view
        $mostUsed = UnsafeAct::mostUsed(5)->get();

        return view('master.unsafe-acts.index', compact('breadcrumbs', 'stats', 'mostUsed'));
    }

    /**
     * Get DataTables data
     */
    protected function getDatatablesData(Request $request)
    {
        $query = UnsafeAct::select([
            'id',
            'code',
            'description',
            'is_active',
            'usage_count',
            'created_at'
        ]);

        // Apply search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        // Apply usage filter
        if ($request->filled('usage_filter')) {
            switch ($request->usage_filter) {
                case 'most_used':
                    $query->where('usage_count', '>', 0)->orderBy('usage_count', 'desc');
                    break;
                case 'never_used':
                    $query->where('usage_count', 0);
                    break;
            }
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('status', function($unsafeAct) {
                $badge = $unsafeAct->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
                return $badge;
            })
            ->addColumn('description_short', function($unsafeAct) {
                return \Illuminate\Support\Str::limit($unsafeAct->description, 60);
            })
            ->addColumn('usage_display', function($unsafeAct) {
                $class = 'badge-secondary';
                if ($unsafeAct->usage_count > 50) {
                    $class = 'badge-danger';
                } elseif ($unsafeAct->usage_count > 20) {
                    $class = 'badge-warning';
                } elseif ($unsafeAct->usage_count > 0) {
                    $class = 'badge-info';
                }
                return '<span class="badge ' . $class . '">' . $unsafeAct->usage_count . ' times</span>';
            })
            ->addColumn('action', function($unsafeAct) {
                return view('master.unsafe-acts.partials.actions', compact('unsafeAct'))->render();
            })
            ->filterColumn('code', function($query, $keyword) {
                $query->where('code', 'like', "%{$keyword}%");
            })
            ->filterColumn('description', function($query, $keyword) {
                $query->where('description', 'like', "%{$keyword}%");
            })
            ->rawColumns(['status', 'usage_display', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new unsafe act
     */
    public function create()
    {
        $this->authorize('manage unsafe acts');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Acts', 'url' => route('unsafe-acts.index')],
            ['label' => 'Create']
        ];

        return view('master.unsafe-acts.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created unsafe act
     */
    public function store(UnsafeActRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? true : false;
            $data['usage_count'] = 0; // Always start with 0

            $unsafeAct = UnsafeAct::create($data);

            // Clear cache
            $this->clearUnsafeActCache();

            DB::commit();

            Log::info('Unsafe Act created', [
                'unsafe_act_id' => $unsafeAct->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Act created successfully',
                'redirect' => route('unsafe-acts.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unsafe Act creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create unsafe act: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified unsafe act
     */
    public function show(UnsafeAct $unsafeAct)
    {
        $this->authorize('manage unsafe acts');
        // Cache unsafe act details with related data
        $unsafeActData = Cache::remember(
            "{$this->cacheKey}.{$unsafeAct->id}",
            $this->cacheDuration,
            function() use ($unsafeAct) {
                return $unsafeAct->load(['cermatReports' => function($q) {
                    $q->latest()->take(10);
                }]);
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Acts', 'url' => route('unsafe-acts.index')],
            ['label' => $unsafeAct->code]
        ];

        return view('master.unsafe-acts.show', [
            'unsafeAct' => $unsafeActData,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Show the form for editing the specified unsafe act
     */
    public function edit(UnsafeAct $unsafeAct)
    {
        $this->authorize('manage unsafe acts');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Unsafe Acts', 'url' => route('unsafe-acts.index')],
            ['label' => 'Edit ' . $unsafeAct->code]
        ];

        return view('master.unsafe-acts.create', [
            'unsafeAct' => $unsafeAct,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Update the specified unsafe act
     */
    public function update(UnsafeActRequest $request, UnsafeAct $unsafeAct)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? true : false;

            // Don't allow updating usage_count through form
            unset($data['usage_count']);

            $unsafeAct->update($data);

            // Clear cache
            $this->clearUnsafeActCache($unsafeAct->id);

            DB::commit();

            Log::info('Unsafe Act updated', [
                'unsafe_act_id' => $unsafeAct->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Act updated successfully',
                'redirect' => route('unsafe-acts.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unsafe Act update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update unsafe act: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified unsafe act (Soft Delete)
     */
    public function destroy(UnsafeAct $unsafeAct)
    {
        try {
            DB::beginTransaction();

            // Check if unsafe act is being used
            if ($unsafeAct->usage_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete unsafe act that has been used in reports'
                ], 422);
            }

            $unsafeAct->delete();

            // Clear cache
            $this->clearUnsafeActCache($unsafeAct->id);

            DB::commit();

            Log::info('Unsafe Act deleted', [
                'unsafe_act_id' => $unsafeAct->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Act deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unsafe Act deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete unsafe act: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted unsafe act
     */
    public function restore($id)
    {
        try {
            $unsafeAct = UnsafeAct::withTrashed()->findOrFail($id);
            $unsafeAct->restore();

            $this->clearUnsafeActCache();

            Log::info('Unsafe Act restored', [
                'unsafe_act_id' => $unsafeAct->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Act restored successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Unsafe Act restoration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore unsafe act: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle unsafe act status
     */
    public function toggleStatus(UnsafeAct $unsafeAct)
    {
        try {
            $unsafeAct->update(['is_active' => !$unsafeAct->is_active]);

            $this->clearUnsafeActCache($unsafeAct->id);

            return response()->json([
                'success' => true,
                'message' => 'Unsafe Act status updated successfully',
                'is_active' => $unsafeAct->is_active
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
     * Reset usage count for an unsafe act
     */
    public function resetUsageCount(UnsafeAct $unsafeAct)
    {
        try {
            $unsafeAct->update(['usage_count' => 0]);

            $this->clearUnsafeActCache($unsafeAct->id);

            Log::info('Unsafe Act usage count reset', [
                'unsafe_act_id' => $unsafeAct->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usage count reset successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Usage count reset failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset usage count'
            ], 500);
        }
    }

    /**
     * Get unsafe act statistics with caching
     */
    protected function getUnsafeActStats()
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, function() {
            return [
                'total' => UnsafeAct::count(),
                'active' => UnsafeAct::where('is_active', true)->count(),
                'inactive' => UnsafeAct::where('is_active', false)->count(),
                'used' => UnsafeAct::where('usage_count', '>', 0)->count(),
                'never_used' => UnsafeAct::where('usage_count', 0)->count(),
                'total_usage' => UnsafeAct::sum('usage_count'),
            ];
        });
    }

    /**
     * Clear unsafe act cache
     */
    protected function clearUnsafeActCache($unsafeActId = null)
    {
        Cache::forget("{$this->cacheKey}.stats");

        if ($unsafeActId) {
            Cache::forget("{$this->cacheKey}.{$unsafeActId}");
        }
    }
}
