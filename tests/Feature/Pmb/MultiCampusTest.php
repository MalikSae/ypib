<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\Campus;
use App\Models\PeriodProgram;
use App\Models\Program;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-kampus: akses staf per kampus, prodi/kuota/biaya per kampus, kontak kampus, landing.
 * Kampus bawaan (Majalengka) dan Cirebon dibuat oleh migrasi.
 */
class MultiCampusTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function mjl(): Campus
    {
        return Campus::where('code', 'MJL')->firstOrFail();
    }

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    /** Tawarkan prodi di Cirebon pada periode dengan kuota dan biaya sendiri. */
    private function offerInCirebon(Program $program, int $quota = 0, int $fee = 350000): PeriodProgram
    {
        return PeriodProgram::create([
            'period_id'        => $this->period()->id,
            'program_id'       => $program->id,
            'campus_id'        => $this->crb()->id,
            'is_offered'       => true,
            'quota'            => $quota,
            'registration_fee' => $fee,
        ]);
    }

    private function fillForm($component, int $campusId, int $programId)
    {
        $data = [
            'full_name' => 'Calon Uji', 'nisn' => '1234567890', 'nik' => '3210000000000001', 'birth_place' => 'Majalengka',
            'birth_date' => '2005-01-01', 'gender' => 'male', 'religion' => 'Islam', 'address' => 'Jl. Uji', 'phone' => '081234567890',
            'campus_id' => (string) $campusId, 'first_choice_program_id' => (string) $programId, 'admission_path' => 'umum',
            'school_name' => 'SMA Uji', 'school_major' => 'IPA', 'graduation_year' => '2024', 'certificate_number' => 'IJ-1',
            'father_name' => 'Ayah', 'mother_name' => 'Ibu', 'father_occupation' => 'Petani', 'mother_occupation' => 'IRT',
        ];

        foreach ($data as $field => $value) {
            $component->set($field, $value);
        }

        return $component;
    }

    // ── Akses staf ─────────────────────────────────────────────────────────

    public function test_campus_scoped_staff_only_see_their_campus_registrations(): void
    {
        $this->period();
        [, $mjlReg] = $this->student('terdaftar', ['campus_id' => $this->mjl()->id, 'full_name' => 'Pendaftar Majalengka']);
        [, $crbReg] = $this->student('terdaftar', ['campus_id' => $this->crb()->id, 'full_name' => 'Pendaftar Cirebon']);

        $crbAdmin = $this->makeUser('admin', ['campus_id' => $this->crb()->id]);

        $this->actingAs($crbAdmin)->get('/admin/pendaftar')
            ->assertOk()->assertSee('Pendaftar Cirebon')->assertDontSee('Pendaftar Majalengka');
        $this->actingAs($crbAdmin)->get("/admin/pendaftar/{$crbReg->id}")->assertOk();
        $this->actingAs($crbAdmin)->get("/admin/pendaftar/{$mjlReg->id}")->assertNotFound();

        $globalAdmin = $this->makeUser('admin');
        $this->actingAs($globalAdmin)->get("/admin/pendaftar/{$crbReg->id}")->assertOk();
        $this->actingAs($globalAdmin)->get("/admin/pendaftar/{$mjlReg->id}")->assertOk();
    }

    public function test_legacy_registration_without_campus_belongs_to_default_campus(): void
    {
        $this->period();
        [, $legacy] = $this->student('terdaftar', ['full_name' => 'Pendaftar Lama']);
        \DB::table('registrations')->where('id', $legacy->id)->update(['campus_id' => null]);

        $mjlAdmin = $this->makeUser('admin', ['campus_id' => $this->mjl()->id]);
        $crbAdmin = $this->makeUser('admin', ['campus_id' => $this->crb()->id]);

        $this->actingAs($mjlAdmin)->get("/admin/pendaftar/{$legacy->id}")->assertOk();
        $this->actingAs($crbAdmin)->get("/admin/pendaftar/{$legacy->id}")->assertNotFound();
    }

    public function test_panitia_can_only_scan_registrations_of_their_campus(): void
    {
        $this->period();
        [, $crbReg] = $this->student('menunggu_interview', [
            'campus_id' => $this->crb()->id, 'registration_number' => 'PMB-CRB-0001',
        ]);

        $mjlPanitia = $this->makeUser('panitia', ['campus_id' => $this->mjl()->id]);
        $crbPanitia = $this->makeUser('panitia', ['campus_id' => $this->crb()->id]);

        // Pendaftar kampus lain diperlakukan sebagai "tidak ditemukan".
        $this->actingAs($mjlPanitia)->get('/panitia/scan/' . $crbReg->registration_number)
            ->assertRedirect(route('panitia.dashboard'))
            ->assertSessionHas('error');
        $this->actingAs($mjlPanitia)->post('/panitia/scan/' . $crbReg->registration_number . '/selesai', ['hasil' => 'diterima'])
            ->assertNotFound();
        $this->assertSame('menunggu_interview', $crbReg->fresh()->status);

        $this->actingAs($crbPanitia)->get('/panitia/scan/' . $crbReg->registration_number)->assertOk();
    }

    public function test_cross_campus_areas_are_global_admin_only(): void
    {
        $period = $this->period();
        $campusAdmin = $this->makeUser('admin', ['campus_id' => $this->crb()->id]);
        $globalAdmin = $this->makeUser('admin');

        foreach (['/admin/campuses', '/admin/periode', "/admin/periode/{$period->id}/biaya", '/admin/akun-staf',
                  '/admin/pengaturan-email', '/admin/afiliasi', '/admin/reward', '/admin/programs',
                  '/admin/faculties', '/admin/bank-soal'] as $uri) {
            $this->actingAs($campusAdmin)->get($uri)->assertForbidden();
        }

        $this->actingAs($globalAdmin)->get('/admin/campuses')->assertOk();
        $this->actingAs($globalAdmin)->get('/admin/programs')->assertOk();
    }

    public function test_campus_scoped_admin_can_still_switch_period(): void
    {
        $period = $this->period();
        $campusAdmin = $this->makeUser('admin', ['campus_id' => $this->crb()->id]);

        $this->actingAs($campusAdmin)->post('/admin/periode/pilih', ['period' => $period->id])
            ->assertRedirect();
    }

    public function test_sequence_and_seat_counts_ignore_the_campus_scope(): void
    {
        $this->period();
        $program = $this->program();
        $this->student('terdaftar', ['campus_id' => $this->mjl()->id, 'first_choice_program_id' => $program->id]);
        $this->student('terdaftar', ['campus_id' => $this->crb()->id, 'first_choice_program_id' => $program->id]);

        $crbAdmin = $this->makeUser('admin', ['campus_id' => $this->crb()->id]);
        $this->actingAs($crbAdmin);

        $this->assertSame(1, Registration::count(), 'scope aktif untuk query registrasi biasa');
        $this->assertSame(2, $program->occupiedSeats(), 'hitung kursi harus lintas kampus');
    }

    // ── Prodi, kuota, biaya per kampus ─────────────────────────────────────

    public function test_program_is_offered_per_campus(): void
    {
        $period = $this->period();
        $program = $this->program();

        $this->assertTrue(Program::offeredIn($period)->whereKey($program->id)->exists());
        $this->assertFalse(Program::offeredIn($period, $this->crb())->whereKey($program->id)->exists());

        $this->offerInCirebon($program);

        $this->assertTrue(Program::offeredIn($period, $this->crb())->whereKey($program->id)->exists());
    }

    public function test_terms_and_quota_are_separate_per_campus(): void
    {
        $period = $this->period();
        $program = $this->program(['registration_fee' => 150000]);
        PeriodProgram::where('program_id', $program->id)->delete();
        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => $this->mjl()->id,
            'is_offered' => true, 'quota' => 5, 'registration_fee' => 150000,
        ]);
        $this->offerInCirebon($program, quota: 1, fee: 350000);

        $fresh = Program::find($program->id);
        $this->assertSame(150000, (int) $fresh->termsFor($period, $this->mjl())->registration_fee);
        $this->assertSame(350000, (int) Program::find($program->id)->termsFor($period, $this->crb())->registration_fee);

        // Satu pendaftar Cirebon memenuhi kuota Cirebon (1), bukan kuota Majalengka (5).
        $this->student('menunggu_pembayaran', ['campus_id' => $this->crb()->id, 'first_choice_program_id' => $program->id]);

        $this->assertTrue(Program::find($program->id)->isFull($period->id, $this->crb()));
        $this->assertFalse(Program::find($program->id)->isFull($period->id, $this->mjl()));
    }

    // ── Form pendaftaran ───────────────────────────────────────────────────

    public function test_form_lists_only_programs_of_the_chosen_campus(): void
    {
        $this->period();
        $mjlOnly = $this->program();
        $both = $this->program();
        $this->offerInCirebon($both);

        $component = Livewire::actingAs($this->makeUser())->test(RegistrationForm::class);

        $component->assertSet('campus_id', '');
        $this->assertCount(0, $component->get('programs'));

        $component->set('campus_id', (string) $this->crb()->id);
        $this->assertEqualsCanonicalizing([$both->id], $component->get('programs')->pluck('id')->all());

        $component->set('campus_id', (string) $this->mjl()->id);
        $this->assertContains($mjlOnly->id, $component->get('programs')->pluck('id')->all());
    }

    public function test_submit_stores_campus_and_rejects_program_not_offered_there(): void
    {
        $this->period();
        $mjlOnly = $this->program();
        $both = $this->program();
        $this->offerInCirebon($both);

        $this->fillForm(Livewire::actingAs($this->makeUser())->test(RegistrationForm::class), $this->crb()->id, $mjlOnly->id)
            ->call('submit')->assertHasErrors('first_choice_program_id');
        $this->assertSame(0, Registration::count());

        $this->fillForm(Livewire::actingAs($this->makeUser())->test(RegistrationForm::class), $this->crb()->id, $both->id)
            ->call('submit')->assertHasNoErrors();

        $registration = Registration::first();
        $this->assertSame($this->crb()->id, $registration->campus_id);
        $this->assertSame($both->id, $registration->first_choice_program_id);
    }

    public function test_form_blocks_submission_when_campus_quota_is_full(): void
    {
        $this->period();
        $program = $this->program();
        $this->offerInCirebon($program, quota: 1);
        $this->student('menunggu_pembayaran', ['campus_id' => $this->crb()->id, 'first_choice_program_id' => $program->id]);

        $this->fillForm(Livewire::actingAs($this->makeUser())->test(RegistrationForm::class), $this->crb()->id, $program->id)
            ->set('nik', '3210000000000099')
            ->call('submit')->assertHasErrors('first_choice_program_id');

        // Kampus bawaan tidak terdampak kuota Cirebon.
        $this->fillForm(Livewire::actingAs($this->makeUser())->test(RegistrationForm::class), $this->mjl()->id, $program->id)
            ->set('nik', '3210000000000098')
            ->call('submit')->assertHasNoErrors();
    }

    // ── Kontak kampus ──────────────────────────────────────────────────────

    public function test_campus_contact_and_bank_fall_back_to_period(): void
    {
        $period = $this->period();
        $period->forceFill([
            'admin_whatsapp' => '0811000000', 'university_bank_name' => 'BANK PERIODE',
            'university_bank_account' => '111', 'university_bank_account_name' => 'YPIB',
        ])->save();

        [, $registration] = $this->student('menunggu_pembayaran', ['campus_id' => $this->crb()->id]);
        $registration->load('campus', 'period');

        $this->assertSame('0811000000', $registration->contactWhatsapp());
        $this->assertSame('BANK PERIODE', $registration->paymentBank()['name']);

        $this->crb()->update([
            'admin_whatsapp' => '0899111222', 'university_bank_name' => 'BANK CRB',
            'university_bank_account' => '999', 'university_bank_account_name' => 'YPIB CRB',
        ]);
        $registration->refresh()->load('campus', 'period');

        $this->assertSame('0899111222', $registration->contactWhatsapp());
        $this->assertSame(
            ['name' => 'BANK CRB', 'account' => '999', 'account_name' => 'YPIB CRB'],
            $registration->paymentBank()
        );
    }

    // ── Landing ────────────────────────────────────────────────────────────

    public function test_landing_shows_program_offered_only_in_another_campus(): void
    {
        $period = $this->period();
        $program = $this->program(['name' => 'Prodi Khusus Cirebon']);
        PeriodProgram::where('program_id', $program->id)->delete();
        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => $this->mjl()->id,
            'is_offered' => false,
        ]);
        $this->offerInCirebon($program, quota: 7, fee: 350000);

        $this->get('/')->assertOk()->assertSee('Prodi Khusus Cirebon')->assertSee('Cirebon');
        $this->get('/prodi/' . $program->slug)->assertOk()->assertSee('Kampus Cirebon')->assertSee('350.000');
    }
}
