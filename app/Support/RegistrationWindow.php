<?php

namespace App\Support;

use App\Models\Registration;

/**
 * Gerbang jendela pendaftaran (PLAN_MULTI_PERIODE.md 5.3, K2, K4, K5).
 *
 * Gerbang memakai periode milik pendaftaran, bukan periode berjalan, supaya pendaftar tahun
 * lalu tidak terpengaruh pergantian periode. Hanya aksi pendaftar yang digerbangi: staf,
 * halaman status, dan unduhan tidak. Seluruh penolakan di sini mati selama saklar
 * PMB_ENFORCE_WINDOW bernilai false; teks pemberitahuan (notice) tetap tampil.
 */
class RegistrationWindow
{
    public static function enabled(): bool
    {
        return (bool) config('pmb.enforce_window');
    }

    /** Bukti bayar / kartu alumni pertama: hanya dalam masa bayar periode. */
    public static function denyFirstProof(Registration $registration): ?string
    {
        if ($message = self::denyMutation($registration)) {
            return $message;
        }

        // Mengganti bukti setelah staf mulai memproses (menunggu_konfirmasi) tidak dibatasi tanggal.
        if (!self::enabled() || $registration->status !== 'menunggu_pembayaran') {
            return null;
        }

        $period = $registration->period;
        if ($period && !$period->isPaymentOpen()) {
            $lastDay = $period->close_date->copy()->addDays((int) $period->payment_grace_days);

            return 'Masa pembayaran periode ' . $period->name . ' sudah berakhir pada '
                . $lastDay->translatedFormat('d F Y') . '.' . self::contact($registration);
        }

        return null;
    }

    /** Bukti daftar ulang: hanya sampai tenggat daftar ulang periode. */
    public static function denyReRegistrationProof(Registration $registration): ?string
    {
        if ($message = self::denyMutation($registration)) {
            return $message;
        }

        if (!self::enabled()) {
            return null;
        }

        $period = $registration->period;
        if ($period && !$period->isReRegistrationOpen()) {
            $deadline = $period->re_registration_deadline;

            return 'Tenggat daftar ulang periode ' . $period->name
                . ($deadline ? ' sudah lewat pada ' . $deadline->translatedFormat('d F Y') : ' sudah berakhir') . '.'
                . self::contact($registration);
        }

        return null;
    }

    /** Aksi pendaftar yang mengubah data (unggah dokumen, ubah data, tes): tidak di periode arsip. */
    public static function denyMutation(Registration $registration): ?string
    {
        if (!self::enabled()) {
            return null;
        }

        $period = $registration->period;
        if ($period && $period->isArchived()) {
            return 'Periode ' . $period->name . ' sudah diarsipkan, sehingga data tidak bisa diubah.'
                . self::contact($registration);
        }

        return null;
    }

    /**
     * Pemberitahuan batas waktu untuk halaman status (selalu tampil, terlepas dari saklar).
     *
     * @return array{tone: string, text: string}|null  tone: info | warning
     */
    public static function notice(Registration $registration): ?array
    {
        $period = $registration->period;
        if (!$period) {
            return null;
        }

        if ($period->isArchived()) {
            return [
                'tone' => 'warning',
                'text' => 'Periode ' . $period->name . ' sudah diarsipkan. Data hanya dapat dilihat dan diunduh.',
            ];
        }

        if ($registration->status === 'menunggu_pembayaran') {
            $lastDay = $period->close_date->copy()->addDays((int) $period->payment_grace_days);

            return [
                'tone' => $period->isPaymentOpen() ? 'info' : 'warning',
                'text' => 'Batas pengunggahan bukti pembayaran: ' . $lastDay->translatedFormat('d F Y') . '.',
            ];
        }

        if (in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang'], true)
            && $period->re_registration_deadline) {
            return [
                'tone' => $period->isReRegistrationOpen() ? 'info' : 'warning',
                'text' => 'Tenggat daftar ulang: ' . $period->re_registration_deadline->translatedFormat('d F Y') . '.',
            ];
        }

        return null;
    }

    private static function contact(Registration $registration): string
    {
        $whatsapp = trim((string) $registration->period?->admin_whatsapp);

        return $whatsapp !== '' ? ' Silakan hubungi admin di WhatsApp ' . $whatsapp . '.' : ' Silakan hubungi admin.';
    }
}
