<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Reset the user's password manually.
     */
    public function resetPassword(Request $request, User $user)
    {
        // Akun staf (admin/operator/panitia) hanya boleh direset oleh admin atau pemilik akun sendiri.
        // Pengelolaan lengkapnya ada di Pengaturan > Akun Staf.
        $actor = $request->user();
        abort_if(
            in_array($user->role, ['admin', 'operator', 'panitia'], true)
                && $actor->role !== 'admin'
                && $actor->id !== $user->id,
            403,
            'Hanya admin yang dapat mereset password akun staf.'
        );

$request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Password lama tidak boleh tetap dipakai oleh sesi yang sudah terbuka.
        \App\Support\StaffSessions::forget($user);
        \Illuminate\Support\Facades\Log::info('User password reset', [
            'target_id' => $user->id,
            'target_role' => $user->role,
            'by_user' => auth()->id(),
        ]);

        $referrer = \App\Models\Referrer::where('user_id', $user->id)->first();
        if ($referrer) {
            $referrer->logs()->create([
                'acted_by' => auth()->id(),
                'action' => 'password_reset',
                'note' => 'Password akun direset oleh admin',
            ]);
        }

        return redirect()->back()->with('success', 'Password pengguna ' . $user->name . ' berhasil direset.');
    }
}
