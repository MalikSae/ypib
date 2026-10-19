<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\PeriodProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Filter kampus pada daftar Program Studi (admin). */
class ProgramCampusFilterTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    /** Tawarkan prodi di Cirebon; $mjl=false mematikannya di kampus bawaan. */
    private function offerInCirebon($program, int $fee, int $quota, bool $mjl = true): void
    {
        $period = $this->period();

        if (!$mjl) {
            PeriodProgram::where('program_id', $program->id)->delete();
            PeriodProgram::create(['period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => Campus::defaultId(), 'is_offered' => false]);
        }

        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => $this->crb()->id,
            'is_offered' => true, 'quota' => $quota, 'registration_fee' => $fee,
        ]);
    }

    public function test_campus_filter_lists_only_programs_of_that_campus_with_its_terms(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $both = $this->program(['name' => 'Prodi Dua Kampus']);
        $cirebonOnly = $this->program(['name' => 'Prodi Hanya Cirebon']);
        $mjlOnly = $this->program(['name' => 'Prodi Hanya Majalengka']);
        $this->offerInCirebon($both, fee: 350000, quota: 15);
        $this->offerInCirebon($cirebonOnly, fee: 750000, quota: 3, mjl: false);

        $crb = $this->crb()->id;
        $html = $this->actingAs($admin)->get("/admin/programs?campus={$crb}")->assertOk()->getContent();

        $this->assertStringContainsString('Prodi Dua Kampus', $html);
        $this->assertStringContainsString('Prodi Hanya Cirebon', $html);
        $this->assertStringNotContainsString('Prodi Hanya Majalengka', $html);
        $this->assertStringContainsString('350.000', $html);
        $this->assertStringContainsString('750.000', $html);

        $mjl = Campus::defaultId();
        $this->actingAs($admin)->get("/admin/programs?campus={$mjl}")->assertOk()
            ->assertSee('Prodi Dua Kampus')->assertSee('Prodi Hanya Majalengka')->assertDontSee('Prodi Hanya Cirebon');
    }

    public function test_without_filter_all_programs_show_with_campus_badges(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $both = $this->program(['name' => 'Prodi Dua Kampus']);
        $this->program(['name' => 'Prodi Hanya Majalengka']);
        $this->offerInCirebon($both, fee: 350000, quota: 15);

        $html = $this->actingAs($admin)->get('/admin/programs')->assertOk()
            ->assertSee('Prodi Dua Kampus')->assertSee('Prodi Hanya Majalengka')
            ->assertSee('Semua kampus')->getContent();

        $this->assertStringContainsString('>Cirebon<', $html);
        $this->assertStringContainsString('>Majalengka<', $html);
    }

    public function test_search_is_combined_with_campus_filter_instead_of_bypassing_it(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $cirebon = $this->program(['name' => 'Farmasi Cirebon Uji']);
        $this->program(['name' => 'Farmasi Majalengka Uji']);
        $this->offerInCirebon($cirebon, fee: 350000, quota: 15, mjl: false);

        $crb = $this->crb()->id;
        $this->actingAs($admin)->get("/admin/programs?campus={$crb}&search=Farmasi")->assertOk()
            ->assertSee('Farmasi Cirebon Uji')->assertDontSee('Farmasi Majalengka Uji');
    }

    public function test_inactive_programs_stay_visible_in_the_campus_filter_and_bad_campus_is_ignored(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $inactive = $this->program(['name' => 'Prodi Nonaktif Cirebon', 'is_active' => false]);
        $this->offerInCirebon($inactive, fee: 100000, quota: 1, mjl: false);

        $crb = $this->crb()->id;
        $this->actingAs($admin)->get("/admin/programs?campus={$crb}")->assertOk()->assertSee('Prodi Nonaktif Cirebon');
        $this->actingAs($admin)->get('/admin/programs?campus=99999')->assertOk()->assertSee('Prodi Nonaktif Cirebon');
    }
}
