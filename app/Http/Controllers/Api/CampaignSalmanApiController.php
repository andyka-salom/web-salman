<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignSalman;
use App\Models\Company;
use App\Models\CoverTemplate;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Carbon\Carbon;

class CampaignSalmanApiController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get Parameters for Form (Dropdowns)
     */
    public function params()
    {
        $user = Auth::user();

        $templates = CoverTemplate::active()->orderBy('name')->select('id', 'name', 'preview_image')->get();

        // Logic Company List
        $companies = collect([]);
        if ($user->hasRole('super-admin')) {
            $companies = Company::orderBy('name')->select('id', 'name')->get();
        } elseif ($user->company_id) {
            $companies = Company::where('id', $user->company_id)->select('id', 'name')->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'templates' => $templates,
                'companies' => $companies,
                'user_company_id' => $user->company_id // Untuk referensi frontend
            ]
        ]);
    }

    /**
     * Display listing (Pagination & Filtering)
     */
    public function index(Request $request)
    {
        $this->authorize('manage campaign salman');
        $user = Auth::user();

        // Base Query with Eager Loading
        $query = CampaignSalman::with(['company:id,name', 'creator:id,name', 'coverTemplate:id,name'])
            ->select('campaign_salman.*');

        // === Authorization Logic ===
        if (!$user->hasRole('super-admin')) {
            if ($user->company_id) {
                $query->where('company_id', $user->company_id);
            } else {
                $query->where('created_by', $user->id);
            }
        }

        // === Filtering ===
        if ($request->filled('company_id') && $user->hasRole('super-admin')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tema', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        // Sorting
        $query->orderBy('tanggal', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 10);
        $data = $query->paginate($perPage);

        // Transform image paths to URLs
        $data->getCollection()->transform(function ($item) {
            $item->dokumentasi_urls = $this->mapFilesToUrls($item->dokumentasi);
            $item->daftar_hadir_urls = $this->mapFilesToUrls($item->daftar_hadir);
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store Report
     */
    public function store(Request $request)
    {
        $this->authorize('manage campaign salman');
        $user = Auth::user();

        $rules = [
            'tanggal' => 'required|date',
            'tema' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'pemateri' => 'required|string|max:255',
            'entitas' => 'required|string|max:255',
            'ringkasan' => 'required|string',
            'cover_template_id' => 'nullable|exists:cover_templates,id',
            'dokumentasi' => 'nullable|array', // Pastikan input array
            'dokumentasi.*' => 'image|max:5120',
            'daftar_hadir' => 'nullable|array',
            'daftar_hadir.*' => 'image|max:5120',
        ];

        if ($user->hasRole('super-admin')) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $validated = $request->validate($rules);

        $companyId = $user->hasRole('super-admin') ? $validated['company_id'] : $user->company_id;

        try {
            $dokumentasiPaths = $this->handleMultipleUpload($request, 'dokumentasi');
            $daftarHadirPaths = $this->handleMultipleUpload($request, 'daftar_hadir');

            $campaign = CampaignSalman::create([
                'company_id' => $companyId,
                'created_by' => $user->id,
                'cover_template_id' => $validated['cover_template_id'] ?? null,
                'tanggal' => $validated['tanggal'],
                'tema' => $validated['tema'],
                'lokasi' => $validated['lokasi'],
                'jumlah_peserta' => $validated['jumlah_peserta'],
                'pemateri' => $validated['pemateri'],
                'entitas' => $validated['entitas'],
                'ringkasan' => $validated['ringkasan'],
                'dokumentasi' => $dokumentasiPaths,
                'daftar_hadir' => $daftarHadirPaths,
            ]);

            if (!empty($validated['cover_template_id'])) {
                CoverTemplate::where('id', $validated['cover_template_id'])->increment('usage_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat!',
                'data' => $campaign
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating campaign salman: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data.'
            ], 500);
        }
    }

    /**
     * Show Detail
     */
    public function show($id)
    {
        $campaignSalman = CampaignSalman::with(['company', 'creator', 'coverTemplate'])->findOrFail($id);

        $this->authorizeAccess($campaignSalman);

        // Append URLs
        $campaignSalman->dokumentasi_urls = $this->mapFilesToUrls($campaignSalman->dokumentasi);
        $campaignSalman->daftar_hadir_urls = $this->mapFilesToUrls($campaignSalman->daftar_hadir);

        return response()->json([
            'success' => true,
            'data' => $campaignSalman
        ]);
    }

    /**
     * Update Report
     * NOTE: Untuk update dengan file via API, gunakan method POST dengan _method = PUT
     * atau x-www-form-urlencoded (tapi sulit untuk file).
     * Saran: Gunakan POST endpoint ini dengan body key: `_method` value: `PUT`.
     */
    public function update(Request $request, $id)
    {
        $campaignSalman = CampaignSalman::findOrFail($id);
        $this->authorizeAccess($campaignSalman);
        $user = Auth::user();

        $rules = [
            'tanggal' => 'required|date',
            'tema' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'jumlah_peserta' => 'required|integer|min:1',
            'pemateri' => 'required|string|max:255',
            'entitas' => 'required|string|max:255',
            'ringkasan' => 'required|string',
            'cover_template_id' => 'nullable|exists:cover_templates,id',
            'dokumentasi.*' => 'nullable|image|max:5120',
            'daftar_hadir.*' => 'nullable|image|max:5120',
        ];

        if ($user->hasRole('super-admin')) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $validated = $request->validate($rules);

        // Merge Files
        $existingDokumentasi = $campaignSalman->dokumentasi ?? [];
        $newDokumentasi = $this->handleMultipleUpload($request, 'dokumentasi');
        $finalDokumentasi = array_merge($existingDokumentasi, $newDokumentasi);

        $existingAbsen = $campaignSalman->daftar_hadir ?? [];
        $newAbsen = $this->handleMultipleUpload($request, 'daftar_hadir');
        $finalAbsen = array_merge($existingAbsen, $newAbsen);

        $updateData = [
            'tanggal' => $validated['tanggal'],
            'tema' => $validated['tema'],
            'lokasi' => $validated['lokasi'],
            'jumlah_peserta' => $validated['jumlah_peserta'],
            'pemateri' => $validated['pemateri'],
            'entitas' => $validated['entitas'],
            'ringkasan' => $validated['ringkasan'],
            'cover_template_id' => $validated['cover_template_id'] ?? null,
            'dokumentasi' => $finalDokumentasi,
            'daftar_hadir' => $finalAbsen,
        ];

        if ($user->hasRole('super-admin')) {
            $updateData['company_id'] = $validated['company_id'];
        }

        $campaignSalman->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diperbarui!',
            'data' => $campaignSalman
        ]);
    }

    /**
     * Destroy
     */
    public function destroy($id)
    {
        $campaignSalman = CampaignSalman::findOrFail($id);
        $this->authorizeAccess($campaignSalman);

        // Optional: Physical file deletion logic here

        $campaignSalman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus.'
        ]);
    }

    /**
     * Delete Specific Image
     */
    public function deleteImage(Request $request, $id)
    {
        $campaignSalman = CampaignSalman::findOrFail($id);
        $this->authorizeAccess($campaignSalman);

        $request->validate([
            'type' => 'required|in:dokumentasi,daftar_hadir',
            'path' => 'required|string'
        ]);

        $type = $request->type;
        $pathToDelete = $request->path;

        // Bersihkan path jika frontend mengirim full URL
        $cleanPath = str_replace(Storage::disk('public')->url(''), '', $pathToDelete);

        $currentImages = $campaignSalman->$type ?? [];

        // Check against raw path or cleaned path
        $index = array_search($pathToDelete, $currentImages);
        if ($index === false) {
             $index = array_search($cleanPath, $currentImages);
        }

        if ($index !== false) {
            unset($currentImages[$index]);

            // Delete physical file
            $finalPath = $currentImages[$index] ?? $cleanPath; // fallback logic
            if (Storage::disk('public')->exists($finalPath)) {
                Storage::disk('public')->delete($finalPath);
            }

            // Update DB
            $campaignSalman->update([
                $type => array_values($currentImages)
            ]);

            return response()->json(['success' => true, 'message' => 'Gambar dihapus.']);
        }

        return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan.'], 404);
    }

    /**
     * Download PDF
     */
    public function downloadPdf($id)
    {
        $campaignSalman = CampaignSalman::with(['company', 'coverTemplate'])->findOrFail($id);
        $this->authorizeAccess($campaignSalman);

        $pdf = Pdf::loadView('features.campaign-salman.pdf', compact('campaignSalman'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download($campaignSalman->getPdfFilename());
    }

    /**
     * Export ZIP
     */
    public function exportZip(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['super-admin', 'koordinator'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $query = CampaignSalman::with(['company', 'coverTemplate'])
            ->whereBetween('tanggal', [$request->start_date, $request->end_date]);

        if (Auth::user()->hasRole('super-admin')) {
            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        } else {
            $query->where('company_id', Auth::user()->company_id);
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada laporan pada periode tersebut.'], 404);
        }

        $zipFileName = 'Laporan_Salman_' . Carbon::now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/public/temp/' . $zipFileName);

        if (!file_exists(dirname($zipPath))) mkdir(dirname($zipPath), 0755, true);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($reports as $report) {
                try {
                    $pdf = Pdf::loadView('features.campaign-salman.pdf', ['campaignSalman' => $report])
                        ->setPaper('a4', 'portrait');

                    $zip->addFromString($report->getPdfFilename(), $pdf->output());
                } catch (\Exception $e) {
                    Log::error("PDF Gen Error ID {$report->id}");
                }
            }
            $zip->close();
        }

        // Return direct download URL or Stream
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // --- Helpers ---

    private function handleMultipleUpload($request, $inputName)
    {
        $paths = [];
        if ($request->hasFile($inputName)) {
            foreach ($request->file($inputName) as $file) {
                $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $paths[] = $file->storeAs('campaign-salman/' . $inputName, $filename, 'public');
            }
        }
        return $paths;
    }

    private function mapFilesToUrls($paths)
    {
        if (empty($paths) || !is_array($paths)) return [];

        return array_map(function($path) {
            return Storage::disk('public')->url($path);
        }, $paths);
    }

    private function authorizeAccess($model)
    {
        $user = Auth::user();
        if ($user->hasRole('super-admin')) return true;

        if ($user->hasAnyRole(['koordinator', 'hsse', 'rses'])) {
            if ($model->company_id !== $user->company_id) {
                abort(403, 'Anda tidak memiliki akses ke data perusahaan lain.');
            }
            return true;
        }

        if ($model->created_by !== $user->id) {
            abort(403, 'Anda hanya dapat mengakses laporan yang Anda buat.');
        }
    }
}
