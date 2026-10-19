<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Referrer;
use App\Models\ReferralClick;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        // Slug lama kampus (alias) tetap dikenali.
        $campus = Campus::findActiveBySlug(\App\Support\QueryInput::string($request, 'kampus'));

        // Catat klik, maksimal sekali per 5 menit untuk kombinasi afiliator + kampus + IP. Tanpa
        // ini, memuat ulang tautan sendiri menggelembungkan total_clicks. Cookie tetap dipasang.
        $firstClick = Cache::add(
            'ref-click:' . $referrer->id . ':' . ($campus?->id ?? 0) . ':' . sha1((string) $request->ip()),
            1,
            now()->addMinutes(5)
        );

        if ($firstClick) {
            ReferralClick::create([
                'referrer_id' => $referrer->id,
                'campus_id'   => $campus?->id,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'converted'   => false,
            ]);

            $referrer->increment('total_clicks');
        }

        // Cookie 'ref' berlaku 7 hari (klik terakhir menang, apa pun kampusnya). Tujuan: landing
        // kampus bila ?kampus= valid, selain itu form pendaftaran.
        $destination = $campus ? $campus->landingUrl() : route('registration.create');

        return redirect($destination)
            ->withCookie(cookie('ref', $code, 60 * 24 * 7));
    }
}
