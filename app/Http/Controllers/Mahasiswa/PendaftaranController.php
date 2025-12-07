<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PendaftaranController extends Controller
{
    // Form pendaftaran
    public function create()
    {
        // Cek apakah sudah pernah daftar
        if (auth()->user()->mahasiswa) {
            return redirect()->route('mahasiswa.pendaftaran.status')
                           ->with('info', 'Anda sudah melakukan pendaftaran');
        }

        // Ambil jurusan dengan menghitung jumlah mahasiswa yang sudah terdaftar
        // HANYA JURUSAN AKTIF
        $jurusans = Jurusan::where('status', 'active')->withCount('mahasiswas')->get();
        return view('mahasiswa.pendaftaran.create', compact('jurusans'));
    }

    // Simpan pendaftaran
    public function store(Request $request)
    {
        // Cek apakah sudah pernah daftar
        if (auth()->user()->mahasiswa) {
            return redirect()->route('mahasiswa.pendaftaran.status')
                           ->with('error', 'Anda sudah melakukan pendaftaran');
        }

        $request->validate([
            'jurusan_id' => 'required|exists:jurusans,id',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'no_hp' => 'required|string',
            'asal_sekolah' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // ===== LOGIC KUOTA DIMULAI DI SINI =====
        // Ambil data jurusan yang dipilih
        $jurusan = Jurusan::withCount('mahasiswas')->findOrFail($request->jurusan_id);
        
        // Cek status aktif
        if ($jurusan->status !== 'active') {
            return redirect()->back()
                           ->with('error', 'Maaf, jurusan ini sedang tidak menerima pendaftaran (Non-Aktif).')
                           ->withInput();
        }

        // Cek apakah kuota jurusan masih tersedia
        if ($jurusan->mahasiswas_count >= $jurusan->kuota) {
            return redirect()->back()
                           ->with('error', 'Maaf, kuota untuk jurusan ' . $jurusan->nama_jurusan . ' sudah penuh!')
                           ->withInput();
        }
        // ===== LOGIC KUOTA SELESAI =====

        try {
            // Generate nomor pendaftaran
            $no_pendaftaran = 'PMB' . date('Y') . str_pad(Mahasiswa::count() + 1, 4, '0', STR_PAD_LEFT);

            // Pastikan folder penyimpanan ada
            if (!Storage::disk('public')->exists('foto-mahasiswa')) {
                Storage::disk('public')->makeDirectory('foto-mahasiswa');
            }

            $fotoPath = $request->file('foto')->store('foto-mahasiswa', 'public');

            Mahasiswa::create([
                'user_id' => auth()->id(),
                'jurusan_id' => $request->jurusan_id,
                'no_pendaftaran' => $no_pendaftaran,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'asal_sekolah' => $request->asal_sekolah,
                'foto' => $fotoPath,
                'status_pendaftaran' => 'pending'
            ]);

            return redirect()->route('mahasiswa.pendaftaran.status')
                        ->with('success', 'Pendaftaran berhasil! Nomor pendaftaran Anda: ' . $no_pendaftaran);

        } catch (\Exception $e) {
            // Bersihkan file jika database gagal
            if (isset($fotoPath) && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }

            return back()->withInput()
                        ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    // Status pendaftaran
    public function status()
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.pendaftaran.create')
                           ->with('info', 'Silakan lengkapi pendaftaran terlebih dahulu');
        }

        return view('mahasiswa.pendaftaran.status', compact('mahasiswa'));
    }
}