<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Akun dengan riwayat pendaftaran/aktivitas dipertahankan demi integritas
        // data (FK + audit). Tolak dengan pesan jelas, sesi tetap aktif.
        $hasHistory = \App\Models\Registration::withTrashed()->where('user_id', $user->id)->exists()
            || \App\Models\PaymentLog::where('acted_by', $user->id)->exists();

        if ($hasHistory) {
            return Redirect::back()->withErrors(
                ['password' => 'Akun ini memiliki riwayat pendaftaran/aktivitas sehingga tidak dapat dihapus mandiri. Hubungi admin bila perlu.'],
                'userDeletion'
            );
        }

        try {
            // Hapus lewat query, bukan $user->delete(): Auth::logout() di bawah memanggil
            // save() pada instance user untuk mengganti remember token, dan pada model
            // yang sudah di-delete() itu akan meng-INSERT ulang barisnya.
            \App\Models\User::whereKey($user->id)->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return Redirect::back()->withErrors(
                ['password' => 'Akun tidak dapat dihapus karena masih terhubung dengan data lain. Hubungi admin.'],
                'userDeletion'
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
