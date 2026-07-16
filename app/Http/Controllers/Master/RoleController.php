<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('manage roles');
        if ($request->ajax()) {
            $query = Role::withCount('users', 'permissions');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('created_at', function($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('permissions_count', function($row) {
                    return '<span class="badge badge-info">'.$row->permissions_count.' Permissions</span>';
                })
                ->addColumn('users_count', function($row) {
                    return '<span class="badge badge-primary">'.$row->users_count.' Users</span>';
                })
                ->addColumn('action', function($role) {
                    return view('master.roles.partials.actions', compact('role'))->render();
                })
                ->rawColumns(['permissions_count', 'users_count', 'action'])
                ->make(true);
        }

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Roles & Permissions']
        ];

        return view('master.roles.index', compact('breadcrumbs'));
    }

    public function create()
    {
        $this->authorize('manage roles');
        $permissions = Permission::all()->groupBy(function($item) {
            // Group by feature name (e.g., 'user.create' -> 'user')
            return explode('.', $item->name)[0];
        });

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Roles', 'url' => route('roles.index')],
            ['label' => 'Create']
        ];

        return view('master.roles.create', compact('permissions', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name']
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();
            Log::info('Role created', ['role' => $role->name, 'by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Role created successfully', 'redirect' => route('roles.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Role creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit(Role $role)
    {
        $this->authorize('manage roles');
        // Prevent editing super-admin role name (optional safety)
        if ($role->name === 'super-admin') {
            // return redirect()->route('roles.index')->with('error', 'Cannot edit Super Admin role.');
        }

        $permissions = Permission::all()->groupBy(function($item) {
            return explode('.', $item->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Roles', 'url' => route('roles.index')],
            ['label' => 'Edit ' . $role->name]
        ];

        return view('master.roles.create', compact('role', 'permissions', 'rolePermissions', 'breadcrumbs'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name']
        ]);

        try {
            DB::beginTransaction();

            $role->update(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->syncPermissions([]); // Remove all if empty
            }

            DB::commit();
            Log::info('Role updated', ['role' => $role->name, 'by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Role updated successfully', 'redirect' => route('roles.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return response()->json(['success' => false, 'message' => 'Cannot delete Super Admin role.'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role assigned to users.'], 422);
        }

        try {
            $role->delete();
            return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
