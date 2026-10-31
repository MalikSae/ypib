<?php

namespace App\Support;

use App\Models\PaymentLog;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use App\Models\User;

/**
 * Pembayaran daftar ulang yang bisa dicicil. Pendaftar mengajukan nominal bersama bukti,
 * admin memverifikasi (boleh mengoreksi nominal) atau mencatat pembayaran tunai langsung.
 * Hanya nominal yang sudah diverifikasi yang masuk laporan; NIM terbit saat totalnya
 * mencapai batas minimal prodi.
 */
class RePayments
{
    public static function minimum(Registration $registration): int
    {
        return (int) ($registration->programTerms()?->re_registration_minimum_payment ?? 0);
    }

    public static function minimumReached(Registration $registration): bool
    {
        return $registration->reRegistrationPaid() >= self::minimum($registration);
    }

    /**
     * Batas nominal yang boleh diajukan pendaftar: pembayaran pertama minimal sebesar batas
     * minimal prodi, berikutnya bebas, dan tidak melebihi sisa tagihan.
     *
     * @return array{min: int, max: ?int, remaining: int}
     */
    public static function studentLimits(Registration $registration): array
    {
        $fee = (int) ($registration->programTerms()?->re_registration_fee ?? 0);
        $remaining = $registration->reRegistrationRemaining();

        $isFirst = !$registration->payments()
            ->reRegistration()
            ->whereIn('status', [RegistrationPayment::STATUS_PENDING, RegistrationPayment::STATUS_VERIFIED])
            ->exists();

        $max = $fee > 0 ? $remaining : null;
        $min = $isFirst ? max(self::minimum($registration), 1) : 1;

        if ($max !== null) {
            $min = min($min, max($max, 1));
        }

        return ['min' => $min, 'max' => $max, 'remaining' => $remaining];
    }

    public static function submitClaim(Registration $registration, int $amount, string $proofPath): RegistrationPayment
    {
        $registration->forceFill(['re_registration_payment_proof' => $proofPath])->save();

        return $registration->payments()->create([
            'type'           => RegistrationPayment::TYPE_RE_REGISTRATION,
            'amount'         => $amount,
            'claimed_amount' => $amount,
            'proof_path'     => $proofPath,
            'status'         => RegistrationPayment::STATUS_PENDING,
        ]);
    }

    public static function verify(RegistrationPayment $payment, int $amount, User $admin, ?string $note = null): RegistrationPayment
    {
        $payment->update([
            'amount'      => $amount,
            'status'      => RegistrationPayment::STATUS_VERIFIED,
            'note'        => $note ?: $payment->note,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        self::log($payment->registration_id, $admin, 're_payment_verified', 'Pembayaran daftar ulang Rp ' . number_format($amount, 0, ',', '.')
            . ' diverifikasi oleh ' . $admin->name
            . ($payment->claimed_amount !== null && $payment->claimed_amount !== $amount ? ' (diajukan Rp ' . number_format($payment->claimed_amount, 0, ',', '.') . ')' : ''));

        return $payment;
    }

    public static function reject(RegistrationPayment $payment, User $admin, string $note): RegistrationPayment
    {
        $payment->update([
            'status'      => RegistrationPayment::STATUS_REJECTED,
            'note'        => $note,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        self::log($payment->registration_id, $admin, 're_payment_rejected', 'Pembayaran daftar ulang Rp ' . number_format($payment->amount, 0, ',', '.')
            . ' ditolak oleh ' . $admin->name . '. Catatan: ' . $note);

        return $payment;
    }

    /** Pembayaran yang dicatat admin sendiri (misal tunai di kantor): langsung terverifikasi. */
    public static function record(Registration $registration, int $amount, ?string $proofPath, ?string $note, User $admin): RegistrationPayment
    {
        if ($proofPath) {
            $registration->forceFill(['re_registration_payment_proof' => $proofPath])->save();
        }

        $payment = $registration->payments()->create([
            'type'        => RegistrationPayment::TYPE_RE_REGISTRATION,
            'amount'      => $amount,
            'proof_path'  => $proofPath,
            'status'      => RegistrationPayment::STATUS_VERIFIED,
            'note'        => $note,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        self::log($registration->id, $admin, 're_payment_recorded', 'Pembayaran daftar ulang Rp ' . number_format($amount, 0, ',', '.')
            . ' dicatat oleh ' . $admin->name . ($note ? ' | Catatan: ' . $note : ''));

        return $payment;
    }

    private static function log(int $registrationId, User $admin, string $action, string $note): void
    {
        PaymentLog::create([
            'registration_id' => $registrationId,
            'acted_by'        => $admin->id,
            'action'          => $action,
            'note'            => $note,
        ]);
    }
}
