<?php

namespace App\Http\Controllers;

use App\Models\PmbPeriod;
use App\Models\Registration;
use App\Models\RegistrationDocument;
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

        $timelineStages = $registration ? $registration->getTimelineStages() : [];

        return view('registration.status', compact('registration', 'timelineStages'));
    }

    public function documents()
    {
        $registration = Registration::where('user_id', Auth::id())
            ->with('documents')
            ->latest()
            ->firstOrFail();

        if (in_array($registration->status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            return redirect()->route('registration.status')->with('error', 'Selesaikan pembayaran terlebih dahulu.');
        }

        $mandatoryLabels = [
            'foto' => 'Foto Close Up (Latar Merah/Biru)',
            'ktp' => 'KTP',
            'kk' => 'Kartu Keluarga (KK)',
            'akta_lahir' => 'Akta Lahir',
            'transkrip_nilai' => 'Transkrip Nilai',
            'surat_keterangan_sehat' => 'Surat Keterangan Sehat',
        ];

        return view('registration.documents', compact('registration', 'mandatoryLabels'));
    }

    public function uploadDocumentFile(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:foto,ktp,kk,akta_lahir,transkrip_nilai,surat_keterangan_sehat,sertifikat,lainnya',
            'label' => 'required_if:document_type,lainnya|nullable|string|max:255',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360',
        ], [
            'document_type.required' => 'Tipe dokumen tidak valid.',
            'document_type.in' => 'Tipe dokumen tidak valid.',
            'label.required_if' => 'Nama dokumen lainnya harus diisi.',
            'file.required' => 'File dokumen wajib diupload.',
            'file.mimes' => 'File harus berformat JPG, PNG, atau PDF.',
            'file.max' => 'Ukuran file maksimal 15MB.',
        ]);

        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        $type = $request->document_type;
        $label = $type === 'lainnya' ? $request->label : null;

        if ($type !== 'lainnya') {
            $existing = RegistrationDocument::where('registration_id', $registration->id)
                ->where('document_type', $type)
                ->first();

            if ($existing) {
                if ($existing->status === 'disetujui') {
                    return back()->with('error', 'Dokumen ini sudah disetujui, tidak bisa diubah lagi. Hubungi admin jika perlu koreksi.');
                }
                
                // Hapus file lama
                if (Storage::disk('public')->exists($existing->file_path)) {
                    Storage::disk('public')->delete($existing->file_path);
                }
                
                $path = $request->file('file')->store('dokumen-pendaftaran', 'public');
                $existing->update([
                    'file_path' => $path,
                    'status' => 'menunggu_review',
                    'review_note' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);
            } else {
                $path = $request->file('file')->store('dokumen-pendaftaran', 'public');
                RegistrationDocument::create([
                    'registration_id' => $registration->id,
                    'document_type' => $type,
                    'file_path' => $path,
                    'status' => 'menunggu_review',
                ]);
            }
        } else {
            $path = $request->file('file')->store('dokumen-pendaftaran', 'public');
            RegistrationDocument::create([
                'registration_id' => $registration->id,
                'document_type' => $type,
                'label' => $label,
                'file_path' => $path,
                'status' => 'menunggu_review',
            ]);
        }

        return redirect()->route('registration.documents')
            ->with('success', 'Dokumen berhasil diupload!');
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

    public function uploadAlumniCard(Request $request)
    {
        $request->validate([
            'alumni_card_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'alumni_card_proof.required' => 'File kartu alumni wajib diupload.',
            'alumni_card_proof.mimes'    => 'File harus berformat JPG, PNG, atau PDF.',
            'alumni_card_proof.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        // Hapus file lama jika ada
        if ($registration->alumni_card_proof && Storage::disk('public')->exists($registration->alumni_card_proof)) {
            Storage::disk('public')->delete($registration->alumni_card_proof);
        }

        $path = $request->file('alumni_card_proof')->store('kartu-alumni', 'public');

        $registration->update([
            'alumni_card_proof' => $path,
            'registration_type' => 'alumni',
            'status'            => 'menunggu_konfirmasi',
        ]);

        return redirect()->route('registration.status')
            ->with('success', 'Kartu alumni berhasil dikirim! Admin akan segera mengkonfirmasi pendaftaran Anda.');
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360',
        ], [
            'document_proof.required' => 'File Ijazah/SKL wajib diupload.',
            'document_proof.mimes'    => 'File harus berformat JPG, PNG, atau PDF.',
            'document_proof.max'      => 'Ukuran file maksimal 15MB.',
        ]);

        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        // Cek status, harus terdaftar atau perlu_revisi_berkas
        if (!in_array($registration->status, ['terdaftar', 'perlu_revisi_berkas'])) {
            return redirect()->route('registration.status')->with('error', 'Status pendaftaran belum memenuhi syarat untuk upload berkas.');
        }

        // Hapus file lama jika ada
        if ($registration->document_proof && Storage::disk('public')->exists($registration->document_proof)) {
            Storage::disk('public')->delete($registration->document_proof);
        }

        $path = $request->file('document_proof')->store('dokumen-ijazah', 'public');

        $registration->update([
            'document_proof' => $path,
            'status'         => 'menunggu_review_berkas',
        ]);

        return redirect()->route('registration.status')
            ->with('success', 'Berkas berhasil diupload! Admin akan segera mereview dokumen Anda.');
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

    public function detail()
    {
        $registration = Registration::where('user_id', Auth::id())
            ->with('firstChoiceProgram')
            ->latest()
            ->firstOrFail();

        $canEdit = !in_array($registration->status, ['diterima', 'ditolak']);

        return view('registration.detail', compact('registration', 'canEdit'));
    }

    public function updateDetail(Request $request)
    {
        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        if (in_array($registration->status, ['diterima', 'ditolak'])) {
            return redirect()->back()->with('error', 'Data tidak bisa diedit lagi karena pendaftaran sudah diproses.');
        }

        $section = $request->input('section');
        $rules = [];

        if ($section === 'data_diri') {
            $rules = [
                'full_name'   => 'required|string|max:255',
                'nisn'        => 'required|string|digits:10',
                'nik'         => 'required|string|size:16',
                'birth_place' => 'required|string|max:100',
                'birth_date'  => 'required|date',
                'gender'      => 'required|in:male,female',
                'religion'    => 'required|string|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu,Lainnya',
                'address'     => 'required|string',
                'phone'       => 'required|string|max:20',
            ];
            $sectionName = 'Data Diri';
        } elseif ($section === 'program_jalur') {
            $rules = [
                'first_choice_program_id' => 'required|exists:programs,id',
                'admission_path'          => 'required|in:umum,prestasi,tahfidz',
            ];
            $sectionName = 'Program Studi & Jalur';
        } elseif ($section === 'data_sekolah') {
            $rules = [
                'school_name'        => 'required|string|max:255',
                'school_major'       => 'required|string|max:255',
                'graduation_year'    => 'required|digits:4',
                'certificate_number' => 'required|string|max:100',
                'school_grade'       => 'nullable|numeric',
            ];
            $sectionName = 'Data Sekolah';
        } elseif ($section === 'data_ortu') {
            $rules = [
                'father_name'       => 'required|string|max:255',
                'mother_name'       => 'required|string|max:255',
                'father_occupation' => 'required|string|max:255',
                'mother_occupation' => 'required|string|max:255',
            ];
            $sectionName = 'Data Orang Tua/Wali';
        } else {
            return redirect()->back()->with('error', 'Section form tidak valid.');
        }

        $validatedData = $request->validate($rules);
        $registration->update($validatedData);

        $anchor = str_replace('_', '-', $section);

        return redirect()->route('registration.detail', ['#' . $anchor])->with('success', "Data {$sectionName} berhasil diperbarui.");
    }

    public function downloadPdf()
    {
        $registration = Registration::where('user_id', Auth::id())
            ->with('firstChoiceProgram')
            ->latest()
            ->firstOrFail();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('registration.pdf-formulir', compact('registration'));
        $fileName = 'Formulir-Pendaftaran-' . \Illuminate\Support\Str::slug($registration->full_name) . '.pdf';

        return $pdf->download($fileName);
    }
}
