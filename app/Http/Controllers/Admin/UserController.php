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
        // Akun staf (admin/operator) hanya boleh direset oleh admin atau pemilik akun sendiri.
        $actor = $request->user();
        abort_if(
            in_array($user->role, ['admin', 'operator'], true)
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
