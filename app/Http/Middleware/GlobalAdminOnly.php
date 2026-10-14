<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengaturan lintas kampus (kampus, periode, biaya, akun panitia, email) hanya untuk
 * admin tanpa batasan kampus (users.campus_id kosong).
 */
class GlobalAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->campus_id) {
            abort(403, 'Pengaturan ini hanya untuk admin semua kampus.');
        }

        return $next($request);
    }
}
