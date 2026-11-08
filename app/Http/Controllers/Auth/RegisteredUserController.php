<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cookie;
use App\Models\Referrer;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Datang dari link form minat: nama dan WA akun mengikuti data prospek.
        return view('auth.register', [
            'prospect' => \App\Support\ProspectService::sessionLink()?->prospect,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Link form minat aktif: nama dan WA dikunci ke data prospek, bukan dari input klien.
        if ($prospect = \App\Support\ProspectService::sessionLink()?->prospect) {
            $request->merge([
                'name'  => $prospect->full_name,
                'phone' => $prospect->phone_normalized,
            ]);
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'regex:/^.+@.+\..+$/i', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'    => ['required', 'numeric', 'digits_between:10,15'],
        ], [
            'email.regex' => 'Format alamat email tidak valid (harus mengandung domain seperti .com, .id, dll).',
        ]);

        $referrerId = null;
        $refCode = Cookie::get('ref');
        if ($refCode) {
            $referrer = Referrer::where('code', $refCode)->where('status', 'active')->first();
            if ($referrer) {
                $referrerId = $referrer->id;
            }
        }

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'phone'       => $request->phone,
            'role'        => 'mahasiswa',
            'referrer_id' => $referrerId,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $response = redirect()->intended(route('dashboard', absolute: false));
        if ($refCode) {
            $response->withoutCookie('ref');
        }

        return $response;
    }
}
