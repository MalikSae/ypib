<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengakhiri sesi login sebuah akun (saat dinonaktifkan atau passwordnya direset).
 * Sesi tersimpan di tabel `sessions` bila driver 'database'; tanpa tabel tidak ada yang dihapus.
 */
class StaffSessions
{
    public static function forget(User $user): void
    {
        if (!Schema::hasTable('sessions')) {
            return;
        }

        DB::table('sessions')
            ->where('user_id', $user->id)
            // Mengubah akun sendiri tidak boleh memutus sesi yang sedang dipakai.
            ->when($user->id === auth()->id(), fn ($q) => $q->where('id', '!=', session()->getId()))
            ->delete();
    }
}
