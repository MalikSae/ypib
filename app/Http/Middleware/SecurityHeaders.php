<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk semua respons web.
 *
 * CSP penuh sengaja belum dipasang: Alpine.js/Livewire dan banyak inline
 * style/script di view membuatnya perlu dirancang terpisah.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // Cegah halaman dibingkai situs lain (clickjacking).
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            // Kamera dipakai pemindai QR panitia (same-origin saja); sisanya dimatikan.
            'Permissions-Policy'     => 'camera=(self), microphone=(), geolocation=(), payment=()',
        ];

        // HSTS hanya bila permintaan memang lewat HTTPS dan di produksi.
        if ($request->isSecure() && app()->environment('production')) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            if (!$response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
