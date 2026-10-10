<?php

namespace Tests\Feature\Pmb;

use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\PaymentLog;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class StudentFlowTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    // ── Bukti bayar & kartu alumni ────────────────────────────────────
    public function test_payment_proof_is_stored_privately_and_moves_to_waiting_confirmation(): void
    {
        [$user, $registration] = $this->student('menunggu_pembayaran', ['registration_number' => null]);

        $this->actingAs($user)->post('/pendaftaran/upload-bukti', ['payment_proof' => UploadedFile::fake()->image('bukti.png')])
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame('menunggu_konfirmasi', $registration->status);
        Storage::disk('local')->assertExists($registration->payment_proof);
        Storage::disk('public')->assertMissing($registration->payment_proof);
    }

    public function test_proof_uploads_are_refused_after_payment_stage(): void
    {
        foreach (['terdaftar', 'menunggu_tes_tulis', 'menunggu_interview', 'diterima', 'daftar_ulang_selesai'] as $status) {
            [$user, $registration] = $this->student($status);

            $this->actingAs($user)->post('/pendaftaran/upload-bukti', ['payment_proof' => UploadedFile::fake()->image('p.png')])
                ->assertRedirect(route('registration.status'));
            $this->actingAs($user)->post('/pendaftaran/upload-kartu-alumni', ['alumni_card_proof' => UploadedFile::fake()->image('a.png')])
                ->assertRedirect(route('registration.status'));

            $registration->refresh();
            $this->assertSame($status, $registration->status, "status mundur pada {$status}");
            $this->assertSame('reguler', $registration->registration_type);
            $this->assertNull($registration->payment_proof);
            $this->assertNull($registration->alumni_card_proof);
        }
    }

    public function test_alumni_card_marks_alumni_while_payment_is_pending(): void
    {
        foreach (['menunggu_pembayaran', 'menunggu_konfirmasi'] as $status) {
            [$user, $registration] = $this->student($status, ['registration_number' => null]);

            $this->actingAs($user)->post('/pendaftaran/upload-kartu-alumni', ['alumni_card_proof' => UploadedFile::fake()->image('a.png')]);

            $registration->refresh();
            $this->assertSame('alumni', $registration->registration_type);
            $this->assertSame('menunggu_konfirmasi', $registration->status);
        }
    }

    // ── Dokumen ───────────────────────────────────────────────────────
    public function test_document_upload_gates_and_validation(): void
    {
        foreach (['menunggu_pembayaran', 'menunggu_konfirmasi'] as $status) {
            [$user, $registration] = $this->student($status);
            $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'foto', 'file' => UploadedFile::fake()->image('a.png')])
                ->assertRedirect(route('registration.status'));
            $this->assertSame(0, $registration->documents()->count());
        }

        [$user, $registration] = $this->student('terdaftar', ['nisn' => null]);   // formulir belum lengkap
        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'foto', 'file' => UploadedFile::fake()->image('a.png')])
            ->assertRedirect(route('registration.detail'));
        $this->assertSame(0, $registration->documents()->count());

        [$user, $registration] = $this->student('terdaftar');
        $resp = $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => '<script>', 'file' => UploadedFile::fake()->image('a.png')]);
        $this->assertLessThan(500, $resp->getStatusCode());
        $resp->assertSessionHasErrors('document_type');

        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'foto', 'file' => UploadedFile::fake()->image('Foto Saya.png')])
            ->assertSessionHas('success');
        $document = $registration->documents()->first();
        $this->assertStringStartsWith('dokumen-pendaftaran/', $document->file_path);
        $this->assertStringNotContainsString('Foto', $document->file_path, 'nama berkas klien tidak boleh dipakai');
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'lainnya', 'file' => UploadedFile::fake()->image('b.png')])
            ->assertSessionHasErrors('label');
    }

    public function test_approved_document_cannot_be_replaced_and_old_file_is_removed_on_replace(): void
    {
        [$user, $registration] = $this->student('terdaftar');

        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'kk', 'file' => UploadedFile::fake()->image('1.png')]);
        $first = $registration->documents()->first();
        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'kk', 'file' => UploadedFile::fake()->image('2.png')]);
        Storage::disk('local')->assertMissing($first->file_path);

        $registration->documents()->first()->update(['status' => 'disetujui']);
        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'kk', 'file' => UploadedFile::fake()->image('3.png')])
            ->assertSessionHas('error');
    }

    // ── Ubah data ─────────────────────────────────────────────────────
    public function test_detail_is_locked_after_decision_or_nim(): void
    {
        $target = $this->program();
        foreach (['diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'] as $status) {
            [$user, $registration] = $this->student($status);
            $original = $registration->first_choice_program_id;

            $this->actingAs($user)->post('/pendaftaran/detail/update', [
                'section' => 'program_jalur', 'first_choice_program_id' => $target->id, 'admission_path' => 'umum',
            ])->assertSessionHas('error');
            $this->assertSame($original, $registration->fresh()->first_choice_program_id);
            $this->actingAs($user)->get('/pendaftaran/detail')->assertOk()->assertSee('tidak bisa diedit', false);
        }

        [$user, $registration] = $this->student('menunggu_interview');
        $registration->forceFill(['nim' => '26111111400'])->save();
        $this->actingAs($user)->post('/pendaftaran/detail/update', [
            'section' => 'program_jalur', 'first_choice_program_id' => $target->id, 'admission_path' => 'umum',
        ])->assertSessionHas('error');

        [$user, $registration] = $this->student('menunggu_interview');
        $this->actingAs($user)->post('/pendaftaran/detail/update', [
            'section' => 'program_jalur', 'first_choice_program_id' => $target->id, 'admission_path' => 'umum',
        ])->assertSessionHas('success');
        $this->assertSame($target->id, $registration->fresh()->first_choice_program_id);
    }

    public function test_detail_validation_for_nik_grade_and_inactive_program(): void
    {
        [$user, $registration] = $this->student('terdaftar');
        $diri = ['section' => 'data_diri', 'full_name' => 'N', 'nisn' => '1234567890', 'birth_place' => 'Majalengka', 'birth_date' => '2005-01-01',
            'gender' => 'male', 'religion' => 'Islam', 'address' => 'x', 'phone' => '081234567890'];

        $this->actingAs($user)->post('/pendaftaran/detail/update', $diri + ['nik' => 'abcdefghijklmnop'])->assertSessionHasErrors('nik');
        $this->actingAs($user)->post('/pendaftaran/detail/update', $diri + ['nik' => '3210000000000009'])->assertSessionHas('success');

        $sekolah = ['section' => 'data_sekolah', 'school_name' => 'S', 'school_major' => 'IPA', 'graduation_year' => '2024', 'certificate_number' => 'C1'];
        foreach (['1000000', '100.01', '-1', 'abc'] as $bad) {
            $resp = $this->actingAs($user)->post('/pendaftaran/detail/update', $sekolah + ['school_grade' => $bad]);
            $this->assertLessThan(500, $resp->getStatusCode());
            $resp->assertSessionHasErrors('school_grade');
        }
        $this->actingAs($user)->post('/pendaftaran/detail/update', $sekolah + ['school_grade' => '87.25'])->assertSessionHas('success');

        $inactive = $this->program(['is_active' => false]);
        $this->actingAs($user)->post('/pendaftaran/detail/update', [
            'section' => 'program_jalur', 'first_choice_program_id' => $inactive->id, 'admission_path' => 'umum',
        ])->assertSessionHasErrors('first_choice_program_id');
    }

    public function test_cannot_switch_into_full_program_but_can_keep_own(): void
    {
        $current = $this->program();
        $full = $this->program(['quota' => 1]);
        $this->registration(null, 'terdaftar', ['first_choice_program_id' => $full->id]);

        [$user, $registration] = $this->student('terdaftar', ['first_choice_program_id' => $current->id]);

        $this->actingAs($user)->post('/pendaftaran/detail/update', [
            'section' => 'program_jalur', 'first_choice_program_id' => $full->id, 'admission_path' => 'umum',
        ])->assertSessionHasErrors('first_choice_program_id');
        $this->assertSame($current->id, $registration->fresh()->first_choice_program_id);

        $this->actingAs($user)->post('/pendaftaran/detail/update', [
            'section' => 'program_jalur', 'first_choice_program_id' => $current->id, 'admission_path' => 'prestasi',
        ])->assertSessionHas('success');
    }

    // ── Tes tulis ─────────────────────────────────────────────────────
    private function questions(int $count): void
    {
        foreach (range(1, $count) as $i) {
            ExamQuestion::forceCreate([
                'question_text' => "Soal $i", 'option_a' => 'a', 'option_b' => 'b', 'option_c' => 'c', 'option_d' => 'd',
                'correct_option' => 'a', 'is_active' => true,
            ]);
        }
    }

    public function test_exam_flow_scores_and_advances_to_interview(): void
    {
        $this->questions(4);
        [$user, $registration] = $this->student('menunggu_tes_tulis');

        $this->actingAs($user)->get('/pendaftaran/tes-tulis')->assertOk();
        $answers = ExamAnswer::orderBy('id')->get();
        $this->assertCount(4, $answers);

        // belum semua dijawab: ditolak
        $this->actingAs($user)->postJson('/pendaftaran/tes-tulis/jawab', ['exam_answer_id' => $answers[0]->id, 'selected_option' => 'a'])->assertOk();
        $this->actingAs($user)->post('/pendaftaran/tes-tulis/selesai')->assertSessionHas('error');
        $this->assertSame('menunggu_tes_tulis', $registration->fresh()->status);

        foreach ($answers as $i => $answer) {
            $this->actingAs($user)->postJson('/pendaftaran/tes-tulis/jawab', ['exam_answer_id' => $answer->id, 'selected_option' => $i === 0 ? 'b' : 'a'])->assertOk();
        }
        $this->actingAs($user)->post('/pendaftaran/tes-tulis/selesai')->assertSessionHas('success');

        $session = ExamSession::first();
        $this->assertEquals(75, (float) $session->score);
        $this->assertSame('baik', $session->result_label);
        $this->assertSame('menunggu_interview', $registration->fresh()->status);
        $this->assertSame(1, PaymentLog::where('registration_id', $registration->id)->where('action', 'exam_completed')->count());

        // jawaban tidak bisa diubah setelah selesai
        $this->actingAs($user)->postJson('/pendaftaran/tes-tulis/jawab', ['exam_answer_id' => $answers[0]->id, 'selected_option' => 'c'])->assertForbidden();
    }

    public function test_exam_answer_of_another_student_is_forbidden(): void
    {
        $this->questions(1);
        [$owner] = $this->student('menunggu_tes_tulis');
        [$intruder] = $this->student('menunggu_tes_tulis');

        $this->actingAs($owner)->get('/pendaftaran/tes-tulis');
        $answer = ExamAnswer::first();

        $this->actingAs($intruder)->postJson('/pendaftaran/tes-tulis/jawab', ['exam_answer_id' => $answer->id, 'selected_option' => 'b'])->assertForbidden();
        $this->assertNull($answer->fresh()->selected_option);
    }

    // ── Panitia & keputusan ───────────────────────────────────────────
    public function test_panitia_decision_assigns_letter_number_once_and_cannot_be_repeated(): void
    {
        $panitia = $this->makeUser('panitia');
        [, $registration] = $this->student('menunggu_interview');

        $this->actingAs($panitia)->get("/panitia/scan/{$registration->registration_number}")->assertOk();
        $this->actingAs($panitia)->post("/panitia/scan/{$registration->registration_number}/selesai", ['hasil' => 'diterima']);

        $registration->refresh();
        $this->assertSame('diterima', $registration->status);
        $this->assertNotEmpty($registration->letter_number);

        $number = $registration->letter_number;
        $this->actingAs($panitia)->post("/panitia/scan/{$registration->registration_number}/selesai", ['hasil' => 'ditolak']);
        $this->assertSame('diterima', $registration->fresh()->status);
        $this->assertSame($number, $registration->fresh()->letter_number);
    }

    public function test_panitia_ignores_soft_deleted_registration(): void
    {
        $panitia = $this->makeUser('panitia');
        [, $registration] = $this->student('menunggu_interview');
        $number = $registration->registration_number;
        $registration->delete();

        $this->assertLessThan(500, $this->actingAs($panitia)->get("/panitia/scan/{$number}")->getStatusCode());
        $this->assertLessThan(500, $this->actingAs($panitia)->post("/panitia/scan/{$number}/selesai", ['hasil' => 'diterima'])->getStatusCode());
        $this->assertLessThan(500, $this->actingAs($panitia)->get('/panitia/dashboard?search=a')->getStatusCode());
    }

    // ── Unduhan ───────────────────────────────────────────────────────
    public function test_form_pdf_downloads_with_and_without_registration_number(): void
    {
        $qr = null;
        View::composer('registration.pdf-formulir', function ($view) use (&$qr) {
            $data = $view->getData();
            $qr = array_key_exists('qrCodeBase64', $data) ? $data['qrCodeBase64'] : 'TIDAK-ADA';
        });

        [$early] = $this->student('menunggu_pembayaran', ['registration_number' => null]);
        $resp = $this->actingAs($early)->get('/pendaftaran/cetak');
        $resp->assertOk();
        $this->assertSame('application/pdf', $resp->headers->get('Content-Type'));
        $this->assertNull($qr, 'QR tidak boleh dibuat tanpa nomor pendaftaran');

        [$confirmed] = $this->student('terdaftar');
        $this->actingAs($confirmed)->get('/pendaftaran/cetak')->assertOk();
        $this->assertIsString($qr);
        $this->assertStringStartsWith("\x89PNG", base64_decode($qr));
    }

    public function test_skl_and_ektm_are_gated(): void
    {
        [$user, $registration] = $this->student('menunggu_interview');
        $this->actingAs($user)->get('/pendaftaran/surat-kelulusan')->assertRedirect(route('registration.status'));
        $this->actingAs($user)->get('/pendaftaran/e-ktm/download')->assertRedirect();

        $registration->forceFill(['status' => 'diterima', 'letter_number' => '001/UNIV-YPIB/SKL/I/2026'])->save();
        $resp = $this->actingAs($user)->get('/pendaftaran/surat-kelulusan');
        $resp->assertOk();
        $this->assertSame('application/pdf', $resp->headers->get('Content-Type'));
    }

    public function test_ektm_and_pdf_work_with_private_photo(): void
    {
        [$user, $registration] = $this->student('daftar_ulang_selesai');
        $registration->forceFill(['nim' => '26111111401'])->save();

        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', ['document_type' => 'foto', 'file' => UploadedFile::fake()->image('f.png', 300, 400)])
            ->assertSessionHas('success');
        $this->assertSame(1, RegistrationDocument::where('registration_id', $registration->id)->count());

        $this->actingAs($user)->get('/pendaftaran/cetak')->assertOk();
        $ktm = $this->actingAs($user)->get('/pendaftaran/e-ktm/download');
        $this->assertLessThan(500, $ktm->getStatusCode());
        $this->assertFalse($ktm->isRedirect(), 'e-KTM tidak menemukan foto pada disk privat');
    }

    public function test_soft_deleted_registrant_sees_no_server_errors(): void
    {
        [$user, $registration] = $this->student('terdaftar');
        $registration->delete();

        foreach (['/pendaftaran/status', '/pendaftaran/detail', '/pendaftaran/dokumen', '/pendaftaran/tes-tulis',
                  '/pendaftaran/cetak', '/pendaftaran/interview', '/pendaftaran/daftar-ulang'] as $url) {
            $this->assertLessThan(500, $this->actingAs($user)->get($url)->getStatusCode(), $url);
        }
    }
}
