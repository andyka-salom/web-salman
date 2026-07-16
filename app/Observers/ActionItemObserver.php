<?php

namespace App\Observers;

use App\Models\ActionItem;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Models\Notification;

class ActionItemObserver
{
    public function creating(ActionItem $actionItem): void
    {
        // Set current_target_date from target_date
        if (!$actionItem->current_target_date) {
            $actionItem->current_target_date = $actionItem->target_date;
        }
    }

    public function created(ActionItem $actionItem): void
    {
        $this->logAudit($actionItem, 'created');

        // Notify responsible person
        Notification::send(
            $actionItem->responsible_id,
            'action_item_assigned',
            'New Action Item Assigned',
            "You have been assigned a new action item: {$actionItem->description}",
            $actionItem
        );
    }

    public function updating(ActionItem $actionItem): void
    {
        // Check if overdue
        $actionItem->checkOverdue();

        // Track status changes
        if ($actionItem->isDirty('status')) {
            $oldStatus = $actionItem->getOriginal('status');
            $newStatus = $actionItem->status;

            ActivityLog::log('status_change', $actionItem,
                "Action item status changed from {$oldStatus} to {$newStatus}");

            // Notify on completion
            if ($newStatus === 'completed') {
                $report = $actionItem->cermatReport;
                if ($report->line_supervisor_id) {
                    Notification::send(
                        $report->line_supervisor_id,
                        'action_item_completed',
                        'Action Item Completed',
                        "Action item completed by {$actionItem->responsible->name}",
                        $actionItem
                    );
                }
            }
        }
    }

    public function updated(ActionItem $actionItem): void
    {
        $this->logAudit($actionItem, 'updated', $actionItem->getOriginal());
    }

    public function deleted(ActionItem $actionItem): void
    {
        $this->logAudit($actionItem, 'deleted');
    }

    private function logAudit(ActionItem $actionItem, string $event, array $oldValues = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($actionItem),
            'auditable_id' => $actionItem->id,
            'event' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $event === 'deleted' ? null : $actionItem->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
