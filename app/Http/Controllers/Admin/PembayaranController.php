<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    // Menampilkan daftar pembayaran
    public function index()
    {
        $pembayarans = Pembayaran::with(['mahasiswa.user'])->latest()->paginate(10);
        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    // Menampilkan detail pembayaran
    public function show($id)
    {
        $pembayaran = Pembayaran::with(['mahasiswa.user', 'mahasiswa.jurusan'])->findOrFail($id);
        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    // Verifikasi pembayaran
    public function verifikasi(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:terverifikasi,ditolak',
            'catatan' => 'nullable|string'
        ]);

        $pembayaran->update([
            'status' => $request->status,
            'catatan' => $request->catatan
        ]);

        return redirect()->back()->with('success', 'Status pembayaran berhasil diupdate');
    }

    // Hapus pembayaran
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        
        // Hapus file bukti pembayaran
        if (file_exists(public_path('storage/' . $pembayaran->bukti_pembayaran))) {
            unlink(public_path('storage/' . $pembayaran->bukti_pembayaran));
        }
        
        $pembayaran->delete();

        return redirect()->route('admin.pembayaran.index')->with('success', 'Data pembayaran berhasil dihapus');
    }
}