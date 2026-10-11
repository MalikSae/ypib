<?php

namespace Tests\Feature\Pmb;

use App\Models\PmbPeriod;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T4 (PLAN_MULTI_PERIODE.md 5.3, K2-K5): gerbang jendela pendaftaran.
 * Periode uji: pendaftaran 1-31 Maret 2026, masa bayar +3 hari, daftar ulang sampai 30 Juni 2026.
 */
class RegistrationWindowGateTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function window(array $extra = []): PmbPeriod
    {
        return PmbPeriod::forceCreate(array_merge([
            'name'                         => 'PMB Gerbang',
            'year'                         => 2026,
            'open_date'                    => '2026-03-01',
            'close_date'                   => '2026-03-31',
            're_registration_deadline'     => '2026-06-30',
            'payment_grace_days'           => 3,
            'university_bank_name'         => 'BRI',
            'university_bank_account'      => '1234567890',
            'university_bank_account_name' => 'YPIB',
            'admin_whatsapp'               => '081234567890',
            'status'                       => 'published',
        ], $extra));
    }

    private function now(string $when): void
    {
        Carbon::setTestNow(Carbon::parse($when, 'Asia/Jakarta'));
    }

    private function enforce(bool $on = true): void
    {
        config(['pmb.enforce_window' => $on]);
    }

    private function upload(Registration $registration, string $url, string $field)
    {
        return $this->actingAs($registration->user)->post($url, [$field => UploadedFile::fake()->image('b.png')]);
    }

    // ── Bukti bayar pertama ──────────────────────────────────────────────

    public function test_first_proof_is_accepted_through_the_last_grace_day_and_refused_after(): void
    {
        $this->enforce();
        $period = $this->window();
        [, $inTime] = $this->student('menunggu_pembayaran', ['period_id' => $period->id]);
        [, $late] = $this->student('menunggu_pembayaran', ['period_id' => $period->id]);

        $this->now('2026-04-03 23:59:00');
        $this->upload($inTime, '/pendaftaran/upload-bukti', 'payment_proof')->assertSessionHas('success');
        $this->assertSame('menunggu_konfirmasi', $inTime->fresh()->status);

        $this->now('2026-04-04 00:00:00');
        $this->upload($late, '/pendaftaran/upload-bukti', 'payment_proof')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Masa pembayaran') && str_contains($m, '081234567890'));

        $late->refresh();
        $this->assertSame('menunggu_pembayaran', $late->status);
        $this->assertNull($late->payment_proof);
    }

    public function test_alumni_card_follows_the_same_gate(): void
    {
        $this->enforce();
        $period = $this->window();
        [, $registration] = $this->student('menunggu_pembayaran', ['period_id' => $period->id]);

        $this->now('2026-04-10 10:00:00');
        $this->upload($registration, '/pendaftaran/upload-kartu-alumni', 'alumni_card_proof')->assertSessionHas('error');

        $this->assertSame('menunggu_pembayaran', $registration->fresh()->status);
    }

    public function test_replacing_a_proof_already_under_review_is_not_date_limited(): void
    {
        $this->enforce();
        $period = $this->window();
        [, $registration] = $this->student('menunggu_konfirmasi', ['period_id' => $period->id, 'payment_proof' => 'bukti-bayar/lama.jpg']);

        $this->now('2026-05-20 10:00:00');
        $this->upload($registration, '/pendaftaran/upload-bukti', 'payment_proof')->assertSessionHas('success');
    }

    public function test_the_switch_off_changes_nothing_but_the_notice_still_shows(): void
    {
        $this->enforce(false);
        $period = $this->window();
        [$user, $registration] = $this->student('menunggu_pembayaran', ['period_id' => $period->id]);

        $this->now('2026-05-20 10:00:00');
        $this->upload($registration, '/pendaftaran/upload-bukti', 'payment_proof')->assertSessionHas('success');

        [$other] = $this->student('menunggu_pembayaran', ['period_id' => $period->id]);
        $this->actingAs($other)->get('/pendaftaran/status')
            ->assertOk()->assertSee('Batas pengunggahan bukti pembayaran: 03 April 2026');
    }

    // ── Bukti daftar ulang ───────────────────────────────────────────────

    public function test_re_registration_proof_follows_the_deadline(): void
    {
        $this->enforce();
        $period = $this->window();
        [, $inTime] = $this->student('diterima', ['period_id' => $period->id]);
        [, $late] = $this->student('diterima', ['period_id' => $period->id]);

        $this->now('2026-06-30 23:00:00');
        $this->upload($inTime, '/pendaftaran/upload-daftar-ulang-bukti', 're_registration_payment_proof')->assertSessionHas('success');
        $this->assertSame('menunggu_konfirmasi_daftar_ulang', $inTime->fresh()->status);

        $this->now('2026-07-01 00:00:00');
        $this->upload($late, '/pendaftaran/upload-daftar-ulang-bukti', 're_registration_payment_proof')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Tenggat daftar ulang'));
        $this->assertSame('diterima', $late->fresh()->status);
    }

    public function test_re_registration_without_a_deadline_is_not_date_limited(): void
    {
        $this->enforce();
        $period = $this->window(['re_registration_deadline' => null]);
        [, $registration] = $this->student('diterima', ['period_id' => $period->id]);

        $this->now('2027-01-15 10:00:00');
        $this->upload($registration, '/pendaftaran/upload-daftar-ulang-bukti', 're_registration_payment_proof')->assertSessionHas('success');
    }

    // ── Periode arsip ────────────────────────────────────────────────────

    public function test_archived_period_refuses_mutating_actions_but_keeps_downloads(): void
    {
        $this->enforce();
        $period = $this->window(['status' => 'archived']);
        [$user, $registration] = $this->student('terdaftar', ['period_id' => $period->id, 'registration_number' => 'PMB-2026-1234']);

        // Unggah dokumen
        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', [
            'document_type' => 'kk', 'file' => UploadedFile::fake()->image('kk.png'),
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'diarsipkan'));
        $this->assertSame(0, $registration->documents()->count());

        // Ubah data
        $this->actingAs($user)->post('/pendaftaran/detail/update', [
            'section' => 'data_sekolah', 'school_name' => 'SMA Baru', 'school_major' => 'IPA',
            'graduation_year' => '2024', 'certificate_number' => 'IJ-9',
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'diarsipkan'));
        $this->assertSame('SMA Uji', $registration->fresh()->school_name);

        // Halaman status dan unduhan tetap bisa.
        $this->actingAs($user)->get('/pendaftaran/status')
            ->assertOk()->assertSee('diarsipkan');
        $this->actingAs($user)->get('/pendaftaran/cetak')->assertOk();
    }

    public function test_archived_period_refuses_exam_start(): void
    {
        $this->enforce();
        $period = $this->window(['status' => 'archived']);
        [$user] = $this->student('menunggu_tes_tulis', ['period_id' => $period->id]);

        $this->actingAs($user)->get('/pendaftaran/tes-tulis')
            ->assertRedirect(route('registration.status'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'diarsipkan'));
    }

    public function test_archived_period_with_the_switch_off_is_not_blocked(): void
    {
        $this->enforce(false);
        $period = $this->window(['status' => 'archived']);
        [$user] = $this->student('menunggu_tes_tulis', ['period_id' => $period->id]);

        $response = $this->actingAs($user)->get('/pendaftaran/tes-tulis');

        $this->assertStringNotContainsString('diarsipkan', (string) session('error'));
        $response->assertStatus(302); // bank soal kosong pada test: tidak terkait gerbang
    }

    // ── K3 dan K5 ────────────────────────────────────────────────────────

    public function test_registrant_confirmed_by_staff_after_close_can_continue_without_proof(): void
    {
        $this->enforce();
        $period = $this->window();
        [$user, $registration] = $this->student('terdaftar', [
            'period_id' => $period->id, 'payment_proof' => null, 'alumni_card_proof' => null,
        ]);

        $this->now('2026-05-10 10:00:00');
        $this->actingAs($user)->post('/pendaftaran/dokumen/upload', [
            'document_type' => 'kk', 'file' => UploadedFile::fake()->image('kk.png'),
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $registration->documents()->count());
    }

    public function test_staff_actions_are_never_gated(): void
    {
        $this->enforce();
        $period = $this->window(['status' => 'archived']);
        $admin = $this->makeUser('admin');
        [, $registration] = $this->student('menunggu_konfirmasi', ['period_id' => $period->id, 'payment_proof' => 'bukti-bayar/uji.jpg']);

        $this->now('2027-02-01 10:00:00');
        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar", ['note' => 'uji'])
            ->assertSessionHas('success');

        $this->assertSame('terdaftar', $registration->fresh()->status);
    }

    // ── Pemberitahuan ────────────────────────────────────────────────────

    public function test_notice_shows_the_re_registration_deadline_for_accepted_registrants(): void
    {
        $period = $this->window();
        [$user] = $this->student('diterima', ['period_id' => $period->id]);

        $this->now('2026-05-10 10:00:00');
        $this->actingAs($user)->get('/pendaftaran/status')->assertOk()->assertSee('Tenggat daftar ulang: 30 Juni 2026');
    }
}
