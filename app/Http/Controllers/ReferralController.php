<?php

namespace App\Http\Controllers;

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

        // Catat klik
        ReferralClick::create([
            'referrer_id' => $referrer->id,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'converted'   => false,
        ]);

        // Increment total_clicks
        $referrer->increment('total_clicks');

        // Set cookie 'ref' expire 7 hari, redirect ke /daftar
        return redirect()->route('registration.create')
            ->withCookie(cookie('ref', $code, 60 * 24 * 7));
    }
}
