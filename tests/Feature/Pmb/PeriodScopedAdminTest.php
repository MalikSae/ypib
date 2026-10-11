<?php

namespace Tests\Feature\Pmb;

use App\Models\PmbPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T6 (PLAN_MULTI_PERIODE.md): pemilih periode global di panel admin.
 */
class PeriodScopedAdminTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private PmbPeriod $current;
    private PmbPeriod $old;

    protected function setUp(): void
    {
        parent::setUp();

        $this->current = $this->period(['name' => 'PMB Berjalan']);
        $this->old = PmbPeriod::forceCreate([
            'name' => 'PMB Lama', 'year' => (int) now()->subYear()->year, 'status' => 'archived',
            'open_date' => now()->subYear()->toDateString(), 'close_date' => now()->subYear()->addMonths(2)->toDateString(),
        ]);
    }

    private function inCurrent(string $name, string $status = 'terdaftar', array $extra = [])
    {
        return $this->registration(null, $status, array_merge(['period_id' => $this->current->id, 'full_name' => $name], $extra));
    }

    private function inOld(string $name, string $status = 'terdaftar', array $extra = [])
    {
        return $this->registration(null, $status, array_merge(['period_id' => $this->old->id, 'full_name' => $name], $extra));
    }

    // ── Pemilih ──────────────────────────────────────────────────────────

    public function test_default_is_the_display_period_and_the_header_lists_all_periods(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('id="admin-period-select"', false)
            ->assertSee('PMB Berjalan')
            ->assertSee('PMB Lama (arsip)')
            ->assertSee('Semua periode');
    }

    public function test_selection_is_stored_per_staff_session_and_validated(): void
    {
        $admin = $this->makeUser('admin');
        $this->inCurrent('Pendaftar Berjalan');
        $this->inOld('Pendaftar Lama');

        $this->actingAs($admin)->post('/admin/periode/pilih', ['period' => $this->old->id])->assertRedirect();
        $this->get('/admin/pendaftar')->assertOk()->assertSee('Pendaftar Lama')->assertDontSee('Pendaftar Berjalan');

        $this->post('/admin/periode/pilih', ['period' => 'all'])->assertRedirect();
        $this->get('/admin/pendaftar')->assertOk()->assertSee('Pendaftar Lama')->assertSee('Pendaftar Berjalan');

        $this->post('/admin/periode/pilih', ['period' => 999999])->assertSessionHasErrors('period');
        $this->post('/admin/periode/pilih', ['period' => 'abc'])->assertSessionHasErrors('period');
    }

    public function test_only_staff_can_change_the_selection(): void
    {
        $this->actingAs($this->makeUser('mahasiswa'))->post('/admin/periode/pilih', ['period' => 'all'])->assertForbidden();
    }

    // ── Daftar dan detail ────────────────────────────────────────────────

    public function test_registration_list_defaults_to_the_current_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->inCurrent('Pendaftar Berjalan');
        $this->inOld('Pendaftar Lama');

        $this->actingAs($admin)->get('/admin/pendaftar')
            ->assertOk()->assertSee('Pendaftar Berjalan')->assertDontSee('Pendaftar Lama');
    }

    public function test_status_tab_counts_follow_the_selected_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->inCurrent('A', 'terdaftar');
        $this->inOld('B', 'terdaftar');
        $this->inOld('C', 'terdaftar');

        $this->actingAs($admin)->get('/admin/pendaftar')
            ->assertViewHas('totalRegistrations', 1);

        $this->post('/admin/periode/pilih', ['period' => 'all']);
        $this->get('/admin/pendaftar')->assertViewHas('totalRegistrations', 3);
    }

    public function test_direct_detail_link_works_for_any_period(): void
    {
        $admin = $this->makeUser('admin');
        $old = $this->inOld('Pendaftar Lama');

        $this->actingAs($admin)->get("/admin/pendaftar/{$old->id}")->assertOk()->assertSee('Pendaftar Lama');
    }

    public function test_trash_follows_the_selected_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->inCurrent('Sampah Berjalan')->delete();
        $this->inOld('Sampah Lama')->delete();

        $this->actingAs($admin)->get('/admin/pendaftar/sampah')
            ->assertOk()->assertSee('Sampah Berjalan')->assertDontSee('Sampah Lama');
    }

    // ── Dashboard ────────────────────────────────────────────────────────

    public function test_dashboard_counts_follow_the_selected_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->inCurrent('A');
        $this->inOld('B');
        $this->inOld('C');

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertViewHas('stats', fn ($s) => $s['total'] === 1);

        $this->post('/admin/periode/pilih', ['period' => $this->old->id]);
        $this->get('/admin/dashboard')->assertViewHas('stats', fn ($s) => $s['total'] === 2);

        $this->post('/admin/periode/pilih', ['period' => 'all']);
        $this->get('/admin/dashboard')->assertViewHas('stats', fn ($s) => $s['total'] === 3);
    }

    public function test_dashboard_top_referrers_count_only_the_selected_period(): void
    {
        $admin = $this->makeUser('admin');
        $referrer = $this->referrer();
        $this->inCurrent('A', 'terdaftar', ['referrer_id' => $referrer->id]);
        $this->inOld('B', 'terdaftar', ['referrer_id' => $referrer->id]);
        $this->inOld('C', 'terdaftar', ['referrer_id' => $referrer->id]);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertViewHas('topReferrers', fn ($rows) => (int) $rows->first()->conversions === 1);

        $this->post('/admin/periode/pilih', ['period' => 'all']);
        $this->get('/admin/dashboard')
            ->assertViewHas('topReferrers', fn ($rows) => (int) $rows->first()->conversions === 3);
    }

    // ── Rekap dan ekspor ─────────────────────────────────────────────────

    public function test_recap_counts_follow_the_selected_period(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['name' => 'Prodi Rekap']);
        $this->inCurrent('A', 'terdaftar', ['first_choice_program_id' => $program->id]);
        $this->inOld('B', 'terdaftar', ['first_choice_program_id' => $program->id]);
        $this->inOld('C', 'terdaftar', ['first_choice_program_id' => $program->id]);

        $this->actingAs($admin)->get('/admin/pendaftar/rekap')
            ->assertViewHas('totals', fn ($t) => $t['pendaftar'] === 1);

        $this->post('/admin/periode/pilih', ['period' => 'all']);
        $this->get('/admin/pendaftar/rekap')
            ->assertViewHas('totals', fn ($t) => $t['pendaftar'] === 3);
    }

    public function test_csv_export_follows_the_selected_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->inCurrent('Ekspor Berjalan');
        $this->inOld('Ekspor Lama');

        $csv = $this->actingAs($admin)->get('/admin/pendaftar/export?format=csv')->streamedContent();
        $this->assertStringContainsString('Ekspor Berjalan', $csv);
        $this->assertStringNotContainsString('Ekspor Lama', $csv);

        $this->post('/admin/periode/pilih', ['period' => 'all']);
        $csv = $this->get('/admin/pendaftar/export?format=csv')->streamedContent();
        $this->assertStringContainsString('Ekspor Berjalan', $csv);
        $this->assertStringContainsString('Ekspor Lama', $csv);
    }

    public function test_referrer_export_filter_defaults_to_the_selected_period_with_phase_labels(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get('/admin/afiliasi')->assertOk();

        $response->assertSee('Semua Periode');
        $response->assertSee('PMB Berjalan (Pendaftaran dibuka)');
        $response->assertSee('PMB Lama (Diarsipkan)');
        $this->assertMatchesRegularExpression(
            '/<option value="' . $this->current->id . '"\s+selected[^>]*>/s',
            $response->getContent()
        );
    }
}
