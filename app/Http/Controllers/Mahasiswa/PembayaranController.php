<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    // Menampilkan riwayat pembayaran
    public function index()
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.pendaftaran.create')
                           ->with('info', 'Silakan lengkapi pendaftaran terlebih dahulu');
        }

        $pembayarans = $mahasiswa->pembayarans()->latest()->get();

        return view('mahasiswa.pembayaran.index', compact('pembayarans', 'mahasiswa'));
    }

    // Form upload pembayaran
    public function create()
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.pendaftaran.create')
                           ->with('info', 'Silakan lengkapi pendaftaran terlebih dahulu');
        }

        return view('mahasiswa.pembayaran.create', compact('mahasiswa'));
    }

    // Simpan pembayaran
    public function store(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $request->validate([
            'jumlah' => 'required|numeric|min:0',
            'tanggal_bayar' => 'required|date',
            'bukti_pembayaran' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        // Generate nomor pembayaran
        $no_pembayaran = 'PAY' . date('Ymd') . str_pad(Pembayaran::count() + 1, 4, '0', STR_PAD_LEFT);

        // Upload bukti pembayaran
        $buktiPath = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        Pembayaran::create([
            'mahasiswa_id' => $mahasiswa->id,
            'no_pembayaran' => $no_pembayaran,
            'jumlah' => $request->jumlah,
            'tanggal_bayar' => $request->tanggal_bayar,
            'bukti_pembayaran' => $buktiPath,
            'status' => 'pending'
        ]);

        return redirect()->route('mahasiswa.pembayaran.index')
                       ->with('success', 'Pembayaran berhasil diupload dengan nomor: ' . $no_pembayaran);
    }

    // Detail pembayaran
    public function show($id)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
                                ->findOrFail($id);

        return view('mahasiswa.pembayaran.show', compact('pembayaran'));
    }
}