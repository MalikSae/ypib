<?php

namespace App\Support;

use App\Models\PmbPeriod;
use App\Models\Registration;
use Illuminate\Support\Collection;

/**
 * Satu proses aktif per orang (NIK), PLAN_MULTI_PERIODE.md K7 dan 5.5.
 *
 * NIK diketik pendaftar dan tidak diverifikasi pihak ketiga, jadi blokir keras hanya
 * berlaku untuk pendaftaran lain yang sudah diverifikasi staf. Pemeriksaan dipanggil dari
 * tempat yang sudah mewajibkan login (RegistrationForm::submit, simpan data diri), dan
 * pesannya generik supaya tidak bisa dipakai menguji status NIK orang lain.
 */
class NikGuard
{
    /** Status yang menahan pendaftaran baru sampai tenggat daftar ulang lewat. */
    private const ACCEPTED_STATUSES = ['diterima', 'menunggu_konfirmasi_daftar_ulang'];

    /** Status setelah dokumen terverifikasi: menahan selama periode belum arsip. */
    private const VERIFIED_STATUSES = ['menunggu_tes_tulis', 'menunggu_interview'];

    /**
     * @return array{blocked: bool, matches: Collection<int, Registration>}
     *   matches: seluruh pendaftaran lain (tidak soft-delete) dengan NIK yang sama.
     */
    public static function inspect(string $nik, ?int $programId, ?int $exceptRegistrationId = null): array
    {
        $matches = Registration::with('period')
            ->where('nik', $nik)
            ->when($exceptRegistrationId, fn ($q) => $q->where('id', '!=', $exceptRegistrationId))
            ->get();

        $today = now()->startOfDay();
        $blocked = false;

        foreach ($matches as $other) {
            $periodArchived = (bool) $other->period?->isArchived();

            // (a) Sudah ber-NIM pada prodi yang sama, kecuali admin mengizinkan.
            if (!empty($other->nim) || $other->status === 'daftar_ulang_selesai') {
                if ($programId && (int) $other->first_choice_program_id === $programId && !$other->reapply_allowed) {
                    $blocked = true;
                }

                continue;
            }

            // (b) Diterima / menunggu daftar ulang, dalam tenggat (tenggat kosong tidak memblokir).
            if (in_array($other->status, self::ACCEPTED_STATUSES, true)) {
                $deadline = $other->period?->re_registration_deadline;

                if (!$periodArchived && $deadline && $today->lte($deadline->copy()->endOfDay())) {
                    $blocked = true;
                }

                continue;
            }

            // (c) Dokumen sudah terverifikasi pada periode yang belum arsip.
            if (in_array($other->status, self::VERIFIED_STATUSES, true) && !$periodArchived) {
                $blocked = true;
            }
        }

        return ['blocked' => $blocked, 'matches' => $matches];
    }

    /** True hanya bila saklar PMB_ENFORCE_NIK_BLOCK menyala dan aturan K7 mengenai NIK ini. */
    public static function blocks(string $nik, ?int $programId, ?int $exceptRegistrationId = null): bool
    {
        if (!config('pmb.enforce_nik_block')) {
            return false;
        }

        return self::inspect($nik, $programId, $exceptRegistrationId)['blocked'];
    }

    /** Pesan generik: tanpa nama prodi, periode, atau status pendaftaran lain. */
    public static function message(?PmbPeriod $period = null): string
    {
        $message = 'Data Anda sudah tercatat pada proses pendaftaran lain, sehingga pendaftaran baru belum bisa dilanjutkan.';

        if ($period && trim((string) $period->admin_whatsapp) !== '') {
            $message .= ' Silakan hubungi admin di WhatsApp ' . $period->admin_whatsapp . '.';
        } else {
            $message .= ' Silakan hubungi admin.';
        }

        return $message;
    }
}
