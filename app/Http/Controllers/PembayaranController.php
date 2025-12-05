<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index()
    {
        $mahasiswa = Mahasiswa::where('user_id', auth()->id())->firstOrFail();
        $pembayaran = $mahasiswa->pembayaran()->latest()->get();

        return view('pembayaran.index', compact('mahasiswa', 'pembayaran'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Mahasiswa::where('user_id', auth()->id())->firstOrFail();

        if ($mahasiswa->status_pendaftaran === 'draft') {
            return back()->with('error', 'Silakan submit pendaftaran terlebih dahulu.');
        }

        $validated = $request->validate([
            'metode_pembayaran' => 'required|in:transfer,cash,virtual_account,e-wallet',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload bukti pembayaran
        $buktiPath = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        // Generate nomor pembayaran
        $lastPembayaran = Pembayaran::whereYear('created_at', date('Y'))->count();
        $noPembayaran = 'PAY' . date('Y') . str_pad($lastPembayaran + 1, 5, '0', STR_PAD_LEFT);

        Pembayaran::create([
            'mahasiswa_id' => $mahasiswa->id,
            'no_pembayaran' => $noPembayaran,
            'jumlah' => $mahasiswa->jurusan->biaya_pendaftaran,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'bukti_pembayaran' => $buktiPath,
            'status' => 'pending',
            'tanggal_bayar' => now(),
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
    }
}