<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Adds defense-in-depth HTTP security headers to every response.
 * - Content-Security-Policy: restricts what scripts/styles/resources can run
 * - X-Content-Type-Options: blocks MIME-sniffing
 * - X-Frame-Options: blocks clickjacking
 * - Referrer-Policy: limits Referer header leakage
 * - Permissions-Policy: disables unused browser features
 * - Strict-Transport-Security: only on HTTPS connections
 *
 * NOTE: This is an "allow list" baseline. Loosen it as needed for known CDNs
 * (Bootstrap, Tailwind CDN, Alpine, Font Awesome, etc.) BEFORE deploying.
 * Test live pages in a staging environment before turning this on in production.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ---------------------------------------------------------------
        // Content-Security-Policy
        // ---------------------------------------------------------------
        // Adjust "script-src" and "style-src" to include any trusted CDNs
        // that the front-end actually depends on. The list below covers
        // what the current Magzani layout uses (Tailwind, Bootstrap, Alpine,
        // Font Awesome, Google Fonts).
        // ---------------------------------------------------------------
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://code.jquery.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "connect-src 'self' wss: ws: https:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // ---------------------------------------------------------------
        // Other security headers
        // ---------------------------------------------------------------
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');

        // ---------------------------------------------------------------
        // HSTS — only when HTTPS is in use
        // ---------------------------------------------------------------
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
