<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CermatReport;
use App\Models\ReportAttachment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Throwable;

// Import Form Requests yang masih digunakan
use App\Http\Requests\Cermat\StoreCermatReportRequest;
use App\Http\Requests\Cermat\UpdateCermatReportRequest;

class CermatReportApiController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mendapatkan daftar laporan CERMAT yang HANYA dilaporkan oleh user yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Filter utama: Hanya laporan yang dibuat oleh user ini
        $query = CermatReport::with(['reporter:id,name', 'area:id,name'])
            ->where('reporter_id', $user->id)
            ->select('cermat_reports.*');

        // Tambahkan filter berdasarkan status jika diminta
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Tambahkan filter berdasarkan supervisor_status jika diminta
        if ($request->has('supervisor_status')) {
            $query->where('supervisor_status', $request->supervisor_status);
        }

        $reports = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($reports, Response::HTTP_OK);
    }

    /**
     * Menampilkan detail laporan CERMAT (Show).
     */
    public function show(CermatReport $report, Request $request): JsonResponse
    {
        $user = $request->user();

        // Otorisasi: Hanya pelapor (reporter) yang diizinkan melihat.
        if ($report->reporter_id !== $user->id) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak diizinkan melihat laporan ini.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Muat relasi sesuai permintaan (data lengkap untuk tampilan)
        $report->load([
            'reporter', 'lineSupervisor', 'hsseOfficer', 'closeOutPerformer', 'area',
            'unsafeActs', 'unsafeConditions', 'pelanggaran', 'securityEventCategory',
            'actionItems.responsible', 'actionItems.actionCategory', 'actionItems.photos',
            'attachments.uploader',
            'riskAssessments'
        ]);

        // Muat riwayat audit
        $audits = $report->audits()->with('user')->latest()->get();

        return response()->json([
            'message' => 'Detail laporan berhasil diambil.',
            'report' => $report,
            'audits' => $audits
        ], Response::HTTP_OK);
    }

    /**
     * Membuat laporan CERMAT baru (Store).
     */
    public function store(StoreCermatReportRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $request->user();

        try {
            $createdReport = DB::transaction(function () use ($validatedData, $request, $user) {
                // Generate Report Number
                $year = Carbon::now()->format('Y');
                $month = Carbon::now()->format('m');

                // Menggunakan lock for update untuk memastikan nomor urut unik
                $latestReportCount = CermatReport::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->lockForUpdate()
                    ->count();

                $reportNumber = sprintf('CERMAT/%s/%s/%04d', $year, $month, $latestReportCount + 1);

                // Buat laporan dengan status workflow yang baru
                $report = CermatReport::create(array_merge($validatedData, [
                    'report_number' => $reportNumber,
                    'reporter_id' => $user->id,

                    // Initialize parallel workflow states (sesuai model baru)
                    'status' => CermatReport::STATUS_IN_REVIEW,
                    'supervisor_status' => CermatReport::SUPERVISOR_STATUS_AWAITING,
                    'hsse_status' => CermatReport::HSSE_STATUS_PENDING_REVIEW,
                    'submitted_at' => now(), // Mark submission time

                    'stop_card_issued' => $request->boolean('stop_card_issued'),
                    'requires_feedback' => $request->boolean('requires_feedback'),
                ]));

                // Upload attachments jika ada
                if ($request->hasFile('attachments')) {
                    $this->storeAttachments($report, $request->file('attachments'), 'initial_report', $user->id);
                }

                // Sinkronisasi relasi many-to-many dengan tracking
                $report->syncUnsafeActsWithTracking($validatedData['selectedUnsafeActs'] ?? []);
                $report->syncUnsafeConditionsWithTracking($validatedData['selectedUnsafeConditions'] ?? []);

                return $report;
            });

            // Kirim notifikasi setelah transaksi berhasil (Submit notification)
            if ($createdReport) {
                Log::info("Cermat report #{$createdReport->report_number} created via API. Dispatching notification.");
                $this->notificationService->sendSupervisorDecisionNotification($createdReport, 'submit');
            }

            return response()->json([
                'message' => "Laporan #{$createdReport->report_number} berhasil dibuat dan dikirim untuk Review. Notifikasi telah dikirim.",
                'report' => $createdReport
            ], Response::HTTP_CREATED);

        } catch (Throwable $e) {
            Log::error('Error creating Cermat report API: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal membuat laporan. Terjadi kesalahan server.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui laporan CERMAT (Update).
     */
    public function update(UpdateCermatReportRequest $request, CermatReport $report): JsonResponse
    {
        $validatedData = $request->validated();
        $user = $request->user();

        // Otorisasi: Hanya reporter yang dapat mengedit, dan hanya jika dapat diedit
        if ($report->reporter_id !== $user->id || !$report->canBeEdited()) {
            return response()->json([
                'message' => 'Anda tidak diizinkan memperbarui laporan ini pada status saat ini.'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            DB::transaction(function () use ($validatedData, $request, $report, $user) {
                // Hapus lampiran yang diminta
                if (!empty($validatedData['delete_attachments'])) {
                    $this->deleteAttachments($report, $validatedData['delete_attachments']);
                    unset($validatedData['delete_attachments']);
                }

                // Tambahkan lampiran baru
                if ($request->hasFile('attachments')) {
                    $this->storeAttachments($report, $request->file('attachments'), 'update', $user->id);
                }

                // Jika laporan ditolak, set ke DRAFT saat di-update
                if ($report->supervisor_status === CermatReport::SUPERVISOR_STATUS_REJECTED) {
                    $validatedData['status'] = CermatReport::STATUS_DRAFT;
                }

                // Perbarui data utama (filter null values)
                $report->update(array_filter($validatedData, fn($value) => !is_null($value)));

                // Sinkronisasi relasi many-to-many dengan tracking
                $report->syncUnsafeActsWithTracking($validatedData['selectedUnsafeActs'] ?? []);
                $report->syncUnsafeConditionsWithTracking($validatedData['selectedUnsafeConditions'] ?? []);
            });

            Log::info("Report #{$report->report_number} updated via API by User ID: {$user->id}");

            return response()->json([
                'message' => 'Laporan berhasil diperbarui.',
                'report' => $report->fresh()
            ], Response::HTTP_OK);

        } catch (Throwable $e) {
            Log::error('Error updating Cermat report API ID ' . $report->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal memperbarui laporan. Terjadi kesalahan server.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mengirim ulang laporan yang ditolak (Resubmit).
     */
    public function resubmit(CermatReport $report, Request $request): JsonResponse
    {
        $user = $request->user();

        // Hanya pelapor yang bisa mengirim ulang jika statusnya ditolak
        $canResubmit = $report->reporter_id === $user->id &&
                       $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_REJECTED;

        if (!$canResubmit) {
            return response()->json([
                'message' => 'Aksi pengiriman ulang tidak diizinkan. Laporan harus ditolak terlebih dahulu.'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            DB::transaction(function () use ($report) {
                $report->update([
                    'status' => CermatReport::STATUS_IN_REVIEW, // Global status moves back to review
                    'supervisor_status' => CermatReport::SUPERVISOR_STATUS_AWAITING, // Reset supervisor workflow
                    'hsse_status' => CermatReport::HSSE_STATUS_PENDING_REVIEW, // Reset HSSE workflow
                    'supervisor_rejected_at' => null,
                    'rejection_reason' => null,
                    'submitted_at' => now(), // Update submission time
                ]);
            });

            // Kirim notifikasi setelah transaksi berhasil (Resubmit notification)
            Log::info("Report #{$report->report_number} resubmitted via API. Dispatching notification.");
            $this->notificationService->sendSupervisorDecisionNotification($report, 'resubmit');

            return response()->json([
                'message' => 'Laporan berhasil dikirim ulang untuk persetujuan supervisor dan HSSE review. Notifikasi telah dikirim.',
                'report' => $report->fresh()
            ], Response::HTTP_OK);

        } catch (Throwable $e) {
            Log::error('Failed to resubmit report API #' . $report->id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal mengirim ulang laporan. Terjadi kesalahan server.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menghapus laporan CERMAT (Destroy).
     */
    public function destroy(CermatReport $report, Request $request): JsonResponse
    {
        $user = $request->user();

        // Otorisasi: Hanya pelapor yang dapat menghapus, dan hanya jika status DRAFT atau REJECTED.
        $canDelete = $report->reporter_id === $user->id &&
                     ($report->status === CermatReport::STATUS_DRAFT ||
                      $report->supervisor_status === CermatReport::SUPERVISOR_STATUS_REJECTED);

        if (!$canDelete) {
            return response()->json([
                'message' => 'Anda tidak diizinkan menghapus laporan ini. Hanya laporan dengan status Draft atau Rejected yang dapat dihapus.'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            DB::transaction(function () use ($report) {
                // Hapus semua lampiran dari storage sebelum menghapus record laporan
                $this->deleteAttachments($report, $report->attachments->pluck('id')->toArray());
                $report->delete();
            });

            Log::info("Report #{$report->report_number} deleted via API by User ID: {$user->id}");

            return response()->json([
                'message' => 'Laporan berhasil dihapus.'
            ], Response::HTTP_OK);

        } catch (Throwable $e) {
            Log::error('Failed to delete report API: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal menghapus laporan. Terjadi kesalahan server.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // --- HELPER METHODS ---

    /**
     * Menyimpan lampiran baru ke storage.
     */
    private function storeAttachments(CermatReport $report, array $files, string $stage, string $userId): void
    {
        foreach ($files as $file) {
            $path = $file->store('report_attachments', 'public');
            $report->attachments()->create([
                'uploaded_by_id' => $userId,
                'upload_stage'   => $stage,
                'file_path'      => $path,
                'file_name'      => $file->getClientOriginalName(),
                'mime_type'      => $file->getMimeType(),
            ]);
        }
    }

    /**
     * Menghapus record lampiran dari DB dan file dari storage.
     */
    private function deleteAttachments(CermatReport $report, array $attachmentIds): void
    {
        $attachmentsToDelete = $report->attachments()->whereIn('id', $attachmentIds)->get();

        foreach ($attachmentsToDelete as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
    }
}
