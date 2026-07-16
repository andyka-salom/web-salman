<?php

namespace App\Services;

use App\Models\BroadcastMessage;
use App\Models\BroadcastRecipient;
use App\Models\WhatsAppGroupCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FonnteService
{
    protected string $token;
    protected string $baseUrl;

    /**
     * Delay ranges yang dikirim ke Fonnte (dalam detik).
     * Format: integer tunggal — lebih aman & terdokumentasi di API Fonnte.
     * Fonnte akan menunggu N detik sebelum kirim ke nomor berikutnya.
     *
     * Range 5–10 detik dipilih karena:
     * - Cukup lambat agar tidak terdeteksi bot oleh WhatsApp
     * - Cukup cepat untuk kebutuhan operasional
     */
    private const DELAY_OPTIONS = [5, 6, 7, 8, 9, 10];

    public function __construct()
    {
        $this->token   = config('services.fonnte.token', '');
        $this->baseUrl = config('services.fonnte.url', '');

        if (empty($this->token)) {
            Log::critical('[FonnteService] API token tidak dikonfigurasi.');
            throw new \RuntimeException('Fonnte API token not configured.');
        }

        if (empty($this->baseUrl)) {
            Log::critical('[FonnteService] API URL tidak dikonfigurasi.');
            throw new \RuntimeException('Fonnte API URL not configured.');
        }
    }

    // ============================================================
    // PUBLIC — BROADCAST
    // ============================================================

    /**
     * Proses broadcast: kumpul semua nomor → kirim dalam SATU API call.
     * Fonnte yang handle jeda antar nomor via parameter 'delay'.
     * Server tidak perlu usleep() sama sekali.
     */
    public function processBroadcast(BroadcastMessage $broadcast): array
    {
        try {
            Log::info('[FonnteService] Mulai proses broadcast.', [
                'broadcast_id' => $broadcast->id,
                'target_type'  => $broadcast->target_type,
                'scheduled'    => !empty($broadcast->scheduled_at),
            ]);

            $recipients = $broadcast->recipients;

            if ($recipients->isEmpty()) {
                Log::warning('[FonnteService] Tidak ada penerima.', ['broadcast_id' => $broadcast->id]);
                return ['success' => false, 'error' => 'No recipients found'];
            }

            // Validasi & resolusi URL media
            $fullMediaUrl = $this->resolveMediaUrl($broadcast);
            if ($broadcast->media_url && $fullMediaUrl === null) {
                // File media ada di record tapi tidak ditemukan di disk
                $broadcast->update(['status' => 'failed', 'error_message' => 'Media file tidak ditemukan di server.']);
                return ['success' => false, 'error' => 'Media file tidak ditemukan di server.'];
            }

            // Format semua target sekaligus: "62xxx|Nama|Role,62yyy|Nama|Role"
            $targets = $recipients->map(function (BroadcastRecipient $recipient) {
                $phone = $this->formatPhoneNumber($recipient->phone_number);
                $name  = $recipient->recipient_name ?? 'User';
                $role  = ucfirst($recipient->recipient_type ?? 'member');
                return "{$phone}|{$name}|{$role}";
            })->implode(',');

            // Payload dasar
            $delay   = $this->getRandomDelay();
            $payload = [
                'target'      => $targets,
                'message'     => $broadcast->message,
                'delay'       => $delay,
                'countryCode' => '62',
                'schedule'    => 0,
            ];

            // Handle jadwal kirim
            if ($broadcast->scheduled_at) {
                $scheduledTs        = Carbon::parse($broadcast->scheduled_at, 'Asia/Makassar')->timestamp;
                $payload['schedule'] = $scheduledTs;

                Log::info('[FonnteService] Broadcast dijadwalkan.', [
                    'broadcast_id' => $broadcast->id,
                    'timestamp'    => $scheduledTs,
                    'formatted'    => Carbon::createFromTimestamp($scheduledTs)->toDateTimeString(),
                ]);
            }

            // Handle media
            if ($fullMediaUrl) {
                $payload['url'] = $fullMediaUrl;
                if ($broadcast->media_filename) {
                    $payload['filename'] = $broadcast->media_filename;
                }
            }

            Log::info('[FonnteService] Mengirim ke Fonnte API.', [
                'broadcast_id'     => $broadcast->id,
                'total_recipients' => $recipients->count(),
                'delay_per_number' => $delay . 's',
                'has_media'        => isset($payload['url']),
                'has_schedule'     => $payload['schedule'] > 0,
            ]);

            $result = $this->sendMessage($payload);

            // Update status broadcast & semua recipient sekaligus
            if ($result['success']) {
                $status = $broadcast->scheduled_at ? 'scheduled' : 'completed';

                $broadcast->update([
                    'status'       => $status,
                    'sent_at'      => now(),
                    'sent_count'   => $recipients->count(),
                    'api_response' => $result,
                ]);

                $broadcast->recipients()->update([
                    'status'  => $status,
                    'sent_at' => now(),
                ]);

                Log::info('[FonnteService] Broadcast berhasil.', [
                    'broadcast_id' => $broadcast->id,
                    'status'       => $status,
                    'sent_count'   => $recipients->count(),
                ]);
            } else {
                $errorMsg = $result['error'] ?? 'Unknown API Error';

                $broadcast->update([
                    'status'        => 'failed',
                    'error_message' => $errorMsg,
                    'failed_count'  => $recipients->count(),
                    'api_response'  => $result,
                ]);

                $broadcast->recipients()->update([
                    'status'        => 'failed',
                    'error_message' => $errorMsg,
                ]);

                Log::error('[FonnteService] Broadcast gagal.', [
                    'broadcast_id' => $broadcast->id,
                    'error'        => $errorMsg,
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('[FonnteService] Exception saat proses broadcast.', [
                'broadcast_id' => $broadcast->id ?? 'unknown',
                'error'        => $e->getMessage(),
            ]);

            $broadcast->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    // PUBLIC — SINGLE / BATCH MESSAGE
    // ============================================================

    /**
     * Kirim pesan ke Fonnte API.
     * Bisa dipakai untuk satu nomor maupun batch (target dipisah koma).
     *
     * @param  array $payload  Payload lengkap sesuai spesifikasi Fonnte.
     * @return array{success: bool, status: int, data: array, error: ?string}
     */
    public function sendMessage(array $payload): array
    {
        try {
            Log::info('[FonnteService] Mengirim request ke Fonnte.', [
                'endpoint'     => "{$this->baseUrl}/send",
                'target_count' => substr_count($payload['target'] ?? '', ',') + 1,
                'has_url'      => isset($payload['url']),
                'has_schedule' => isset($payload['schedule']) && $payload['schedule'] > 0,
                'msg_preview'  => mb_substr($payload['message'] ?? '', 0, 60) . '...',
            ]);

            $response = Http::withHeaders(['Authorization' => $this->token])
                ->timeout(60)
                ->asMultipart()
                ->post("{$this->baseUrl}/send", $payload);

            $responseData = $response->json() ?? [];

            Log::info('[FonnteService] Response diterima dari Fonnte.', [
                'http_status'  => $response->status(),
                'api_status'   => $responseData['status'] ?? 'N/A',
                'reason'       => $responseData['reason'] ?? 'N/A',
                'body_preview' => mb_substr($response->body(), 0, 300),
            ]);

            $isSuccess    = $response->successful() && !empty($responseData['status']);
            $errorMessage = null;

            if (!$isSuccess) {
                $errorMessage = $responseData['reason']
                    ?? $responseData['message']
                    ?? $responseData['error']
                    ?? $responseData['detail']
                    ?? "HTTP {$response->status()}: " . mb_substr($response->body(), 0, 200);

                Log::warning('[FonnteService] Pesan gagal dikirim.', ['error' => $errorMessage]);
            }

            return [
                'success'  => $isSuccess,
                'status'   => $response->status(),
                'data'     => $responseData,
                'response' => $response->body(),
                'error'    => $errorMessage,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[FonnteService] Connection error.', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Gagal terhubung ke server Fonnte: ' . $e->getMessage()];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('[FonnteService] Request error.', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Request error: ' . $e->getMessage()];

        } catch (\Exception $e) {
            Log::error('[FonnteService] Exception tidak terduga.', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    // PUBLIC — WHATSAPP GROUPS
    // ============================================================

    /**
     * Ambil daftar grup WhatsApp dari Fonnte API.
     * Hasil di-cache di database selama 6 jam.
     */
    public function getWhatsAppGroups(bool $forceRefresh = false): array
    {
        try {
            if (!$forceRefresh) {
                $cached = WhatsAppGroupCache::where('last_synced_at', '>', now()->subHours(6))->get();
                if ($cached->isNotEmpty()) {
                    Log::info('[FonnteService] Menggunakan cache grup.', ['count' => $cached->count()]);
                    return ['success' => true, 'data' => $cached, 'from_cache' => true];
                }
            }

            $response = Http::withHeaders(['Authorization' => $this->token])
                ->timeout(30)
                ->post("{$this->baseUrl}/get-whatsapp-group");

            if (!$response->successful()) {
                Log::error('[FonnteService] Gagal ambil grup.', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return ['success' => false, 'error' => 'Failed to fetch WhatsApp groups', 'status' => $response->status()];
            }

            $data = $response->json();

            if (empty($data['status'])) {
                Log::error('[FonnteService] Response tidak valid dari Fonnte.', ['data' => $data]);
                return ['success' => false, 'error' => 'Invalid response from Fonnte API'];
            }

            $groups = collect($data['data'])->map(function (array $group) {
                $members = json_decode($group['member'], true) ?? [];

                return WhatsAppGroupCache::updateOrCreate(
                    ['group_id' => $group['id']],
                    [
                        'group_name'     => $group['name'],
                        'members'        => $members,
                        'member_count'   => count($members),
                        'last_synced_at' => now(),
                    ]
                );
            });

            Log::info('[FonnteService] Grup berhasil disinkronkan.', ['count' => $groups->count()]);

            return ['success' => true, 'data' => $groups, 'from_cache' => false];

        } catch (\Exception $e) {
            Log::error('[FonnteService] Exception saat ambil grup.', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Test koneksi ke Fonnte API.
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders(['Authorization' => $this->token])
                ->timeout(10)
                ->get("{$this->baseUrl}/device");

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'data'    => $response->json(),
                'message' => $response->successful() ? 'Connection successful' : 'Connection failed',
            ];
        } catch (\Exception $e) {
            Log::error('[FonnteService] Test koneksi gagal.', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    // PUBLIC — UTILITIES
    // ============================================================

    /**
     * Format nomor HP ke format 62xxxx.
     * Dipanggil juga dari NotificationService.
     */
    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    // ============================================================
    // PRIVATE — HELPERS
    // ============================================================

    /**
     * Pilih delay acak (integer, dalam detik) dari DELAY_OPTIONS.
     *
     * Menggunakan integer tunggal — lebih kompatibel dengan Fonnte API
     * dibanding format string "min-max" yang belum tentu didukung.
     */
    private function getRandomDelay(): int
    {
        return self::DELAY_OPTIONS[array_rand(self::DELAY_OPTIONS)];
    }

    /**
     * Resolve media URL dari broadcast record.
     * Return null jika tidak ada media, atau false jika file tidak ditemukan.
     */
    private function resolveMediaUrl(BroadcastMessage $broadcast): ?string
    {
        if (empty($broadcast->media_url)) {
            return null; // Tidak ada media — normal
        }

        // Sudah berupa URL penuh
        if (filter_var($broadcast->media_url, FILTER_VALIDATE_URL)) {
            return $broadcast->media_url;
        }

        // Path relatif — validasi keberadaan file
        $relativePath = ltrim(str_replace('/storage/', '', $broadcast->media_url), '/');

        if (!Storage::disk('public')->exists($relativePath)) {
            Log::error('[FonnteService] File media tidak ditemukan.', [
                'broadcast_id'  => $broadcast->id,
                'relative_path' => $relativePath,
                'media_url'     => $broadcast->media_url,
            ]);
            return null; // Sinyal error ke caller
        }

        return asset('storage/' . $relativePath);
    }

    /**
     * Replace placeholder dalam pesan broadcast.
     * Dipanggil jika diperlukan pesan per-penerima.
     */
    protected function replacePlaceholders(string $message, BroadcastRecipient $recipient): string
    {
        return str_replace(
            ['{name}', '{phone}'],
            [$recipient->recipient_name ?? 'User', $recipient->phone_number],
            $message
        );
    }
}
