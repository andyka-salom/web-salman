<?php
// app/Policies/CermatReportPolicy.php

namespace App\Policies;

use App\Models\{CermatReport, User};

class CermatReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CermatReport $report): bool
    {
        return $user->id === $report->reporter_id
            || $user->id === $report->line_supervisor_id
            || $user->id === $report->hsse_officer_id
            || $user->hasAnyRole(['super-admin', 'hsse', 'rses']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CermatReport $report): bool
    {
        if ($user->id === $report->reporter_id && $report->canEdit()) {
            return true;
        }

        if ($user->id === $report->line_supervisor_id) {
            return true;
        }

        return $user->hasAnyRole(['super-admin', 'hsse', 'rses']);
    }

    public function delete(User $user, CermatReport $report): bool
    {
        return $user->hasRole('super-admin');
    }

    public function approve(User $user, CermatReport $report): bool
    {
        return $user->id === $report->line_supervisor_id
            && $report->canApprove();
    }

    public function review(User $user, CermatReport $report): bool
    {
        return $user->hasAnyRole(['hsse', 'rses'])
            && $report->canReview();
    }

    public function close(User $user, CermatReport $report): bool
    {
        return $user->hasAnyRole(['hsse', 'rses'])
            && $report->status === 'awaiting_closeout'
            && $report->allActionsCompleted();
    }

    public function resubmit(User $user, CermatReport $report): bool
    {
        return $user->id === $report->reporter_id
            && $report->status === 'rejected_by_supervisor';
    }

    public function downloadPdf(User $user, CermatReport $report): bool
    {
        return $this->view($user, $report);
    }
}
