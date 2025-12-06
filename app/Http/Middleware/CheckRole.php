<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // TODO: Revert this bypass after debugging
        // return $next($request);

        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRole = auth()->user()->role;
        
        // Debug output to log (visible in laravel.log)
        // \Log::info("CheckRole Debug: User Role = {$userRole}, Required Role = {$role}");

        // Cek role user
        // Allow calon_mahasiswa to access mahasiswa routes
        if ($role === 'mahasiswa' && $userRole === 'calon_mahasiswa') {
            return $next($request);
        }

        if ($userRole !== $role) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}