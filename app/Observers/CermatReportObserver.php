<?php

namespace App\Observers;

use App\Models\CermatReport;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class CermatReportObserver
{
    public function creating(CermatReport $report): void
    {
        // Auto-generate report number if not set
        if (!$report->report_number) {
            $report->report_number = $this->generateReportNumber();
        }

        // Set current_target_date from internal_deadline
        if ($report->internal_deadline && !$report->current_target_date) {
            $report->current_target_date = $report->internal_deadline;
        }
    }

    public function created(CermatReport $report): void
    {
        $this->logAudit($report, 'created');
        ActivityLog::logCreate($report);
    }

    public function updating(CermatReport $report): void
    {
        // Track status changes
        if ($report->isDirty('status')) {
            $oldStatus = $report->getOriginal('status');
            $newStatus = $report->status;

            ActivityLog::log('status_change', $report,
                "Status changed from {$oldStatus} to {$newStatus}");
        }
    }

    public function updated(CermatReport $report): void
    {
        $this->logAudit($report, 'updated', $report->getOriginal());
        ActivityLog::logUpdate($report);
    }

    public function deleted(CermatReport $report): void
    {
        $this->logAudit($report, 'deleted');
        ActivityLog::logDelete($report);
    }

    public function restored(CermatReport $report): void
    {
        $this->logAudit($report, 'restored');
        ActivityLog::log('restore', $report, 'Report restored from trash');
    }

    private function logAudit(CermatReport $report, string $event, array $oldValues = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($report),
            'auditable_id' => $report->id,
            'event' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $event === 'deleted' ? null : $report->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function generateReportNumber(): string
    {
        $prefix = 'CRM';
        $year = now()->year;
        $month = now()->format('m');

        $lastReport = CermatReport::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->latest('id')
            ->first();

        $sequence = $lastReport ? (int) substr($lastReport->report_number, -4) + 1 : 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}
