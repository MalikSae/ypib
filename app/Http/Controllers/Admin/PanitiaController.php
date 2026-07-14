<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class PanitiaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $panitias = User::where('role', 'panitia')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.panitia.index', compact('panitias', 'search'));
    }

    public function create()
    {
        return view('admin.panitia.form', [
            'panitia' => new User(),
            'action' => route('admin.panitia.store'),
            'method' => 'POST'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'panitia',
            'email_verified_at' => now(), // Auto verify for admin-created accounts
        ]);

        return redirect()->route('admin.panitia.index')->with('success', 'Akun Panitia berhasil ditambahkan.');
    }

    public function edit(User $panitium) // Route resource uses singular 'panitium' or 'panitia' based on Laravel's inflector. Let's force parameter name.
    {
        return view('admin.panitia.form', [
            'panitia' => $panitium,
            'action' => route('admin.panitia.update', $panitium->id),
            'method' => 'PUT'
        ]);
    }

    public function update(Request $request, User $panitium)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$panitium->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $panitium->name = $validated['name'];
        $panitium->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $panitium->password = Hash::make($validated['password']);
        }
        
        $panitium->save();

        return redirect()->route('admin.panitia.index')->with('success', 'Akun Panitia berhasil diperbarui.');
    }

    public function destroy(User $panitium)
    {
        $panitium->delete();
        return redirect()->route('admin.panitia.index')->with('success', 'Akun Panitia berhasil dihapus.');
    }
}
