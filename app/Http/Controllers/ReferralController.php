<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Referrer;
use App\Models\ReferralClick;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function track(string $code, Request $request)
    {
        $referrer = Referrer::where('code', $code)->first();

        // Referrer tidak ada atau tidak aktif → redirect biasa
        if (!$referrer || $referrer->status !== 'active') {
            return redirect()->route('registration.create');
        }

        // ?kampus=slug: tautan afiliasi per kampus. Kampus tidak dikenal atau nonaktif diabaikan
        // (jatuh ke perilaku lama: form pendaftaran), jadi tautan tidak pernah mati.
        $slug = (string) $request->query('kampus', '');
        $campus = $slug !== '' ? Campus::active()->where('slug', $slug)->first() : null;

        // Catat klik
        ReferralClick::create([
            'referrer_id' => $referrer->id,
            'campus_id'   => $campus?->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'converted'   => false,
        ]);

        // Increment total_clicks
        $referrer->increment('total_clicks');

        // Cookie 'ref' berlaku 7 hari (klik terakhir menang, apa pun kampusnya). Tujuan: landing
        // kampus bila ?kampus= valid, selain itu form pendaftaran.
        $destination = match (true) {
            $campus === null => route('registration.create'),
            $campus->is_default => route('landing'),
            default => route('campus.landing', $campus->slug),
        };

        return redirect($destination)
            ->withCookie(cookie('ref', $code, 60 * 24 * 7));
    }
}
