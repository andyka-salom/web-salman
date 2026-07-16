<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\EntityFunction;
use App\Http\Requests\Master\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yajra\DataTables\Facades\DataTables;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected $cacheDuration = 3600; // 1 hour

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('manage users');

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Users']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        // Get filter options
        $companies = $this->getCompaniesList();

        return view('master.users.index', compact('breadcrumbs', 'companies'));
    }

    /**
     * Get DataTables data for users.
     */
    protected function getDatatablesData(Request $request)
    {
        $query = User::with(['company', 'entityFunction'])
            ->select(['id', 'name', 'email', 'phone', 'jabatan', 'company_id', 'entity_function_id', 'photo_path', 'is_active', 'last_login_at']);

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        if ($request->filled('company_id') && $request->company_id !== '') {
            $query->where('company_id', $request->company_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('photo', function($row) {
                $url = $row->photo_path ? Storage::url($row->photo_path) : asset('assets/img/profile-3.jpg');
                return '<img src="'.$url.'" class="rounded-circle" width="40" height="40" alt="avatar" style="object-fit: cover;">';
            })
            ->addColumn('company_name', function($row) {
                return $row->company->name ?? '-';
            })
            ->addColumn('role', function(User $user) {
                $roles = $user->roles->pluck('name')->map(fn($r) => ucfirst($r))->implode(', ');
                return $roles ?: '-';
            })
            ->editColumn('last_login_at', function($row) {
                return $row->last_login_at ? $row->last_login_at->diffForHumans() : 'Never';
            })
            ->addColumn('status', function($row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function($user) {
                return view('master.users.partials.actions', compact('user'))->render();
            })
            ->rawColumns(['photo', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Users', 'url' => route('users.index')],
            ['label' => 'Create']
        ];

        $companies = $this->getCompaniesList();
        $functions = $this->getEntityFunctionsList();
        $roles = $this->getRolesList();

        return view('master.users.create', compact('breadcrumbs', 'companies', 'functions', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['password'] = Hash::make($request->password);
            $data['is_active'] = $request->has('is_active');

            if ($request->hasFile('photo')) {
                $data['photo_path'] = $this->handlePhotoUpload($request->file('photo'));
            }

            $user = User::create($data);

            // Sync Roles
            $roleIds = $request->input('roles', []);
            if (!empty($roleIds)) {
                $rolesToSync = Role::whereIn('id', $roleIds)->get();
                $user->syncRoles($rolesToSync);
            }

            DB::commit();

            Log::info('User created', ['id' => $user->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'redirect' => route('users.index')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat user. Silakan periksa kembali data Anda.'
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['company', 'entityFunction', 'roles']);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Users', 'url' => route('users.index')],
            ['label' => $user->name]
        ];

        return view('master.users.show', compact('user', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Users', 'url' => route('users.index')],
            ['label' => 'Edit ' . $user->name]
        ];

        $companies = $this->getCompaniesList();
        $functions = $this->getEntityFunctionsList();
        $roles = $this->getRolesList();

        // Ambil ID roles yang sudah dimiliki user
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('master.users.create', compact('user', 'breadcrumbs', 'companies', 'functions', 'roles', 'userRoles'));
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            $data['is_active'] = $request->has('is_active');

            if ($request->hasFile('photo')) {
                if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                    Storage::disk('public')->delete($user->photo_path);
                }
                $data['photo_path'] = $this->handlePhotoUpload($request->file('photo'));
            }

            $user->update($data);

            // Sync Roles
            $roleIds = $request->input('roles', []);
            $rolesToSync = Role::whereIn('id', $roleIds)->get();
            $user->syncRoles($rolesToSync);

            DB::commit();

            Log::info('User updated', ['id' => $user->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate',
                'redirect' => route('users.index')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate user. Silakan periksa kembali data Anda.'
            ], 500);
        }
    }

    /**
     * Remove the specified user (Hard Delete).
     */
    public function destroy(User $user)
    {
        try {
            DB::beginTransaction();

            // Delete photo if exists
            if ($user->photo_path && Storage::disk('public')->exists($user->photo_path)) {
                Storage::disk('public')->delete($user->photo_path);
            }

            $userId = $user->id;
            $user->forceDelete();

            DB::commit();

            Log::info('User permanently deleted', ['id' => $userId, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user)
    {
        try {
            $user->update(['is_active' => !$user->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    // --- Helpers ---

    /**
     * Helper to get list of Roles from Spatie.
     */
    protected function getRolesList()
    {
        return Cache::remember('users.roles_list', $this->cacheDuration, function() {
            return Role::select('id', 'name', 'guard_name')->orderBy('name')->get();
        });
    }

    /**
     * Handle photo upload and optimization.
     */
    protected function handlePhotoUpload($file)
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('users/photos', $filename, 'public');
        $fullPath = storage_path('app/public/' . $path);

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($fullPath);
            if ($image->width() > 300) {
                $image->scale(width: 300);
            }
            $image->save($fullPath, quality: 80);
        } catch (\Exception $e) {
            Log::warning('Image optimization failed', ['error' => $e->getMessage()]);
        }

        return $path;
    }

    /**
     * Get list of active companies.
     */
    protected function getCompaniesList()
    {
        return Cache::remember('users.companies_list', $this->cacheDuration, function() {
            return Company::select('id', 'name')->active()->orderBy('name')->get();
        });
    }

    /**
     * Get list of entity functions.
     */
    protected function getEntityFunctionsList()
    {
        return Cache::remember('users.functions_list', $this->cacheDuration, function() {
            return EntityFunction::select('id', 'name')->orderBy('name')->get();
        });
    }
}
