<?php

namespace App\Jobs;

use App\Models\ActionItem;
use App\Models\CermatReport;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job untuk mengirim notifikasi WhatsApp via Fonnte.
 *
 * Digunakan oleh CermatReportController untuk semua event:
 *   - 'supervisor_decision' : laporan baru, disetujui, ditolak, resubmit
 *   - 'status_change'       : progress, awaiting closeout, closed, no action
 *   - 'action_item'         : penugasan action item ke responsible
 *
 * Job ini ringan — tidak ada usleep() di dalamnya.
 * Semua jeda antar pesan dihandle Fonnte di sisinya via parameter 'delay'.
 *
 * Retry: 2x dengan backoff 60 detik.
 * Timeout: 120 detik (cukup untuk generate PDF + 1 API call Fonnte).
 */
class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimal percobaan ulang jika job gagal.
     */
    public int $tries = 2;

    /**
     * Timeout job dalam detik.
     * 120 detik cukup untuk generate PDF + satu API call Fonnte,
     * tanpa risiko timeout akibat usleep() berantai.
     */
    public int $timeout = 120;

    /**
     * Jeda sebelum retry (detik).
     */
    public int $backoff = 60;

    // Semua ID bertipe string karena model menggunakan HasUuids
    private string  $type;
    private string  $reportId;
    private ?string $actionItemId;
    private ?string $recipientId;
    private ?string $decisionType;

    /**
     * @param  string          $type          'supervisor_decision' | 'status_change' | 'action_item'
     * @param  CermatReport    $report
     * @param  ActionItem|null $actionItem    Wajib jika $type === 'action_item'
     * @param  string|null     $recipientId   Wajib jika $type === 'action_item' (UUID)
     * @param  string|null     $decisionType  Wajib jika $type === 'supervisor_decision'
     *                                        ('submit' | 'approve' | 'reject' | 'resubmit')
     */
    public function __construct(
        string       $type,
        CermatReport $report,
        ?ActionItem  $actionItem   = null,
        ?string      $recipientId  = null, // UUID — string, bukan int
        ?string      $decisionType = null,
    ) {
        $this->type         = $type;
        $this->reportId     = $report->id;
        $this->actionItemId = $actionItem?->id;
        $this->recipientId  = $recipientId;
        $this->decisionType = $decisionType;
    }

    /**
     * Eksekusi job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info('[SendWhatsAppNotification] Job dimulai.', [
            'type'           => $this->type,
            'report_id'      => $this->reportId,
            'action_item_id' => $this->actionItemId,
            'recipient_id'   => $this->recipientId,
            'decision_type'  => $this->decisionType,
            'attempt'        => $this->attempts(),
        ]);

        // Re-fetch fresh dari database untuk menghindari stale data
        $report = CermatReport::find($this->reportId);

        if (!$report) {
            Log::warning('[SendWhatsAppNotification] Laporan tidak ditemukan, job diabaikan.', [
                'report_id' => $this->reportId,
            ]);
            return;
        }

        try {
            match ($this->type) {

                'supervisor_decision' => $this->handleSupervisorDecision($notificationService, $report),

                'status_change'       => $notificationService->sendCermatNotification($report),

                'action_item'         => $this->handleActionItem($notificationService, $report),

                default => Log::warning('[SendWhatsAppNotification] Tipe notifikasi tidak dikenal.', [
                    'type' => $this->type,
                ]),
            };

            Log::info('[SendWhatsAppNotification] Job selesai.', [
                'type'      => $this->type,
                'report_id' => $this->reportId,
            ]);

        } catch (Throwable $e) {
            Log::error('[SendWhatsAppNotification] Job gagal.', [
                'type'      => $this->type,
                'report_id' => $this->reportId,
                'error'     => $e->getMessage(),
                'attempt'   => $this->attempts(),
            ]);

            // Lempar ulang agar queue mencatat sebagai failed dan bisa retry
            throw $e;
        }
    }

    /**
     * Dipanggil setelah semua retry habis.
     */
    public function failed(Throwable $e): void
    {
        Log::critical('[SendWhatsAppNotification] Job gagal setelah semua retry.', [
            'type'      => $this->type,
            'report_id' => $this->reportId,
            'error'     => $e->getMessage(),
        ]);
    }

    // ============================================================
    // PRIVATE — HANDLER PER TIPE
    // ============================================================

    private function handleSupervisorDecision(NotificationService $service, CermatReport $report): void
    {
        if (empty($this->decisionType)) {
            Log::warning('[SendWhatsAppNotification] decisionType kosong untuk supervisor_decision.');
            return;
        }

        $service->sendSupervisorDecisionNotification($report, $this->decisionType);
    }

    private function handleActionItem(NotificationService $service, CermatReport $report): void
    {
        if (!$this->actionItemId || !$this->recipientId) {
            Log::warning('[SendWhatsAppNotification] actionItemId atau recipientId kosong untuk action_item.');
            return;
        }

        $actionItem = ActionItem::find($this->actionItemId);
        $recipient  = User::find($this->recipientId);

        if (!$actionItem || !$recipient) {
            Log::warning('[SendWhatsAppNotification] ActionItem atau Recipient tidak ditemukan.', [
                'action_item_id' => $this->actionItemId,
                'recipient_id'   => $this->recipientId,
            ]);
            return;
        }

        $service->sendCermatNotification($recipient, $report, $actionItem);
    }
}