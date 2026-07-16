<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupOldPdfs extends Command
{
    protected $signature = 'cermat:cleanup-pdfs';
    protected $description = 'Clean up old temporary PDF files from notifications (older than 24 hours)';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Starting cleanup of old PDF files...');

        try {
            $notificationService->cleanupOldPdfs();
            $this->info('✅ Cleanup completed successfully!');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
