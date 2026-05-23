<?php

namespace App\Http\Controllers;

use App\Models\PmbPeriod;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function create(Request $request)
    {
        $period = PmbPeriod::active()->first();

        if (!$period) {
            return redirect()->route('landing')->with('error', 'Pendaftaran belum dibuka.');
        }
        
        $prodiId = $request->query('prodi');
        if (!$prodiId) {
            return redirect()->route('landing')->with('error', 'Silakan pilih program studi terlebih dahulu.');
        }

        // Jika sudah login dan sudah punya registrasi, redirect ke status
        if (Auth::check()) {
            $existing = Registration::where('user_id', Auth::id())->first();
            if ($existing) {
                return redirect()->route('registration.status');
            }
        } else {
            session()->put('url.intended', request()->fullUrl());
        }

        return view('registration.create', compact('period'));
    }

    public function index()
    {
        return redirect()->route('registration.status');
    }

    public function status()
    {
        $registration = Registration::where('user_id', Auth::id())
            ->with(['firstChoiceProgram', 'secondChoiceProgram', 'period'])
            ->latest()
            ->first();

        return view('registration.status', compact('registration'));
    }

    public function uploadProof(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'payment_proof.required' => 'File bukti bayar wajib diupload.',
            'payment_proof.mimes'    => 'File harus berformat JPG, PNG, atau PDF.',
            'payment_proof.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        // Hapus file lama jika ada
        if ($registration->payment_proof && Storage::disk('public')->exists($registration->payment_proof)) {
            Storage::disk('public')->delete($registration->payment_proof);
        }

        $path = $request->file('payment_proof')->store('bukti-bayar', 'public');

        $registration->update([
            'payment_proof' => $path,
            'status'        => 'menunggu_konfirmasi',
        ]);

        return redirect()->route('registration.status')
            ->with('success', 'Bukti transfer berhasil dikirim! Admin akan segera mengkonfirmasi pembayaran Anda.');
    }

    public function uploadReRegistrationProof(Request $request)
    {
        $request->validate([
            're_registration_payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            're_registration_payment_proof.required' => 'File bukti bayar daftar ulang wajib diupload.',
            're_registration_payment_proof.mimes'    => 'File harus berformat JPG, PNG, atau PDF.',
            're_registration_payment_proof.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        if ($registration->status !== 'diterima' && $registration->status !== 'menunggu_konfirmasi_daftar_ulang') {
            return redirect()->route('registration.status')->with('error', 'Status pendaftaran belum memenuhi syarat untuk daftar ulang.');
        }

        // Hapus file lama jika ada
        if ($registration->re_registration_payment_proof && Storage::disk('public')->exists($registration->re_registration_payment_proof)) {
            Storage::disk('public')->delete($registration->re_registration_payment_proof);
        }

        $path = $request->file('re_registration_payment_proof')->store('bukti-bayar-daftar-ulang', 'public');

        $registration->update([
            're_registration_payment_proof' => $path,
            'status'                        => 'menunggu_konfirmasi_daftar_ulang',
        ]);

        return redirect()->route('registration.status')
            ->with('success', 'Bukti transfer daftar ulang berhasil dikirim! Admin akan segera mengkonfirmasi pembayaran Anda.');
    }
}
