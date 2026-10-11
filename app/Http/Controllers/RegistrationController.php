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
        $hasAnyRegistration = Auth::check() && Registration::where('user_id', Auth::id())->exists();
        $period = PmbPeriod::current();

        if (!$period) {
            // Pendaftar lama tetap diarahkan ke halaman statusnya.
            if ($hasAnyRegistration) {
                return redirect()->route('registration.status');
            }

            $message = PmbPeriod::displayPeriod()
                ? 'Pendaftaran belum dibuka atau sudah ditutup.'
                : 'Pendaftaran belum dibuka.';

            return redirect()->route('landing')->with('error', $message);
        }

        // Hanya pendaftaran di periode berjalan yang mengalihkan ke status; akun yang mendaftar
        // pada tahun ajaran lalu boleh mendaftar lagi (K1, diperiksa K7 saat submit).
        if (Auth::check() && Registration::where('user_id', Auth::id())->where('period_id', $period->id)->exists()) {
            return redirect()->route('registration.status');
        }

        $prodiId = $request->query('prodi');
        if (!$prodiId) {
            return redirect()->route('landing')->with('error', 'Silakan pilih program studi terlebih dahulu.');
        }

        if (!Auth::check()) {
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
        $registration = $this->ownRegistration(['firstChoiceProgram', 'secondChoiceProgram', 'period'], false);

        $timelineStages = $registration ? $registration->getTimelineStages() : [];
        $reapply = $registration ? $this->reapplyOffer($registration) : null;
        $windowNotice = $registration ? \App\Support\RegistrationWindow::notice($registration) : null;

        // Riwayat: seluruh pendaftaran akun ini, dan langkah tertunda di pendaftaran lain (T7).
        $myRegistrations = collect();
        $pendingElsewhere = collect();

        if ($registration) {
            $myRegistrations = Registration::with(['period', 'firstChoiceProgram'])
                ->where('user_id', Auth::id())
                ->latest('id')
                ->get();

            $actionStatuses = ['menunggu_pembayaran', 'terdaftar', 'menunggu_tes_tulis', 'diterima'];
            $pendingElsewhere = $myRegistrations->filter(fn ($other) => $other->id !== $registration->id
                && in_array($other->status, $actionStatuses, true)
                && !$other->period?->isArchived());
        }

        return view('registration.status', compact(
            'registration', 'timelineStages', 'reapply', 'windowNotice', 'myRegistrations', 'pendingElsewhere'
        ));
    }

    public function documents()
    {
        $registration = $this->ownRegistration('documents');

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

        $registration = $this->ownRegistration();

        if (!in_array($registration->status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            return redirect()->route('registration.status')->with('error', 'Upload tidak dapat dilakukan pada tahap pendaftaran saat ini.');
        }

        // Bukti pertama hanya dalam masa bayar periode; periode arsip hanya baca (PMB_ENFORCE_WINDOW).
        if ($denied = \App\Support\RegistrationWindow::denyFirstProof($registration->loadMissing('period'))) {
            return redirect()->route('registration.status')->with('error', $denied);
        }

        // Hapus file lama jika ada
        \App\Support\PrivateFile::delete($registration->payment_proof);

        $path = \App\Support\PrivateFile::store($request->file('payment_proof'), 'bukti-bayar');

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

        $registration = $this->ownRegistration();

        if (!in_array($registration->status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            return redirect()->route('registration.status')->with('error', 'Upload tidak dapat dilakukan pada tahap pendaftaran saat ini.');
        }

        // Bukti pertama hanya dalam masa bayar periode; periode arsip hanya baca (PMB_ENFORCE_WINDOW).
        if ($denied = \App\Support\RegistrationWindow::denyFirstProof($registration->loadMissing('period'))) {
            return redirect()->route('registration.status')->with('error', $denied);
        }

        // Hapus file lama jika ada
        \App\Support\PrivateFile::delete($registration->alumni_card_proof);

        $path = \App\Support\PrivateFile::store($request->file('alumni_card_proof'), 'kartu-alumni');

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
            'document_type' => ['required', 'string', \Illuminate\Validation\Rule::in(RegistrationDocument::TYPES)],
            'file'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360',
            'label'         => 'required_if:document_type,lainnya|string|max:255',
        ], [
            'file.required' => 'File wajib diupload.',
            'file.mimes'    => 'File harus berformat JPG, PNG, atau PDF.',
            'file.max'      => 'Ukuran file maksimal 15MB.',
            'label.required_if' => 'Nama dokumen wajib diisi untuk dokumen tambahan.',
        ]);

        $registration = $this->ownRegistration();

        // Gerbang yang sama dengan halaman documents()
        if (in_array($registration->status, ['menunggu_pembayaran', 'menunggu_konfirmasi'])) {
            return redirect()->route('registration.status')->with('error', 'Selesaikan pembayaran terlebih dahulu.');
        }

        if ($denied = \App\Support\RegistrationWindow::denyMutation($registration->loadMissing('period'))) {
            return redirect()->route('registration.documents')->with('error', $denied);
        }

        if (!$registration->isFormComplete()) {
            return redirect()->route('registration.detail')
                ->with('error', 'Lengkapi seluruh data formulir terlebih dahulu sebelum mengunggah dokumen.');
        }

        $type = $request->input('document_type');
        $file = $request->file('file');

        if ($type !== 'lainnya') {
            // Cek jika sudah ada file untuk tipe ini
            $existingDoc = $registration->documents()->where('document_type', $type)->first();
            
            if ($existingDoc && $existingDoc->status === 'disetujui') {
                return redirect()->route('registration.documents')->with('error', 'Dokumen ini sudah disetujui dan tidak dapat diubah.');
            }
            
            $path = \App\Support\PrivateFile::store($file, 'dokumen-pendaftaran');

            if ($existingDoc) {
                // Hapus file lama
                \App\Support\PrivateFile::delete($existingDoc->file_path);

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
            $path = \App\Support\PrivateFile::store($file, 'dokumen-pendaftaran');

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

        $registration = $this->ownRegistration();

        if ($registration->status !== 'diterima' && $registration->status !== 'menunggu_konfirmasi_daftar_ulang') {
            return redirect()->route('registration.status')->with('error', 'Status pendaftaran belum memenuhi syarat untuk daftar ulang.');
        }

        if ($denied = \App\Support\RegistrationWindow::denyReRegistrationProof($registration->loadMissing('period'))) {
            return redirect()->route('registration.status')->with('error', $denied);
        }

        // Hapus file lama jika ada
        \App\Support\PrivateFile::delete($registration->re_registration_payment_proof);

        $path = \App\Support\PrivateFile::store($request->file('re_registration_payment_proof'), 'bukti-bayar-daftar-ulang');

        $registration->update([
            're_registration_payment_proof' => $path,
            'status'                        => 'menunggu_konfirmasi_daftar_ulang',
        ]);

        return redirect()->route('registration.status')
            ->with('success', 'Bukti transfer daftar ulang berhasil dikirim! Admin akan segera mengkonfirmasi pembayaran Anda.');
    }

    public function reRegistration()
    {
        $registration = $this->ownRegistration(['firstChoiceProgram', 'period']);

        if (!in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            return redirect()->route('registration.status')->with('error', 'Anda belum sampai pada tahap Daftar Ulang.');
        }

        return view('registration.re-registration', compact('registration'));
    }

    public function detail()
    {
        $registration = $this->ownRegistration('firstChoiceProgram');

        $canEdit = !$registration->isDetailLocked();

        return view('registration.detail', compact('registration', 'canEdit'));
    }

    public function updateDetail(Request $request)
    {
        $registration = $this->ownRegistration();

        if ($registration->isDetailLocked()) {
            return redirect()->back()->with('error', 'Data tidak bisa diedit lagi karena pendaftaran sudah diproses.');
        }

        if ($denied = \App\Support\RegistrationWindow::denyMutation($registration->loadMissing('period'))) {
            return redirect()->back()->with('error', $denied);
        }

        $section = $request->input('section');
        $rules = [];

        if ($section === 'data_diri') {
            $rules = [
                'full_name'   => 'required|string|max:255',
                'nisn'        => 'required|string|digits:10',
                'nik'         => 'required|digits:16',
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
                // Hanya prodi aktif (atau prodi yang sedang dipilih pendaftar)
                'first_choice_program_id' => ['required', \Illuminate\Validation\Rule::exists('programs', 'id')
                    ->where(fn ($q) => $q->where('is_active', true)
                        ->orWhere('id', $registration->first_choice_program_id))],
                'admission_path'          => 'required|in:umum,prestasi,tahfidz',
            ];
            $sectionName = 'Program Studi & Jalur';
        } elseif ($section === 'data_sekolah') {
            $rules = [
                'school_name'        => 'required|string|max:255',
                'school_major'       => 'required|string|max:255',
                'graduation_year'    => 'required|digits:4',
                'certificate_number' => 'required|string|max:100',
                'school_grade'       => 'nullable|numeric|between:0,100',
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

        // Mengubah NIK tidak boleh dipakai melewati aturan satu proses aktif per orang (K7).
        if ($section === 'data_diri'
            && (string) $validatedData['nik'] !== (string) $registration->nik
            && \App\Support\NikGuard::blocks($validatedData['nik'], (int) $registration->first_choice_program_id, $registration->id)) {
            return redirect()->back()->withInput()->withErrors([
                'nik' => \App\Support\NikGuard::message($registration->period),
            ]);
        }

        // Pindah prodi: tolak bila prodi tujuan sudah penuh pada periode ini.
        if ($section === 'program_jalur'
            && (int) $validatedData['first_choice_program_id'] !== (int) $registration->first_choice_program_id) {
            $target = \App\Models\Program::find($validatedData['first_choice_program_id']);

            // Prodi tujuan harus ditawarkan pada periode pendaftaran ini (period_programs).
            if ($target && !\App\Models\Program::offeredIn($registration->period_id)->whereKey($target->id)->exists()) {
                return redirect()->back()->withInput()->withErrors([
                    'first_choice_program_id' => 'Program studi tujuan tidak ditawarkan pada periode pendaftaran Anda.',
                ]);
            }

            if ($target && $target->isFull($registration->period_id)) {
                return redirect()->back()->withInput()->withErrors([
                    'first_choice_program_id' => 'Kuota program studi tujuan sudah penuh. Silakan pilih program studi lain.',
                ]);
            }
        }

        $registration->update($validatedData);

        $anchor = str_replace('_', '-', $section);

        return redirect()->route('registration.detail', ['#' . $anchor])->with('success', "Data {$sectionName} berhasil diperbarui.");
    }

    public function downloadPdf()
    {
        $registration = $this->ownRegistration(['firstChoiceProgram', 'documents']);

        // Nomor pendaftaran baru diberikan saat pembayaran dikonfirmasi. Sebelum itu formulir
        // tetap bisa diunduh tanpa QR (view sudah menangani $qrCodeBase64 yang null).
        $qrCodeBase64 = null;
        if (!empty($registration->registration_number)) {
            $qrWriter = new \Endroid\QrCode\Writer\PngWriter();
            $qrCode = \Endroid\QrCode\QrCode::create($registration->registration_number)->setSize(120)->setMargin(4);
            $qrCodeBase64 = base64_encode($qrWriter->write($qrCode)->getString());
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('registration.pdf-formulir', compact('registration', 'qrCodeBase64'));
        $fileName = 'Formulir-Pendaftaran-' . \Illuminate\Support\Str::slug($registration->full_name) . '.pdf';

        return $pdf->download($fileName);
    }

    public function downloadSkl()
    {
        $registration = $this->ownRegistration(['firstChoiceProgram', 'period']);

        if (!in_array($registration->status, ['diterima', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'])) {
            return redirect()->route('registration.status')->with('error', 'Anda belum dinyatakan lulus.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('registration.pdf-skl', compact('registration'));
        $fileName = 'Surat-Keterangan-Lulus-' . \Illuminate\Support\Str::slug($registration->full_name) . '.pdf';

        return $pdf->download($fileName);
    }

    public function downloadEktm()
    {
        $registration = $this->ownRegistration(['firstChoiceProgram.faculty', 'documents']);

        if (empty($registration->nim)) {
            return redirect()->back()->with('error', 'NIM belum tersedia, e-KTM belum bisa didownload.');
        }

        $foto = $registration->documents->where('document_type', 'foto')->first();
        if (!$foto) {
            return redirect()->back()->with('error', 'Foto pendaftar tidak ditemukan.');
        }

        $fotoPath = \App\Support\PrivateFile::absolutePath($foto->file_path);
        if (!$fotoPath || !file_exists($fotoPath)) {
            return redirect()->back()->with('error', 'File foto pendaftar tidak ditemukan di server.');
        }

        // 1. Load template pakai GD dan konversi ke truecolor untuk mencegah pallete issue
        $templatePath = public_path('images/e-ktm-blank.png');
        $sourceImg = imagecreatefrompng($templatePath);
        $img = imagecreatetruecolor(imagesx($sourceImg), imagesy($sourceImg));
        imagecopy($img, $sourceImg, 0, 0, 0, 0, imagesx($sourceImg), imagesy($sourceImg));
        imagedestroy($sourceImg);

        // 2. FOTO - pakai Intervention Image untuk fit/crop 220x290
        $manager = new \Intervention\Image\ImageManager(\Intervention\Image\Drivers\Gd\Driver::class);
        $fotoImg = $manager->decodePath($fotoPath);
        $fotoImg->cover(220, 290);
        
        $fotoData = $fotoImg->encode()->toString();
        $fotoGd = imagecreatefromstring($fotoData);
        imagecopy($img, $fotoGd, 705, 240, 0, 0, 220, 290);
        imagedestroy($fotoGd);

        // 3. QR CODE - generate QR berisi NIM
        $qrWriter = new \Endroid\QrCode\Writer\PngWriter();
        $qrCode = \Endroid\QrCode\QrCode::create($registration->nim)->setSize(115)->setMargin(0);
        $qrData = $qrWriter->write($qrCode)->getString();
        $qrGd = imagecreatefromstring($qrData);
        imagecopy($img, $qrGd, 40, 505, 0, 0, 115, 115);
        imagedestroy($qrGd);

        // 4. TEKS VALUE - pakai imagettftext
        $fontPath = public_path('fonts/inter/Inter-Regular.ttf');
        $color = imagecolorallocate($img, 5, 30, 84); // #051e54
        
        $angkatan = '20' . substr($registration->nim, 0, 2);
        
        imagettftext($img, 16, 0, 275, 299, $color, $fontPath, $registration->full_name);
        imagettftext($img, 16, 0, 275, 337, $color, $fontPath, $registration->nim);
        imagettftext($img, 16, 0, 275, 375, $color, $fontPath, $registration->firstChoiceProgram->faculty->name ?? '-');
        imagettftext($img, 16, 0, 275, 413, $color, $fontPath, $registration->firstChoiceProgram->name ?? '-');
        imagettftext($img, 16, 0, 275, 451, $color, $fontPath, $angkatan);

        // 5. Output sebagai PNG
        ob_start();
        imagepng($img);
        $imageData = ob_get_clean();
        imagedestroy($img);

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="e-ktm-' . $registration->nim . '.png"',
        ]);
    }

    public function exam()
    {
        $registration = $this->ownRegistration();

        if ($registration->status !== 'menunggu_tes_tulis') {
            $session = \App\Models\ExamSession::where('registration_id', $registration->id)->where('status', 'completed')->first();
            if ($session) {
                return view('registration.exam', ['session' => $session, 'registration' => $registration, 'isCompleted' => true]);
            }
            return redirect()->route('registration.status')->with('error', 'Status Anda belum memenuhi syarat untuk tes tulis.');
        }

        if ($denied = \App\Support\RegistrationWindow::denyMutation($registration->loadMissing('period'))) {
            return redirect()->route('registration.status')->with('error', $denied);
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

        if ($denied = \App\Support\RegistrationWindow::denyMutation($session->registration->loadMissing('period'))) {
            return response()->json(['success' => false, 'message' => $denied], 403);
        }

        $answer->update(['selected_option' => $validated['selected_option']]);

        return response()->json(['success' => true]);
    }

    public function submitExam(Request $request)
    {
        $registration = $this->ownRegistration('period');

        if ($denied = \App\Support\RegistrationWindow::denyMutation($registration)) {
            return redirect()->route('registration.status')->with('error', $denied);
        }

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
        $registration = $this->ownRegistration('period');

        if ($registration->status !== 'menunggu_interview') {
            return redirect()->route('registration.status')->with('error', 'Status Anda bukan di tahap interview saat ini.');
        }

        return view('registration.interview', compact('registration'));
    }

    /**
     * Pendaftaran yang sedang dilihat akun ini: `?periode=` bila ada, selain itu di periode
     * berjalan, selain itu yang terbaru. Hanya milik akun yang login.
     */
    private function ownRegistration(array|string $with = [], bool $orFail = true): ?Registration
    {
        $registration = \App\Support\CurrentRegistration::forRequest(Auth::user(), request());

        if (!$registration) {
            if ($orFail) {
                abort(404);
            }

            return null;
        }

        return $with ? $registration->load($with) : $registration;
    }

    /**
     * Tawaran "Daftar di periode berjalan" pada halaman status: ada periode berjalan, akun belum
     * punya pendaftaran di sana, dan NIK tidak terblokir K7.
     *
     * @return array{period: PmbPeriod, url: string}|null
     */
    private function reapplyOffer(Registration $registration): ?array
    {
        $current = PmbPeriod::current();

        if (!$current || (int) $registration->period_id === (int) $current->id) {
            return null;
        }

        if (Registration::where('user_id', Auth::id())->where('period_id', $current->id)->exists()) {
            return null;
        }

        if (\App\Support\NikGuard::blocks((string) $registration->nik, (int) $registration->first_choice_program_id)) {
            return null;
        }

        // `create` mewajibkan ?prodi=, jadi arahkan ke prodi yang sama bila ditawarkan di periode ini.
        $sameProgramOffered = \App\Models\Program::offeredIn($current)
            ->whereKey($registration->first_choice_program_id)->exists();

        return [
            'period' => $current,
            'url'    => $sameProgramOffered
                ? route('registration.create', ['prodi' => $registration->first_choice_program_id])
                : route('landing') . '#program-studi',
        ];
    }
}
