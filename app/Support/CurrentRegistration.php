<?php

namespace App\Support;

use App\Models\PmbPeriod;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Pendaftaran yang sedang dilihat pendaftar (PLAN_MULTI_PERIODE.md T3, T7).
 *
 * Satu akun bisa punya pendaftaran di beberapa periode. Urutan pilihan:
 *   1. `?periode={id}` pada permintaan ini (disimpan di sesi supaya halaman lain,
 *      unduhan, dan aksi ikut pendaftaran yang sama; hanya milik akun ini),
 *   2. pilihan yang tersimpan di sesi,
 *   3. pendaftaran di periode berjalan,
 *   4. pendaftaran terbaru.
 * `?periode=reset` menghapus pilihan tersimpan.
 */
class CurrentRegistration
{
    public const SESSION_KEY = 'registration_period';

    public static function forRequest(?User $user, Request $request): ?Registration
    {
        if (!$user) {
            return null;
        }

        $param = $request->query('periode');

        if ($param === 'reset') {
            $request->session()->forget(self::SESSION_KEY);

            return self::forUser($user);
        }

        if (is_numeric($param)) {
            $registration = self::forUser($user, (int) $param);

            if ($registration) {
                // Terikat ke akun: sesi yang dipakai bergantian tidak membawa pilihan akun lain.
                $request->session()->put(self::SESSION_KEY, ['user' => $user->id, 'period' => (int) $param]);

                return $registration;
            }
        }

        $stored = $request->session()->get(self::SESSION_KEY);
        if (is_array($stored) && (int) ($stored['user'] ?? 0) === $user->id && is_numeric($stored['period'] ?? null)) {
            $registration = self::forUser($user, (int) $stored['period']);

            if ($registration) {
                return $registration;
            }

            $request->session()->forget(self::SESSION_KEY);
        }

        return self::forUser($user);
    }

    public static function forUser(?User $user, ?int $requestedPeriodId = null): ?Registration
    {
        if (!$user) {
            return null;
        }

        $base = fn () => Registration::where('user_id', $user->id);

        if ($requestedPeriodId) {
            return $base()->where('period_id', $requestedPeriodId)->latest('id')->first();
        }

        $current = PmbPeriod::current();
        if ($current) {
            $inCurrent = $base()->where('period_id', $current->id)->latest('id')->first();
            if ($inCurrent) {
                return $inCurrent;
            }
        }

        return $base()->latest('id')->first();
    }
}
