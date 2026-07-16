<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use App\Models\ActionItemPhoto;
use App\Models\CermatReport;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class MyActionItemsController extends Controller
{
    /**
     * Menampilkan daftar Action Item milik user (Kanban data).
     */
    public function index()
    {
        // 1. Authorize (Pastikan user punya permission)
        // $this->authorize('manage my action'); // Uncomment jika menggunakan Policy via Gate API

        // 2. Definisi Status
        $statuses = [
            ActionItem::STATUS_DO => 'Akan Dikerjakan',
            ActionItem::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            ActionItem::STATUS_COMPLETED => 'Selesai',
            ActionItem::STATUS_CANT_DO => 'Tidak Dapat Dikerjakan',
        ];

        // 3. Query Data
        $actionItems = ActionItem::where('responsible_id', Auth::id())
                        ->with(['cermatReport.area', 'actionCategory', 'photos'])
                        ->orderBy('target_date', 'asc')
                        ->get();

        // 4. Grouping (Opsional: Bisa di-group di Frontend atau Backend)
        // Di sini kita kirim struktur grouped agar mirip dengan logic web sebelumnya
        $groupedItems = $actionItems->groupBy('status');

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => [
                'statuses' => $statuses,
                'items' => $groupedItems, // Grouped by status
                'all_items' => $actionItems // Flat list jika frontend butuh raw data
            ]
        ], 200);
    }

    /**
     * Menampilkan detail satu Action Item.
     */
    public function show($id)
    {
        try {
            $actionItem = ActionItem::with(['photos', 'cermatReport.area', 'cermatReport.reporter', 'actionCategory'])
                ->where('responsible_id', Auth::id())
                ->findOrFail($id);

            return response()->json([
                'message' => 'Detail tugas berhasil diambil',
                'data' => $actionItem
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Tugas tidak ditemukan atau Anda tidak memiliki akses.'], 404);
        }
    }

    /**
     * Memperbarui Action Item (Status, Notes, Photos).
     *
     * NOTE UNTUK FRONTEND:
     * Jika mengirim file/foto, gunakan Method POST dengan field '_method' = 'PUT'
     * Header: 'Content-Type': 'multipart/form-data'
     */
    public function update(Request $request, $id)
    {
        $actionItem = ActionItem::find($id);

        if (!$actionItem) {
            return response()->json(['message' => 'Tugas tidak ditemukan.'], 404);
        }

        // Authorization Check
        if ($actionItem->responsible_id !== Auth::id()) {
            return response()->json(['message' => 'Anda tidak diizinkan memperbarui tugas ini.'], 403);
        }

        Log::info('=== Mulai Update ActionItem API ===', [
            'user_id' => Auth::id(),
            'action_item_id' => $actionItem->id,
        ]);

        $validStatuses = [
            ActionItem::STATUS_DO,
            ActionItem::STATUS_IN_PROGRESS,
            ActionItem::STATUS_COMPLETED,
            ActionItem::STATUS_CANT_DO,
        ];

        // 1. Validasi
        $validated = $request->validate([
            'status' => ['required', Rule::in($validStatuses)],
            'completion_notes' => [
                Rule::requiredIf($request->status === ActionItem::STATUS_COMPLETED),
                'nullable',
                'string',
                'max:5000'
            ],
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'completion_notes.required' => 'Catatan penyelesaian wajib diisi jika status Selesai.'
        ]);

        // 2. Assign Data
        $actionItem->status = $validated['status'];

        // Hanya update notes jika dikirim (untuk handle partial update drag & drop)
        if ($request->has('completion_notes')) {
            $actionItem->completion_notes = $validated['completion_notes'];
        }

        // 3. Upload Foto
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('action_items_proof', 'public');

                ActionItemPhoto::create([
                    'action_item_id' => $actionItem->id,
                    'file_path'      => $path,
                    'file_name'      => $photo->getClientOriginalName(),
                    'mime_type'      => $photo->getClientMimeType(),
                    'file_size'      => $photo->getSize(),
                ]);
            }
        }

        // 4. Update Timestamp completed_at
        if ($actionItem->isDirty('status')) {
            if ($actionItem->status === ActionItem::STATUS_COMPLETED && is_null($actionItem->completed_at)) {
                $actionItem->completed_at = now();
            } elseif ($actionItem->status !== ActionItem::STATUS_COMPLETED) {
                $actionItem->completed_at = null;
            }
        }

        $actionItem->save();

        // 5. Logika Pengecekan Laporan Induk & Notifikasi
        $this->checkParentReportStatus($actionItem);

        // Refresh data untuk response
        $updatedActionItem = ActionItem::with(['cermatReport.area', 'actionCategory', 'photos'])
            ->find($actionItem->id);

        return response()->json([
            'message' => 'Tugas berhasil diperbarui.',
            'data' => $updatedActionItem
        ], 200);
    }

    /**
     * Menghapus Foto Bukti.
     */
    public function destroyPhoto($photoId)
    {
        try {
            $photo = ActionItemPhoto::with('actionItem')->findOrFail($photoId);

            // Cek akses
            if ($photo->actionItem->responsible_id !== Auth::id()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            // Hapus file fisik
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }

            $photo->delete();

            return response()->json(['message' => 'Foto berhasil dihapus.'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Foto tidak ditemukan.'], 404);
        } catch (Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus foto.'], 500);
        }
    }

    /**
     * Helper: Cek Status Parent Report & Kirim Notifikasi
     * Dipisahkan agar method update lebih bersih.
     */
    private function checkParentReportStatus(ActionItem $actionItem)
    {
        try {
            $report = $actionItem->cermatReport;

            if ($report && $report->status === CermatReport::STATUS_ACTION_IN_PROGRESS) {

                // Refresh relasi untuk mendapatkan status terbaru item lain
                $allActionItems = $report->actionItems()->get();

                $allCompleted = $allActionItems->every(function ($item) {
                    return in_array($item->status, [
                        ActionItem::STATUS_COMPLETED,
                        ActionItem::STATUS_CANT_DO
                    ]);
                });

                if ($allCompleted) {
                    $report->update(['status' => CermatReport::STATUS_AWAITING_CLOSEOUT]);

                    Log::info("API: Laporan #{$report->id} status berubah ke AWAITING_CLOSEOUT.");

                    // Kirim Notifikasi
                    $notificationService = app(NotificationService::class);
                    $notificationService->sendCermatNotification($report);
                }
            }
        } catch (Throwable $e) {
            Log::error('API: Gagal update status laporan induk atau notifikasi: ' . $e->getMessage());
            // Tidak throw error agar response ke user tetap sukses (karena update task sukses)
        }
    }
}
