<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\Referrer;
use App\Models\ReferralClick;
use App\Models\Registration;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = Registration::with(['user', 'firstChoiceProgram', 'secondChoiceProgram', 'referrer']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($prodi = $request->get('prodi')) {
            $query->where('first_choice_program_id', $prodi);
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();

        // Calculate counts for the tabs
        $statusCounts = Registration::select('status', \DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $totalRegistrations = array_sum($statusCounts);

        return view('admin.registrations.index', compact('registrations', 'statusCounts', 'totalRegistrations'));
    }

    public function show(int $id)
    {
        $registration = Registration::with([
            'user', 'referrer.user',
            'firstChoiceProgram', 'secondChoiceProgram',
            'period', 'paymentLogs.actor', 'rewards'
        ])->findOrFail($id);

        return view('admin.registrations.show', compact('registration'));
    }

    public function confirmPayment(Request $request, int $id)
    {
        $registration = Registration::with('period')->findOrFail($id);

        if (!$registration->payment_proof) {
            $request->validate([
                'bukti_bayar' => 'required_without:note|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'note'        => 'required_without:bukti_bayar|string|max:1000',
            ], [
                'bukti_bayar.required_without' => 'Bukti bayar wajib diupload jika catatan kosong.',
                'note.required_without'        => 'Catatan wajib diisi jika bukti bayar tidak diupload.',
                'bukti_bayar.mimes'            => 'Format file harus JPG, PNG, atau PDF.',
                'bukti_bayar.max'              => 'Ukuran file maksimal 2MB.',
            ]);

            if ($request->hasFile('bukti_bayar')) {
                $path = $request->file('bukti_bayar')->store('bukti-bayar', 'public');
                $registration->payment_proof = $path;
            }
        }

        // Generate nomor pendaftaran unik jika belum ada
        if (!$registration->registration_number) {
            $year = $registration->period?->year ?? date('Y');
            do {
                $number = 'PMB-' . $year . '-' . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            } while (Registration::where('registration_number', $number)->exists());

            $registration->registration_number = $number;
        }

        $registration->status = 'terdaftar';
        $registration->save();

        $actionNote = 'Pembayaran dikonfirmasi oleh ' . Auth::user()->name . '. Nomor pendaftaran: ' . $registration->registration_number;
        if ($request->filled('note')) {
            $actionNote .= ' | Catatan: ' . $request->note;
        }

        // Audit log
        PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by'        => Auth::id(),
            'action'          => 'payment_confirmed',
            'note'            => $actionNote,
        ]);

        // Referral reward
        if ($registration->referrer_id) {
            ReferralClick::where('referrer_id', $registration->referrer_id)
                ->where('converted', false)
                ->latest()
                ->first()?->update([
                    'converted'    => true,
                    'converted_at' => now(),
                ]);

            Referrer::where('id', $registration->referrer_id)->increment('total_conversions');

            if (!$registration->rewards()->where('reward_type', 'registration')->exists()) {
                $amount = $registration->period?->referral_reward_amount ?? 50000;
                Reward::create([
                    'referrer_id'     => $registration->referrer_id,
                    'registration_id' => $registration->id,
                    'amount'          => $amount,
                    'reward_type'     => 'registration',
                    'status'          => 'approved',
                    'approved_by'     => Auth::id(),
                    'approved_at'     => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pembayaran dikonfirmasi. Nomor pendaftaran: ' . $registration->registration_number);
    }

    public function uploadBukti(Request $request, int $id)
    {
        $request->validate([
            'bukti_bayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'bukti_bayar.required' => 'File bukti bayar wajib dipilih.',
            'bukti_bayar.mimes'    => 'Format file harus JPG, PNG, atau PDF.',
            'bukti_bayar.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $registration = Registration::findOrFail($id);

        // Hapus file lama jika ada
        if ($registration->payment_proof && Storage::disk('public')->exists($registration->payment_proof)) {
            Storage::disk('public')->delete($registration->payment_proof);
        }

        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'public');

        $registration->update([
            'payment_proof' => $path,
            'status'        => $registration->status === 'menunggu_pembayaran'
                                ? 'menunggu_konfirmasi'
                                : $registration->status,
        ]);

        PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by'        => Auth::id(),
            'action'          => 'bukti_uploaded_admin',
            'note'            => 'Bukti bayar diupload oleh admin ' . Auth::user()->name,
        ]);

        return redirect()->back()->with('success', 'Bukti bayar berhasil diupload.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:diterima,ditolak,perlu_revisi',
        ]);

        $registration = Registration::findOrFail($id);

        // Hanya bisa update status jika sudah terdaftar
        if ($registration->status !== 'terdaftar') {
            return redirect()->back()->with('error', 'Status hanya bisa diubah setelah pendaftar berstatus Terdaftar.');
        }

        $registration->update(['status' => $request->status]);

        $labels = ['diterima' => 'Diterima', 'ditolak' => 'Ditolak', 'perlu_revisi' => 'Perlu Revisi'];

        PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by'        => Auth::id(),
            'action'          => 'status_changed',
            'note'            => 'Status diubah menjadi ' . ($labels[$request->status] ?? $request->status) . ' oleh ' . Auth::user()->name,
        ]);

        return redirect()->back()->with('success', 'Status berhasil diperbarui menjadi ' . ($labels[$request->status] ?? $request->status) . '.');
    }

    public function confirmReRegistration(Request $request, int $id)
    {
        $registration = Registration::with('period')->findOrFail($id);

        if (!in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang'])) {
            return redirect()->back()->with('error', 'Status pendaftar belum memenuhi syarat untuk daftar ulang.');
        }

        if (!$registration->re_registration_payment_proof) {
            $request->validate([
                'bukti_daftar_ulang' => 'required_without:note|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'note'               => 'required_without:bukti_daftar_ulang|string|max:1000',
            ], [
                'bukti_daftar_ulang.required_without' => 'Bukti daftar ulang wajib diupload jika catatan kosong.',
                'note.required_without'               => 'Catatan wajib diisi jika bukti daftar ulang tidak diupload.',
                'bukti_daftar_ulang.mimes'            => 'Format file harus JPG, PNG, atau PDF.',
                'bukti_daftar_ulang.max'              => 'Ukuran file maksimal 2MB.',
            ]);

            if ($request->hasFile('bukti_daftar_ulang')) {
                $path = $request->file('bukti_daftar_ulang')->store('bukti-daftar-ulang', 'public');
                $registration->re_registration_payment_proof = $path;
            }
        }

        $registration->status = 'daftar_ulang_selesai';
        $registration->save();

        $actionNote = 'Pembayaran daftar ulang dikonfirmasi oleh ' . Auth::user()->name;
        if ($request->filled('note')) {
            $actionNote .= ' | Catatan: ' . $request->note;
        }

        PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by'        => Auth::id(),
            'action'          => 're_registration_confirmed',
            'note'            => $actionNote,
        ]);

        // Referral reward untuk Daftar Ulang
        if ($registration->referrer_id) {
            $existingReward = Reward::where('registration_id', $registration->id)
                ->where('reward_type', 're_registration')
                ->first();

            if (!$existingReward) {
                $amount = $registration->period?->re_registration_reward_amount ?? 0;
                if ($amount > 0) {
                    Reward::create([
                        'referrer_id'     => $registration->referrer_id,
                        'registration_id' => $registration->id,
                        'amount'          => $amount,
                        'reward_type'     => 're_registration',
                        'status'          => 'approved',
                        'approved_by'     => Auth::id(),
                        'approved_at'     => now(),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Pembayaran daftar ulang berhasil dikonfirmasi.');
    }

    public function addNote(Request $request, int $id)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $registration = Registration::findOrFail($id);
        $registration->update(['internal_notes' => $request->note]);

        return redirect()->back()->with('success', 'Catatan internal berhasil disimpan.');
    }
}
