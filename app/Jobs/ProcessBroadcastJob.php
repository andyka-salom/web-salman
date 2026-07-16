<?php

namespace App\Jobs;

use App\Models\BroadcastMessage;
use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimal percobaan jika job gagal
     */
    public int $tries = 2;

    /**
     * Timeout per job (detik)
     * Estimasi: 50 penerima x 9 detik delay = ~450 detik
     * Diberi buffer menjadi 600 detik (10 menit)
     */
    public int $timeout = 600;

    /**
     * Jeda sebelum retry jika gagal (detik)
     */
    public int $backoff = 60;

    public function __construct(
        public readonly string $broadcastId, // UUID — string, bukan int
    ) {}

    /**
     * Eksekusi job
     */
    public function handle(FonnteService $fonnteService): void
    {
        $broadcast = BroadcastMessage::with('recipients')->find($this->broadcastId);

        if (!$broadcast) {
            Log::error("[ProcessBroadcastJob] Broadcast not found", [
                'broadcast_id' => $this->broadcastId,
            ]);
            return;
        }

        // Skip jika sudah selesai atau di-cancel
        if (in_array($broadcast->status, ['completed', 'scheduled', 'cancelled'], true)) {
            Log::info("[ProcessBroadcastJob] Skipped — already in terminal status", [
                'broadcast_id' => $this->broadcastId,
                'status'       => $broadcast->status,
            ]);
            return;
        }

        Log::info("[ProcessBroadcastJob] Job started", [
            'broadcast_id'     => $broadcast->id,
            'attempt'          => $this->attempts(),
            'total_recipients' => $broadcast->total_recipients,
        ]);

        $result = $fonnteService->processBroadcast($broadcast);

        if ($result['success']) {
            Log::info("[ProcessBroadcastJob] Job completed successfully", [
                'broadcast_id' => $broadcast->id,
            ]);
        } else {
            Log::error("[ProcessBroadcastJob] Job completed with error", [
                'broadcast_id' => $broadcast->id,
                'error'        => $result['error'] ?? 'Unknown error',
            ]);

            // Lempar exception agar queue mencatat sebagai failed & trigger retry
            throw new \RuntimeException(
                "[ProcessBroadcastJob] Fonnte API failed: " . ($result['error'] ?? 'Unknown error')
            );
        }
    }

    /**
     * Tangani jika job gagal permanen (setelah semua retry habis)
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[ProcessBroadcastJob] Job failed permanently after {$this->tries} attempts", [
            'broadcast_id' => $this->broadcastId,
            'error'        => $exception->getMessage(),
        ]);

        // Update status broadcast ke failed
        $broadcast = BroadcastMessage::find($this->broadcastId);
        if ($broadcast) {
            $broadcast->update([
                'status'        => 'failed',
                'error_message' => 'Job gagal setelah ' . $this->tries . ' percobaan: ' . $exception->getMessage(),
            ]);
        }
    }
}