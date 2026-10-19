<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengelolaan akun staf hanya untuk admin yang tidak dibatasi kampus.
 * Operator dan admin kampus tidak boleh (mencegah menaikkan hak akses sendiri).
 */
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin' || $user->campus_id) {
            abort(403, 'Hanya admin semua kampus yang dapat mengelola akun staf.');
        }

        return $next($request);
    }
}
