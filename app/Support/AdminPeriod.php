<?php

namespace App\Support;

use App\Models\PmbPeriod;
use Illuminate\Support\Collection;

/**
 * Periode yang sedang dilihat staf di panel admin (PLAN_MULTI_PERIODE.md T6).
 *
 * Disimpan di sesi per staf. Bawaan: periode tampilan (`PmbPeriod::displayPeriod()`);
 * "all" berarti semua periode. Hanya memengaruhi tampilan dan ekspor, tidak pernah aksi.
 */
class AdminPeriod
{
    public const SESSION_KEY = 'admin_period';
    public const ALL = 'all';

    /** ID periode terpilih, atau null untuk semua periode. */
    public static function id(): ?int
    {
        $request = request();
        $cached = $request->attributes;

        if ($cached->has('admin_period_id')) {
            return $cached->get('admin_period_id');
        }

        $stored = session(self::SESSION_KEY);
        $id = null;

        if ($stored === self::ALL) {
            $id = null;
        } elseif (is_numeric($stored) && PmbPeriod::whereKey((int) $stored)->exists()) {
            $id = (int) $stored;
        } else {
            $id = PmbPeriod::displayPeriod()?->id;
        }

        $cached->set('admin_period_id', $id);

        return $id;
    }

    public static function isAll(): bool
    {
        return self::id() === null;
    }

    public static function current(): ?PmbPeriod
    {
        $id = self::id();

        return $id ? PmbPeriod::find($id) : null;
    }

    /** Daftar pilihan: terbaru dulu. */
    public static function options(): Collection
    {
        return PmbPeriod::orderByDesc('open_date')->orderByDesc('id')->get();
    }

    public static function choose(string|int $value): void
    {
        session([self::SESSION_KEY => $value === self::ALL ? self::ALL : (int) $value]);
        request()->attributes->remove('admin_period_id');
    }
}
