<?php

namespace App\Services;

use App\Models\ActionItem;
use App\Models\CermatReport;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;

class NotificationService
{
    protected FonnteService $fonnteService;

    /**
     * Delay (detik) yang dikirim ke Fonnte untuk jeda antar nomor.
     * Fonnte yang handle — server tidak usleep() sama sekali.
     *
     * Range 5–10 dipilih agar terlihat human-like di sisi WhatsApp.
     */
    private const FONNTE_DELAY_OPTIONS = [5, 6, 7, 8, 9, 10];

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    // ============================================================
    // PUBLIC ENTRY POINTS
    // ============================================================

    /**
     * Entry point utama untuk notifikasi CeRMAT.
     *
     * @param  User|CermatReport  $target
     * @param  CermatReport|null  $reportContext  Diperlukan jika $target adalah User (action item)
     * @param  ActionItem|null    $actionItem     Diperlukan jika $target adalah User (action item)
     */
    public function sendCermatNotification(
        User|CermatReport $target,
        ?CermatReport     $reportContext = null,
        ?ActionItem       $actionItem    = null
    ): void {
        try {
            if ($target instanceof User && $reportContext && $actionItem) {
                Log::info('[NotificationService] Action item assignment.', [
                    'user_id'   => $target->id,
                    'report_id' => $reportContext->id,
                    'action_id' => $actionItem->id,
                ]);
                $this->sendActionItemNotification($target, $reportContext, $actionItem);

            } elseif ($target instanceof CermatReport) {
                Log::info('[NotificationService] Status change.', [
                    'report_id' => $target->id,
                    'status'    => $target->status,
                ]);
                $this->sendStatusChangeNotification($target);
            }
        } catch (Throwable $e) {
            Log::error('[NotificationService] Error tidak terduga.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Kirim notifikasi keputusan supervisor.
     */
    public function sendSupervisorDecisionNotification(CermatReport $report, string $decisionType): void
    {
        try {
            $report->loadMissing(['reporter', 'reporter.company', 'lineSupervisor', 'hsseOfficer', 'closeOutPerformer', 'area']);

            $info = $this->getSupervisorDecisionInfo($report, $decisionType);
            if (!$info) {
                Log::info('[NotificationService] Tidak ada konfigurasi untuk keputusan.', ['decision' => $decisionType]);
                return;
            }

            $recipients = $this->filterRecipients($report, $info['recipients']->filter());
            if ($recipients->isEmpty()) {
                Log::warning('[NotificationService] Tidak ada penerima valid.', ['decision' => $decisionType]);
                return;
            }

            Log::info('[NotificationService] Mengirim notifikasi keputusan SPV.', [
                'decision'         => $decisionType,
                'recipient_count'  => $recipients->count(),
            ]);

            $pdfUrl  = $this->generateAndStorePdf($report);
            $message = $this->buildMessage($info['subject'], $info['intro'], $info['actionUrl']);

            $this->sendBatch($recipients, $message, $pdfUrl);

        } catch (Throwable $e) {
            Log::error('[NotificationService] Error notifikasi keputusan SPV.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Hapus PDF sementara yang sudah lebih dari 24 jam.
     * Dipanggil dari schedule Kernel.
     */
    public function cleanupOldPdfs(): void
    {
        try {
            $files        = Storage::disk('public')->files('temp_notifications');
            $now          = now()->timestamp;
            $deletedCount = 0;

            foreach ($files as $file) {
                if (($now - Storage::disk('public')->lastModified($file)) > 86400) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('[NotificationService] Cleanup PDF selesai.', ['deleted' => $deletedCount]);

        } catch (Throwable $e) {
            Log::error('[NotificationService] Cleanup PDF gagal.', ['error' => $e->getMessage()]);
        }
    }

    // ============================================================
    // PRIVATE — NOTIFICATION SENDERS
    // ============================================================

    /**
     * Notifikasi penugasan action item ke satu orang (responsible).
     * Tetap satu penerima, langsung kirim tanpa loop.
     */
    private function sendActionItemNotification(
        User         $recipient,
        CermatReport $report,
        ActionItem   $actionItem
    ): void {
        if (empty($recipient->phone)) {
            Log::warning('[NotificationService] User tidak punya nomor HP.', ['user_id' => $recipient->id]);
            return;
        }

        $subject = "Penugasan Tindakan Perbaikan - Laporan #{$report->report_number}";
        $intro   = sprintf(
            'Anda ditugaskan melakukan tindakan perbaikan: "%s" dengan target penyelesaian %s.',
            $actionItem->description,
            Carbon::parse($actionItem->target_date)->isoFormat('D MMMM YYYY')
        );

        $pdfUrl  = $this->generateAndStorePdf($report);
        $message = $this->buildMessage($subject, $intro, route('cermat.reports.show', $report->id), $recipient);

        $this->sendSingle($recipient, $message, $pdfUrl);
    }

    /**
     * Notifikasi perubahan status laporan ke semua penerima terkait.
     * Semua penerima dikirim dalam SATU API call Fonnte.
     */
    private function sendStatusChangeNotification(CermatReport $report): void
    {
        $report->loadMissing(['reporter', 'reporter.company', 'lineSupervisor', 'hsseOfficer', 'closeOutPerformer', 'area']);

        $info = $this->getStatusNotificationInfo($report);
        if (!$info) {
            Log::info('[NotificationService] Tidak ada konfigurasi notifikasi untuk status.', ['status' => $report->status]);
            return;
        }

        $recipients = $this->filterRecipients($report, $info['recipients']->filter());
        if ($recipients->isEmpty()) {
            Log::warning('[NotificationService] Tidak ada penerima valid untuk status change.', ['status' => $report->status]);
            return;
        }

        Log::info('[NotificationService] Mengirim notifikasi status change.', [
            'status'          => $report->status,
            'recipient_count' => $recipients->count(),
        ]);

        $pdfUrl  = $this->generateAndStorePdf($report);
        $message = $this->buildMessage($info['subject'], $info['intro'], $info['actionUrl']);

        $this->sendBatch($recipients, $message, $pdfUrl);
    }

    // ============================================================
    // PRIVATE — FONNTE SENDERS
    // ============================================================

    /**
     * Kirim pesan ke SATU penerima.
     * Dipakai untuk action item (penerima selalu 1 orang = responsible).
     */
    private function sendSingle(User $recipient, string $message, ?string $pdfUrl = null): void
    {
        $phone = $this->fonnteService->formatPhoneNumber($recipient->phone);

        $payload = [
            'target'      => $phone,
            'message'     => $message,
            'countryCode' => '62',
        ];

        if ($pdfUrl) {
            $payload['url']      = $pdfUrl;
            $payload['filename'] = basename(parse_url($pdfUrl, PHP_URL_PATH));
        }

        Log::info('[NotificationService] Mengirim pesan ke satu penerima.', [
            'user_id' => $recipient->id,
            'phone'   => $phone,
        ]);

        $result = $this->fonnteService->sendMessage($payload);

        if ($result['success']) {
            Log::info('[NotificationService] Berhasil kirim ke penerima.', ['user_id' => $recipient->id]);
        } else {
            Log::error('[NotificationService] Gagal kirim ke penerima.', [
                'user_id' => $recipient->id,
                'error'   => $result['error'],
            ]);
        }
    }

    /**
     * Kirim pesan ke BANYAK penerima dalam SATU API call Fonnte.
     *
     * Semua nomor digabung sebagai target dipisah koma.
     * Fonnte yang handle jeda antar nomor via parameter 'delay'.
     * TIDAK ada usleep() di sini — server tidak diblokir.
     *
     * Pesan yang dikirim adalah pesan generik (tidak per-penerima)
     * karena Fonnte batch API tidak support pesan berbeda per nomor.
     * Untuk pesan personal, gunakan sendSingle() per penerima.
     */
    private function sendBatch(Collection $recipients, string $message, ?string $pdfUrl = null): void
    {
        if ($recipients->isEmpty()) {
            return;
        }

        // Gabungkan semua target: "62xxx|Nama,62yyy|Nama"
        $targets = $recipients->map(function (User $user) {
            $phone = $this->fonnteService->formatPhoneNumber($user->phone);
            $name  = $user->name ?? 'User';
            return "{$phone}|{$name}";
        })->implode(',');

        $delay   = $this->getRandomFonnteDelay();
        $payload = [
            'target'      => $targets,
            'message'     => $message,
            'delay'       => $delay,
            'countryCode' => '62',
        ];

        if ($pdfUrl) {
            $payload['url']      = $pdfUrl;
            $payload['filename'] = basename(parse_url($pdfUrl, PHP_URL_PATH));
        }

        Log::info('[NotificationService] Mengirim batch ke Fonnte.', [
            'recipient_count'  => $recipients->count(),
            'delay_per_number' => $delay . 's',
            'has_pdf'          => !empty($pdfUrl),
        ]);

        $result = $this->fonnteService->sendMessage($payload);

        if ($result['success']) {
            Log::info('[NotificationService] Batch berhasil dikirim.', [
                'recipient_count' => $recipients->count(),
            ]);
        } else {
            Log::error('[NotificationService] Batch gagal dikirim.', [
                'error'           => $result['error'],
                'recipient_count' => $recipients->count(),
            ]);
        }
    }

    // ============================================================
    // PRIVATE — PDF HELPER
    // ============================================================

    /**
     * Generate PDF laporan dan simpan sementara di storage public.
     * Return URL publik yang bisa diakses Fonnte untuk lampirkan ke pesan.
     */
    private function generateAndStorePdf(CermatReport $report): ?string
    {
        try {
            $report->loadMissing([
                'reporter', 'lineSupervisor', 'hsseOfficer', 'closeOutPerformer', 'area',
                'unsafeActs', 'unsafeConditions', 'pelanggaran', 'securityEventCategory',
                'actionItems.responsible', 'actionItems.actionCategory',
                'attachments', 'riskAssessments',
            ]);

            $pdf = Pdf::loadView('cermat.pdf', compact('report'))
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $safeNumber = str_replace(['/', '\\', ' '], '_', $report->report_number);
            $filename   = 'temp_notifications/Laporan_CeRMAT_' . $safeNumber . '_' . time() . '.pdf';

            Storage::disk('public')->put($filename, $pdf->output());

            $url = Storage::disk('public')->url($filename);
            Log::info('[NotificationService] PDF berhasil digenerate.', ['url' => $url]);

            return $url;

        } catch (Throwable $e) {
            Log::error('[NotificationService] Gagal generate PDF.', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // ============================================================
    // PRIVATE — MESSAGE BUILDER
    // ============================================================

    /**
     * Susun teks pesan WhatsApp.
     *
     * Jika $recipient diberikan, sapaan disesuaikan per role.
     * Jika tidak (batch generic), sapaan menggunakan "Yth. Bapak/Ibu".
     *
     * Footer divariasikan secara acak agar pesan tidak 100% identik
     * antar broadcast — membantu menghindari deteksi spam oleh WhatsApp.
     */
    private function buildMessage(
        string  $subject,
        string  $intro,
        string  $actionUrl,
        ?User   $recipient = null
    ): string {
        $greeting = $recipient
            ? $this->resolveGreeting($recipient)
            : 'Yth. Bapak/Ibu';

        $closing = $this->getRandomClosing();

        return implode("\n", [
            "📢 *{$subject}*",
            "",
            "{$greeting},",
            "",
            $intro,
            "",
            "🔗 Detail laporan: {$actionUrl}",
            "",
            $closing,
        ]);
    }

    // ============================================================
    // PRIVATE — RECIPIENT HELPERS
    // ============================================================

    /**
     * Filter penerima:
     * - Harus punya nomor HP
     * - Reporter dari PHM hanya menerima notifikasi jika requires_feedback aktif
     */
    private function filterRecipients(CermatReport $report, Collection $recipients): Collection
    {
        $isReporterPHM = strtolower($report->reporter->company->name ?? '') === 'pertamina hulu mahakam';

        return $recipients->filter(function (?User $user) use ($report, $isReporterPHM) {
            if (!$user || empty($user->phone)) {
                return false;
            }

            // Bukan reporter — selalu kirim
            if ($user->id !== $report->reporter_id) {
                return true;
            }

            // Reporter PHM — kirim hanya jika requires_feedback aktif
            if ($isReporterPHM) {
                return (bool) $report->requires_feedback;
            }

            return true;

        })->unique('id')->values();
    }

    /**
     * Ambil user koordinator berdasarkan company reporter.
     */
    private function getCompanyCoordinators(CermatReport $report): Collection
    {
        $companyId = $report->reporter->company_id ?? null;

        if (!$companyId) {
            return collect();
        }

        return User::role('koordinator')->where('company_id', $companyId)->get();
    }

    // ============================================================
    // PRIVATE — ANTI-SPAM HELPERS
    // ============================================================

    /**
     * Ambil delay acak (integer detik) untuk dikirim ke Fonnte.
     * Fonnte yang menerapkan jeda ini antar nomor di sisinya.
     */
    private function getRandomFonnteDelay(): int
    {
        return self::FONNTE_DELAY_OPTIONS[array_rand(self::FONNTE_DELAY_OPTIONS)];
    }

    /**
     * Variasi penutup pesan.
     * Pesan yang tidak 100% identik antar penerima lebih sulit dideteksi spam.
     */
    private function getRandomClosing(): string
    {
        $closings = [
            "Terima kasih,\nTim CERMAT PHM",
            "Salam,\nTim CERMAT PHM",
            "Hormat kami,\nCERMAT PHM",
            "Terima kasih atas perhatiannya,\nTim CERMAT PHM",
        ];

        return $closings[array_rand($closings)];
    }

    /**
     * Tentukan sapaan berdasarkan role user.
     */
    private function resolveGreeting(User $user): string
    {
        return match (true) {
            $user->hasRole('koordinator')                                         => 'Halo Partner',
            $user->hasAnyRole(['hsse', 'hsse_officer'])                          => 'Halo Tim HSSE',
            $user->hasRole('rses')                                                => 'Halo Tim RSES',
            $user->hasAnyRole(['supervisor', 'line_supervisor'])                  => 'Halo Supervisor',
            default                                                               => "Halo {$user->name}",
        };
    }

    // ============================================================
    // PRIVATE — NOTIFICATION INFO BUILDERS
    // ============================================================

    /**
     * Tentukan penerima & konten notifikasi berdasarkan STATUS laporan.
     */
    private function getStatusNotificationInfo(CermatReport $report): ?array
    {
        $hsseOfficer = $report->hsseOfficer?->name ?? 'Tim HSSE';

        return match ($report->status) {

            CermatReport::STATUS_ACTION_IN_PROGRESS => [
                'recipients' => collect([
                    $report->reporter,
                    $report->lineSupervisor,
                    ...User::role('rses')->get()->all(),
                ])->filter()->unique('id'),
                'subject'   => "Tindakan Perbaikan Dimulai - #{$report->report_number}",
                'intro'     => "Laporan direkomendasikan oleh {$hsseOfficer}. Tindakan perbaikan sedang berjalan.",
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            CermatReport::STATUS_AWAITING_CLOSEOUT => [
                'recipients' => collect([
                    $report->reporter,
                    $report->lineSupervisor,
                    $report->hsseOfficer,
                    ...User::role(['hsse', 'rses'])->get()->all(),
                ])->filter()->unique('id'),
                'subject'   => "Laporan #{$report->report_number} Siap untuk Close-out",
                'intro'     => "Semua tindakan perbaikan telah selesai. Menunggu close-out dari HSSE.",
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            CermatReport::STATUS_CLOSED => [
                'recipients' => collect([
                    $report->reporter,
                    $report->lineSupervisor,
                    $report->hsseOfficer,
                    $report->closeOutPerformer,
                    ...User::role(['rses', 'hsse'])->get()->all(),
                ])->filter()->unique('id'),
                'subject'   => "Selesai: Laporan #{$report->report_number} Ditutup",
                'intro'     => 'Laporan telah resmi ditutup.',
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            CermatReport::STATUS_NO_ACTION_REQUIRED => [
                'recipients' => collect([
                    $report->reporter,
                    $report->lineSupervisor,
                    $report->hsseOfficer,
                    ...User::role('rses')->get()->all(),
                ])->filter()->unique('id'),
                'subject'   => "Laporan #{$report->report_number} - Tidak Perlu Tindakan",
                'intro'     => 'Disetujui Supervisor: tidak diperlukan tindakan lebih lanjut.',
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            default => null,
        };
    }

    /**
     * Tentukan penerima & konten notifikasi berdasarkan KEPUTUSAN SUPERVISOR.
     */
    private function getSupervisorDecisionInfo(CermatReport $report, string $decisionType): ?array
    {
        $reporterName    = $report->reporter?->name ?? 'N/A';
        $supervisorName  = $report->lineSupervisor?->name ?? 'Supervisor';
        $rejectionReason = $report->rejection_reason ?? $report->supervisor_notes ?? 'N/A';

        return match ($decisionType) {

            'submit' => [
                'recipients' => collect([
                    $report->lineSupervisor,
                    ...User::role(['hsse', 'rses'])->get()->all(),
                    ...$this->getCompanyCoordinators($report)->all(),
                ])->filter()->unique('id'),
                'subject'   => "Laporan Baru #{$report->report_number} Menunggu Persetujuan",
                'intro'     => "Laporan dari {$reporterName} memerlukan persetujuan Supervisor.",
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            'approve' => [
                'recipients' => collect([
                    $report->reporter,
                    $report->lineSupervisor,
                    $report->hsseOfficer,
                    ...User::role(['hsse', 'rses'])->get()->all(),
                ])->filter()->unique('id'),
                'subject'   => "Laporan #{$report->report_number} Disetujui Supervisor",
                'intro'     => "Disetujui oleh {$supervisorName}, diteruskan ke HSSE.",
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            'reject' => [
                'recipients' => collect([
                    $report->reporter,
                    $report->lineSupervisor,
                    $report->hsseOfficer,
                    ...User::role('rses')->get()->all(),
                ])->filter()->unique('id'),
                'subject'   => "Laporan #{$report->report_number} DITOLAK oleh Supervisor",
                'intro'     => "Ditolak oleh {$supervisorName}. Alasan: {$rejectionReason}.",
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            'resubmit' => [
                'recipients' => collect([
                    $report->lineSupervisor,
                    ...User::role(['hsse', 'rses'])->get()->all(),
                    ...$this->getCompanyCoordinators($report)->all(),
                ])->filter()->unique('id'),
                'subject'   => "Revisi #{$report->report_number} Menunggu Persetujuan",
                'intro'     => "Laporan revisi dari {$reporterName} telah dikirim ulang.",
                'actionUrl' => route('cermat.reports.show', $report->id),
            ],

            default => null,
        };
    }
}
