<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Perbaikan dari code review: konfirmasi nonaktifkan, highlight hero, rute /{slug}, reset password staf. */
class CampusReviewFixesTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    // ── Konfirmasi nonaktifkan akun ────────────────────────────────────────

    public function test_deactivating_asks_for_confirmation_but_activating_does_not(): void
    {
        $admin = $this->makeUser('admin');
        $active = $this->makeUser('operator', ['name' => 'Operator Aktif']);
        $inactive = $this->makeUser('operator', ['name' => 'Operator Mati', 'is_active' => false]);

        $html = $this->actingAs($admin)->get('/admin/akun-staf')->assertOk()->getContent();

        $activeBlock = $this->blockAround($html, route('admin.staff.toggle', $active->id));
        $inactiveBlock = $this->blockAround($html, route('admin.staff.toggle', $inactive->id));

        $this->assertStringContainsString('data-confirm="', $activeBlock);
        $this->assertStringContainsString('Operator Aktif', $activeBlock);
        $this->assertStringNotContainsString('data-confirm=', $inactiveBlock);
    }

    /** Potongan HTML 400 karakter sebelum action tombol, cukup untuk melihat pembungkus form-nya. */
    private function blockAround(string $html, string $action): string
    {
        $position = strpos($html, 'action="' . $action . '"');
        $this->assertNotFalse($position, "tombol toggle {$action} tidak ditemukan");

        return substr($html, max(0, $position - 400), 500);
    }

    // ── Highlight hero ─────────────────────────────────────────────────────

    public function test_hero_highlight_does_not_break_html_entities(): void
    {
        $campus = new Campus(['name' => 'Kampus Uji']);

        // "amp" tidak ada di teks mentah, jadi tidak boleh menyorot bagian dalam "&amp;".
        $campus->hero_headline = 'Obat & Alat Kesehatan';
        $campus->hero_highlight = 'amp';
        $this->assertSame('Obat &amp; Alat Kesehatan', (string) $campus->heroHeadlineHtml());

        // Highlight yang memuat karakter khusus tetap tepat dan ter-escape.
        $campus->hero_headline = 'Farmasi & Apoteker';
        $campus->hero_highlight = '& Apoteker';
        $this->assertSame('Farmasi <span class="text-[#F1B10E]">&amp; Apoteker</span>', (string) $campus->heroHeadlineHtml());

        // Bagian sebelum dan sesudah sorotan tetap ter-escape.
        $campus->hero_headline = 'A & B Karirmu "C"';
        $campus->hero_highlight = 'Karirmu';
        $this->assertSame('A &amp; B <span class="text-[#F1B10E]">Karirmu</span> &quot;C&quot;', (string) $campus->heroHeadlineHtml());
    }

    // ── Rute /{slug} ───────────────────────────────────────────────────────

    public function test_landing_follows_a_changed_campus_slug(): void
    {
        $program = $this->program();
        $this->period();

        $this->get('/cirebon')->assertOk();
        $this->get('/baru')->assertNotFound();

        $this->crb()->forceFill(['slug' => 'baru'])->save();

        $this->get('/baru')->assertOk();
        $this->get('/cirebon')->assertNotFound();
    }

    public function test_catch_all_route_is_throttled(): void
    {
        for ($i = 0; $i < 120; $i++) {
            $this->get('/bukan-kampus')->assertNotFound();
        }

        $this->get('/bukan-kampus')->assertStatus(429);
    }

    public function test_every_first_segment_of_a_registered_route_is_rejected_as_a_campus_slug(): void
    {
        $admin = $this->makeUser('admin');

        $segments = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => explode('/', trim($route->uri(), '/'))[0])
            ->filter(fn ($segment) => $segment !== '' && !str_starts_with($segment, '{') && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $segment))
            ->unique()->values();

        $this->assertGreaterThan(10, $segments->count());

        foreach ($segments as $index => $segment) {
            $this->actingAs($admin)->post('/admin/campuses', [
                'name' => 'Kampus ' . $index, 'code' => 'T' . $index, 'slug' => $segment,
                'hero_headline' => 'Judul hero untuk uji rute', 'hero_subtitle' => 'Deskripsi hero untuk uji rute sistem.',
            ])->assertSessionHasErrors('slug');
        }

        $this->assertSame(2, Campus::count(), 'tidak ada kampus yang lolos dengan slug rute sistem');
    }

    // ── Reset password lewat jalur lama ────────────────────────────────────

    public function test_legacy_reset_password_covers_panitia_and_ends_sessions(): void
    {
        $operator = $this->makeUser('operator');
        $admin = $this->makeUser('admin');
        $panitia = $this->makeUser('panitia');
        $new = ['password' => 'Changed#12345', 'password_confirmation' => 'Changed#12345'];

        // Operator tidak boleh mereset akun panitia; itu urusan admin (Akun Staf).
        $this->actingAs($operator)->post("/admin/users/{$panitia->id}/reset-password", $new)->assertForbidden();
        $this->assertTrue(Hash::check('password-lama', $panitia->fresh()->password));

        DB::table('sessions')->insert([
            'id' => 'sesi-panitia-lama', 'user_id' => $panitia->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->post("/admin/users/{$panitia->id}/reset-password", $new)->assertSessionHas('success');

        $this->assertTrue(Hash::check('Changed#12345', $panitia->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-panitia-lama']);
    }
}
