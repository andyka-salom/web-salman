<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Tentukan environment
        $isDevelopment = app()->environment('local', 'development');
        $isProduction = app()->environment('production');

        // ===== CONTENT SECURITY POLICY (CSP) =====
        // Sesuaikan dengan kebutuhan aplikasi
        if ($isDevelopment) {
            // Development: More permissive untuk debugging
            $csp = [
                "default-src 'self' http://localhost http://127.0.0.1",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http://localhost http://127.0.0.1",
                "style-src 'self' 'unsafe-inline' https: http://localhost http://127.0.0.1",
                "font-src 'self' data: https: http://localhost http://127.0.0.1",
                "img-src 'self' data: https: blob: http://localhost http://127.0.0.1",
                "connect-src 'self' https: http://localhost http://127.0.0.1",
                "media-src 'self' https: blob: http://localhost http://127.0.0.1",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ];
        } else {
            // Production: Strict CSP with necessary CDN allowances
            $csp = [
                "default-src 'self'",
                // Allow scripts from self and trusted CDNs
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com",
                // Allow styles from self and trusted CDNs
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                // Allow fonts from self and trusted CDNs
                "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com data:",
                "img-src 'self' data: https: blob:",
                "connect-src 'self' https:",
                "media-src 'self' https: blob:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ];

            // Upgrade insecure requests hanya di production
            $csp[] = "upgrade-insecure-requests";
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // ===== FRAMING PROTECTION =====
        // X-Frame-Options: Proteksi dari Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // ===== MIME-TYPE PROTECTION =====
        // X-Content-Type-Options: Proteksi MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ===== XSS PROTECTION =====
        // X-XSS-Protection: Proteksi XSS (untuk browser lama, deprecated tapi tetap useful)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ===== REFERRER POLICY =====
        // Kontrol informasi referrer yang dikirim ke domain lain
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ===== PERMISSIONS POLICY =====
        // Batasi akses ke fitur browser sensitif
        $permissions = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
            'midi=()',
            'picture-in-picture=()',
        ];
        $response->headers->set('Permissions-Policy', implode(', ', $permissions));

        // ===== HTTPS ENFORCEMENT =====
        // HSTS: HTTP Strict Transport Security (hanya untuk HTTPS)
        if ($request->secure() || $isProduction) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // ===== CROSS-ORIGIN POLICIES =====
        // Cross-Origin-Opener-Policy
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Cross-Origin-Embedder-Policy
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');

        // Cross-Origin-Resource-Policy
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // ===== REMOVE INFORMATION DISCLOSURE HEADERS =====
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('X-AspNet-Version');
        $response->headers->remove('X-AspNetMvc-Version');

        // ===== SET GENERIC SERVER HEADER =====
        // Jangan reveal teknologi yang digunakan
        if ($isDevelopment) {
            $response->headers->set('X-Powered-By', 'Laravel');
        } else {
            // Production: Jangan reveal info apapun
            $response->headers->set('X-Powered-By', 'Web Server');
        }

        // ===== ADDITIONAL SECURITY HEADERS =====
        // Mencegah DNS prefetching untuk privacy
        $response->headers->set('X-DNS-Prefetch-Control', 'off');

        // Disable cache untuk sensitive responses
        if ($request->getRequestUri() === '/login' ||
            $request->getRequestUri() === '/register' ||
            str_contains($request->getRequestUri(), '/admin')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
