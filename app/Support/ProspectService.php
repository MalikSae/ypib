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

/** Logika bersama form minat prospek: simpan prospek, link daftar, dan tautan WA admin. */
class ProspectService
{
    /**
     * Simpan atau perbarui prospek dari form minat. Satu baris per nomor WA. Pemilik (referrer)
     * ditetapkan saat pertama kali; prospek yang sudah punya pemilik tidak berpindah.
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
                if (!$prospect->referrer_id && $referrer) {
                    $prospect->referrer_id = $referrer->id;
                    $prospect->fraud_flag = self::isReferrerOwnPhone($referrer, $phone);
                }
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
     * Link daftar prospek yang sedang dibawa sesi (setelah membuka /minat/daftar/{token}), bila
     * masih berlaku. Link yang tidak berlaku dibuang dari sesi.
     */
    public static function sessionLink(): ?ProspectLink
    {
        $token = session('prospect_token');
        if (!$token) {
            return null;
        }

        $link = ProspectLink::where('token', $token)->with(['prospect', 'event'])->first();
        $owner = $link?->prospect?->referrer_id ? Referrer::find($link->prospect->referrer_id) : null;

        $valid = $link
            && $link->isUsable()
            && $link->prospect
            && $link->event
            && !$link->prospect->isRegistered()
            && !($owner && $owner->status !== 'active');

        if (!$valid) {
            session()->forget('prospect_token');

            return null;
        }

        return $link;
    }

    /** Link daftar yang masih bisa dipakai, atau buat baru. Sekali pakai, kedaluwarsa mengikuti periode. */
    public static function linkFor(Prospect $prospect, ?ProspectEvent $event): ProspectLink
    {
        $usable = $prospect->links()->get()->first(fn (ProspectLink $link) => $link->isUsable());

        if ($usable) {
            return $usable;
        }

        return ProspectLink::create([
            'prospect_id'       => $prospect->id,
            'prospect_event_id' => $event?->id,
            'token'             => ProspectLink::newToken(),
            'expires_at'        => self::expiry($event),
        ]);
    }

    /** Tautan WA admin kampus dengan pesan prefilled. Null bila nomor admin belum diatur. */
    public static function whatsappUrl(Prospect $prospect, ?Campus $campus, ?string $linkUrl): ?string
    {
        $raw = $campus?->admin_whatsapp ?: PmbPeriod::displayPeriod()?->admin_whatsapp;
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
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

    /** Chat WA biasa ke nomor prospek, tanpa link. Untuk follow-up admin. */
    public static function prospectChatUrl(Prospect $prospect, ?Campus $campus): string
    {
        $digits = preg_replace('/\D+/', '', (string) $prospect->phone_normalized);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        $text = 'Halo ' . $prospect->full_name . ', ini dari admin'
            . ($campus ? ' YPIB ' . $campus->name : ' YPIB') . '. Ada yang bisa kami bantu soal pendaftaran?';

        return 'https://wa.me/' . $digits . '?text=' . urlencode($text);
    }

    /** WA ke nomor prospek (admin yang mengirim): berisi link pendaftaran. */
    public static function prospectWhatsappUrl(Prospect $prospect, ?Campus $campus, string $linkUrl): string
    {
        $digits = preg_replace('/\D+/', '', (string) $prospect->phone_normalized);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        $text = 'Halo ' . $prospect->full_name . ', terima kasih sudah mengisi form minat'
            . ($campus ? ' YPIB ' . $campus->name : ' YPIB') . '.'
            . "\n" . 'Ini link pendaftaran kamu: ' . $linkUrl;

        return 'https://wa.me/' . $digits . '?text=' . urlencode($text);
    }

    private static function expiry(?ProspectEvent $event): \Carbon\CarbonInterface
    {
        $close = ($event?->period ?? PmbPeriod::displayPeriod())?->close_date;

        return $close ? $close->copy()->endOfDay() : now()->addDays(30);
    }

    private static function isReferrerOwnPhone(?Referrer $referrer, string $phone): bool
    {
        $own = $referrer?->user?->phone;

        return $own !== null && Prospect::normalizePhone($own) === $phone;
    }
}
