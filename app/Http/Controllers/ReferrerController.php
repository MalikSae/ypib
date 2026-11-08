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

        // Nominal tertinggi yang ditampilkan: dari prodi yang ditawarkan pada periode berjalan,
        // cadangan ke nilai master prodi bila periode belum punya matriks.
        $period = \App\Models\PmbPeriod::displayPeriod();
        $offered = $period
            ? \App\Models\PeriodProgram::where('period_id', $period->id)->where('is_offered', true)
            : null;

        $maxReferralReward = ($offered?->max('referral_reward_amount')) ?? \App\Models\Program::max('referral_reward_amount') ?? 50000;
        $maxReRegistrationReward = ($offered?->max('re_registration_reward_amount')) ?? \App\Models\Program::max('re_registration_reward_amount') ?? 1000000;

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

        // Tautan khusus untuk tiap kampus aktif selain kampus bawaan (landing kampus). Landing kampus
        // bawaan adalah halaman utama yang sama dengan tautan umum, jadi tidak diberi kartu sendiri.
        $base = $baseUrl . '/ref/' . $referrer->code;
        $linkTargets = \App\Models\Campus::active()->where('is_default', false)->orderBy('name')->get()
            ->map(fn ($campus) => [
                'label' => $campus->name,
                'hint'  => 'Halaman kampus: prodi, biaya, dan kuota ' . $campus->name . '.',
                'url'   => $base . '?kampus=' . $campus->slug,
                'share' => 'Daftar kuliah di YPIB ' . $campus->name . ' lewat link ini: ',
            ])
            // Link Umum selalu di urutan pertama (dibuka otomatis di dashboard).
            ->prepend([
                'label' => 'Link Umum',
                'hint'  => 'Untuk semua kampus: langsung ke form pendaftaran, kampus dipilih sendiri oleh pendaftar.',
                'url'   => $base,
                'share' => 'Daftar kuliah di YPIB lewat link ini: ',
            ]);

        // Prospek form minat dari link affiliator ini (WA disamarkan sampai mendaftar di view).
        $prospects = \App\Models\Prospect::withoutGlobalScope(\App\Models\Scopes\CampusScope::class)
            ->where('referrer_id', $referrer->id)
            ->with(['program', 'sources.event'])
            ->latest('id')
            ->get();

        // Kampanye online yang aktif (bukan event kampus), lengkap dengan tautan berisi kode referral.
        $interestLinks = \App\Models\ProspectEvent::withoutGlobalScope(\App\Models\Scopes\CampusScope::class)
            ->with(['campus', 'period'])
            ->online()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn ($event) => $event->acceptsInterest())
            ->map(fn ($event) => [
                'label' => $event->name,
                'tag'   => 'Form minat',
                'hint'  => ($event->campus ? $event->campus->name . ' · ' : '')
                    . 'Potongan daftar ulang Rp ' . number_format($event->discount_amount, 0, ',', '.'),
                'url'   => route('prospect.show', $event->slug) . '?ref=' . $referrer->code,
                'share' => 'Isi form minat kuliah di YPIB lewat link ini: ',
            ]);

        // Semua link affiliator dalam satu daftar (ditampilkan sebagai accordion).
        $shareLinks = $linkTargets
            ->map(fn ($target) => $target + ['tag' => 'Pendaftaran'])
            ->concat($interestLinks)
            ->values();

        return view('referrer.dashboard', compact('referrer', 'stats', 'registrations', 'baseUrl', 'shareLinks', 'prospects'));
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
