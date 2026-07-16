<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller; // Standard Controller
use App\Models\ActionCategory;
use App\Http\Requests\Master\ActionCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class ActionCategoryController extends Controller
    {
    use AuthorizesRequests;
    protected $cacheKey = 'action_categories';
    protected $cacheDuration = 3600; // 1 hour

    public function index(Request $request)
    {
        $this->authorize('manage action categories');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Action Categories']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        return view('master.action-categories.index', compact('breadcrumbs'));
    }

    protected function getDatatablesData(Request $request)
    {
        $query = ActionCategory::query()->select([
            'id', 'code', 'name', 'duration_range', 'priority', 'is_active', 'created_at' // Kolom diperbarui
        ]);

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            // Bagian editColumn 'color' Dihapus
            ->editColumn('duration_range', function($row) { // Menggunakan duration_range
                // Menampilkan rentang durasi (<7, 8-15, dll.)
                return $row->duration_range;
            })
            ->addColumn('status', function($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function($actionCategory) {
                return view('master.action-categories.partials.actions', compact('actionCategory'))->render();
            })
            ->rawColumns(['status', 'action']) // 'color' dihapus dari rawColumns
            ->make(true);
    }

    public function create()
    {
        $this->authorize('manage action categories');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Action Categories', 'url' => route('action-categories.index')],
            ['label' => 'Create']
        ];

        // Data rentang durasi yang tersedia (untuk digunakan di view/form)
        $durationRanges = ['<7', '8-15', '16-30', '>30'];

        return view('master.action-categories.create', compact('breadcrumbs', 'durationRanges'));
    }

    public function store(ActionCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $category = ActionCategory::create($data);

            $this->clearCache();

            DB::commit();

            Log::info('Action Category created', ['id' => $category->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'redirect' => route('action-categories.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Action Category creation failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to create: ' . $e->getMessage()], 500);
        }
    }

    public function show(ActionCategory $actionCategory)
    {
        $this->authorize('manage action categories');
        $data = Cache::remember(
            "{$this->cacheKey}.{$actionCategory->id}",
            $this->cacheDuration,
            function() use ($actionCategory) {
                return $actionCategory;
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Action Categories', 'url' => route('action-categories.index')],
            ['label' => $data->code]
        ];

        return view('master.action-categories.show', [
            'actionCategory' => $data,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    public function edit(ActionCategory $actionCategory)
    {
        $this->authorize('manage action categories');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Action Categories', 'url' => route('action-categories.index')],
            ['label' => 'Edit ' . $actionCategory->code]
        ];

        // Data rentang durasi yang tersedia (untuk digunakan di view/form)
        $durationRanges = ['<7', '8-15', '16-30', '>30'];

        return view('master.action-categories.create', compact('actionCategory', 'breadcrumbs', 'durationRanges'));
    }

    public function update(ActionCategoryRequest $request, ActionCategory $actionCategory)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            $actionCategory->update($data);

            $this->clearCache($actionCategory->id);

            DB::commit();

            Log::info('Action Category updated', ['id' => $actionCategory->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'redirect' => route('action-categories.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Action Category update failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(ActionCategory $actionCategory)
    {
        try {
            DB::beginTransaction();

            $actionCategory->delete();
            $this->clearCache($actionCategory->id);

            DB::commit();

            Log::info('Action Category deleted', ['id' => $actionCategory->id, 'by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Category deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Action Category deletion failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(ActionCategory $actionCategory)
    {
        try {
            $actionCategory->update(['is_active' => !$actionCategory->is_active]);
            $this->clearCache($actionCategory->id);

            Log::info('Action Category status toggled', ['id' => $actionCategory->id, 'status' => $actionCategory->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'is_active' => $actionCategory->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Status toggle failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error updating status'], 500);
        }
    }

    protected function clearCache($id = null)
    {
        if ($id) Cache::forget("{$this->cacheKey}.{$id}");
    }
}
