<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActionItem;
use App\Models\ActionItemPhoto;
use App\Models\CermatReport;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Throwable;

class MyActionItemsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Maximum number of photos per action item
     */
    private const MAX_PHOTOS = 5;

    /**
     * Maximum photo file size in KB
     */
    private const MAX_PHOTO_SIZE = 5120;

    /**
     * Maximum image width for optimization
     */
    private const MAX_IMAGE_WIDTH = 1200;

    /**
     * Image compression quality (1-100)
     */
    private const IMAGE_QUALITY = 80;

    /**
     * Storage path for action item photos
     */
    private const STORAGE_PATH = 'action_items_proof';

    /**
     * Display user's action items in Kanban board
     */
    public function index()
    {
        $this->authorize('manage my action');

        // Get status labels from model constants
        $statuses = [
            ActionItem::STATUS_DO => 'Akan Dikerjakan',
            ActionItem::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
            ActionItem::STATUS_COMPLETED => 'Selesai',
            ActionItem::STATUS_CANT_DO => 'Tidak Dapat Dikerjakan',
        ];

        // Fetch user's action items with relationships
        $actionItems = ActionItem::where('responsible_id', Auth::id())
            ->with([
                'cermatReport:id,report_number,status,area_id',
                'cermatReport.area:id,name',
                'actionCategory:id,name',
                'photos:id,action_item_id,file_path,file_name'
            ])
            ->orderBy('target_date', 'asc')
            ->get()
            ->groupBy('status');

        return view('user.my-actions-kanban', [
            'statuses' => $statuses,
            'groupedItems' => $actionItems
        ]);
    }

    /**
     * Get action item details for editing
     */
    public function edit(ActionItem $actionItem): JsonResponse
    {
        $this->authorize('manage my action');
        $this->authorizeUserAccess($actionItem);

        $actionItem->load([
            'photos:id,action_item_id,file_path,file_name',
            'cermatReport:id,report_number,area_id,reporter_id',
            'cermatReport.area:id,name',
            'cermatReport.reporter:id,name',
            'actionCategory:id,name'
        ]);

        return response()->json($actionItem);
    }

    /**
     * Update action item status and details
     */
    public function update(Request $request, ActionItem $actionItem): JsonResponse
    {
        Log::info('=== Action Item Update Started ===', [
            'user_id' => Auth::id(),
            'action_item_id' => $actionItem->id,
            'request_keys' => array_keys($request->all())
        ]);

        $this->authorizeUserAccess($actionItem);

        try {
            DB::beginTransaction();

            // Determine update type and validate accordingly
            if ($this->isDragDropUpdate($request)) {
                $this->handleDragDropUpdate($request, $actionItem);
            } else {
                $this->handleFormUpdate($request, $actionItem);
            }

            // Update completion timestamp
            $this->updateCompletionTimestamp($actionItem);

            // Save changes
            $actionItem->save();

            // Check and update parent report status if needed
            $this->checkAndUpdateParentReport($actionItem);

            DB::commit();

            Log::info('Action item updated successfully', [
                'action_item_id' => $actionItem->id,
                'changed_fields' => $actionItem->getChanges()
            ]);

            // Return updated data
            $updatedActionItem = $this->getUpdatedActionItem($actionItem->id);

            return response()->json([
                'success' => 'Tugas berhasil diperbarui',
                'item_id' => $updatedActionItem->id,
                'item_status' => $updatedActionItem->status,
                'html' => $this->renderKanbanCard($updatedActionItem)
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Action item update failed', [
                'action_item_id' => $actionItem->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Gagal memperbarui tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete action item photo
     */
    public function destroyPhoto(ActionItemPhoto $photo): JsonResponse
    {
        $this->authorizeUserAccess($photo->actionItem);

        try {
            DB::beginTransaction();

            // Delete file from storage
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }

            // Delete database record
            $photo->delete();

            DB::commit();

            Log::info('Action item photo deleted', [
                'photo_id' => $photo->id,
                'action_item_id' => $photo->action_item_id,
                'deleted_by' => Auth::id()
            ]);

            return response()->json([
                'success' => 'Foto berhasil dihapus'
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete action item photo', [
                'photo_id' => $photo->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Gagal menghapus foto'
            ], 500);
        }
    }

    // ============================================================
    // PRIVATE HELPER METHODS
    // ============================================================

    /**
     * Check if user has access to action item
     */
    private function authorizeUserAccess(ActionItem $actionItem): void
    {
        if ($actionItem->responsible_id !== Auth::id()) {
            Log::warning('Unauthorized access attempt to action item', [
                'action_item_id' => $actionItem->id,
                'user_id' => Auth::id(),
                'responsible_id' => $actionItem->responsible_id
            ]);

            abort(403, 'Anda tidak diizinkan mengakses tugas ini.');
        }
    }

    /**
     * Check if update is from drag & drop
     */
    private function isDragDropUpdate(Request $request): bool
    {
        return $request->has('status') && count($request->all()) === 1;
    }

    /**
     * Get valid action item statuses
     */
    private function getValidStatuses(): array
    {
        return [
            ActionItem::STATUS_DO,
            ActionItem::STATUS_IN_PROGRESS,
            ActionItem::STATUS_COMPLETED,
            ActionItem::STATUS_CANT_DO,
        ];
    }

    /**
     * Handle drag & drop status update
     */
    private function handleDragDropUpdate(Request $request, ActionItem $actionItem): void
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->getValidStatuses())]
        ]);

        $actionItem->status = $validated['status'];

        Log::info('Drag & drop update', [
            'action_item_id' => $actionItem->id,
            'new_status' => $validated['status']
        ]);
    }

    /**
     * Handle form modal update
     */
    private function handleFormUpdate(Request $request, ActionItem $actionItem): void
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->getValidStatuses())],
            'completion_notes' => [
                Rule::requiredIf($request->status === ActionItem::STATUS_COMPLETED),
                'nullable',
                'string',
                'max:5000'
            ],
            'photos' => 'nullable|array|max:' . self::MAX_PHOTOS,
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:' . self::MAX_PHOTO_SIZE,
        ], [
            'completion_notes.required' => 'Catatan penyelesaian wajib diisi jika status Selesai.',
            'photos.max' => 'Maksimal ' . self::MAX_PHOTOS . ' foto dapat diunggah.',
        ]);

        Log::info('Form modal update', [
            'action_item_id' => $actionItem->id,
            'validated_keys' => array_keys($validated)
        ]);

        $actionItem->status = $validated['status'];
        $actionItem->completion_notes = $validated['completion_notes'] ?? null;

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $this->handlePhotoUploads($request, $actionItem);
        }
    }

    /**
     * Handle multiple photo uploads with optimization
     */
    private function handlePhotoUploads(Request $request, ActionItem $actionItem): void
    {
        $manager = new ImageManager(new Driver());

        foreach ($request->file('photos') as $photo) {
            try {
                $extension = strtolower($photo->getClientOriginalExtension());
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $path = self::STORAGE_PATH . '/' . $filename;

                // Store original file first (acts as fallback if optimize fails)
                $fullPath = $photo->storeAs(self::STORAGE_PATH, $filename, 'public');

                // Optimize image
                try {
                    $image = $manager->read($photo);

                    // Resize if too large
                    if ($image->width() > self::MAX_IMAGE_WIDTH) {
                        $image->scaleDown(width: self::MAX_IMAGE_WIDTH);
                    }

                    // Encode with compression
                    $encoded = match($extension) {
                        'jpg', 'jpeg' => $image->toJpeg(quality: self::IMAGE_QUALITY),
                        'png' => $image->toPng(),
                        'gif' => $image->toGif(),
                        default => $image->toJpeg(quality: self::IMAGE_QUALITY)
                    };

                    // Save optimized image (overwrites the original on the public disk)
                    Storage::disk('public')->put($fullPath, (string) $encoded);

                    Log::info('Photo uploaded and optimized', [
                        'action_item_id' => $actionItem->id,
                        'filename' => $filename
                    ]);

                } catch (\Exception $e) {
                    Log::warning('Photo optimization failed, using original', [
                        'action_item_id' => $actionItem->id,
                        'filename' => $filename,
                        'error' => $e->getMessage()
                    ]);
                }

                // Create database record
                ActionItemPhoto::create([
                    'action_item_id' => $actionItem->id,
                    'file_path' => $fullPath,
                    'file_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getClientMimeType(),
                    'file_size' => $photo->getSize(),
                ]);

            } catch (\Exception $e) {
                Log::error('Photo upload failed', [
                    'action_item_id' => $actionItem->id,
                    'error' => $e->getMessage()
                ]);
                // Continue with other photos
            }
        }
    }

    /**
     * Update completion timestamp based on status
     */
    private function updateCompletionTimestamp(ActionItem $actionItem): void
    {
        if (!$actionItem->isDirty('status')) {
            return;
        }

        if ($actionItem->status === ActionItem::STATUS_COMPLETED && is_null($actionItem->completed_at)) {
            $actionItem->completed_at = now();

            Log::info('Completion timestamp set', [
                'action_item_id' => $actionItem->id,
                'completed_at' => $actionItem->completed_at
            ]);
        } elseif ($actionItem->status !== ActionItem::STATUS_COMPLETED) {
            $actionItem->completed_at = null;

            Log::info('Completion timestamp cleared', [
                'action_item_id' => $actionItem->id
            ]);
        }
    }

    /**
     * Check if all action items completed and update parent report
     */
    private function checkAndUpdateParentReport(ActionItem $actionItem): void
    {
        try {
            $report = $actionItem->cermatReport;

            // Only check if report is in ACTION_IN_PROGRESS status
            if (!$report || $report->status !== CermatReport::STATUS_ACTION_IN_PROGRESS) {
                return;
            }

            // Check if all action items are completed or can't do
            $allActionItems = $report->actionItems()->get();

            $allCompleted = $allActionItems->every(function ($item) {
                return in_array($item->status, [
                    ActionItem::STATUS_COMPLETED,
                    ActionItem::STATUS_CANT_DO
                ], true);
            });

            // Update report status and send notification
            if ($allCompleted) {
                $report->update(['status' => CermatReport::STATUS_AWAITING_CLOSEOUT]);

                Log::info('Parent report status updated to AWAITING_CLOSEOUT', [
                    'report_id' => $report->id,
                    'report_number' => $report->report_number,
                    'triggered_by_action_item' => $actionItem->id
                ]);

                // Send notification
                $this->sendReportNotification($report);
            }

        } catch (Throwable $e) {
            // Don't let notification errors fail the update
            Log::error('Failed to update parent report or send notification', [
                'action_item_id' => $actionItem->id,
                'report_id' => $report->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send notification for report status change
     */
    private function sendReportNotification(CermatReport $report): void
    {
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->sendCermatNotification($report);

            Log::info('Notification sent for report status change', [
                'report_id' => $report->id,
                'report_number' => $report->report_number
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to send notification', [
                'report_id' => $report->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get updated action item with relationships
     *
     * @param string|int $actionItemId Action item ID (supports ULID string or integer)
     * @return ActionItem
     */
    private function getUpdatedActionItem(string|int $actionItemId): ActionItem
    {
        return ActionItem::with([
            'cermatReport:id,report_number,area_id',
            'cermatReport.area:id,name',
            'actionCategory:id,name',
            'photos:id,action_item_id,file_path,file_name'
        ])->findOrFail($actionItemId);
    }

    /**
     * Render Kanban card HTML
     */
    private function renderKanbanCard(ActionItem $item): string
    {
        return view('user.partials.kanban_card', compact('item'))->render();
    }
}
