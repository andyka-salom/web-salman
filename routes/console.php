<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

// ============================================================
// QUEUE WORKER
// Memproses semua job dari antrian:
//   - SendWhatsAppNotification (notifikasi CeRMAT)
//   - ProcessBroadcastJob (broadcast manual & group contact)
//
// --stop-when-empty : berhenti otomatis setelah semua job selesai
//                     (cocok untuk shared hosting tanpa persistent worker)
// --tries=2         : retry maksimal 2x jika job gagal
// --timeout=600     : batas waktu 10 menit per job
//                     (broadcast besar butuh waktu lebih lama dari notifikasi biasa)
// withoutOverlapping: cegah dua proses queue berjalan bersamaan
// runInBackground   : tidak memblokir schedule lain
// ============================================================
Schedule::command('queue:work --stop-when-empty --tries=2 --timeout=600')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/queue-worker.log'));

// Clean up old PDF files every day at 2:00 AM
Schedule::command('cermat:cleanup-pdfs')
    ->dailyAt('02:00')
    ->withoutOverlapping();
