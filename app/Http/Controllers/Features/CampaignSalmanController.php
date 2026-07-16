<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Models\CampaignSalman;
use App\Models\Company;
use App\Models\CoverTemplate;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use ZipArchive;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
// INTERVENTION IMAGE V3 IMPORTS
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CampaignSalmanController extends Controller
{
    use AuthorizesRequests;

    // =========================================================
    // INDEX
    // =========================================================
    public function index(Request $request)
    {
        $this->authorize('manage campaign salman');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaign Salman Reports']
        ];

        if ($request->ajax()) {
            $user = Auth::user();

            $query = CampaignSalman::with(['company', 'creator.entityFunction', 'coverTemplate'])
                ->select('campaign_salman.*');

            if ($user->hasAnyRole(['super-admin', 'hsse'])) {
                // full access
            } elseif ($user->company_id) {
                $userCompany = Company::find($user->company_id);

                if ($userCompany && $userCompany->slug === 'pertamina-hulu-mahakam') {
                    if ($user->entity_function_id) {
                        $query->where('company_id', $user->company_id)
                            ->whereHas('creator', function ($q) use ($user) {
                                $q->where('entity_function_id', $user->entity_function_id);
                            });
                    } else {
                        $query->where('company_id', $user->company_id);
                    }
                } else {
                    $query->where('company_id', $user->company_id);
                }
            } else {
                $query->whereRaw('1 = 0');
            }

            if ($user->hasAnyRole(['super-admin', 'hsse'])) {
                if ($request->filled('company_id')) {
                    $query->where('company_id', $request->company_id);
                }
                if ($request->filled('entity_function_id')) {
                    $query->whereHas('creator', function ($q) use ($request) {
                        $q->where('entity_function_id', $request->entity_function_id);
                    });
                }
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('tanggal', fn ($row) => $row->tanggal->format('d M Y'))
                ->addColumn('creator_name', fn ($row) => $row->creator ? $row->creator->name : 'Unknown')
                ->addColumn('company_name', fn ($row) => $row->company ? $row->company->name : '-')
                ->addColumn('entity_function_name', fn ($row) => $row->creator && $row->creator->entityFunction
                    ? $row->creator->entityFunction->name : '-')
                ->addColumn('action', fn ($row) => view('features.campaign-salman.partials.actions', compact('row'))->render())
                ->rawColumns(['action'])
                ->make(true);
        }

        $companies = Auth::user()->hasAnyRole(['super-admin', 'hsse'])
            ? Company::orderBy('name')->get()
            : collect([]);

        $entityFunctions = Auth::user()->hasAnyRole(['super-admin', 'hsse'])
            ? \App\Models\EntityFunction::active()->orderBy('name')->get()
            : collect([]);

        return view('features.campaign-salman.index', compact('companies', 'entityFunctions', 'breadcrumbs'));
    }

    // =========================================================
    // CREATE
    // =========================================================
    public function create()
    {
        $this->authorize('manage campaign salman');
        $user = Auth::user();

        if (!$user->hasAnyRole(['super-admin', 'hsse']) && !$user->company_id && !$user->entity_function_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun Anda belum terhubung dengan Perusahaan atau Fungsi Entitas. Hubungi Admin.');
        }

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaign Salman Reports', 'url' => route('campaign-salman.index')],
            ['label' => 'Create Report']
        ];

        $templates = CoverTemplate::active()->orderBy('name')->get();
        $defaultTemplate = CoverTemplate::where('slug', 'default-template')->where('is_active', true)->first();

        $companies = $user->hasAnyRole(['super-admin', 'hsse'])
            ? Company::orderBy('name')->get()
            : collect([]);

        $userCompany = $user->company_id ? Company::find($user->company_id) : null;

        return view('features.campaign-salman.create', compact(
            'templates', 'defaultTemplate', 'companies', 'userCompany', 'breadcrumbs'
        ));
    }

    // =========================================================
    // STORE
    // =========================================================
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'tanggal'          => 'required|date',
            'tema'             => 'required|string|max:255',
            'lokasi'           => 'required|string|max:255',
            'jumlah_peserta'   => 'required|integer|min:1',
            'pemateri'         => 'required|string|max:255',
            'entitas'          => 'required|string|max:255',
            'ringkasan'        => 'required|string',
            'cover_template_id'=> 'nullable|exists:cover_templates,id',
            'dokumentasi.*'    => 'nullable|image|max:5120',
            'daftar_hadir.*'   => 'nullable|image|max:5120',
        ];

        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $validated = $request->validate($rules);

        $companyId = $user->hasAnyRole(['super-admin', 'hsse'])
            ? $validated['company_id']
            : $user->company_id;

        $dokumentasiPaths = $this->handleMultipleUpload($request, 'dokumentasi');
        $daftarHadirPaths = $this->handleMultipleUpload($request, 'daftar_hadir');

        try {
            CampaignSalman::create([
                'company_id'        => $companyId,
                'created_by'        => $user->id,
                'cover_template_id' => $validated['cover_template_id'] ?? null,
                'tanggal'           => $validated['tanggal'],
                'tema'              => $validated['tema'],
                'lokasi'            => $validated['lokasi'],
                'jumlah_peserta'    => $validated['jumlah_peserta'],
                'pemateri'          => $validated['pemateri'],
                'entitas'           => $validated['entitas'],
                'ringkasan'         => $validated['ringkasan'],
                'dokumentasi'       => $dokumentasiPaths,
                'daftar_hadir'      => $daftarHadirPaths,
            ]);

            if (!empty($validated['cover_template_id'])) {
                CoverTemplate::where('id', $validated['cover_template_id'])->increment('usage_count');
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Laporan berhasil dibuat!',
                'redirect' => route('campaign-salman.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating campaign salman: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // SHOW
    // =========================================================
    public function show(CampaignSalman $campaignSalman)
    {
        $this->authorize('manage campaign salman');
        $this->authorizeAccess($campaignSalman);
        $campaignSalman->load(['company', 'creator.entityFunction', 'coverTemplate']);

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaign Salman Reports', 'url' => route('campaign-salman.index')],
            ['label' => $campaignSalman->tema]
        ];

        return view('features.campaign-salman.show', compact('campaignSalman', 'breadcrumbs'));
    }

    // =========================================================
    // EDIT
    // =========================================================
    public function edit(CampaignSalman $campaignSalman)
    {
        $this->authorize('manage campaign salman');
        $this->authorizeAccess($campaignSalman);
        $user = Auth::user();

        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Campaign Salman Reports', 'url' => route('campaign-salman.index')],
            ['label' => 'Edit ' . $campaignSalman->tema]
        ];

        $templates = CoverTemplate::active()->orderBy('name')->get();

        if ($campaignSalman->cover_template_id && !$templates->contains('id', $campaignSalman->cover_template_id)) {
            $currentTemplate = CoverTemplate::find($campaignSalman->cover_template_id);
            if ($currentTemplate) $templates->push($currentTemplate);
        }

        $defaultTemplate = CoverTemplate::where('slug', 'default-template')->where('is_active', true)->first();

        $companies = $user->hasAnyRole(['super-admin', 'hsse'])
            ? Company::orderBy('name')->get()
            : collect([]);

        $userCompany = $user->company_id ? Company::find($user->company_id) : null;

        return view('features.campaign-salman.edit', compact(
            'campaignSalman', 'templates', 'defaultTemplate', 'companies', 'userCompany', 'breadcrumbs'
        ));
    }

    // =========================================================
    // UPDATE
    // =========================================================
    public function update(Request $request, CampaignSalman $campaignSalman)
    {
        $this->authorizeAccess($campaignSalman);
        $user = Auth::user();

        $rules = [
            'tanggal'          => 'required|date',
            'tema'             => 'required|string|max:255',
            'lokasi'           => 'required|string|max:255',
            'jumlah_peserta'   => 'required|integer|min:1',
            'pemateri'         => 'required|string|max:255',
            'entitas'          => 'required|string|max:255',
            'ringkasan'        => 'required|string',
            'cover_template_id'=> 'nullable|exists:cover_templates,id',
            'dokumentasi.*'    => 'nullable|image|max:5120',
            'daftar_hadir.*'   => 'nullable|image|max:5120',
        ];

        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            $rules['company_id'] = 'required|exists:companies,id';
        }

        $validated = $request->validate($rules);

        $existingDokumentasi = $campaignSalman->dokumentasi ?? [];
        $newDokumentasi      = $this->handleMultipleUpload($request, 'dokumentasi');
        $finalDokumentasi    = array_merge($existingDokumentasi, $newDokumentasi);

        $existingAbsen = $campaignSalman->daftar_hadir ?? [];
        $newAbsen      = $this->handleMultipleUpload($request, 'daftar_hadir');
        $finalAbsen    = array_merge($existingAbsen, $newAbsen);

        $updateData = [
            'tanggal'           => $validated['tanggal'],
            'tema'              => $validated['tema'],
            'lokasi'            => $validated['lokasi'],
            'jumlah_peserta'    => $validated['jumlah_peserta'],
            'pemateri'          => $validated['pemateri'],
            'entitas'           => $validated['entitas'],
            'ringkasan'         => $validated['ringkasan'],
            'cover_template_id' => $validated['cover_template_id'] ?? null,
            'dokumentasi'       => $finalDokumentasi,
            'daftar_hadir'      => $finalAbsen,
        ];

        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            $updateData['company_id'] = $validated['company_id'];
        }

        $campaignSalman->update($updateData);

        return response()->json([
            'success'  => true,
            'message'  => 'Laporan berhasil diperbarui!',
            'redirect' => route('campaign-salman.index')
        ]);
    }

    // =========================================================
    // DESTROY
    // =========================================================
    public function destroy(CampaignSalman $campaignSalman)
    {
        $this->authorizeAccess($campaignSalman);
        $campaignSalman->delete();
        return response()->json(['success' => true, 'message' => 'Laporan dihapus.']);
    }

    // =========================================================
    // PREVIEW PDF
    // =========================================================
    public function previewPdf(CampaignSalman $campaignSalman)
    {
        $this->authorizeAccess($campaignSalman);
        $campaignSalman->load(['company', 'coverTemplate', 'creator.entityFunction']);

        $pdf = Pdf::loadView('features.campaign-salman.pdf', compact('campaignSalman'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->stream($campaignSalman->getPdfFilename());
    }

    // =========================================================
    // DOWNLOAD PDF
    // =========================================================
    public function downloadPdf(CampaignSalman $campaignSalman)
    {
        $this->authorizeAccess($campaignSalman);
        $campaignSalman->load(['company', 'coverTemplate', 'creator.entityFunction']);

        $pdf = Pdf::loadView('features.campaign-salman.pdf', compact('campaignSalman'))
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true]);

        return $pdf->download($campaignSalman->getPdfFilename());
    }

    // =========================================================
    // DELETE IMAGE
    // =========================================================
    public function deleteImage(Request $request, CampaignSalman $campaignSalman)
    {
        $this->authorizeAccess($campaignSalman);

        $request->validate([
            'type' => 'required|in:dokumentasi,daftar_hadir',
            'path' => 'required|string'
        ]);

        $type         = $request->type;
        $pathToDelete = $request->path;
        $currentImages = $campaignSalman->$type ?? [];

        if (($key = array_search($pathToDelete, $currentImages)) !== false) {
            unset($currentImages[$key]);

            if (Storage::disk('public')->exists($pathToDelete)) {
                Storage::disk('public')->delete($pathToDelete);
            }

            $campaignSalman->update([$type => array_values($currentImages)]);

            return response()->json(['success' => true, 'message' => 'Gambar dihapus.']);
        }

        return response()->json(['success' => false, 'message' => 'Gambar tidak ditemukan.'], 404);
    }

    // =========================================================
    // EXPORT ZIP CHECK — validasi & hitung jumlah laporan (cepat)
    // =========================================================
    public function exportZipCheck(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['super-admin', 'hsse'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'company_id'         => 'nullable|exists:companies,id',
            'entity_function_id' => 'nullable|exists:entity_functions,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        if ($startDate->diffInDays($endDate) > 7) {
            return response()->json(['message' => 'Rentang tanggal tidak boleh lebih dari 7 hari.'], 422);
        }

        $query = CampaignSalman::whereBetween('tanggal', [$request->start_date, $request->end_date]);
        if ($request->filled('company_id'))         $query->where('company_id', $request->company_id);
        if ($request->filled('entity_function_id')) {
            $query->whereHas('creator', fn($q) => $q->where('entity_function_id', $request->entity_function_id));
        }

        $count = $query->count();
        if ($count === 0)  return response()->json(['message' => 'Tidak ada laporan pada periode tersebut.'], 404);
        if ($count > 30)   return response()->json(['message' => "Terlalu banyak laporan ({$count}). Maksimal 30 per export ZIP."], 422);

        return response()->json(['count' => $count, 'ok' => true]);
    }

    // =========================================================
    // EXPORT ZIP — generate PDF dengan isRemoteEnabled: true
    // agar cover image & template tetap muncul di PDF
    // =========================================================
    public function exportZip(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['super-admin', 'hsse'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'company_id'         => 'nullable|exists:companies,id',
            'entity_function_id' => 'nullable|exists:entity_functions,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        if ($startDate->diffInDays($endDate) > 7) {
            return response()->json(['message' => 'Rentang tidak boleh lebih dari 7 hari.'], 422);
        }

        $query = CampaignSalman::with(['company', 'coverTemplate', 'creator.entityFunction'])
            ->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        if ($request->filled('company_id'))         $query->where('company_id', $request->company_id);
        if ($request->filled('entity_function_id')) {
            $query->whereHas('creator', fn($q) => $q->where('entity_function_id', $request->entity_function_id));
        }

        $totalCount = $query->count();
        if ($totalCount === 0) return response()->json(['message' => 'Tidak ada laporan.'], 404);
        if ($totalCount > 30)  return response()->json(['message' => "Maks 30 laporan per ZIP ({$totalCount} ditemukan)."], 422);

        set_time_limit(max(180, $totalCount * 15));
        ini_set('memory_limit', '512M');

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);
        $zipPath = $tempDir . '/zip_' . Auth::id() . '_' . time() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Gagal membuat ZIP.'], 500);
        }

        $successCount = 0;
        foreach ($query->get() as $report) {
            try {
                // Konversi path gambar ke base64 data URI agar DomPDF
                // bisa render gambar TANPA HTTP request (cepat & tidak butuh isRemoteEnabled)
                $reportData = clone $report;
                $reportData->dokumentasi_b64  = $this->imagesToBase64($report->dokumentasi ?? []);   // FIXED: was pathsToBase64()
                $reportData->daftar_hadir_b64 = $this->imagesToBase64($report->daftar_hadir ?? []);  // FIXED: was pathsToBase64()

                $pdf = Pdf::loadView('features.campaign-salman.pdf', ['campaignSalman' => $reportData])
                    ->setPaper('a4', 'portrait')
                    ->setOptions([
                        'isRemoteEnabled'      => false,
                        'isHtml5ParserEnabled' => true,
                        'defaultFont'          => 'sans-serif',
                        'dpi'                  => 96,
                    ]);
                $zip->addFromString($report->getPdfFilename(), $pdf->output());
                $successCount++;
                unset($pdf, $reportData);
                gc_collect_cycles();
            } catch (\Exception $e) {
                Log::error("ZIP PDF failed [{$report->id}]: " . $e->getMessage());
            }
        }
        $zip->close();

        if ($successCount === 0 || !file_exists($zipPath) || filesize($zipPath) === 0) {
            @unlink($zipPath);
            return response()->json(['message' => 'Semua PDF gagal dibuat.'], 500);
        }

        // Bersihkan output buffer agar file tidak corrupt
        while (ob_get_level()) ob_end_clean();

        $downloadName = 'Laporan_Salman_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.zip';
        return response()->download($zipPath, $downloadName, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
            'Content-Length'      => filesize($zipPath),
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ])->deleteFileAfterSend(true);
    }

    // =========================================================
    // EXPORT ZIP PROGRESS — tidak digunakan lagi, keep untuk
    // backward compat jika ada cache lama
    // =========================================================
    public function exportZipProgress(Request $request)
    {
        return response()->json(['done' => 0, 'total' => 0, 'status' => 'idle']);
    }

    // =========================================================
    // EXPORT EXCEL SUMMARY  (tanpa attachment, hanya data)
    // Catatan: semua error dikembalikan sebagai JSON (bukan redirect)
    // =========================================================
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['super-admin', 'hsse'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after_or_equal:start_date',
            'company_id'         => 'nullable|exists:companies,id',
            'entity_function_id' => 'nullable|exists:entity_functions,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        if ($startDate->diffInDays($endDate) > 365) {
            return response()->json(['message' => 'Rentang tanggal tidak boleh lebih dari 1 tahun.'], 422);
        }

        $query = CampaignSalman::with(['company', 'creator.entityFunction'])
            ->whereBetween('tanggal', [$request->start_date, $request->end_date])
            ->orderBy('tanggal', 'asc');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('entity_function_id')) {
            $query->whereHas('creator', function ($q) use ($request) {
                $q->where('entity_function_id', $request->entity_function_id);
            });
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Tidak ada laporan pada periode tersebut.'], 404);
        }

        // === BUILD EXCEL ===
        set_time_limit(120);
        ini_set('memory_limit', '256M');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Campaign Salman Summary');

        // --- STYLE HELPERS ---
        $headerBg    = 'FF1F4E79'; // dark blue
        $subHeaderBg = 'FFD6E4F7'; // light blue
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF999999'],
                ],
            ],
        ];

        // === TITLE ===
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'CAMPAIGN SALMAN – SUMMARY LAPORAN');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $headerBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // === SUB TITLE (periode) ===
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Periode: ' . $startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF333333'], 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $subHeaderBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // === HEADER ROW ===
        $headers = ['No', 'Tanggal', 'Tema', 'Perusahaan', 'Entity Function', 'Pemateri', 'Lokasi', 'Entitas', 'Jml Peserta', 'Dibuat Oleh', 'Ringkasan'];
        $col     = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Arial', 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $headerBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $col++;
        }
        $sheet->getRowDimension(3)->setRowHeight(22);

        // === DATA ROWS ===
        $rowNum = 4;
        $no     = 1;

        foreach ($reports as $report) {
            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValue('B' . $rowNum, $report->tanggal->format('d M Y'));
            $sheet->setCellValue('C' . $rowNum, $report->tema);
            $sheet->setCellValue('D' . $rowNum, $report->company ? $report->company->name : '-');
            $sheet->setCellValue('E' . $rowNum, $report->creator && $report->creator->entityFunction ? $report->creator->entityFunction->name : '-');
            $sheet->setCellValue('F' . $rowNum, $report->pemateri);
            $sheet->setCellValue('G' . $rowNum, $report->lokasi);
            $sheet->setCellValue('H' . $rowNum, $report->entitas);
            $sheet->setCellValue('I' . $rowNum, $report->jumlah_peserta);
            $sheet->setCellValue('J' . $rowNum, $report->creator ? $report->creator->name : '-');
            $sheet->setCellValue('K' . $rowNum, $report->ringkasan ?? '-');
            $sheet->getStyle('K' . $rowNum)->getAlignment()->setWrapText(true);

            // Zebra stripe
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F9FF']],
                ]);
            }

            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setWrapText(true);

            $no++;
            $rowNum++;
        }

        // === BORDER semua data ===
        $sheet->getStyle('A3:K' . ($rowNum - 1))->applyFromArray($borderStyle);

        // === TOTAL ROW ===
        $sheet->setCellValue('A' . $rowNum, 'TOTAL');
        $sheet->setCellValue('I' . $rowNum, '=SUM(I4:I' . ($rowNum - 1) . ')');
        $sheet->getStyle('A' . $rowNum . ':J' . $rowNum)->applyFromArray([
            'font'      => ['bold' => true, 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $subHeaderBg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->mergeCells('A' . $rowNum . ':I' . $rowNum);

        // === COLUMN WIDTHS ===
        $colWidths = ['A' => 5, 'B' => 14, 'C' => 35, 'D' => 25, 'E' => 25, 'F' => 20, 'G' => 20, 'H' => 20, 'I' => 12, 'J' => 22, 'K' => 50];
        foreach ($colWidths as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // === FREEZE HEADER ===
        $sheet->freezePane('A4');

        // === OUTPUT ===
        $fileName = 'Summary_CampaignSalman_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';
        $tempDir  = storage_path('app/temp');
        $tempPath = $tempDir . '/' . $fileName;

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // === BERSIHKAN OUTPUT BUFFER sebelum kirim file binary ===
        while (ob_get_level()) ob_end_clean();

        return response()->download($tempPath, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => filesize($tempPath),
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ])->deleteFileAfterSend(true);
    }

    // =========================================================
    // HELPER: Konversi array path gambar → array base64 data URI
    // Digunakan saat export ZIP agar DomPDF tidak perlu HTTP request
    // =========================================================
    private function imagesToBase64(array $paths): array
    {
        $result = [];
        foreach ($paths as $path) {
            try {
                $fullPath = Storage::disk('public')->path($path);
                if (!file_exists($fullPath)) continue;

                $mime = mime_content_type($fullPath) ?: 'image/jpeg';
                $data = base64_encode(file_get_contents($fullPath));
                $result[] = 'data:' . $mime . ';base64,' . $data;
            } catch (\Exception $e) {
                Log::warning('imagesToBase64 failed for: ' . $path . ' — ' . $e->getMessage());
            }
        }
        return $result;
    }

    // =========================================================
    // HELPER: Upload & Compress Images
    // =========================================================
    private function handleMultipleUpload($request, $inputName)
    {
        $paths = [];

        if ($request->hasFile($inputName)) {
            $manager = new ImageManager(new Driver());

            foreach ($request->file($inputName) as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $filename  = uniqid() . '_' . time() . '.' . $extension;
                $directory = 'campaign-salman/' . $inputName;
                $fullPath  = $directory . '/' . $filename;

                try {
                    $image = $manager->read($file);
                    $image->scaleDown(width: 1920);

                    $encoded = match ($extension) {
                        'jpg', 'jpeg' => $image->toJpeg(quality: 75),
                        'png'         => $image->toPng(),
                        'webp'        => $image->toWebp(quality: 75),
                        'gif'         => $image->toGif(),
                        default       => $image->toJpeg(quality: 75),
                    };

                    Storage::disk('public')->put($fullPath, (string) $encoded);
                    $paths[] = $fullPath;

                } catch (\Exception $e) {
                    Log::error("Image Upload Failed for {$inputName}: " . $e->getMessage());
                }
            }
        }

        return $paths;
    }

    // =========================================================
    // HELPER: Authorize Access
    // =========================================================
    private function authorizeAccess($model)
    {
        $user = Auth::user();

        if (!$model->relationLoaded('creator')) {
            $model->load('creator');
        }

        if ($user->hasAnyRole(['super-admin', 'hsse'])) {
            return true;
        }

        if (!$model->creator) {
            abort(403, 'Data creator tidak ditemukan.');
        }

        if ($user->company_id) {
            $userCompany = Company::find($user->company_id);

            if ($userCompany && $userCompany->slug === 'pertamina-hulu-mahakam') {
                if ($user->entity_function_id) {
                    if ($model->creator->company_id !== $user->company_id ||
                        $model->creator->entity_function_id !== $user->entity_function_id) {
                        abort(403, 'Anda tidak memiliki akses ke data fungsi entitas lain.');
                    }
                } else {
                    if ($model->creator->company_id !== $user->company_id) {
                        abort(403, 'Anda tidak memiliki akses ke data perusahaan lain.');
                    }
                }
            } else {
                if ($model->creator->company_id !== $user->company_id) {
                    abort(403, 'Anda tidak memiliki akses ke data perusahaan lain.');
                }
            }

            return true;
        }

        abort(403, 'Anda tidak memiliki akses ke data ini.');
    }
}
