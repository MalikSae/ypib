<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    // Menampilkan daftar mahasiswa
    public function index()
    {
        $mahasiswas = Mahasiswa::with(['user', 'jurusan'])->latest()->paginate(10);
        return view('admin.mahasiswa.index', compact('mahasiswas'));
    }

    // Menampilkan detail mahasiswa
    public function show($id)
    {
        $mahasiswa = Mahasiswa::with(['user', 'jurusan', 'pembayarans'])->findOrFail($id);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    // Verifikasi pendaftaran mahasiswa
    public function verifikasi(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        
        $request->validate([
            'status_pendaftaran' => 'required|in:diverifikasi,ditolak',
            'catatan_admin' => 'nullable|string'
        ]);

        $mahasiswa->update([
            'status_pendaftaran' => $request->status_pendaftaran,
            'catatan_admin' => $request->catatan_admin
        ]);

        return redirect()->back()->with('success', 'Status pendaftaran berhasil diupdate');
    }

    // Mengelola status penerimaan
    public function updateStatus(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        
        $request->validate([
            'status_pendaftaran' => 'required|in:pending,diverifikasi,ditolak,diterima',
        ]);

        $mahasiswa->update([
            'status_pendaftaran' => $request->status_pendaftaran,
        ]);

        return redirect()->back()->with('success', 'Status mahasiswa berhasil diupdate');
    }

    // Hapus mahasiswa
    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->delete();

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa berhasil dihapus');
    }
}