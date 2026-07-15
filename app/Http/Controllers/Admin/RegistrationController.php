<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentLog;
use App\Models\Referrer;
use App\Models\ReferralClick;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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

        $referrers = Referrer::with('user')->where('status', 'active')->get();

        return view('admin.registrations.show', compact('registration', 'referrers'));
    }

    public function confirmPayment(Request $request, int $id)
    {
        $registration = Registration::with(['period', 'firstChoiceProgram'])->findOrFail($id);

        if ($registration->registration_type === 'alumni') {
            if (!$registration->alumni_card_proof && !$request->filled('note')) {
                return redirect()->back()->with('error', 'Kartu alumni belum diunggah. Silakan isi catatan jika ingin melewati (override manual).');
            }
        } else {
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
                $amount = $registration->firstChoiceProgram?->referral_reward_amount ?? 50000;
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

    public function resetExam(int $id)
    {
        $registration = Registration::findOrFail($id);
        
        $session = \App\Models\ExamSession::where('registration_id', $registration->id)->where('status', 'completed')->first();
        if (!$session) {
            return redirect()->back()->with('error', 'Tidak ada sesi tes yang bisa direset.');
        }

        $session->answers()->delete();
        $session->delete();

        $registration->update(['status' => 'menunggu_tes_tulis']);

        \App\Models\PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by' => \Illuminate\Support\Facades\Auth::id(),
            'action' => 'exam_reset',
            'note' => 'Admin mereset sesi tes tulis, pendaftar dapat mengerjakan ulang.'
        ]);

        return redirect()->back()->with('success', 'Sesi tes tulis berhasil direset.');
    }

    public function overrideStatus(Request $request, int $id)
    {
        $request->validate([
            'hasil' => 'required|in:diterima,ditolak',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $registration = Registration::findOrFail($id);

        $allowedStatuses = ['menunggu_interview', 'diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang'];
        if (!in_array($registration->status, $allowedStatuses)) {
            return redirect()->back()->with('error', 'Status pendaftar ini tidak dapat diubah lewat fitur override (sudah melewati tahap daftar ulang).');
        }

        $catatanNote = $request->catatan ? $request->catatan : '-';

        \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $request, $catatanNote) {
            $registration->status = $request->hasil;

            if ($request->hasil === 'diterima' && empty($registration->letter_number)) {
                $registration->letter_number = $registration->generateLetterNumber();
            }

            $internalNotes = $registration->internal_notes ? $registration->internal_notes . "\n\n" : "";
            $internalNotes .= "[Override Admin] Diproses oleh " . Auth::user()->name . " pada " . now()->format('d M Y H:i:s') . "\nCatatan: " . $catatanNote;

            $registration->internal_notes = $internalNotes;
            $registration->save();

            PaymentLog::create([
                'registration_id' => $registration->id,
                'acted_by' => Auth::id(),
                'action' => 'admin_status_override',
                'note' => 'Status diubah manual oleh admin menjadi: ' . strtoupper($request->hasil) . '. Catatan: ' . $catatanNote,
            ]);
        });

        return redirect()->back()->with('success', 'Status pendaftar berhasil diubah menjadi ' . strtoupper($request->hasil) . '.');
    }

    public function confirmReRegistration(Request $request, int $id)
    {
        $registration = Registration::with(['period', 'firstChoiceProgram'])->findOrFail($id);

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
        
        if (empty($registration->nim)) {
            $registration->nim = $registration->generateNim();
        }
        
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
                $amount = $registration->firstChoiceProgram?->re_registration_reward_amount ?? 0;
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

    public function reviewDocument(Request $request, $documentId)
    {
        $request->validate([
            'status' => 'required|in:disetujui,perlu_revisi',
            'review_note' => 'required_if:status,perlu_revisi|nullable|string|max:1000',
        ]);

        $document = RegistrationDocument::with('registration')->findOrFail($documentId);
        
        $document->update([
            'status' => $request->status,
            'review_note' => $request->status === 'perlu_revisi' ? $request->review_note : null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Auto-progress check
        $this->checkAndProgressDocuments($document->registration);

        return redirect()->back()->with('success', 'Review dokumen berhasil disimpan.');
    }

    private function checkAndProgressDocuments($registration)
    {
        $mandatoryCount = count(RegistrationDocument::MANDATORY_TYPES);
        
        $approvedMandatoryCount = RegistrationDocument::where('registration_id', $registration->id)
            ->whereIn('document_type', RegistrationDocument::MANDATORY_TYPES)
            ->where('status', 'disetujui')
            ->count();

        if ($approvedMandatoryCount === $mandatoryCount) {
            // Check if status is still in early stages
            if ($registration->status === 'terdaftar') {
                $registration->update(['status' => 'menunggu_tes_tulis']);
                
                PaymentLog::create([
                    'registration_id' => $registration->id,
                    'acted_by' => Auth::id(),
                    'action' => 'documents_approved',
                    'note' => 'Semua dokumen wajib disetujui, lanjut ke tahap Tes Tulis.',
                ]);
            }
        }
    }

    public function uploadDocument(Request $request, $registrationId)
    {
        $request->validate([
            'document_type' => 'required|in:foto,ktp,kk,akta_lahir,transkrip_nilai,surat_keterangan_sehat,sertifikat',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360',
        ]);

        $registration = Registration::findOrFail($registrationId);
        $documentType = $request->document_type;
        $file = $request->file('file');

        $document = RegistrationDocument::where('registration_id', $registration->id)
            ->where('document_type', $documentType)
            ->first();

        $path = $file->store('dokumen-pendaftaran', 'public');

        if ($document) {
            if ($document->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
            }
            $document->update([
                'file_path' => $path,
                'status' => 'disetujui',
                'review_note' => null,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        } else {
            RegistrationDocument::create([
                'registration_id' => $registration->id,
                'document_type' => $documentType,
                'file_path' => $path,
                'status' => 'disetujui',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        }

        $this->checkAndProgressDocuments($registration);

        PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by' => Auth::id(),
            'action' => 'document_uploaded_by_admin',
            'note' => "Dokumen {$documentType} diupload/diganti langsung oleh admin (" . Auth::user()->name . ").",
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil diupload.');
    }

    public function bulkReviewDocuments(Request $request, int $id)
    {
        $request->validate([
            'document_ids'   => 'required|array|min:1',
            'document_ids.*' => 'integer|exists:registration_documents,id',
            'bulk_status'    => 'required|in:disetujui,perlu_revisi',
            'bulk_note'      => 'required_if:bulk_status,perlu_revisi|nullable|string|max:1000',
        ]);

        $registration = Registration::findOrFail($id);

        $reviewNote = $request->bulk_status === 'perlu_revisi' ? $request->bulk_note : null;

        RegistrationDocument::where('registration_id', $registration->id)
            ->whereIn('id', $request->document_ids)
            ->update([
                'status'      => $request->bulk_status,
                'review_note' => $reviewNote,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

        // Auto-progress check (same logic as single review)
        $mandatoryCount = count(RegistrationDocument::MANDATORY_TYPES);

        $approvedMandatoryCount = RegistrationDocument::where('registration_id', $registration->id)
            ->whereIn('document_type', RegistrationDocument::MANDATORY_TYPES)
            ->where('status', 'disetujui')
            ->count();

        if ($approvedMandatoryCount === $mandatoryCount) {
            if ($registration->status === 'terdaftar') {
                $registration->update(['status' => 'menunggu_tes_tulis']);

                PaymentLog::create([
                    'registration_id' => $registration->id,
                    'acted_by'        => Auth::id(),
                    'action'          => 'documents_approved',
                    'note'            => 'Semua dokumen wajib disetujui (bulk), lanjut ke tahap Tes Tulis.',
                ]);
            }
        }

        $count = count($request->document_ids);
        return redirect()->back()->with('success', "{$count} dokumen berhasil di-review.");
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

    public function updateReferral(Request $request, int $id)
    {
        $request->validate([
            'referrer_id' => 'nullable|exists:referrers,id',
        ]);

        $registration = Registration::findOrFail($id);
        $oldReferrerId = $registration->referrer_id;
        $newReferrerId = $request->referrer_id;

        if ($oldReferrerId != $newReferrerId) {
            $registration->update(['referrer_id' => $newReferrerId]);
            
            if ($registration->user_id) {
                \App\Models\User::where('id', $registration->user_id)->update(['referrer_id' => $newReferrerId]);
            }

            // Pindahkan komisi (Reward) dan sesuaikan total konversi
            $hasConverted = \App\Models\Reward::where('registration_id', $registration->id)->exists();
            
            if ($hasConverted) {
                if ($oldReferrerId) {
                    \App\Models\Referrer::where('id', $oldReferrerId)->decrement('total_conversions');
                }
                
                if ($newReferrerId) {
                    \App\Models\Referrer::where('id', $newReferrerId)->increment('total_conversions');
                    \App\Models\Reward::where('registration_id', $registration->id)->update(['referrer_id' => $newReferrerId]);
                } else {
                    \App\Models\Reward::where('registration_id', $registration->id)->delete();
                }
            }

            $referrerName = $newReferrerId ? Referrer::with('user')->find($newReferrerId)->user->name : 'Dihapus / Tidak Ada';
            
            PaymentLog::create([
                'registration_id' => $registration->id,
                'acted_by'        => Auth::id(),
                'action'          => 'referral_changed',
                'note'            => 'Afiliator diubah menjadi ' . $referrerName . ' oleh admin ' . Auth::user()->name . '. Komisi disesuaikan.',
            ]);
        }

        return redirect()->back()->with('success', 'Afiliator dan komisi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {
            $registration = Registration::findOrFail($id);

            // Ambil semua reward bertipe 'registration' milik pendaftar ini
            $rewards = Reward::where('registration_id', $registration->id)
                ->where('reward_type', 'registration')
                ->get();

            foreach ($rewards as $reward) {
                if (in_array($reward->status, ['pending', 'approved'])) {
                    $reward->update([
                        'status' => 'cancelled',
                        'notes' => ($reward->notes ? $reward->notes . "\n" : "") . "Dibatalkan otomatis - pendaftar dihapus pada " . now()->format('d/m/Y H:i'),
                    ]);
                    
                    if ($reward->referrer_id) {
                        $referrer = Referrer::find($reward->referrer_id);
                        if ($referrer && $referrer->total_conversions > 0) {
                            $referrer->decrement('total_conversions');
                        }
                    }
                } elseif ($reward->status === 'disbursed') {
                    // Jangan ubah status reward, tapi tetap kurangi total_conversions
                    if ($reward->referrer_id) {
                        $referrer = Referrer::find($reward->referrer_id);
                        if ($referrer && $referrer->total_conversions > 0) {
                            $referrer->decrement('total_conversions');
                        }
                    }
                }
            }

            $registration->delete();
        });

        return redirect()->route('admin.registrations.index')->with('success', 'Pendaftar berhasil dihapus/diarsipkan.');
    }

    public function trash(Request $request)
    {
        $query = Registration::onlyTrashed()->with(['user', 'firstChoiceProgram', 'secondChoiceProgram', 'referrer']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        $registrations = $query->latest('deleted_at')->paginate(20)->withQueryString();
        $totalRegistrations = Registration::onlyTrashed()->count();

        return view('admin.registrations.trash', compact('registrations', 'totalRegistrations'));
    }

    public function restore(int $id)
    {
        $registration = Registration::onlyTrashed()->findOrFail($id);
        $registration->restore();

        return redirect()->back()->with('success', 'Pendaftar berhasil dipulihkan. Reward/komisi terkait perlu dicek manual jika diperlukan.');
    }

    public function export(Request $request)
    {
        $periodId = $request->query('period_id');
        $status   = $request->query('status');
        $filename = 'Data_Pendaftar_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new \App\Exports\RegistrationExport($periodId, $status), $filename);
    }
}
