<?php
namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CrewAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class CertificateVerificationController extends Controller
{
    private const RATE_LIMIT = 10;

    public function index()
    {
        return view('public.certificate-verification.index', [
            'captcha' => $this->generateCaptcha(),
        ]);
    }

    /**
     * SPA (AJAX) → JSON | Fallback non-JS → view.
     */
    public function verify(Request $request)
    {
        // ── Rate limiting ──────────────────────────────────────────
        $key = 'cert-verify:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT)) {
            $seconds = RateLimiter::availableIn($key);
            $msg     = "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.";
            return $request->expectsJson()
                ? response()->json(['error' => 'rate_limit', 'message' => $msg], 429)
                : back()->withInput()->withErrors(['rate_limit' => $msg]);
        }
        RateLimiter::hit($key, 60);

        // ── Validate ───────────────────────────────────────────────
        $request->validate([
            'nik'              => 'nullable|string|max:100',
            'nomor_sertifikat' => 'nullable|string|max:100',
            'captcha_answer'   => 'required|integer',
            'captcha_expected' => 'required|string',
        ]);

        $newCaptcha = $this->generateCaptcha();

        // ── Captcha check ──────────────────────────────────────────
        $expected = $this->decryptCaptcha($request->captcha_expected);
        if ($expected === null || (int) $request->captcha_answer !== $expected) {
            return $request->expectsJson()
                ? response()->json(['error' => 'captcha', 'message' => 'Jawaban captcha salah.', 'captcha' => $newCaptcha], 422)
                : back()->withInput($request->only('nik', 'nomor_sertifikat'))
                        ->withErrors(['captcha' => 'Jawaban captcha salah.'])
                        ->with('captcha', $newCaptcha);
        }

        $nik    = strip_tags(trim($request->nik ?? ''));
        $sertif = strip_tags(trim($request->nomor_sertifikat ?? ''));

        if ($nik === '' && $sertif === '') {
            return $request->expectsJson()
                ? response()->json(['error' => 'input', 'message' => 'Isi minimal satu field pencarian.', 'captcha' => $newCaptcha], 422)
                : back()->withInput()->withErrors(['nik' => 'Isi minimal satu field pencarian.'])->with('captcha', $newCaptcha);
        }

        // ── Query — tampilkan SEMUA hasil apapun result/status-nya ─
        // Kolom DB:
        //   crew_members.nik                     → NIK / kode pelaut
        //   crew_assessments.certificate_number  → nomor sertifikat
        $query = CrewAssessment::with([
            'crewMember:id,name,nik,position',
            'vessel:id,name',
            'company:id,name',
        ]);

        if ($nik !== '') {
            $query->where(function ($q) use ($nik) {
                $q->whereHas('crewMember', fn ($r) => $r->where('nik', $nik))
                  ->orWhere('certificate_number', $nik);
            });
        }

        if ($sertif !== '') {
            $query->where('certificate_number', $sertif);
        }

        $results = $query->orderBy('assessment_date', 'desc')->limit(20)->get();

        // ── Format ─────────────────────────────────────────────────
        $formatted = $results->map(fn ($r) => [
            'name'               => $r->crewMember?->name ?? '—',
            'nik'                => $r->crewMember?->nik  ?? '—',
            'certificate_number' => $r->certificate_number ?: '—',
            'coc'                => $r->coc ?: ($r->crewMember?->position ?: '—'),
            'position_proposed'  => $r->position_proposed  ?: '—',
            'position_type'      => $r->position_type       ?: '—',
            'vessel'             => $r->vessel?->name        ?? '—',
            'company'            => $r->company?->name       ?? '—',
            'assessment_date'    => $r->assessment_date?->format('d M Y') ?? '—',
            'assessor_mar'       => $r->assessor_mar  ?: null,
            'assessor_hse'       => $r->assessor_hse  ?: null,
            'assessor_fmc'       => $r->assessor_fmc  ?: null,
            'valid_from'         => $r->valid_from?->format('d M Y'),
            'valid_until'        => $r->valid_until?->format('d M Y'),
            'result'             => $r->result          ?: 'Belum Dinilai',
            'result_color'       => $r->result_color,
            'status'             => $r->status,
            'notes'              => $r->notes,
            'mev_type'           => $r->mev_type ?: null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['results' => $formatted, 'captcha' => $newCaptcha]);
        }

        // Non-JS fallback
        return view('public.certificate-verification.index', [
            'captcha'      => $newCaptcha,
            'results'      => $formatted,
            'searched'     => true,
            'input_nik'    => $nik,
            'input_sertif' => $sertif,
        ]);
    }

    // ── Captcha helpers ───────────────────────────────────────────────────────

    private function generateCaptcha(): array
    {
        $a      = rand(1, 20);
        $b      = rand(1, 15);
        $answer = $a + $b;
        $token  = Str::random(16);
        $hmac   = hash_hmac('sha256', $answer . '|' . $token, config('app.key'));
        $payload = base64_encode($answer . '|' . $token . '|' . $hmac);
        return ['question' => "{$a} + {$b}", 'payload' => $payload];
    }

    private function decryptCaptcha(string $payload): ?int
    {
        try {
            $decoded = base64_decode($payload, true);
            if ($decoded === false) return null;
            $parts = explode('|', $decoded, 3);
            if (count($parts) !== 3) return null;
            [$answer, $token, $expectedHmac] = $parts;
            $computed = hash_hmac('sha256', $answer . '|' . $token, config('app.key'));
            if (!hash_equals($expectedHmac, $computed)) return null;
            return (int) $answer;
        } catch (\Throwable) {
            return null;
        }
    }
}
