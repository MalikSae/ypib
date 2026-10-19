<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Kartu "Dibuka di Kampus Mana?" pada form Program Studi (menulis period_programs). */
class ProgramCampusOffersTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    private function base(Program $program, array $extra = []): array
    {
        return array_merge([
            'name' => $program->name, 'registration_track' => 'reguler', 'faculty_id' => $program->faculty_id,
            'kode_prodi' => $program->kode_prodi, 'is_active' => 1,
        ], $extra);
    }

    private function row(Program $program, Campus $campus): ?PeriodProgram
    {
        return PeriodProgram::where('period_id', $this->period()->id)
            ->where('program_id', $program->id)->where('campus_id', $campus->id)->first();
    }

    /** Prodi yang sudah ditawarkan di kampus bawaan dengan biaya 150.000 dan kuota 100. */
    private function programInDefaultCampus(): Program
    {
        $program = $this->program();
        PeriodProgram::where('program_id', $program->id)->delete();
        PeriodProgram::create([
            'period_id' => $this->period()->id, 'program_id' => $program->id, 'campus_id' => Campus::defaultId(),
            'is_offered' => true, 'quota' => 100, 'registration_fee' => 150000,
            're_registration_fee' => 6000000, 'referral_reward_amount' => 50000,
        ]);

        return $program;
    }

    public function test_edit_form_shows_campus_card_with_current_values_and_prefill(): void
    {
        $program = $this->programInDefaultCampus();

        $html = $this->actingAs($this->makeUser('admin'))->get("/admin/programs/{$program->id}/edit")
            ->assertOk()->assertSee('Dibuka di Kampus Mana?')->assertSee('Kampus Majalengka')->assertSee('Kampus Cirebon')
            ->assertSee('offers_present', false)->getContent();

        // Kampus bawaan: nilai tersimpan; Cirebon belum ditawarkan: terisi awal dari kampus bawaan.
        $this->assertMatchesRegularExpression('/name="offers\[' . Campus::defaultId() . '\]\[fee\]"[^>]*value="150000"/', $html);
        $this->assertMatchesRegularExpression('/name="offers\[' . $this->crb()->id . '\]\[fee\]"[^>]*value="150000"/', $html);
    }

    public function test_ticking_a_campus_creates_its_row_with_fee_quota_and_borrowed_terms(): void
    {
        $program = $this->programInDefaultCampus();
        $mjl = Campus::defaultId();
        $crb = $this->crb()->id;

        $this->actingAs($this->makeUser('admin'))->put("/admin/programs/{$program->id}", $this->base($program, [
            'offers_present' => 1,
            'offers' => [
                $mjl => ['offered' => 1, 'fee' => 150000, 'quota' => 100],
                $crb => ['offered' => 1, 'fee' => 350000, 'quota' => 40],
            ],
        ]))->assertRedirect(route('admin.programs.index'));

        $row = $this->row($program, $this->crb());
        $this->assertTrue($row->is_offered);
        $this->assertSame(350000, (int) $row->registration_fee);
        $this->assertSame(40, (int) $row->quota);
        $this->assertSame(6000000, (int) $row->re_registration_fee, 'nilai daftar ulang dipinjam dari kampus bawaan');
        $this->assertSame(50000, (int) $row->referral_reward_amount);

        $this->assertTrue(Program::offeredIn($this->period(), $this->crb())->whereKey($program->id)->exists());
        $this->assertSame(150000, (int) $this->row($program, Campus::find($mjl))->registration_fee, 'kampus bawaan tidak berubah');
    }

    public function test_unticking_keeps_the_row_but_stops_offering_it(): void
    {
        $program = $this->programInDefaultCampus();
        PeriodProgram::create([
            'period_id' => $this->period()->id, 'program_id' => $program->id, 'campus_id' => $this->crb()->id,
            'is_offered' => true, 'quota' => 5, 'registration_fee' => 350000,
        ]);

        // Cirebon tidak dikirim (tidak dicentang); kampus bawaan tetap.
        $this->actingAs($this->makeUser('admin'))->put("/admin/programs/{$program->id}", $this->base($program, [
            'offers_present' => 1,
            'offers' => [Campus::defaultId() => ['offered' => 1, 'fee' => 150000, 'quota' => 100]],
        ]))->assertRedirect();

        $row = $this->row($program, $this->crb());
        $this->assertFalse($row->is_offered);
        $this->assertSame(350000, (int) $row->registration_fee, 'nilai lama tidak dibuang');
        $this->assertFalse(Program::offeredIn($this->period(), $this->crb())->whereKey($program->id)->exists());
    }

    public function test_unticking_default_campus_is_recorded_explicitly(): void
    {
        $program = $this->program(); // tanpa baris: dianggap ditawarkan di kampus bawaan
        PeriodProgram::where('program_id', $program->id)->delete();
        $this->assertTrue(Program::offeredIn($this->period())->whereKey($program->id)->exists());

        $this->actingAs($this->makeUser('admin'))->put("/admin/programs/{$program->id}", $this->base($program, [
            'offers_present' => 1,
            'offers' => [$this->crb()->id => ['offered' => 1, 'fee' => 250000, 'quota' => 10]],
        ]))->assertRedirect();

        $this->assertFalse(Program::offeredIn($this->period())->whereKey($program->id)->exists(), 'prodi tidak lagi di kampus bawaan');
        $this->assertTrue(Program::offeredIn($this->period(), $this->crb())->whereKey($program->id)->exists());
    }

    public function test_create_form_can_open_a_new_program_in_a_campus(): void
    {
        $this->period();
        $faculty = $this->faculty();

        $this->actingAs($this->makeUser('admin'))->post('/admin/programs', [
            'name' => 'Prodi Baru Cirebon', 'registration_track' => 'reguler', 'faculty_id' => $faculty->id, 'is_active' => 1,
            'offers_present' => 1,
            'offers' => [$this->crb()->id => ['offered' => 1, 'fee' => 400000, 'quota' => 25]],
        ])->assertRedirect(route('admin.programs.index'));

        $program = Program::where('name', 'Prodi Baru Cirebon')->firstOrFail();
        $this->assertTrue(Program::offeredIn($this->period(), $this->crb())->whereKey($program->id)->exists());
        $this->assertFalse(Program::offeredIn($this->period())->whereKey($program->id)->exists(), 'kampus bawaan tidak dicentang');
        $this->assertSame(400000, (int) $this->row($program, $this->crb())->registration_fee);
    }

    public function test_invalid_numbers_are_rejected_and_nothing_is_saved(): void
    {
        $program = $this->programInDefaultCampus();
        $crb = $this->crb()->id;

        foreach ([['fee' => -1, 'quota' => 1], ['fee' => 'abc', 'quota' => 1], ['fee' => 100, 'quota' => -5], ['fee' => 3000000000, 'quota' => 1]] as $bad) {
            $this->actingAs($this->makeUser('admin'))->put("/admin/programs/{$program->id}", $this->base($program, [
                'name' => 'Nama Berubah', 'offers_present' => 1, 'offers' => [$crb => ['offered' => 1] + $bad],
            ]))->assertSessionHasErrors();
        }

        $this->assertNull($this->row($program, $this->crb()));
        $this->assertSame($program->name, $program->fresh()->name);
    }

    public function test_form_without_the_card_or_for_an_archived_period_changes_nothing(): void
    {
        $program = $this->programInDefaultCampus();
        $admin = $this->makeUser('admin');

        // Tanpa penanda offers_present (mis. klien lama): baris kampus tidak disentuh.
        $this->actingAs($admin)->put("/admin/programs/{$program->id}", $this->base($program, [
            'offers' => [$this->crb()->id => ['offered' => 1, 'fee' => 1, 'quota' => 1]],
        ]))->assertRedirect();
        $this->assertNull($this->row($program, $this->crb()));

        // Periode arsip hanya baca (dipilih admin lewat pemilih periode di header).
        $archived = $this->period();
        $archived->forceFill(['status' => PmbPeriod::STATUS_ARCHIVED, 'is_active' => false])->save();
        $pick = [\App\Support\AdminPeriod::SESSION_KEY => $archived->id];

        $this->withSession($pick)->actingAs($admin)->put("/admin/programs/{$program->id}", $this->base($program, [
            'offers_present' => 1,
            'offers' => [$this->crb()->id => ['offered' => 1, 'fee' => 1, 'quota' => 1]],
        ]))->assertRedirect();
        $this->assertNull($this->row($program, $this->crb()));

        $this->withSession($pick)->actingAs($admin)->get("/admin/programs/{$program->id}/edit")->assertOk()
            ->assertSee('hanya bisa dilihat')->assertDontSee('offers_present', false);
    }

    public function test_card_warns_when_a_campus_already_has_registrations(): void
    {
        $program = $this->programInDefaultCampus();
        $this->student('menunggu_pembayaran', ['campus_id' => Campus::defaultId(), 'first_choice_program_id' => $program->id]);

        $this->actingAs($this->makeUser('admin'))->get("/admin/programs/{$program->id}/edit")->assertOk()
            ->assertSee('1 pendaftar')->assertSee('Prodi hanya berhenti menerima pendaftar baru');
    }

    public function test_campus_scoped_admin_cannot_use_the_program_form(): void
    {
        $program = $this->programInDefaultCampus();
        $scoped = $this->makeUser('admin', ['campus_id' => $this->crb()->id]);

        $this->actingAs($scoped)->get("/admin/programs/{$program->id}/edit")->assertForbidden();
        $this->actingAs($scoped)->put("/admin/programs/{$program->id}", $this->base($program, [
            'offers_present' => 1, 'offers' => [$this->crb()->id => ['offered' => 1, 'fee' => 1, 'quota' => 1]],
        ]))->assertForbidden();
        $this->assertNull($this->row($program, $this->crb()));
    }
}
