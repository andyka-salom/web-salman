<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CoverTemplate;
use App\Http\Requests\Master\CoverTemplateRequest;
use App\Jobs\OptimizeCoverImage; // Pastikan Job ini sudah dibuat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yajra\DataTables\Facades\DataTables;

class CoverTemplateController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('manage cover templates');

        if ($request->ajax()) {
            $query = CoverTemplate::query()
                ->select(['id', 'name', 'slug', 'cover_image_path', 'is_active', 'usage_count', 'created_at']);

            if ($request->filled('status')) {
                $query->where('is_active', (int) $request->status);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('cover_image_path', function($row) {
                    $url = $row->cover_url; // Pastikan accessor ini ada di Model
                    return '<img src="'.$url.'" loading="lazy" class="rounded shadow-sm" style="width: 50px; height: 70px; object-fit: cover; border: 1px solid #ddd;">';
                })
                ->addColumn('status', function($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-danger">Inactive</span>';
                })
                // PERUBAHAN DISINI: Menggunakan View Partial
                ->addColumn('action', function($coverTemplate) {
                    return view('master.cover-templates.partials.actions', compact('coverTemplate'))->render();
                })
                ->rawColumns(['cover_image_path', 'status', 'action'])
                ->make(true);
        }

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Cover Templates']
        ];

        return view('master.cover-templates.index', compact('breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('manage cover templates');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Cover Templates', 'url' => route('cover-templates.index')],
            ['label' => 'Create']
        ];
        return view('master.cover-templates.create', compact('breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CoverTemplateRequest $request)
    {
        // Variabel untuk melacak file yang diupload agar bisa dihapus jika DB gagal
        $uploadedPaths = [];

        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');
            $data['slug'] = $this->generateUniqueSlug($request->name);

            // OPTIMASI 4: Upload File RAW (Mentah) di luar transaksi DB
            // Ini mencegah Database Locking saat proses I/O Disk berjalan
            if ($request->hasFile('cover_image')) {
                $path = $this->uploadRaw($request->file('cover_image'), 'cover');
                $data['cover_image_path'] = $path;
                $uploadedPaths[] = $path;
            }
            if ($request->hasFile('page_image')) {
                $path = $this->uploadRaw($request->file('page_image'), 'page');
                $data['page_image_path'] = $path;
                $uploadedPaths[] = $path;
            }

            // OPTIMASI 5: Transaksi Database (Cepat)
            $template = DB::transaction(function() use ($data) {
                return CoverTemplate::create($data);
            });

            // OPTIMASI 6: Dispatch Job untuk Resize Gambar
            // dispatchAfterResponse: Job jalan setelah respon "Success" dikirim ke browser
            foreach($uploadedPaths as $path) {
                OptimizeCoverImage::dispatchAfterResponse($path);
            }

            Log::info('Cover Template created', ['id' => $template->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'redirect' => route('cover-templates.index')
            ]);

        } catch (\Exception $e) {
            // Rollback: Hapus file fisik jika insert DB gagal
            foreach($uploadedPaths as $path) {
                if(Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Template creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CoverTemplate $coverTemplate)
    {
        $this->authorize('manage cover templates');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Cover Templates', 'url' => route('cover-templates.index')],
            ['label' => $coverTemplate->name]
        ];
        return view('master.cover-templates.show', compact('coverTemplate', 'breadcrumbs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoverTemplate $coverTemplate)
    {
        $this->authorize('manage cover templates');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Cover Templates', 'url' => route('cover-templates.index')],
            ['label' => 'Edit']
        ];
        return view('master.cover-templates.create', compact('coverTemplate', 'breadcrumbs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CoverTemplateRequest $request, CoverTemplate $coverTemplate)
    {
        $newFiles = []; // File baru yang berhasil diupload
        $oldFiles = []; // File lama yang harus dihapus setelah sukses

        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active');

            // Handle Files Update
            if ($request->hasFile('cover_image')) {
                $oldFiles[] = $coverTemplate->cover_image_path;
                $path = $this->uploadRaw($request->file('cover_image'), 'cover');
                $data['cover_image_path'] = $path;
                $newFiles[] = $path;
            }

            if ($request->hasFile('page_image')) {
                $oldFiles[] = $coverTemplate->page_image_path;
                $path = $this->uploadRaw($request->file('page_image'), 'page');
                $data['page_image_path'] = $path;
                $newFiles[] = $path;
            }

            // Update Slug Check
            if ($request->name !== $coverTemplate->name) {
                $data['slug'] = $this->generateUniqueSlug($request->name, $coverTemplate->id);
            } else {
                unset($data['name']);
            }

            // Transaksi Database
            DB::transaction(function() use ($coverTemplate, $data) {
                $coverTemplate->update($data);
            });

            // Dispatch Job Optimasi untuk file baru
            foreach($newFiles as $path) {
                OptimizeCoverImage::dispatchAfterResponse($path);
            }

            // Bersihkan file lama (Safe delete)
            foreach($oldFiles as $oldPath) {
                if($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            Log::info('Cover Template updated', ['id' => $coverTemplate->id, 'by' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'redirect' => route('cover-templates.index')
            ]);

        } catch (\Exception $e) {
            // Jika error, hapus file BARU yang terlanjur diupload
            foreach($newFiles as $path) {
                if(Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoverTemplate $coverTemplate)
    {
        $this->authorize('manage cover templates');
        try {
            // Opsional: Hapus file fisik (bisa juga via Observer atau Job)
            // if ($coverTemplate->cover_image_path) Storage::disk('public')->delete($coverTemplate->cover_image_path);
            // if ($coverTemplate->page_image_path) Storage::disk('public')->delete($coverTemplate->page_image_path);

            $coverTemplate->delete();
            return response()->json(['success' => true, 'message' => 'Template deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle Active Status
     */
    public function toggleStatus(CoverTemplate $coverTemplate)
    {
        $this->authorize('manage cover templates');
        try {
            $coverTemplate->update(['is_active' => !$coverTemplate->is_active]);
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Upload Raw File (Fastest Method)
     */
    protected function uploadRaw($file, $type)
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        // storeAs melakukan streaming file, efisien memori
        return $file->storeAs("templates/{$type}s", $filename, 'public');
    }

    /**
     * Helper: Generate Slug
     */
    protected function generateUniqueSlug($name, $ignoreId = null)
    {
        $slug = Str::slug($name);
        // Jika nama sederhana dan unik, query ini sangat ringan
        $count = 1;
        $original = $slug;
        while (CoverTemplate::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $original . '-' . $count++;
        }
        return $slug;
    }
}
