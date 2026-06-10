<?php

namespace App\Http\Controllers;

use App\Models\Referrer;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;

class ReferrerController extends Controller
{
    public function index()
    {
        // Jika sudah login dan punya referrer record, redirect ke dashboard
        if (Auth::check() && Auth::user()->is_referrer) {
            return redirect()->route('referrer.dashboard');
        }

        $maxReferralReward = \App\Models\Program::max('referral_reward_amount') ?? 50000;
        $maxReRegistrationReward = \App\Models\Program::max('re_registration_reward_amount') ?? 1000000;

        return view('referrer.index', compact('maxReferralReward', 'maxReRegistrationReward'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'regex:/^.+@.+\..+$/i', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'    => ['required', 'numeric', 'digits_between:10,15'],
        ], [
            'email.regex' => 'Format alamat email tidak valid (harus mengandung domain seperti .com, .id, dll).',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
            'role'     => 'mahasiswa',
            'is_referrer' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Generate kode unik (6 karakter alphanumeric uppercase)
        do {
            $code = strtoupper(Str::random(6));
        } while (Referrer::where('code', $code)->exists());

        Referrer::create([
            'user_id' => $user->id,
            'code'    => $code,
            'status'  => 'active',
        ]);

        return redirect()->route('referrer.dashboard')
            ->with('success', 'Akun afiliasi berhasil dibuat! Kode unikmu: ' . $code);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Jika sudah punya referrer record
        if ($user->is_referrer) {
            return redirect()->route('referrer.dashboard');
        }

        // Generate kode unik
        do {
            $code = strtoupper(Str::random(6));
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
            ->with('success', 'Akun afiliasi berhasil diaktifkan! Kode unikmu: ' . $code);
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Jika belum jadi referrer, redirect ke halaman index afiliasi
        if (!$user->is_referrer) {
            return redirect()->route('referrer.index');
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
            ->with(['user', 'firstChoiceProgram', 'rewards'])
            ->latest()
            ->get();

        $baseUrl = config('app.url');

        return view('referrer.dashboard', compact('referrer', 'stats', 'registrations', 'baseUrl'));
    }

    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:255'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ]);

        $referrer = Referrer::where('user_id', Auth::id())->firstOrFail();
        
        $referrer->update([
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ]);

        return redirect()->route('referrer.dashboard')->with('success', 'Data rekening bank berhasil disimpan.');
    }
}
