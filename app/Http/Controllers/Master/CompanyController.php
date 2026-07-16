<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Requests\Master\CompanyRequest;

class CompanyController extends Controller
{
    use AuthorizesRequests;
    protected $cacheKey = 'companies';
    protected $cacheDuration = 3600; // 1 hour

    /**
     * Display a listing of companies
     */
    public function index(Request $request)
    {
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Companies']
        ];

        if ($request->ajax()) {
            return $this->getDatatablesData($request);
        }

        // Get statistics for dashboard cards
        $stats = $this->getCompanyStats();

        return view('master.companies.index', compact('breadcrumbs', 'stats'));
    }

    /**
     * Get DataTables data with caching
     */
    protected function getDatatablesData(Request $request)
    {
        $query = Company::with(['users' => function($q) {
                $q->select('id', 'company_id')->where('is_active', true);
            }])
            ->select([
                'id',
                'name',
                'is_active',
                'created_at',
                'logo'
            ]);

        // Apply search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->search($search);
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', (int) $request->status);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('logo_display', function($company) {
                if ($company->logo) {
                    return '<img src="' . Storage::url($company->logo) . '" class="rounded-circle" width="40" height="40" alt="Logo">';
                }
                return '<div class="avatar avatar-sm rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-white fw-bold">' . strtoupper(substr($company->name, 0, 2)) . '</span>
                        </div>';
            })
            ->addColumn('status', function($company) {
                $badge = $company->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
                return $badge;
            })
            ->addColumn('users_count', function($company) {
                $count = $company->users->count();
                return '<span class="badge badge-info">' . $count . ' user' . ($count != 1 ? 's' : '') . '</span>';
            })
            ->addColumn('action', function($company) {
                return view('master.companies.partials.actions', compact('company'))->render();
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['logo_display', 'status', 'users_count', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new company
     */
    public function create()
    {
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Companies', 'url' => route('companies.index')],
            ['label' => 'Create']
        ];

        return view('master.companies.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created company
     */
    public function store(CompanyRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle checkbox is_active (jika tidak dicentang, set false)
            $data['is_active'] = $request->has('is_active') ? true : false;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $data['logo'] = $this->handleLogoUpload($request->file('logo'));
            }

            $company = Company::create($data);

            // Clear cache
            $this->clearCompanyCache();

            DB::commit();

            Log::info('Company created', ['company_id' => $company->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'redirect' => route('companies.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company creation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create company: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        // Cache company details
        $companyData = Cache::remember(
            "{$this->cacheKey}.{$company->id}",
            $this->cacheDuration,
            function() use ($company) {
                return $company->load([
                    'users' => function($q) {
                        $q->active()->latest()->take(10);
                    },
                    'areas' => function($q) {
                        $q->latest()->take(5);
                    },
                    'vessels' => function($q) {
                        $q->latest()->take(5);
                    }
                ]);
            }
        );

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Companies', 'url' => route('companies.index')],
            ['label' => $company->name]
        ];

        return view('master.companies.show', [
            'company' => $companyData,
            'breadcrumbs' => $breadcrumbs
        ]);
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company)
    {
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Companies', 'url' => route('companies.index')],
            ['label' => 'Edit ' . $company->name]
        ];

        return view('master.companies.create', compact('company', 'breadcrumbs'));
    }

    /**
     * Update the specified company
     */
    public function update(CompanyRequest $request, Company $company)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle checkbox is_active
            $data['is_active'] = $request->has('is_active') ? true : false;

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                    Storage::disk('public')->delete($company->logo);
                }
                $data['logo'] = $this->handleLogoUpload($request->file('logo'));
            }

            $company->update($data);

            // Clear cache
            $this->clearCompanyCache($company->id);

            DB::commit();

            Log::info('Company updated', ['company_id' => $company->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully',
                'redirect' => route('companies.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update company: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified company (Soft Delete)
     */
    public function destroy(Company $company)
    {
        try {
            DB::beginTransaction();

            // Check if company has active users
            if ($company->users()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete company with active users'
                ], 422);
            }

            $company->delete();

            // Clear cache
            $this->clearCompanyCache($company->id);

            DB::commit();

            Log::info('Company deleted', ['company_id' => $company->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company deletion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete company: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted company
     */
    public function restore($id)
    {
        try {
            $company = Company::withTrashed()->findOrFail($id);
            $company->restore();

            $this->clearCompanyCache();

            Log::info('Company restored', ['company_id' => $company->id, 'user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Company restored successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Company restoration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore company: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle company status
     */
    public function toggleStatus(Company $company)
    {
        try {
            $company->update(['is_active' => !$company->is_active]);

            $this->clearCompanyCache($company->id);

            return response()->json([
                'success' => true,
                'message' => 'Company status updated successfully',
                'is_active' => $company->is_active
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
     * Handle logo upload with optimization
     */
    protected function handleLogoUpload($file)
    {
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Store original
        $path = $file->storeAs('companies/logos', $filename, 'public');

        // Optimize image (resize to max 400x400, maintain aspect ratio)
        $fullPath = storage_path('app/public/' . $path);

        try {
            // Create ImageManager instance with GD driver
            $manager = new ImageManager(new Driver());

            // Read image from file system
            $image = $manager->read($fullPath);

            // Scale down if image is larger than 400px
            if ($image->width() > 400 || $image->height() > 400) {
                $image->scale(width: 400, height: 400);
            }

            // Save optimized image with 85% quality
            $image->save($fullPath, quality: 85);

        } catch (\Exception $e) {
            Log::warning('Image optimization failed', ['error' => $e->getMessage()]);
            // Continue anyway with original image
        }

        return $path;
    }

    /**
     * Get company statistics with caching
     */
    protected function getCompanyStats()
    {
        return Cache::remember("{$this->cacheKey}.stats", $this->cacheDuration, function() {
            return [
                'total' => Company::count(),
                'active' => Company::active()->count(),
                'inactive' => Company::where('is_active', false)->count(),
                'with_users' => Company::has('users')->count(),
            ];
        });
    }

    /**
     * Clear company cache
     */
    protected function clearCompanyCache($companyId = null)
    {
        Cache::forget("{$this->cacheKey}.stats");

        if ($companyId) {
            Cache::forget("{$this->cacheKey}.{$companyId}");
        }
    }

    public function editProfile()
    {
        try {
            $user = Auth::user();

            // Otorisasi: Cek apakah user punya perusahaan DAN role yang sesuai
            if (!$user->company_id || !$user->hasAnyRole(['koordinator-kapal', 'super-admin'])) {
                Log::warning("Unauthorized access attempt to editProfile by user: {$user->id}");
                return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }

            // Ambil data company melalui relationship
            $company = $user->company;

            if (!$company) {
                Log::error("Company not found for user ID: {$user->id}, company_id: {$user->company_id}");
                return redirect()->route('dashboard')->with('error', 'Data perusahaan tidak ditemukan.');
            }

            // Breadcrumbs khusus halaman profil
            $breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Company Profile', 'url' => '#'],
                ['label' => 'Edit']
            ];

            return view('master.companies.edit-profile', compact('company', 'breadcrumbs'));

        } catch (Exception $e) {
            Log::error("Error in editProfile: " . $e->getMessage(), ['user_id' => Auth::id()]);
            return redirect()->route('dashboard')->with('error', 'Terjadi kesalahan saat memuat halaman.');
        }
    }

    /**
     * Memperbarui data profil perusahaan tempat user terdaftar.
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            // Otorisasi
            if (!$user->company_id || !$user->hasAnyRole(['koordinator-kapal', 'super-admin'])) {
                if ($request->ajax()) return response()->json(['error' => 'Unauthorized'], 403);
                abort(403);
            }

            $company = $user->company;

            if (!$company) {
                if ($request->ajax()) return response()->json(['error' => 'Company not found'], 404);
                return redirect()->route('dashboard')->with('error', 'Data perusahaan tidak ditemukan.');
            }

            // Validasi Input (Spesifik untuk update profile mandiri)
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
                'address' => 'required|string|max:500',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            DB::beginTransaction();

            // Handle upload logo
            if ($request->hasFile('logo')) {
                // Hapus logo lama
                if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                    Storage::disk('public')->delete($company->logo);
                }
                // Upload & Optimize baru
                $validated['logo'] = $this->handleLogoUpload($request->file('logo'));
            }

            // Update data
            $company->update($validated);

            // Clear Cache
            $this->clearCompanyCache($company->id);

            DB::commit();

            Log::info('Company profile updated by user', ['company_id' => $company->id, 'user_id' => $user->id]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profil perusahaan berhasil diperbarui!',
                    'logo_url' => $company->logo ? Storage::url($company->logo) : null
                ]);
            }

            return redirect()->route('companies.edit-profile')->with('success', 'Profil perusahaan berhasil diperbarui!');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Database error updating company profile: " . $e->getMessage());

            $msg = 'Terjadi kesalahan database.';
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 500);
            return back()->withErrors(['error' => $msg]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error updating company profile: " . $e->getMessage());

            $msg = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 500);
            return back()->withErrors(['error' => $msg]);
        }
    }
}
