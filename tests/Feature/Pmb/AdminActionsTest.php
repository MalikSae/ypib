<?php

namespace Tests\Feature\Pmb;

use App\Models\ExamSession;
use App\Models\PaymentLog;
use App\Models\Registration;
use App\Models\RegistrationDocument;
use App\Models\Reward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class AdminActionsTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    // ── Konfirmasi pembayaran ─────────────────────────────────────────
    public function test_confirm_payment_is_idempotent_and_creates_one_reward(): void
    {
        $admin = $this->makeUser('admin');
        $referrer = $this->referrer();
        [, $registration] = $this->student('menunggu_konfirmasi', [
            'registration_number' => null, 'payment_proof' => 'bukti-bayar/x.png', 'referrer_id' => $referrer->id,
        ]);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar")->assertSessionHas('success');
        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar")->assertSessionHas('error');

        $registration->refresh();
        $this->assertSame('terdaftar', $registration->status);
        $this->assertNotNull($registration->registration_number);
        $this->assertSame(1, (int) $referrer->fresh()->total_conversions);
        $this->assertSame(1, Reward::where('registration_id', $registration->id)->count());
        $this->assertSame(1, PaymentLog::where('registration_id', $registration->id)->where('action', 'payment_confirmed')->count());
    }

    public function test_confirm_payment_never_regresses_advanced_status(): void
    {
        $admin = $this->makeUser('admin');

        foreach (['terdaftar', 'menunggu_tes_tulis', 'menunggu_interview', 'diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'] as $status) {
            [, $registration] = $this->student($status, ['payment_proof' => 'x.png']);

            $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar")->assertSessionHas('error');
            $this->assertSame($status, $registration->fresh()->status);
        }
    }

    public function test_alumni_can_be_confirmed_manually_with_a_note(): void
    {
        $admin = $this->makeUser('admin');
        [, $registration] = $this->student('menunggu_pembayaran', ['registration_type' => 'alumni', 'registration_number' => null]);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar", ['note' => 'verifikasi manual'])
            ->assertSessionHas('success');
        $this->assertSame('terdaftar', $registration->fresh()->status);
    }

    // ── Daftar ulang & NIM ────────────────────────────────────────────
    public function test_re_registration_needs_kode_prodi_then_issues_nim(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['kode_prodi' => null]);
        [, $registration] = $this->student('menunggu_konfirmasi_daftar_ulang', [
            'first_choice_program_id' => $program->id, 're_registration_payment_proof' => 'x.png',
        ]);
        $url = "/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang";

        $this->actingAs($admin)->post($url)->assertSessionHas('error');
        $registration->refresh();
        $this->assertSame('menunggu_konfirmasi_daftar_ulang', $registration->status);
        $this->assertNull($registration->nim);
        $this->assertSame(0, PaymentLog::where('registration_id', $registration->id)->where('action', 're_registration_confirmed')->count());

        $program->update(['kode_prodi' => '']);
        $this->actingAs($admin)->post($url)->assertSessionHas('error');

        $program->update(['kode_prodi' => '12345']);
        $this->actingAs($admin)->post($url)->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame('daftar_ulang_selesai', $registration->status);
        $this->assertStringStartsWith(substr((string) $this->period()->year, -2) . '12345' . '1', $registration->nim);
    }

    public function test_re_registration_keeps_existing_nim(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['kode_prodi' => null]);
        [, $registration] = $this->student('menunggu_konfirmasi_daftar_ulang', [
            'first_choice_program_id' => $program->id, 're_registration_payment_proof' => 'x.png',
        ]);
        $registration->forceFill(['nim' => '26999991400'])->save();

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang")->assertSessionHas('success');
        $this->assertSame('26999991400', $registration->fresh()->nim);
    }

    // ── Reset tes tulis ───────────────────────────────────────────────
    public function test_exam_reset_only_before_interview_decision(): void
    {
        $admin = $this->makeUser('admin');

        foreach (['diterima', 'ditolak', 'menunggu_konfirmasi_daftar_ulang', 'daftar_ulang_selesai'] as $status) {
            [, $registration] = $this->student($status);
            ExamSession::forceCreate(['registration_id' => $registration->id, 'status' => 'completed', 'score' => 90, 'result_label' => 'sangat_baik', 'started_at' => now(), 'completed_at' => now()]);

            $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/reset-tes-tulis")->assertSessionHas('error');
            $this->assertSame($status, $registration->fresh()->status);
            $this->assertSame(1, ExamSession::where('registration_id', $registration->id)->count());
        }

        [, $registration] = $this->student('menunggu_interview');
        ExamSession::forceCreate(['registration_id' => $registration->id, 'status' => 'completed', 'score' => 70, 'result_label' => 'baik', 'started_at' => now(), 'completed_at' => now()]);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/reset-tes-tulis")->assertSessionHas('success');
        $this->assertSame('menunggu_tes_tulis', $registration->fresh()->status);
        $this->assertSame(0, ExamSession::where('registration_id', $registration->id)->count());
    }

    // ── Afiliator pada pendaftar ──────────────────────────────────────
    public function test_referral_change_blocked_when_commission_was_disbursed(): void
    {
        $admin = $this->makeUser('admin');
        $old = $this->referrer(['total_conversions' => 1]);
        $new = $this->referrer();
        [, $registration] = $this->student('terdaftar', ['referrer_id' => $old->id]);
        $paid = $this->reward($old, $registration, 'disbursed');

        foreach ([['referrer_id' => ''], ['referrer_id' => $new->id]] as $payload) {
            $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/referral", $payload)->assertSessionHas('error');
            $this->assertSame($old->id, $registration->fresh()->referrer_id);
            $this->assertSame($old->id, $paid->fresh()->referrer_id);
            $this->assertSame('disbursed', $paid->fresh()->status);
            $this->assertSame(1, (int) $old->fresh()->total_conversions);
        }
    }

    public function test_referral_removal_cancels_open_rewards_and_counter_never_goes_negative(): void
    {
        $admin = $this->makeUser('admin');
        $old = $this->referrer(['total_conversions' => 0]);
        [, $registration] = $this->student('terdaftar', ['referrer_id' => $old->id]);
        $approved = $this->reward($old, $registration, 'approved');
        $pending = $this->reward($old, $registration, 'pending', 're_registration');

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/referral", ['referrer_id' => ''])->assertSessionHas('success');

        $this->assertNull($registration->fresh()->referrer_id);
        foreach ([$approved, $pending] as $reward) {
            $this->assertSame('cancelled', $reward->fresh()->status);
            $this->assertStringContainsString('Dibatalkan otomatis', (string) $reward->fresh()->notes);
        }
        $this->assertSame(0, (int) $old->fresh()->total_conversions);
    }

    public function test_referral_change_moves_open_rewards_and_updates_both_counters(): void
    {
        $admin = $this->makeUser('admin');
        $old = $this->referrer(['total_conversions' => 1]);
        $new = $this->referrer(['total_conversions' => 0]);
        [, $registration] = $this->student('terdaftar', ['referrer_id' => $old->id]);
        $reward = $this->reward($old, $registration, 'approved');
        $cancelled = $this->reward($old, $registration, 'cancelled', 're_registration');

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/referral", ['referrer_id' => $new->id])->assertSessionHas('success');

        $this->assertSame($new->id, $reward->fresh()->referrer_id);
        $this->assertSame($old->id, $cancelled->fresh()->referrer_id);
        $this->assertSame(0, (int) $old->fresh()->total_conversions);
        $this->assertSame(1, (int) $new->fresh()->total_conversions);
    }

    // ── Catatan internal ──────────────────────────────────────────────
    public function test_internal_note_edit_uses_optimistic_lock_and_keeps_audit(): void
    {
        $admin = $this->makeUser('admin');
        $original = "[Interview] Diproses oleh Panitia A\nCatatan: bagus";
        [, $registration] = $this->student('diterima', ['internal_notes' => $original]);
        $url = "/admin/pendaftar/{$registration->id}/catatan";

        $html = $this->actingAs($admin)->get("/admin/pendaftar/{$registration->id}")->assertOk()->getContent();
        $this->assertStringContainsString('name="notes_checksum" value="' . md5($original) . '"', $html);

        $edited = $original . "\n\nTambahan admin";
        $this->actingAs($admin)->post($url, ['note' => $edited, 'notes_checksum' => md5($original)])->assertSessionHas('success');
        $this->assertSame($edited, $registration->fresh()->internal_notes);

        $log = PaymentLog::where('registration_id', $registration->id)->where('action', 'internal_note_edited')->first();
        $this->assertStringContainsString('[Interview] Diproses oleh Panitia A', $log->note);

        // checksum basi: ditolak, isi utuh
        $this->actingAs($admin)->post($url, ['note' => 'Timpa semuanya', 'notes_checksum' => md5($original)])->assertSessionHas('error');
        $this->assertSame($edited, $registration->fresh()->internal_notes);

        // tanpa perubahan
        $this->actingAs($admin)->post($url, ['note' => $edited, 'notes_checksum' => md5($edited)])->assertSessionHas('info');

        // catatan panjang hasil akumulasi tetap bisa disimpan
        $long = rtrim(str_repeat('Catatan panjang. ', 300));
        $this->actingAs($admin)->post($url, ['note' => $long, 'notes_checksum' => md5($edited)])->assertSessionHas('success');
        $this->assertSame($long, $registration->fresh()->internal_notes);
    }

    // ── Review dokumen ────────────────────────────────────────────────
    public function test_document_review_progression_and_isolation(): void
    {
        $admin = $this->makeUser('admin');
        [, $registration] = $this->student('terdaftar');
        [, $other] = $this->student('terdaftar');

        $docs = [];
        foreach (['foto', 'kk', 'akta_lahir'] as $type) {
            $docs[$type] = RegistrationDocument::forceCreate(['registration_id' => $registration->id, 'document_type' => $type, 'file_path' => "d/$type.png", 'status' => 'menunggu_review']);
        }
        $foreign = RegistrationDocument::forceCreate(['registration_id' => $other->id, 'document_type' => 'foto', 'file_path' => 'd/o.png', 'status' => 'menunggu_review']);

        $this->actingAs($admin)->post("/admin/pendaftar/document/{$docs['foto']->id}/review", ['status' => 'perlu_revisi'])
            ->assertSessionHasErrors('review_note');

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/documents/bulk-review", [
            'document_ids' => [$docs['foto']->id, $docs['kk']->id, $foreign->id], 'bulk_status' => 'disetujui',
        ])->assertSessionHas('success');
        $this->assertSame('menunggu_review', $foreign->fresh()->status, 'dokumen pendaftar lain ikut direview');
        $this->assertSame('terdaftar', $registration->fresh()->status);

        $this->actingAs($admin)->post("/admin/pendaftar/document/{$docs['akta_lahir']->id}/review", ['status' => 'disetujui'])->assertSessionHas('success');
        $this->assertSame('menunggu_tes_tulis', $registration->fresh()->status);
        $this->assertSame(1, PaymentLog::where('registration_id', $registration->id)->where('action', 'documents_approved')->count());
    }

    // ── Edit data & hapus/pulihkan ────────────────────────────────────
    public function test_admin_update_data_rules_and_audit(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeUser('mahasiswa', ['email' => 'dipakai@uji.test']);
        [$owner, $registration] = $this->student('terdaftar');

        $payload = [
            'full_name' => 'Nama Baru', 'nik' => '3210000000000002', 'birth_place' => 'Majalengka', 'birth_date' => '2005-02-02',
            'gender' => 'female', 'phone' => '0811', 'address' => 'Alamat Baru', 'school_name' => 'SMA B', 'graduation_year' => '2023',
            'school_grade' => '85.5', 'admission_path' => 'prestasi', 'email' => 'baru@uji.test',
            'first_choice_program_id' => $registration->first_choice_program_id,
        ];
        $url = "/admin/pendaftar/{$registration->id}/update-data";

        $this->actingAs($admin)->post($url, array_merge($payload, ['nik' => 'abc']))->assertSessionHasErrors('nik');
        $this->actingAs($admin)->post($url, array_merge($payload, ['email' => 'dipakai@uji.test']))->assertSessionHasErrors('email');
        $this->actingAs($admin)->post($url, array_merge($payload, ['school_grade' => '150']))->assertSessionHasErrors('school_grade');

        $this->actingAs($admin)->post($url, $payload)->assertSessionHas('success');
        $this->assertSame('Nama Baru', $registration->fresh()->full_name);
        $this->assertSame('baru@uji.test', $owner->fresh()->email);
        $this->assertSame(1, PaymentLog::where('registration_id', $registration->id)->where('action', 'data_edited')->count());

        $this->actingAs($admin)->post($url, $payload)->assertSessionHas('info');

        // field opsional boleh tidak dikirim
        unset($payload['school_grade']);
        $resp = $this->actingAs($admin)->post($url, array_merge($payload, ['full_name' => 'Nama Lagi']));
        $this->assertLessThan(500, $resp->getStatusCode());
        $this->assertSame('Nama Lagi', $registration->fresh()->full_name);
    }

    public function test_program_change_is_blocked_once_nim_exists(): void
    {
        $admin = $this->makeUser('admin');
        $other = $this->program();
        [, $registration] = $this->student('daftar_ulang_selesai');
        $registration->forceFill(['nim' => '26111111402'])->save();
        $original = $registration->first_choice_program_id;

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/update-data", [
            'full_name' => $registration->full_name, 'nik' => $registration->nik, 'birth_place' => 'Majalengka', 'birth_date' => '2005-01-01',
            'gender' => 'male', 'phone' => '0811', 'address' => 'x', 'school_name' => 'S', 'graduation_year' => '2024',
            'admission_path' => 'umum', 'email' => $registration->user->email, 'first_choice_program_id' => $other->id,
        ])->assertSessionHas('error');

        $this->assertSame($original, $registration->fresh()->first_choice_program_id);
    }

    public function test_destroy_and_restore_keep_commission_accounting(): void
    {
        $admin = $this->makeUser('admin');
        $referrer = $this->referrer(['total_conversions' => 1]);
        [, $registration] = $this->student('terdaftar', ['referrer_id' => $referrer->id]);
        $reward = $this->reward($referrer, $registration, 'approved');

        $this->actingAs($admin)->delete("/admin/pendaftar/{$registration->id}")->assertSessionHas('success');
        $this->assertSame('cancelled', $reward->fresh()->status);
        $this->assertSame(0, (int) $referrer->fresh()->total_conversions);
        $this->assertSoftDeleted('registrations', ['id' => $registration->id]);

        $this->actingAs($admin)->get('/admin/pendaftar/sampah')->assertOk();
        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/restore")->assertSessionHas('success');
        $this->assertNotSoftDeleted('registrations', ['id' => $registration->id]);

        $this->actingAs($admin)->delete("/admin/pendaftar/{$registration->id}");
        $this->assertLessThan(500, $this->actingAs($admin)->delete("/admin/pendaftar/{$registration->id}")->getStatusCode());
    }

    public function test_admin_pages_render_with_data(): void
    {
        $admin = $this->makeUser('admin');
        [, $registration] = $this->student('terdaftar');

        foreach (['/admin/dashboard', '/admin/pendaftar', '/admin/pendaftar/rekap', '/admin/pendaftar/sampah', "/admin/pendaftar/{$registration->id}",
                  '/admin/afiliasi', '/admin/reward', '/admin/programs', '/admin/programs/create', '/admin/faculties', '/admin/partners',
                  '/admin/facilities', '/admin/bank-soal', '/admin/panitia', '/admin/pengaturan', '/admin/pengaturan-email'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
