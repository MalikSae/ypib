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

    public function test_main_landing_hero_has_a_campus_button_and_the_fee_sits_below_the_buttons(): void
    {
        $this->cirebonOnlyProgram();
        $this->program(['name' => 'Prodi Majalengka']);

        $html = $this->get('/')->assertOk()->getContent();

        $register = strpos($html, 'Daftar Sekarang');
        $campusButton = strpos($html, 'href="' . url('/cirebon') . '"');
        $fee = strpos($html, 'Mulai Rp');

        $this->assertNotFalse($register);
        $this->assertNotFalse($campusButton, 'tombol Kampus Cirebon harus ada');
        $this->assertNotFalse($fee);
        $this->assertTrue($register < $campusButton && $campusButton < $fee, 'urutan: Daftar Sekarang, tombol kampus, lalu biaya');
        $this->assertStringContainsString('Kampus Cirebon', substr($html, $campusButton, 400));
    }

    public function test_campus_button_is_hidden_without_offered_programs_or_when_inactive_and_on_campus_pages(): void
    {
        $this->period();
        $this->program();

        // Cirebon belum membuka prodi: tidak ada tombol di landing utama.
        $this->assertStringNotContainsString('href="' . url('/cirebon') . '"', $this->get('/')->assertOk()->getContent());

        $this->cirebonOnlyProgram();
        $this->assertStringContainsString('href="' . url('/cirebon') . '"', $this->get('/')->getContent());

        // Halaman kampus sendiri tidak menampilkan tombol kampus lain.
        $campusPage = $this->get('/cirebon')->assertOk()->getContent();
        $hero = substr($campusPage, 0, strpos($campusPage, 'id="program-studi"'));
        $this->assertStringNotContainsString('border-2 border-white/30', $hero, 'landing kampus tidak punya tombol kampus lain');

        // Kampus nonaktif tidak tampil sebagai tombol.
        $this->crb()->update(['is_active' => false]);
        $this->assertStringNotContainsString('href="' . url('/cirebon') . '"', $this->get('/')->getContent());
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

    public function test_campus_page_navbar_overlays_the_hero_like_the_main_landing(): void
    {
        $this->cirebonOnlyProgram();

        // Navbar transparan dengan logo putih hanya terbaca bila hero menempel di atas halaman:
        // body tidak boleh diberi pt-20 (itu untuk halaman dalam seperti detail prodi).
        $campusBody = $this->get('/cirebon')->assertOk()->getContent();
        $mainBody = $this->get('/')->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/<body[^>]*\bpt-20\b/', $campusBody);
        $this->assertDoesNotMatchRegularExpression('/<body[^>]*\bpt-20\b/', $mainBody);
        $this->assertStringContainsString("isHome: true", $campusBody);

        $program = Program::first();
        $this->assertMatchesRegularExpression('/<body[^>]*\bpt-20\b/', $this->get('/prodi/' . $program->slug)->getContent());
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
        return array_merge([
            'name' => 'Kampus Bandung', 'code' => 'BDG', 'city' => 'Bandung', 'is_active' => 1,
            'hero_headline' => 'Wujudkan Karirmu di Bidang Teknologi.', 'hero_highlight' => 'Karirmu',
            'hero_subtitle' => 'Kampus Bandung membuka S1 Ilmu Komputer dan Sistem Informasi.',
        ], $extra);
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
            'hero_headline' => $cirebon->hero_headline, 'hero_subtitle' => $cirebon->hero_subtitle,
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
    // ── Teks hero ──────────────────────────────────────────────────────────

    public function test_hero_copy_is_required_for_non_default_campuses(): void
    {
        $admin = $this->admin();

        foreach (['hero_headline', 'hero_subtitle'] as $field) {
            $this->actingAs($admin)->post('/admin/campuses', $this->campusPayload([$field => '']))
                ->assertSessionHasErrors($field);
        }
        $this->assertNull(Campus::where('code', 'BDG')->first());

        // Kampus yang sudah ada (non-bawaan) juga wajib: tidak ada kampus tanpa hero khusus.
        $cirebon = $this->crb();
        $this->actingAs($admin)->put("/admin/campuses/{$cirebon->id}", [
            'name' => $cirebon->name, 'code' => 'CRB', 'is_active' => 1, 'hero_headline' => '', 'hero_subtitle' => '',
        ])->assertSessionHasErrors(['hero_headline', 'hero_subtitle']);
    }

    public function test_hero_copy_rules(): void
    {
        $admin = $this->admin();
        $bad = [
            'hero_headline' => ['pendek', str_repeat('a', 91), 'Judul <b>tebal</b> di sini'],
            'hero_subtitle' => ['terlalu pendek', str_repeat('a', 181), 'Deskripsi dengan <script>alert(1)</script> di dalamnya'],
            'hero_highlight' => [str_repeat('a', 41), 'Tidak Ada Di Judul', 'karirmu', '<i>x</i>'],
        ];

        foreach ($bad as $field => $values) {
            foreach ($values as $value) {
                $this->actingAs($admin)->post('/admin/campuses', $this->campusPayload([$field => $value]))
                    ->assertSessionHasErrors($field);
            }
        }

        $this->assertNull(Campus::where('code', 'BDG')->first());

        // Kata sorotan opsional.
        $this->actingAs($admin)->post('/admin/campuses', $this->campusPayload(['hero_highlight' => '']))->assertSessionHasNoErrors();
        $this->assertNull(Campus::where('code', 'BDG')->value('hero_highlight'));
    }

    public function test_default_campus_does_not_need_hero_copy(): void
    {
        $default = Campus::find(Campus::defaultId());

        $this->actingAs($this->admin())->put("/admin/campuses/{$default->id}", [
            'name' => $default->name, 'code' => $default->code, 'is_active' => 1,
        ])->assertSessionHasNoErrors();
    }

    public function test_campus_page_renders_hero_copy_with_highlight_and_escapes_it(): void
    {
        $this->cirebonOnlyProgram();
        $this->crb()->forceFill([
            'hero_headline' => 'Wujudkan Karirmu di Bidang Farmasi.',
            'hero_highlight' => 'Karirmu',
            'hero_subtitle' => 'Kampus Cirebon membuka S1 Farmasi & Profesi Apoteker.',
        ])->save();

        $html = $this->get('/cirebon')->assertOk()->getContent();

        $this->assertStringContainsString('Wujudkan <span class="text-[#F1B10E]">Karirmu</span> di Bidang Farmasi.', $html);
        $this->assertStringContainsString('Kampus Cirebon membuka S1 Farmasi &amp; Profesi Apoteker.', $html);
        $this->assertStringNotContainsString('Kesehatan &amp; Teknologi', $html, 'klaim umum landing utama tidak boleh muncul');
        $this->assertStringNotContainsString('di Bidang Kesehatan', $html);

        // Data tidak tepercaya di-escape (jaring pengaman bila data masuk tanpa lewat form).
        $this->crb()->forceFill(['hero_headline' => 'Judul <script>x()</script>', 'hero_highlight' => null])->save();
        $this->assertStringNotContainsString('<script>x()</script>', $this->get('/cirebon')->getContent());
    }

    public function test_campus_without_hero_copy_gets_a_neutral_fallback(): void
    {
        $this->cirebonOnlyProgram();
        $this->crb()->forceFill(['hero_headline' => null, 'hero_highlight' => null, 'hero_subtitle' => null])->save();

        $this->get('/cirebon')->assertOk()
            ->assertSee('Kampus Cirebon', false)
            ->assertDontSee('Kesehatan &amp; Teknologi', false);
    }
}
