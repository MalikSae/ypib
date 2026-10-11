<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Pra-pemeriksaan data sebelum migrasi multi-periode (PLAN_MULTI_PERIODE.md, T1).
 *
 * Hanya membaca. Dipakai oleh migrasi (menolak jalan bila ada pelanggar yang
 * membuat unique gagal) dan oleh `php artisan periods:precheck`.
 */
class PeriodPrecheck
{
    /** Status akhir: pendaftaran tidak lagi berjalan. */
    private const FINAL_STATUSES = ['daftar_ulang_selesai', 'ditolak'];

    /**
     * Akun dengan lebih dari satu pendaftaran aktif (tidak soft-delete) pada periode yang sama.
     * Pelanggar unique `active_key`.
     *
     * @return array<int, object>
     */
    public static function duplicateActiveRegistrations(): array
    {
        return DB::table('registrations')
            ->whereNull('deleted_at')
            ->select('user_id', 'period_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'period_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->all();
    }

    /**
     * NIM yang dipakai lebih dari satu baris (termasuk soft-delete). Pelanggar unique `nim`.
     *
     * @return array<int, object>
     */
    public static function duplicateNims(): array
    {
        return DB::table('registrations')
            ->whereNotNull('nim')
            ->where('nim', '!=', '')
            ->select('nim', DB::raw('COUNT(*) as total'))
            ->groupBy('nim')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->all();
    }

    /**
     * Periode tidak aktif yang masih punya pendaftaran berjalan. Backfill akan menandainya
     * `archived`; ini bukan pelanggar migrasi, hanya perlu ditinjau manusia.
     *
     * @return array<int, object>
     */
    public static function inactivePeriodsWithOpenRegistrations(): array
    {
        return DB::table('pmb_periods as p')
            ->join('registrations as r', 'r.period_id', '=', 'p.id')
            ->whereNull('r.deleted_at')
            ->where('p.is_active', false)
            ->whereNotIn('r.status', self::FINAL_STATUSES)
            ->select('p.id', 'p.name', DB::raw('COUNT(r.id) as total'))
            ->groupBy('p.id', 'p.name')
            ->get()
            ->all();
    }
}
