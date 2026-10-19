<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\PeriodProgram;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Landing per kampus (/cirebon): rute, isi, SEO, dan alamat halaman kampus. */
class CampusLandingTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    /** Prodi hanya di Cirebon (dimatikan di kampus bawaan), dengan biaya dan kuota sendiri. */
    private function cirebonOnlyProgram(string $name = 'Prodi Khusus Cirebon', int $fee = 350000, int $quota = 77): Program
    {
        $period = $this->period();
        $program = $this->program(['name' => $name]);

        PeriodProgram::where('program_id', $program->id)->delete();
        PeriodProgram::create(['period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => Campus::defaultId(), 'is_offered' => false]);
        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => $this->crb()->id,
            'is_offered' => true, 'quota' => $quota, 'registration_fee' => $fee,
        ]);

        return $program;
    }

    public function test_campus_page_lists_only_programs_of_that_campus_with_its_own_terms(): void
    {
        $this->crb()->update(['address' => 'Jl. Perjuangan No. 10a, Kota Cirebon', 'admin_whatsapp' => '0813999888']);
        $this->cirebonOnlyProgram('Prodi Khusus Cirebon', fee: 350000, quota: 77);
        $this->program(['name' => 'Prodi Hanya Majalengka']);

        $this->get('/cirebon')
            ->assertOk()
            ->assertSee('Prodi Khusus Cirebon')
            ->assertDontSee('Prodi Hanya Majalengka')
            ->assertSee('350.000')
            ->assertSee('Kuota 77')
            ->assertSee('Kampus Cirebon')
            ->assertSee('Jl. Perjuangan No. 10a, Kota Cirebon')
            ->assertSee('0813999888');
    }

    public function test_campus_page_seo_and_links(): void
    {
        $program = $this->cirebonOnlyProgram();

        $html = $this->get('/cirebon')->assertOk()->getContent();

        $this->assertStringContainsString('<title>PMB Kampus Cirebon — Universitas YPIB Majalengka</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/cirebon') . '">', $html);
        $this->assertStringContainsString('prodi/' . $program->slug . '?kampus=cirebon', $html);
    }

    public function test_campus_page_hides_global_gallery_and_partners_and_shows_only_its_footer(): void
    {
        $this->cirebonOnlyProgram();

        $this->get('/cirebon')->assertOk()
            ->assertDontSee('Jelajahi potret')
            ->assertDontSee('Jl. Gerakan Koperasi'); // alamat Majalengka tidak muncul di footer Cirebon

        // Landing utama tidak berubah: kedua kampus tampil di footer.
        $this->get('/')->assertOk()->assertSee('Jl. Gerakan Koperasi')->assertSee('Jl. Perjuangan No. 10a')
            ->assertSee('<title>PMB Universitas YPIB Majalengka</title>', false);
    }

    public function test_default_campus_redirects_and_unknown_or_inactive_campus_is_404(): void
    {
        $this->period();

        $this->get('/majalengka')->assertStatus(301)->assertRedirect(route('landing'));
        $this->get('/kampus-tidak-ada')->assertNotFound();

        $this->crb()->update(['is_active' => false]);
        $this->get('/cirebon')->assertNotFound();
    }

    public function test_campus_slug_route_does_not_hijack_system_routes(): void
    {
        $this->period();

        $this->get('/login')->assertOk()->assertDontSee('Kampus Cirebon');
        $this->get('/register')->assertOk();
        $this->get('/daftar')->assertOk(); // form pendaftaran (tanpa ?prodi), bukan landing kampus
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/panitia/dashboard')->assertRedirect('/login');
        $this->get('/prodi/tidak-ada')->assertNotFound();
    }

    public function test_campus_without_offered_programs_shows_empty_state(): void
    {
        $this->period();

        $this->get('/cirebon')->assertOk()
            ->assertSee('belum dibuka untuk periode ini');
    }

    public function test_program_page_shows_terms_of_the_requested_campus(): void
    {
        $period = $this->period();
        $program = $this->program(['name' => 'Prodi Dua Kampus']);
        PeriodProgram::where('program_id', $program->id)->delete();
        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => Campus::defaultId(),
            'is_offered' => true, 'quota' => 10, 'registration_fee' => 150000,
        ]);
        PeriodProgram::create([
            'period_id' => $period->id, 'program_id' => $program->id, 'campus_id' => $this->crb()->id,
            'is_offered' => true, 'quota' => 20, 'registration_fee' => 350000,
        ]);

        $this->get('/prodi/' . $program->slug)->assertOk()->assertSee('150.000')->assertDontSee('350.000');
        $this->get('/prodi/' . $program->slug . '?kampus=cirebon')->assertOk()->assertSee('350.000')->assertDontSee('150.000');
        // Kampus yang tidak menawarkan prodi ini diabaikan, bukan error.
        $this->get('/prodi/' . $program->slug . '?kampus=tidak-ada')->assertOk()->assertSee('150.000');
    }

    // ── Alamat halaman (slug) di admin ─────────────────────────────────────

    private function admin()
    {
        return $this->makeUser('admin');
    }

    private function campusPayload(array $extra = []): array
    {
        return array_merge(['name' => 'Kampus Bandung', 'code' => 'BDG', 'city' => 'Bandung', 'is_active' => 1], $extra);
    }

    public function test_slug_is_derived_from_city_and_stays_stable_when_campus_is_renamed(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/campuses', $this->campusPayload())->assertRedirect(route('admin.campuses.index'));
        $this->assertSame('bandung', Campus::where('code', 'BDG')->value('slug'));

        // Mengganti nama tidak boleh mengubah alamat halaman yang sudah dipakai.
        $cirebon = $this->crb();
        $this->actingAs($admin)->put("/admin/campuses/{$cirebon->id}", [
            'name' => 'Universitas YPIB Cirebon', 'code' => 'CRB', 'is_active' => 1,
        ])->assertRedirect(route('admin.campuses.index'));

        $this->assertSame('cirebon', $cirebon->fresh()->slug);
        $this->assertSame('Universitas YPIB Cirebon', $cirebon->fresh()->name);
    }

    public function test_slug_can_be_set_explicitly_and_is_validated(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/campuses', $this->campusPayload(['slug' => 'bdg-kota']))->assertRedirect();
        $this->assertSame('bdg-kota', Campus::where('code', 'BDG')->value('slug'));

        // Format salah, bentrok dengan kampus lain, dan dicadangkan sistem ditolak.
        foreach (['Bandung Kota', 'cirebon', 'admin', 'login', 'daftar', 'prodi', 'afiliasi', 'panitia'] as $bad) {
            $this->actingAs($admin)->post('/admin/campuses', $this->campusPayload(['name' => 'Kampus X ' . $bad, 'code' => 'X' . strlen($bad), 'slug' => $bad]))
                ->assertSessionHasErrors('slug');
        }

        $this->assertSame(3, Campus::count()); // bawaan + Cirebon + Bandung; semua percobaan buruk ditolak
    }

    public function test_reserved_slug_cannot_be_derived_from_city_either(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/campuses', $this->campusPayload(['name' => 'Kampus Admin', 'code' => 'ADM', 'city' => 'Admin']))
            ->assertSessionHasErrors('slug');

        $this->assertNull(Campus::where('code', 'ADM')->first());
    }
}
