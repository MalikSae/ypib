<?php

namespace App\Support;

use App\Models\Campus;
use App\Models\PmbPeriod;
use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\ProspectLink;
use App\Models\ProspectSource;
use App\Models\Referrer;
use Illuminate\Support\Facades\DB;

/** Logika bersama form minat prospek: simpan prospek, link daftar, dan tautan WA. */
class ProspectService
{
    /** Kunci sesi untuk token link daftar prospek yang sedang dibawa pengunjung. */
    public const SESSION_KEY = 'prospect_token';

    /**
     * Simpan atau perbarui prospek dari form minat. Satu baris per nomor WA. Pemilik (referrer)
     * hanya ditetapkan saat prospek pertama kali dibuat (first-touch): prospek yang lahir tanpa
     * affiliator tetap tanpa pemilik, dan pemilik tidak pernah berpindah.
     */
    public static function capture(ProspectEvent $event, array $data, ?Referrer $referrer): Prospect
    {
        $phone = Prospect::normalizePhone($data['phone']);

        return DB::transaction(function () use ($event, $data, $referrer, $phone) {
            $prospect = Prospect::where('phone_normalized', $phone)->lockForUpdate()->first();

            if (!$prospect) {
                $prospect = Prospect::create([
                    'full_name'        => $data['full_name'],
                    'phone'            => $data['phone'],
                    'phone_normalized' => $phone,
                    'school_name'      => $data['school_name'] ?? null,
                    'education_level'  => $data['education_level'] ?? null,
                    'campus_id'        => $event->campus_id,
                    'program_id'       => $data['program_id'] ?? null,
                    'referrer_id'      => $referrer?->id,
                    'status'           => Prospect::STATUS_BARU,
                    'fraud_flag'       => self::isReferrerOwnPhone($referrer, $phone),
                ]);
            } else {
                if (!$prospect->education_level && !empty($data['education_level'])) {
                    $prospect->education_level = $data['education_level'];
                }
                if (!$prospect->program_id && !empty($data['program_id'])) {
                    $prospect->program_id = $data['program_id'];
                }
                $prospect->save();
            }

            ProspectSource::firstOrCreate(
                ['prospect_id' => $prospect->id, 'prospect_event_id' => $event->id],
                ['referrer_id' => $referrer?->id, 'created_at' => now()]
            );

            return $prospect;
        });
    }

    /**
     * Link masih boleh dipakai untuk mendaftar: belum dipakai, belum kedaluwarsa, prospek belum
     * mendaftar, event masih ada, dan affiliator pemilik (bila ada) masih aktif.
     */
    public static function isLinkOpen(?ProspectLink $link): bool
    {
        if (!$link || !$link->isUsable() || !$link->prospect || !$link->event || $link->prospect->isRegistered()) {
            return false;
        }

        $owner = $link->prospect->referrer;

        return $owner === null || $owner->isActive();
    }

    /**
     * Link daftar prospek yang sedang dibawa sesi (setelah membuka /minat/daftar/{token}), bila
     * masih berlaku. Link yang tidak berlaku dibuang dari sesi.
     */
    public static function sessionLink(): ?ProspectLink
    {
        $token = session(self::SESSION_KEY);
        if (!$token) {
            return null;
        }

        $link = ProspectLink::where('token', $token)->with(['prospect.referrer', 'event'])->first();

        if (!self::isLinkOpen($link)) {
            session()->forget(self::SESSION_KEY);

            return null;
        }

        return $link;
    }

    /**
     * Link daftar untuk prospek. Link yang masih berlaku untuk event yang sama dipakai ulang.
     * Bila prospek mengisi form dari event lain, link lama ditutup dan dibuat link baru untuk
     * event itu, sehingga potongan selalu mengikuti form minat terakhir. Potongan disimpan
     * (snapshot) saat link dibuat.
     */
    public static function linkFor(Prospect $prospect, ?ProspectEvent $event): ProspectLink
    {
        $usable = $prospect->links()->get()->filter(fn (ProspectLink $link) => $link->isUsable());

        $same = $usable->first(fn (ProspectLink $link) => $event === null || (int) $link->prospect_event_id === (int) $event->id);
        if ($same) {
            return $same;
        }

        // Tutup link lama yang belum dipakai (event berbeda).
        $usable->each(fn (ProspectLink $link) => $link->forceFill(['expires_at' => now()])->save());

        return ProspectLink::create([
            'prospect_id'       => $prospect->id,
            'prospect_event_id' => $event?->id,
            'discount_amount'   => (int) ($event?->discount_amount ?? 0),
            'token'             => ProspectLink::newToken(),
            'expires_at'        => self::expiry($event),
        ]);
    }

    /** Tautan WA admin kampus dengan pesan prefilled (siswa yang mengirim). Null bila nomor admin belum diatur. */
    public static function whatsappUrl(Prospect $prospect, ?Campus $campus, ?string $linkUrl): ?string
    {
        $digits = Prospect::toWhatsappDigits($campus?->admin_whatsapp ?: PmbPeriod::displayPeriod()?->admin_whatsapp);

        if ($digits === '') {
            return null;
        }

        $lines = [
            'Halo admin, saya ' . $prospect->full_name . ($prospect->school_name ? ' dari ' . $prospect->school_name : '') . '.',
            'Saya tertarik kuliah di YPIB' . ($campus ? ' ' . $campus->name : '') . '. Kode minat: MNT-' . $prospect->id . '.',
        ];
        if ($linkUrl) {
            $lines[] = 'Link pendaftaran saya: ' . $linkUrl;
        }

        return 'https://wa.me/' . $digits . '?text=' . urlencode(implode("\n", $lines));
    }

    /** Chat WA biasa ke nomor prospek (admin yang mengirim), tanpa link. Untuk follow-up. */
    public static function prospectChatUrl(Prospect $prospect, ?Campus $campus): string
    {
        $text = 'Halo ' . $prospect->full_name . ', ini dari admin'
            . ($campus ? ' YPIB ' . $campus->name : ' YPIB') . '. Ada yang bisa kami bantu soal pendaftaran?';

        return 'https://wa.me/' . $prospect->whatsappDigits() . '?text=' . urlencode($text);
    }

    /** WA ke nomor prospek (admin yang mengirim): berisi link pendaftaran. */
    public static function prospectWhatsappUrl(Prospect $prospect, ?Campus $campus, string $linkUrl): string
    {
        $text = 'Halo ' . $prospect->full_name . ', terima kasih sudah mengisi form minat'
            . ($campus ? ' YPIB ' . $campus->name : ' YPIB') . '.'
            . "\n" . 'Ini link pendaftaran kamu: ' . $linkUrl;

        return 'https://wa.me/' . $prospect->whatsappDigits() . '?text=' . urlencode($text);
    }

    private static function expiry(?ProspectEvent $event): \Carbon\CarbonInterface
    {
        $close = ($event?->period ?? PmbPeriod::displayPeriod())?->close_date;

        // Tanpa tanggal tutup periode, link berlaku 30 hari.
        return $close ? $close->copy()->endOfDay() : now()->addDays(30);
    }

    private static function isReferrerOwnPhone(?Referrer $referrer, string $phone): bool
    {
        $own = $referrer?->user?->phone;

        return $own !== null && Prospect::normalizePhone($own) === $phone;
    }
}
