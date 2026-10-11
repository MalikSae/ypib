<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\PmbPeriod;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T3 (PLAN_MULTI_PERIODE.md): satu akun, beberapa periode; resolver pendaftaran,
 * pintu masuk /daftar, tombol "daftar lagi", dan banner landing.
 */
class MultiPeriodRegistrationTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** Periode tahun lalu: sudah tutup, masih tayang. */
    private function lastYearPeriod(string $status = 'published'): PmbPeriod
    {
        return PmbPeriod::forceCreate([
            'name'                         => 'PMB Tahun Lalu',
            'year'                         => (int) now()->subYear()->year,
            'open_date'                    => now()->subYear()->toDateString(),
            'close_date'                   => now()->subYear()->addMonths(2)->toDateString(),
            're_registration_deadline'     => now()->subMonths(6)->toDateString(),
            'university_bank_name'         => 'BRI',
            'university_bank_account'      => '1234567890',
            'university_bank_account_name' => 'YPIB',
            'admin_whatsapp'               => '081234567890',
            'status'                       => $status,
        ]);
    }

    private function fillForm($component, int $programId, string $nik = '3210000000000001')
    {
        $data = [
            'full_name' => 'Calon Uji', 'nisn' => '1234567890', 'nik' => $nik, 'birth_place' => 'Majalengka',
            'birth_date' => '2005-01-01', 'gender' => 'male', 'religion' => 'Islam', 'address' => 'Jl. Uji', 'phone' => '081234567890',
            'first_choice_program_id' => (string) $programId, 'admission_path' => 'umum',
            'school_name' => 'SMA Uji', 'school_major' => 'IPA', 'graduation_year' => '2024', 'certificate_number' => 'IJ-1',
            'father_name' => 'Ayah', 'mother_name' => 'Ibu', 'father_occupation' => 'Petani', 'mother_occupation' => 'IRT',
        ];

        foreach ($data as $field => $value) {
            $component->set($field, $value);
        }

        return $component;
    }

    // ── Daftar lagi (K1) ─────────────────────────────────────────────────

    public function test_an_account_from_last_year_can_register_again_in_the_current_period(): void
    {
        $current = $this->period();
        $old = $this->lastYearPeriod();
        $program = $this->program();
        $user = $this->makeUser();
        $this->registration($user, 'ditolak', ['period_id' => $old->id, 'first_choice_program_id' => $program->id]);

        $this->actingAs($user)->get('/daftar?prodi=' . $program->id)->assertOk();

        $this->fillForm(Livewire::actingAs($user)->test(RegistrationForm::class), $program->id)
            ->call('submit')->assertHasNoErrors();

        $this->assertSame(2, Registration::where('user_id', $user->id)->count());
        $this->assertTrue(Registration::where('user_id', $user->id)->where('period_id', $current->id)->exists());
    }

    public function test_an_account_already_registered_in_the_current_period_is_sent_to_status(): void
    {
        $current = $this->period();
        $program = $this->program();
        [$user] = $this->student('menunggu_pembayaran', ['period_id' => $current->id, 'first_choice_program_id' => $program->id]);

        $this->actingAs($user)->get('/daftar?prodi=' . $program->id)->assertRedirect(route('registration.status'));
    }

    public function test_register_page_when_no_period_is_open(): void
    {
        $old = $this->lastYearPeriod();
        $program = $this->program();

        // Tamu: kembali ke landing dengan pesan "sudah ditutup".
        $this->get('/daftar?prodi=' . $program->id)
            ->assertRedirect(route('landing'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'sudah ditutup'));

        // Pendaftar lama tetap diarahkan ke status.
        [$user] = $this->student('terdaftar', ['period_id' => $old->id, 'first_choice_program_id' => $program->id]);
        $this->actingAs($user)->get('/daftar?prodi=' . $program->id)->assertRedirect(route('registration.status'));
    }

    // ── Resolver pendaftaran ─────────────────────────────────────────────

    public function test_status_prefers_the_current_period_and_period_query_selects_history(): void
    {
        $current = $this->period();
        $old = $this->lastYearPeriod();
        $oldProgram = $this->program(['name' => 'Prodi Tahun Lalu']);
        $newProgram = $this->program(['name' => 'Prodi Tahun Ini']);
        $user = $this->makeUser();

        $this->registration($user, 'ditolak', ['period_id' => $old->id, 'first_choice_program_id' => $oldProgram->id, 'full_name' => 'Nama Tahun Lalu']);
        $this->registration($user, 'menunggu_pembayaran', ['period_id' => $current->id, 'first_choice_program_id' => $newProgram->id, 'full_name' => 'Nama Tahun Ini']);

        // Halaman status juga memuat daftar riwayat (kedua prodi), jadi pendaftaran yang dilihat
        // dibedakan lewat halaman data (detail) yang mengikuti pilihan.
        $this->actingAs($user)->get('/pendaftaran/status')->assertOk();
        $this->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Ini')->assertDontSee('Nama Tahun Lalu');

        $this->get('/pendaftaran/status?periode=' . $old->id)->assertOk();
        $this->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Lalu')->assertDontSee('Nama Tahun Ini');
    }

    public function test_status_without_a_current_period_shows_the_latest_registration(): void
    {
        $old = $this->lastYearPeriod();
        $program = $this->program(['name' => 'Prodi Satu-satunya']);
        [$user] = $this->student('menunggu_tes_tulis', ['period_id' => $old->id, 'first_choice_program_id' => $program->id]);

        $this->actingAs($user)->get('/pendaftaran/status')->assertOk()->assertSee('Prodi Satu-satunya');
    }

    public function test_period_query_never_exposes_another_accounts_registration(): void
    {
        $current = $this->period();
        $program = $this->program(['name' => 'Prodi Rahasia Orang Lain']);
        $this->student('terdaftar', ['period_id' => $current->id, 'first_choice_program_id' => $program->id]);

        $stranger = $this->makeUser();
        $this->actingAs($stranger)->get('/pendaftaran/status?periode=' . $current->id)
            ->assertOk()->assertDontSee('Prodi Rahasia Orang Lain')->assertSee('Belum Ada Pendaftaran');
    }

    public function test_status_page_names_the_registrations_own_period(): void
    {
        $this->period();
        $old = $this->lastYearPeriod();
        [$user] = $this->student('terdaftar', ['period_id' => $old->id]);

        $this->actingAs($user)->get('/pendaftaran/status?periode=' . $old->id)->assertOk()->assertSee('PMB Tahun Lalu');
    }

    // ── Tombol "Daftar di periode berjalan" ──────────────────────────────

    public function test_reapply_offer_points_to_the_same_program_when_it_is_offered(): void
    {
        $this->period();
        $old = $this->lastYearPeriod();
        $program = $this->program();
        [$user] = $this->student('ditolak', ['period_id' => $old->id, 'first_choice_program_id' => $program->id]);

        $this->actingAs($user)->get('/pendaftaran/status')
            ->assertOk()
            ->assertSee('Daftar di Periode Ini')
            ->assertSee('/daftar?prodi=' . $program->id, false);
    }

    public function test_reapply_offer_falls_back_to_the_program_list_when_not_offered(): void
    {
        $current = $this->period();
        $old = $this->lastYearPeriod();
        $program = $this->program();
        \App\Models\PeriodProgram::forceCreate([
            'period_id' => $current->id, 'program_id' => $program->id, 'is_offered' => false,
        ]);
        [$user] = $this->student('ditolak', ['period_id' => $old->id, 'first_choice_program_id' => $program->id]);

        $response = $this->actingAs($user)->get('/pendaftaran/status')->assertOk()->assertSee('Daftar di Periode Ini');
        $response->assertDontSee('/daftar?prodi=' . $program->id, false);
        $response->assertSee('#program-studi', false);
    }

    public function test_reapply_offer_is_hidden_when_already_registered_or_no_period_is_open(): void
    {
        $current = $this->period();
        $old = $this->lastYearPeriod();
        $program = $this->program();
        $user = $this->makeUser();
        $this->registration($user, 'ditolak', ['period_id' => $old->id, 'first_choice_program_id' => $program->id]);
        $this->registration($user, 'menunggu_pembayaran', ['period_id' => $current->id, 'first_choice_program_id' => $program->id]);

        $this->actingAs($user)->get('/pendaftaran/status?periode=' . $old->id)->assertOk()->assertDontSee('Daftar di Periode Ini');

        // Tanpa periode berjalan tidak ada tawaran.
        $current->update(['status' => 'archived']);
        $this->actingAs($user)->get('/pendaftaran/status?periode=' . $old->id)->assertOk()->assertDontSee('Daftar di Periode Ini');
    }

    public function test_reapply_offer_is_hidden_when_the_nik_block_applies(): void
    {
        config(['pmb.enforce_nik_block' => true]);
        $this->period();
        $old = $this->lastYearPeriod();
        $program = $this->program();
        [$user] = $this->student('daftar_ulang_selesai', [
            'period_id' => $old->id, 'first_choice_program_id' => $program->id, 'nim' => '26123451001',
        ]);

        $this->actingAs($user)->get('/pendaftaran/status')->assertOk()->assertDontSee('Daftar di Periode Ini');

        config(['pmb.enforce_nik_block' => false]);
        $this->actingAs($user)->get('/pendaftaran/status')->assertOk()->assertSee('Daftar di Periode Ini');
    }

    // ── Banner landing ───────────────────────────────────────────────────

    public function test_landing_banner_follows_the_period_phase(): void
    {
        $period = PmbPeriod::forceCreate([
            'name' => 'PMB Banner', 'year' => 2026, 'status' => 'published',
            'open_date' => '2026-03-01', 'close_date' => '2026-03-31',
        ]);
        $this->program();

        Carbon::setTestNow(Carbon::parse('2026-03-10 10:00:00', 'Asia/Jakarta'));
        $this->get('/')->assertOk()->assertSee('Pendaftaran dibuka hingga')->assertSee('31 Maret 2026');

        Carbon::setTestNow(Carbon::parse('2026-02-10 10:00:00', 'Asia/Jakarta'));
        $this->get('/')->assertOk()->assertSee('Pendaftaran dibuka pada')->assertSee('01 Maret 2026');

        Carbon::setTestNow(Carbon::parse('2026-04-10 10:00:00', 'Asia/Jakarta'));
        $this->get('/')->assertOk()->assertSee('Pendaftaran ditutup pada')->assertDontSee('Pendaftaran dibuka hingga');

        $period->update(['status' => 'archived']);
        $this->get('/')->assertOk()->assertSee('Pendaftaran belum dibuka');
    }

    public function test_program_page_disables_register_button_when_closed(): void
    {
        $program = $this->program(['slug' => 'prodi-uji-cta']);
        $period = $this->period();

        $this->get('/prodi/prodi-uji-cta')->assertOk()->assertSee('Daftar ke Prodi Ini');

        $period->update(['close_date' => now()->subDay()->toDateString(), 'open_date' => now()->subDays(10)->toDateString()]);

        $this->get('/prodi/prodi-uji-cta')->assertOk()
            ->assertDontSee('Daftar ke Prodi Ini')
            ->assertSee('Pendaftaran ditutup pada');
    }
}
