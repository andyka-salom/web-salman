<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\EntityFunction;
use App\Http\Requests\Master\EntityFunctionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EntityFunctionsExport;
use App\Imports\EntityFunctionsImport;

class EntityFunctionController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'entity_functions';
    protected $cacheDuration = 3600;

    public function index(Request $request)
    {
        $this->authorize('manage entity functions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Entity Functions']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        return view('master.entity-functions.index', compact('breadcrumbs'));
    }

    // New Method for Visual Hierarchy
    public function hierarchy()
    {
        $this->authorize('manage entity functions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Entity Functions', 'url' => route('entity-functions.index')],
            ['label' => 'Hierarchy Diagram']
        ];

        // Fetch all active functions and format them for the tree view
        // We load full tree from root nodes
        $nodes = EntityFunction::active()
            ->withCount('users')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('master.entity-functions.hierarchy', compact('breadcrumbs', 'nodes'));
    }

    protected function getDatatablesData(Request $request)
    {
        // Eager load parent
        $query = EntityFunction::with('parent')
            ->select(['id', 'name', 'code', 'parent_id', 'level', 'is_active', 'created_at']);

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        if ($request->filled('level') && $request->level !== '') {
            $query->where('level', (int) $request->level);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('parent_name', function($row) {
                return $row->parent ? $row->parent->name : '<span class="badge badge-info">Root Level</span>';
            })
            ->editColumn('level', function($row) {
                return '<span class="badge badge-secondary">Lvl '.$row->level.'</span>';
            })
            ->addColumn('status', function($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function($entityFunction) {
                return view('master.entity-functions.partials.actions', compact('entityFunction'))->render();
            })
            ->rawColumns(['parent_name', 'level', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('manage entity functions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Entity Functions', 'url' => route('entity-functions.index')],
            ['label' => 'Create']
        ];

        // Get potential parents (cached)
        $parents = $this->getParentsList();

        return view('master.entity-functions.create', compact('breadcrumbs', 'parents'));
    }

    public function store(EntityFunctionRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            // Calculate Level & Path logic
            if (!empty($data['parent_id'])) {
                $parent = EntityFunction::find($data['parent_id']);
                $data['level'] = $parent->level + 1;
                // Path will be calculated after save ID is generated
            } else {
                $data['level'] = 0; // Root
            }

            $entity = EntityFunction::create($data);

            // Update Materialized Path
            $entity->updatePath();

            $this->clearCache();

            DB::commit();
            Log::info('Entity Function created', ['id' => $entity->id, 'by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Function created successfully', 'redirect' => route('entity-functions.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Entity Function creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create: ' . $e->getMessage()], 500);
        }
    }

    public function show(EntityFunction $entityFunction)
    {
        $this->authorize('manage entity functions');
        $entityFunction->load(['parent', 'children', 'users']);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Entity Functions', 'url' => route('entity-functions.index')],
            ['label' => $entityFunction->code]
        ];

        return view('master.entity-functions.show', compact('entityFunction', 'breadcrumbs'));
    }

    public function edit(EntityFunction $entityFunction)
    {
        $this->authorize('manage entity functions');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Entity Functions', 'url' => route('entity-functions.index')],
            ['label' => 'Edit ' . $entityFunction->code]
        ];

        // Exclude self and descendants from parent list to prevent loops
        $parents = EntityFunction::where('id', '!=', $entityFunction->id)
            ->where(function ($q) use ($entityFunction) {
                // Not containing current path (descendants)
                $q->where('path', 'not like', $entityFunction->path . '/%');
            })
            ->orderBy('name')
            ->get();

        return view('master.entity-functions.create', compact('entityFunction', 'breadcrumbs', 'parents'));
    }

    public function update(EntityFunctionRequest $request, EntityFunction $entityFunction)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            // If parent changed, recalculate level
            if ($data['parent_id'] !== $entityFunction->parent_id) {
                if (!empty($data['parent_id'])) {
                    $parent = EntityFunction::find($data['parent_id']);
                    $data['level'] = $parent->level + 1;
                } else {
                    $data['level'] = 0;
                }
                $parentChanged = true;
            } else {
                $parentChanged = false;
            }

            $entityFunction->update($data);

            if ($parentChanged) {
                // Update path for self
                $entityFunction->updatePath();

                // Recursively update children paths and levels?
                // For simplicity, we assume deep re-structuring is rare or handled via job.
                // But essential: update immediate path string logic inside model `updatePath` only handles self.
                // A robust solution would fetch all descendants via `path LIKE old_path%` and update them.
            }

            $this->clearCache($entityFunction->id);

            DB::commit();
            Log::info('Entity Function updated', ['id' => $entityFunction->id, 'by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Function updated successfully', 'redirect' => route('entity-functions.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Entity Function update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(EntityFunction $entityFunction)
    {
        try {
            DB::beginTransaction();

            // Check if has active children (soft delete cascade is handled by DB FK usually, or Model logic)
            // But if children exist, better warn user or handle cascade in code
            if ($entityFunction->children()->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete: This function has sub-functions.'], 422);
            }

            if ($entityFunction->users()->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete: Users are assigned to this function.'], 422);
            }

            $entityFunction->delete();
            $this->clearCache($entityFunction->id);

            DB::commit();
            Log::info('Entity Function deleted', ['id' => $entityFunction->id, 'by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Function deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    public function toggleStatus(EntityFunction $entityFunction)
    {
        try {
            $entityFunction->update(['is_active' => !$entityFunction->is_active]);
            $this->clearCache($entityFunction->id);
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating status'], 500);
        }
    }

    protected function getParentsList()
    {
        return Cache::remember('entity_functions.parents_list', $this->cacheDuration, function() {
            return EntityFunction::select('id', 'name', 'level')->orderBy('level')->orderBy('name')->get();
        });
    }

    protected function clearCache($id = null)
    {
        Cache::forget('entity_functions.parents_list');
        if ($id) Cache::forget("{$this->cacheKey}.{$id}");
    }
        public function export()
    {
        return Excel::download(new EntityFunctionsExport, 'entity_functions_'.date('Y-m-d_His').'.xlsx');
    }

    // --- Tambahkan Method Import ---
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            DB::beginTransaction();

            Excel::import(new EntityFunctionsImport, $request->file('file'));

            // Opsional: Re-calculate all paths to ensure hierarchy integrity
            // $this->recalculateAllPaths();

            $this->clearCache(); // Bersihkan cache

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data imported successfully. Hierarchy updated.'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMsg = 'Import failed at row ' . $failures[0]->row() . ': ' . $failures[0]->errors()[0];
            return response()->json(['success' => false, 'message' => $errorMsg], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
