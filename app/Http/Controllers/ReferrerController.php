<?php

namespace App\Http\Controllers;

use App\Models\Referrer;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReferrerController extends Controller
{
    public function create()
    {
        // Jika sudah punya referrer record, redirect ke dashboard
        if (Auth::user()->referrer) {
            return redirect()->route('referrer.dashboard');
        }

        return view('referrer.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Jika sudah punya referrer record
        if ($user->referrer) {
            return redirect()->route('referrer.dashboard');
        }

        // Generate kode unik REF-XXXXXX (6 karakter alphanumeric uppercase)
        do {
            $code = 'REF-' . strtoupper(Str::random(6));
        } while (Referrer::where('code', $code)->exists());

        // Buat record referrer
        Referrer::create([
            'user_id' => $user->id,
            'code'    => $code,
            'status'  => 'active',
        ]);

        // Set is_referrer = true
        $user->update(['is_referrer' => true]);

        return redirect()->route('referrer.dashboard')
            ->with('success', 'Akun referrer berhasil diaktifkan! Kode unikmu: ' . $code);
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Jika belum jadi referrer, redirect ke halaman daftar
        if (!$user->is_referrer) {
            return redirect()->route('referrer.create');
        }

        $referrer = Referrer::where('user_id', $user->id)
            ->with(['rewards', 'clicks'])
            ->firstOrFail();

        // Statistik
        $stats = [
            'total_clicks'       => $referrer->total_clicks,
            'total_conversions'  => $referrer->total_conversions,
            'total_rewards'      => $referrer->rewards
                ->whereIn('status', ['approved', 'disbursed'])
                ->sum('amount'),
            'disbursed_rewards'   => $referrer->rewards
                ->where('status', 'disbursed')
                ->sum('amount'),
        ];

        // List pendaftar via referral ini
        $registrations = Registration::where('referrer_id', $referrer->id)
            ->with(['user', 'firstChoiceProgram', 'reward'])
            ->latest()
            ->get();

        $baseUrl = config('app.url');

        return view('referrer.dashboard', compact('referrer', 'stats', 'registrations', 'baseUrl'));
    }
}
