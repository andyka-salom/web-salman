<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActionItem;
use App\Models\Notification;

class SendDueSoonReminders extends Command
{
    protected $signature = 'actions:send-due-soon-reminders';
    protected $description = 'Send reminders for action items due soon';

    public function handle(): int
    {
        $this->info('Sending due soon reminders...');

        // Actions due in 3 days
        $dueSoonActions = ActionItem::dueSoon(3)
            ->whereNotIn('status', ['completed', 'cannot_do'])
            ->get();

        foreach ($dueSoonActions as $action) {
            $daysRemaining = $action->days_remaining;

            Notification::send(
                $action->responsible_id,
                'action_item_due_soon',
                'Action Item Due Soon',
                "Action item due in {$daysRemaining} days: {$action->description}",
                $action,
                ['days_remaining' => $daysRemaining]
            );

            $this->info("Sent reminder for action #{$action->id}");
        }

        $this->info("Sent {$dueSoonActions->count()} reminders");

        return Command::SUCCESS;
    }
}

