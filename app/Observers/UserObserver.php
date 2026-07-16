<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AuditLog;

class UserObserver
{
    public function created(User $user): void
    {
        $this->logAudit($user, 'created');
    }

    public function updated(User $user): void
    {
        // Don't log password changes in audit
        $original = $user->getOriginal();
        unset($original['password']);

        $current = $user->toArray();
        unset($current['password']);

        $this->logAudit($user, 'updated', $original);
    }

    public function deleted(User $user): void
    {
        $this->logAudit($user, 'deleted');
    }

    private function logAudit(User $user, string $event, array $oldValues = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'event' => $event,
            'old_values' => $oldValues ?: null,
            'new_values' => $event === 'deleted' ? null : $user->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
