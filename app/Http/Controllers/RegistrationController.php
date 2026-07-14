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

        if (!$registration->isFormComplete()) {
            return redirect()->route('registration.detail')
                ->with('error', 'Lengkapi seluruh data formulir terlebih dahulu sebelum melanjutkan ke Pemberkasan Dokumen.');
        }

        $mandatoryLabels = [
            'foto' => 'Foto Close Up',
            'kk' => 'Kartu Keluarga (KK)',
            'akta_lahir' => 'Akta Lahir',
        ];

        $optionalLabels = [
            'ktp' => 'KTP (Opsional)',
            'transkrip_nilai' => 'Transkrip Nilai (Opsional)',
            'surat_keterangan_sehat' => 'Surat Keterangan Sehat (Opsional)',
            'sertifikat' => 'Sertifikat (Opsional)',
        ];

        return view('registration.documents', compact('registration', 'mandatoryLabels', 'optionalLabels'));
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

    public function uploadDocumentFile(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'file'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360',
            'label'         => 'required_if:document_type,lainnya|string|max:255',
        ], [
            'file.required' => 'File wajib diupload.',
            'file.mimes'    => 'File harus berformat JPG, PNG, atau PDF.',
            'file.max'      => 'Ukuran file maksimal 15MB.',
            'label.required_if' => 'Nama dokumen wajib diisi untuk dokumen tambahan.',
        ]);

        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();



        $type = $request->input('document_type');
        $file = $request->file('file');
        
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = \Illuminate\Support\Str::slug($originalName) . '-' . time() . '.' . $extension;
        
        if ($type !== 'lainnya') {
            // Cek jika sudah ada file untuk tipe ini
            $existingDoc = $registration->documents()->where('document_type', $type)->first();
            
            if ($existingDoc && $existingDoc->status === 'disetujui') {
                return redirect()->route('registration.documents')->with('error', 'Dokumen ini sudah disetujui dan tidak dapat diubah.');
            }
            
            $path = $file->storeAs('dokumen-pendaftaran', $safeName, 'public');
            
            if ($existingDoc) {
                // Hapus file lama
                if (Storage::disk('public')->exists($existingDoc->file_path)) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }
                
                $existingDoc->update([
                    'file_path' => $path,
                    'status' => 'menunggu_review',
                    'review_note' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);
            } else {
                $registration->documents()->create([
                    'document_type' => $type,
                    'label' => null,
                    'file_path' => $path,
                    'status' => 'menunggu_review',
                ]);
            }
        } else {
            // Untuk dokumen lainnya, selalu buat baru
            $path = $file->storeAs('dokumen-pendaftaran', $safeName, 'public');
            
            $registration->documents()->create([
                'document_type' => 'lainnya',
                'label' => $request->input('label'),
                'file_path' => $path,
                'status' => 'menunggu_review',
            ]);
        }

        return redirect()->route('registration.documents')->with('success', 'Dokumen berhasil diunggah!');
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

    public function reRegistration()
    {
        $registration = Registration::where('user_id', Auth::id())
            ->with(['firstChoiceProgram', 'period'])
            ->latest()
            ->firstOrFail();

        if (!in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            return redirect()->route('registration.status')->with('error', 'Anda belum sampai pada tahap Daftar Ulang.');
        }

        return view('registration.re-registration', compact('registration'));
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
            ->with(['firstChoiceProgram', 'documents'])
            ->latest()
            ->firstOrFail();

        $qrWriter = new \Endroid\QrCode\Writer\PngWriter();
        $qrCode = \Endroid\QrCode\QrCode::create($registration->registration_number)->setSize(120)->setMargin(4);
        $qrCodeBase64 = base64_encode($qrWriter->write($qrCode)->getString());

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('registration.pdf-formulir', compact('registration', 'qrCodeBase64'));
        $fileName = 'Formulir-Pendaftaran-' . \Illuminate\Support\Str::slug($registration->full_name) . '.pdf';

        return $pdf->download($fileName);
    }

    public function downloadSkl()
    {
        $registration = Registration::where('user_id', Auth::id())
            ->with(['firstChoiceProgram', 'period'])
            ->latest()
            ->firstOrFail();

        if (!in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            return redirect()->route('registration.status')->with('error', 'Anda belum dinyatakan lulus.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('registration.pdf-skl', compact('registration'));
        $fileName = 'Surat-Keterangan-Lulus-' . \Illuminate\Support\Str::slug($registration->full_name) . '.pdf';

        return $pdf->download($fileName);
    }

    public function exam()
    {
        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();

        if ($registration->status !== 'menunggu_tes_tulis') {
            $session = \App\Models\ExamSession::where('registration_id', $registration->id)->where('status', 'completed')->first();
            if ($session) {
                return view('registration.exam', ['session' => $session, 'registration' => $registration, 'isCompleted' => true]);
            }
            return redirect()->route('registration.status')->with('error', 'Status Anda belum memenuhi syarat untuk tes tulis.');
        }

        $session = \App\Models\ExamSession::where('registration_id', $registration->id)->first();
        
        if (!$session) {
            $questions = \App\Models\ExamQuestion::where('is_active', true)->get();
            if ($questions->isEmpty()) {
                return redirect()->route('registration.status')->with('error', 'Bank soal belum tersedia, silakan hubungi admin.');
            }
            
            $session = \App\Models\ExamSession::create([
                'registration_id' => $registration->id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            foreach ($questions as $q) {
                \App\Models\ExamAnswer::create([
                    'exam_session_id' => $session->id,
                    'exam_question_id' => $q->id,
                ]);
            }
        } elseif ($session->status === 'completed') {
            return view('registration.exam', ['session' => $session, 'registration' => $registration, 'isCompleted' => true]);
        }

        $answers = \App\Models\ExamAnswer::with('question')
            ->where('exam_session_id', $session->id)
            ->orderBy('exam_question_id')
            ->get();

        return view('registration.exam', ['session' => $session, 'answers' => $answers, 'isCompleted' => false]);
    }

    public function saveExamAnswer(Request $request)
    {
        $validated = $request->validate([
            'exam_answer_id' => 'required|exists:exam_answers,id',
            'selected_option' => 'required|in:a,b,c,d',
        ]);

        $answer = \App\Models\ExamAnswer::findOrFail($validated['exam_answer_id']);
        $session = $answer->session;

        if ($session->registration->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($session->status !== 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Ujian sudah selesai'], 403);
        }

        $answer->update(['selected_option' => $validated['selected_option']]);

        return response()->json(['success' => true]);
    }

    public function submitExam(Request $request)
    {
        $registration = Registration::where('user_id', Auth::id())->latest()->firstOrFail();
        $session = \App\Models\ExamSession::where('registration_id', $registration->id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $answers = \App\Models\ExamAnswer::with('question')->where('exam_session_id', $session->id)->get();
        
        if ($answers->whereNull('selected_option')->count() > 0) {
            return redirect()->back()->with('error', 'Masih ada soal yang belum dijawab, silakan lengkapi semua jawaban.');
        }

        $correctCount = 0;
        foreach ($answers as $ans) {
            $isCorrect = $ans->selected_option === $ans->question->correct_option;
            $ans->update(['is_correct' => $isCorrect]);
            if ($isCorrect) $correctCount++;
        }

        $score = ($correctCount / $answers->count()) * 100;
        $resultLabel = $score >= 80 ? 'sangat_baik' : 'baik';

        $session->update([
            'status' => 'completed',
            'score' => $score,
            'result_label' => $resultLabel,
            'completed_at' => now(),
        ]);

        $registration->update(['status' => 'menunggu_interview']);

        \App\Models\PaymentLog::create([
            'registration_id' => $registration->id,
            'acted_by' => Auth::id(),
            'action' => 'exam_completed',
            'note' => 'Tes tulis online selesai dengan hasil: ' . ($resultLabel === 'sangat_baik' ? 'Sangat Baik' : 'Baik')
        ]);

        return redirect()->route('registration.exam')->with('success', 'Tes tulis berhasil diselesaikan.');
    }

    public function interview()
    {
        $registration = Registration::where('user_id', Auth::id())->with('period')->latest()->firstOrFail();

        if ($registration->status !== 'menunggu_interview') {
            return redirect()->route('registration.status')->with('error', 'Status Anda bukan di tahap interview saat ini.');
        }

        return view('registration.interview', compact('registration'));
    }
}
