<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActionItem;
use App\Models\Notification;

class CheckOverdueActions extends Command
{
    protected $signature = 'actions:check-overdue';
    protected $description = 'Check for overdue action items and send notifications';

    public function handle(): int
    {
        $this->info('Checking for overdue action items...');

        $overdueActions = ActionItem::whereNotIn('status', ['completed', 'cannot_do'])
            ->where('current_target_date', '<', now())
            ->where('is_overdue', false)
            ->get();

        foreach ($overdueActions as $action) {
            $action->update(['is_overdue' => true]);

            // Notify responsible person
            Notification::send(
                $action->responsible_id,
                'action_item_overdue',
                'Action Item Overdue',
                "Action item is overdue: {$action->description}",
                $action,
                ['days_overdue' => now()->diffInDays($action->current_target_date)]
            );

            // Notify supervisor
            $report = $action->cermatReport;
            if ($report->line_supervisor_id) {
                Notification::send(
                    $report->line_supervisor_id,
                    'action_item_overdue',
                    'Action Item Overdue (Your Team)',
                    "Action item by {$action->responsible->name} is overdue",
                    $action
                );
            }

            $this->info("Marked action #{$action->id} as overdue");
        }

        $this->info("Processed {$overdueActions->count()} overdue action items");

        return Command::SUCCESS;
    }
}
