<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Reward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T3b (PLAN_MULTI_PERIODE.md, K6): biaya, kuota, dan komisi per periode.
 */
class PeriodProgramPricingTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function periodNamed(string $name, int $year, string $status = 'published'): PmbPeriod
    {
        return PmbPeriod::forceCreate([
            'name'                         => $name,
            'year'                         => $year,
            'open_date'                    => "{$year}-01-01",
            'close_date'                   => "{$year}-12-31",
            're_registration_deadline'     => ($year + 1) . '-06-30',
            'university_bank_name'         => 'BRI',
            'university_bank_account'      => '1234567890',
            'university_bank_account_name' => 'YPIB',
            'admin_whatsapp'               => '081234567890',
            'status'                       => $status,
        ]);
    }

    private function terms(PmbPeriod $period, Program $program, array $values = []): PeriodProgram
    {
        return PeriodProgram::updateOrCreate(
            ['period_id' => $period->id, 'program_id' => $program->id],
            array_merge([
                'is_offered'                      => true,
                'quota'                           => 0,
                'registration_fee'                => 250000,
                're_registration_fee'             => 0,
                're_registration_minimum_payment' => 0,
                'referral_reward_amount'          => 50000,
                're_registration_reward_amount'   => 0,
            ], $values)
        );
    }

    private function payload(Program $program, array $values = []): array
    {
        return ['programs' => [$program->id => array_merge([
            'is_offered'                      => '1',
            'quota'                           => '10',
            'registration_fee'                => '300000',
            're_registration_minimum_payment' => '1000000',
            'referral_reward_amount'          => '75000',
            're_registration_reward_amount'   => '500000',
        ], $values)]];
    }

    // ── Pembacaan per periode ────────────────────────────────────────────

    public function test_a_registration_reads_the_fees_of_its_own_period(): void
    {
        $program = $this->program(['registration_fee' => 111000, 're_registration_fee' => 1]);
        $periodA = $this->periodNamed('PMB A', 2025);
        $periodB = $this->periodNamed('PMB B', 2026);
        $this->terms($periodA, $program, ['registration_fee' => 300000, 're_registration_fee' => 9000000]);
        $this->terms($periodB, $program, ['registration_fee' => 400000, 're_registration_fee' => 12000000]);

        $a = $this->registration(null, 'menunggu_pembayaran', ['first_choice_program_id' => $program->id, 'period_id' => $periodA->id]);
        $b = $this->registration(null, 'menunggu_pembayaran', ['first_choice_program_id' => $program->id, 'period_id' => $periodB->id]);

        $this->assertSame(300000, (int) $a->programTerms()->registration_fee);
        $this->assertSame(9000000, (int) $a->programTerms()->re_registration_fee);
        $this->assertSame(400000, (int) $b->programTerms()->registration_fee);

        $this->actingAs($a->user)->get('/pendaftaran/status')->assertOk()->assertSee('300.000')->assertDontSee('400.000');
        $this->actingAs($b->user)->get('/pendaftaran/status')->assertOk()->assertSee('400.000')->assertDontSee('300.000');
    }

    public function test_changing_next_years_price_does_not_change_an_older_registration(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $old = $this->periodNamed('PMB Lama', 2025);
        $new = $this->periodNamed('PMB Baru', 2026, 'draft');
        $this->terms($old, $program, ['registration_fee' => 250000, 're_registration_fee' => 10000000]);
        $this->terms($new, $program);

        $registration = $this->registration(null, 'menunggu_pembayaran', ['first_choice_program_id' => $program->id, 'period_id' => $old->id]);

        $this->actingAs($admin)
            ->put("/admin/periode/{$new->id}/biaya", $this->payload($program, ['registration_fee' => '999000']))
            ->assertSessionHasNoErrors();

        $this->assertSame(999000, (int) PeriodProgram::where('period_id', $new->id)->first()->registration_fee);
        $this->assertSame(250000, (int) $registration->fresh()->programTerms()->registration_fee);
        $this->actingAs($registration->user)->get('/pendaftaran/status')->assertSee('250.000')->assertDontSee('999.000');
    }

    public function test_programs_columns_are_the_fallback_when_a_period_has_no_row(): void
    {
        $program = $this->program(['quota' => 7, 'registration_fee' => 123000, 'referral_reward_amount' => 11000]);
        $period = $this->periodNamed('PMB Tanpa Matriks', 2026);

        $terms = $program->termsFor($period);

        $this->assertFalse($terms->exists);
        $this->assertTrue($terms->is_offered);
        $this->assertSame(7, (int) $terms->quota);
        $this->assertSame(123000, (int) $terms->registration_fee);
        $this->assertSame(11000, (int) $terms->referral_reward_amount);
    }

    // ── Kuota ────────────────────────────────────────────────────────────

    public function test_quota_is_per_period(): void
    {
        $program = $this->program(['quota' => 0]);
        $periodA = $this->periodNamed('PMB A', 2025);
        $periodB = $this->periodNamed('PMB B', 2026);
        $this->terms($periodA, $program, ['quota' => 1]);
        $this->terms($periodB, $program, ['quota' => 0]);

        $this->registration(null, 'terdaftar', ['first_choice_program_id' => $program->id, 'period_id' => $periodA->id]);

        $fresh = Program::find($program->id);
        $this->assertTrue($fresh->isFull($periodA->id));
        $this->assertFalse(Program::find($program->id)->isFull($periodB->id));
    }

    public function test_period_quota_is_enforced_by_the_public_form(): void
    {
        $program = $this->program(['quota' => 0]);
        $period = $this->periodNamed('PMB Kuota', (int) now()->year);
        $this->terms($period, $program, ['quota' => 1]);
        $this->registration(null, 'terdaftar', ['first_choice_program_id' => $program->id, 'period_id' => $period->id]);

        Livewire::actingAs($this->makeUser())->test(RegistrationForm::class)
            ->set('first_choice_program_id', (string) $program->id)
            ->set('admission_path', 'umum')
            ->set('step', 2)
            ->call('nextStep')
            ->assertHasErrors(['first_choice_program_id']);
    }

    // ── Prodi tidak ditawarkan ───────────────────────────────────────────

    public function test_a_program_not_offered_is_hidden_and_cannot_be_chosen(): void
    {
        $offered = $this->program(['name' => 'Prodi Ditawarkan']);
        $hidden = $this->program(['name' => 'Prodi Disembunyikan', 'slug' => 'prodi-disembunyikan']);
        $period = $this->periodNamed('PMB Ofer', (int) now()->year);
        $this->terms($period, $offered);
        $this->terms($period, $hidden, ['is_offered' => false]);

        $this->get('/')->assertOk()->assertSee('Prodi Ditawarkan')->assertDontSee('Prodi Disembunyikan');
        $this->get('/prodi/prodi-disembunyikan')->assertNotFound();

        Livewire::actingAs($this->makeUser())->test(RegistrationForm::class)
            ->set('first_choice_program_id', (string) $hidden->id)
            ->set('admission_path', 'umum')
            ->set('step', 2)
            ->call('nextStep')
            ->assertHasErrors(['first_choice_program_id']);
    }

    public function test_a_registrant_cannot_switch_to_a_program_not_offered_in_their_period(): void
    {
        $current = $this->program();
        $hidden = $this->program(['slug' => 'prodi-tidak-ditawarkan']);
        $period = $this->periodNamed('PMB Pindah', (int) now()->year);
        $this->terms($period, $current);
        $this->terms($period, $hidden, ['is_offered' => false]);

        $registration = $this->registration(null, 'terdaftar', ['first_choice_program_id' => $current->id, 'period_id' => $period->id]);

        $this->actingAs($registration->user)->post('/pendaftaran/detail/update', [
            'section' => 'program_jalur', 'first_choice_program_id' => $hidden->id, 'admission_path' => 'umum',
        ])->assertSessionHasErrors('first_choice_program_id');

        $this->assertSame($current->id, (int) $registration->fresh()->first_choice_program_id);
    }

    // ── Komisi ───────────────────────────────────────────────────────────

    public function test_registration_reward_uses_the_period_amount(): void
    {
        $admin = $this->makeUser('admin');
        $referrer = $this->referrer();
        $program = $this->program(['referral_reward_amount' => 50000]);
        $period = $this->periodNamed('PMB Komisi', (int) now()->year);
        $this->terms($period, $program, ['referral_reward_amount' => 75000]);

        $registration = $this->registration(null, 'menunggu_konfirmasi', [
            'first_choice_program_id' => $program->id, 'period_id' => $period->id,
            'referrer_id' => $referrer->id, 'payment_proof' => 'bukti-bayar/uji.jpg',
        ]);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar", ['note' => 'uji'])
            ->assertSessionHas('success');

        $this->assertSame(75000, (int) Reward::where('registration_id', $registration->id)->value('amount'));
    }

    // ── Matriks admin ────────────────────────────────────────────────────

    public function test_only_staff_can_open_or_save_the_pricing_matrix(): void
    {
        $program = $this->program();
        $period = $this->periodNamed('PMB Akses', 2026);

        $student = $this->makeUser('mahasiswa');
        $this->actingAs($student)->get("/admin/periode/{$period->id}/biaya")->assertForbidden();
        $this->actingAs($student)->put("/admin/periode/{$period->id}/biaya", $this->payload($program))->assertForbidden();

        $this->actingAs($this->makeUser('operator'))->get("/admin/periode/{$period->id}/biaya")
            ->assertOk()->assertSee('Biaya &amp; Kuota', false)->assertSee($program->name);
    }

    public function test_saving_the_matrix_stores_values_and_computes_total_from_details(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $period = $this->periodNamed('PMB Simpan', 2026, 'draft');

        $payload = $this->payload($program, [
            'fee_names'   => ['Dana Pengembangan', 'SPP Semester 1', ''],
            'fee_amounts' => ['5.000.000', '2500000', '999'],
        ]);

        $this->actingAs($admin)->put("/admin/periode/{$period->id}/biaya", $payload)->assertSessionHas('success');

        $row = PeriodProgram::where('period_id', $period->id)->where('program_id', $program->id)->firstOrFail();
        $this->assertTrue($row->is_offered);
        $this->assertSame(10, (int) $row->quota);
        $this->assertSame(300000, (int) $row->registration_fee);
        $this->assertSame(1000000, (int) $row->re_registration_minimum_payment);
        $this->assertSame(75000, (int) $row->referral_reward_amount);
        $this->assertSame(500000, (int) $row->re_registration_reward_amount);
        $this->assertSame(7500000, (int) $row->re_registration_fee);
        $this->assertSame([
            ['name' => 'Dana Pengembangan', 'amount' => 5000000],
            ['name' => 'SPP Semester 1', 'amount' => 2500000],
        ], $row->re_registration_fee_details);
    }

    public function test_unchecked_offer_flag_is_saved_as_not_offered(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $period = $this->periodNamed('PMB Ofer Off', 2026, 'draft');

        $this->actingAs($admin)
            ->put("/admin/periode/{$period->id}/biaya", $this->payload($program, ['is_offered' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) PeriodProgram::where('period_id', $period->id)->first()->is_offered);
    }

    public function test_matrix_validates_numbers_and_ignores_unknown_programs(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $period = $this->periodNamed('PMB Validasi', 2026, 'draft');

        $this->actingAs($admin)->put("/admin/periode/{$period->id}/biaya", $this->payload($program, ['quota' => '-1']))
            ->assertSessionHasErrors("programs.{$program->id}.quota");

        $payload = $this->payload($program);
        $payload['programs'][999999] = $payload['programs'][$program->id];
        $this->actingAs($admin)->put("/admin/periode/{$period->id}/biaya", $payload)->assertSessionHasNoErrors();

        $this->assertSame(1, PeriodProgram::where('period_id', $period->id)->count());
    }

    public function test_archived_period_matrix_is_read_only(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $period = $this->periodNamed('PMB Arsip', 2025, 'archived');
        $this->terms($period, $program, ['registration_fee' => 250000]);

        $this->actingAs($admin)->get("/admin/periode/{$period->id}/biaya")->assertOk()->assertSee('hanya baca');

        $this->actingAs($admin)->put("/admin/periode/{$period->id}/biaya", $this->payload($program))
            ->assertSessionHas('error');
        $this->assertSame(250000, (int) PeriodProgram::where('period_id', $period->id)->value('registration_fee'));

        $other = $this->periodNamed('PMB Sumber', 2026);
        $this->terms($other, $program, ['registration_fee' => 700000]);
        $this->actingAs($admin)->post("/admin/periode/{$period->id}/biaya/salin", ['source_period_id' => $other->id])
            ->assertSessionHas('error');
        $this->assertSame(250000, (int) PeriodProgram::where('period_id', $period->id)->value('registration_fee'));
    }

    public function test_copy_overwrites_values_from_another_period(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $source = $this->periodNamed('PMB Sumber', 2025);
        $target = $this->periodNamed('PMB Tujuan', 2026, 'draft');
        $this->terms($source, $program, [
            'registration_fee' => 700000, 'quota' => 33, 'is_offered' => false,
            're_registration_fee' => 4000000, 're_registration_fee_details' => [['name' => 'SPP', 'amount' => 4000000]],
        ]);
        $this->terms($target, $program, ['registration_fee' => 1, 'quota' => 1]);

        $this->actingAs($admin)->post("/admin/periode/{$target->id}/biaya/salin", ['source_period_id' => $source->id])
            ->assertSessionHas('success');

        $row = PeriodProgram::where('period_id', $target->id)->where('program_id', $program->id)->firstOrFail();
        $this->assertSame(700000, (int) $row->registration_fee);
        $this->assertSame(33, (int) $row->quota);
        $this->assertFalse($row->is_offered);
        $this->assertSame([['name' => 'SPP', 'amount' => 4000000]], $row->re_registration_fee_details);
    }

    // ── Penyiapan matriks ────────────────────────────────────────────────

    public function test_new_period_and_duplicate_prepare_the_matrix(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['registration_fee' => 321000, 'quota' => 5]);

        $this->actingAs($admin)->post('/admin/periode', [
            'name' => 'PMB Baru', 'year' => 2027, 'open_date' => '2027-01-01', 'close_date' => '2027-03-31',
            'payment_grace_days' => 3, 'nim_sequence_start' => 1,
        ])->assertRedirect('/admin/periode');

        $created = PmbPeriod::where('name', 'PMB Baru')->firstOrFail();
        $row = PeriodProgram::where('period_id', $created->id)->where('program_id', $program->id)->firstOrFail();
        $this->assertSame(321000, (int) $row->registration_fee);
        $this->assertSame(5, (int) $row->quota);

        $this->terms($created, $program, ['registration_fee' => 555000, 'is_offered' => false]);

        $this->actingAs($admin)->post("/admin/periode/{$created->id}/salin")->assertSessionHas('success');
        $copy = PmbPeriod::where('id', '!=', $created->id)->latest('id')->firstOrFail();
        $copied = PeriodProgram::where('period_id', $copy->id)->where('program_id', $program->id)->firstOrFail();
        $this->assertSame(555000, (int) $copied->registration_fee);
        $this->assertFalse($copied->is_offered);
    }

    public function test_a_new_program_is_not_offered_until_its_terms_are_set(): void
    {
        $admin = $this->makeUser('admin');
        $draft = $this->periodNamed('PMB Draft', 2027, 'draft');
        $archived = $this->periodNamed('PMB Arsip', 2024, 'archived');
        $faculty = $this->faculty();

        $this->actingAs($admin)->post('/admin/programs', [
            'name' => 'Prodi Baru Uji', 'kode_prodi' => '44444', 'registration_track' => 'reguler',
            'faculty_id' => $faculty->id, 'accreditation' => 'Baik', 'is_active' => 1, 'description' => '<p>x</p>',
        ])->assertSessionHasNoErrors();

        $program = Program::where('name', 'Prodi Baru Uji')->firstOrFail();

        $row = PeriodProgram::where('period_id', $draft->id)->where('program_id', $program->id)->firstOrFail();
        $this->assertFalse($row->is_offered);
        $this->assertNull(PeriodProgram::where('period_id', $archived->id)->where('program_id', $program->id)->first());
    }

    public function test_editing_a_program_no_longer_changes_its_money_columns(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['quota' => 5, 'registration_fee' => 250000, 'referral_reward_amount' => 50000]);

        $this->actingAs($admin)->put("/admin/programs/{$program->id}", [
            'name' => 'Nama Baru', 'kode_prodi' => $program->kode_prodi, 'registration_track' => 'reguler',
            'faculty_id' => $program->faculty_id, 'is_active' => 1, 'description' => '<p>x</p>',
            'quota' => 999, 'registration_fee' => 1, 'referral_reward_amount' => 1,
        ])->assertSessionHasNoErrors();

        $program->refresh();
        $this->assertSame('Nama Baru', $program->name);
        $this->assertSame(5, (int) $program->quota);
        $this->assertSame(250000, (int) $program->registration_fee);
        $this->assertSame(50000, (int) $program->referral_reward_amount);
    }

    // ── Performa ─────────────────────────────────────────────────────────

    public function test_landing_loads_period_terms_in_one_query_regardless_of_program_count(): void
    {
        $period = $this->periodNamed('PMB Landing', (int) now()->year);
        foreach (range(1, 6) as $i) {
            $this->terms($period, $this->program(), ['registration_fee' => 100000 + $i]);
        }

        $termQueries = 0;
        DB::listen(function ($query) use (&$termQueries) {
            if (str_starts_with($query->sql, 'select * from `period_programs`')) {
                $termQueries++;
            }
        });

        $this->get('/')->assertOk();

        $this->assertSame(1, $termQueries);
    }
}
