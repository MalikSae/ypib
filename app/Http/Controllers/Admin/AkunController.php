<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AkunController extends Controller
{
    public function index()
    {
        // Prioritaskan yang pending
        $users = User::where('role', 'calon_mahasiswa')
                     ->orderByRaw("CASE WHEN status_akun = 'pending' THEN 1 ELSE 2 END")
                     ->latest()
                     ->paginate(10);
                     
        return view('admin.akun.index', compact('users'));
    }

    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status_akun' => 'required|in:aktif,ditolak'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'status_akun' => $request->status_akun
        ]);

        $message = $request->status_akun == 'aktif' ? 'Akun berhasil diaktifkan' : 'Akun berhasil ditolak';
        
        return redirect()->back()->with('success', $message);
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return redirect()->back()->with('success', 'Akun berhasil dihapus');
    }
}