<?php
// app/Enums/ReportStatus.php

namespace App\Enums;

enum ReportStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case AWAITING_SUPERVISOR_APPROVAL = 'awaiting_supervisor_approval';
    case REJECTED_BY_SUPERVISOR = 'rejected_by_supervisor';
    case AWAITING_HSSE_REVIEW = 'awaiting_hsse_review';
    case ACTION_IN_PROGRESS = 'action_in_progress';
    case AWAITING_CLOSEOUT = 'awaiting_closeout';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::AWAITING_SUPERVISOR_APPROVAL => 'Awaiting Supervisor Approval',
            self::REJECTED_BY_SUPERVISOR => 'Rejected by Supervisor',
            self::AWAITING_HSSE_REVIEW => 'Awaiting HSSE Review',
            self::ACTION_IN_PROGRESS => 'Action In Progress',
            self::AWAITING_CLOSEOUT => 'Awaiting Close-out',
            self::CLOSED => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::SUBMITTED => 'primary',
            self::AWAITING_SUPERVISOR_APPROVAL => 'info',
            self::REJECTED_BY_SUPERVISOR => 'danger',
            self::AWAITING_HSSE_REVIEW => 'warning',
            self::ACTION_IN_PROGRESS => 'primary',
            self::AWAITING_CLOSEOUT => 'success',
            self::CLOSED => 'success',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::REJECTED_BY_SUPERVISOR,
        ]);
    }

    public function canApprove(): bool
    {
        return $this === self::AWAITING_SUPERVISOR_APPROVAL;
    }

    public function canReview(): bool
    {
        return in_array($this, [
            self::AWAITING_HSSE_REVIEW,
            self::AWAITING_SUPERVISOR_APPROVAL,
            self::SUBMITTED,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
