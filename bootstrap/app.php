<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Global (bukan grup web) agar header juga terpasang pada 404 untuk rute yang tidak ada.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Situs berjalan di belakang Cloudflare/proxy hosting. Tanpa ini $request->ip()
        // adalah IP edge proxy, sehingga pembatas laju (login, pendaftaran) dipakai
        // bersama oleh semua pengunjung dan isSecure() selalu false.
        // Override lewat .env: TRUSTED_PROXIES="ip1,cidr2" atau "*" (hanya bila origin
        // tidak bisa diakses langsung; "*" memungkinkan pemalsuan X-Forwarded-For).
        $trusted = trim((string) env('TRUSTED_PROXIES', ''));
        $middleware->trustProxies(
            at: $trusted === '*'
                ? '*'
                : ($trusted !== '' ? array_map('trim', explode(',', $trusted)) : [
                    // Cloudflare (https://www.cloudflare.com/ips/)
                    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
                    '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
                    '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
                    '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
                    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
                    '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
                    // Reverse proxy lokal / jaringan privat hosting
                    '127.0.0.1', '::1', '10.0.0.0/8', '192.168.0.0/16',
                ]),
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
