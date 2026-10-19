<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\CampusSlugRedirect;
use App\Models\ReferralClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Review lanjutan: query array tidak boleh 500, dedupe klik afiliasi, dan slug lama kampus. */
class CampusLinkHardeningTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    private function cirebonPayload(array $extra = []): array
    {
        $crb = $this->crb();

        return array_merge([
            'name' => $crb->name, 'code' => 'CRB', 'is_active' => 1,
            'hero_headline' => $crb->hero_headline, 'hero_highlight' => $crb->hero_highlight, 'hero_subtitle' => $crb->hero_subtitle,
        ], $extra);
    }

    // ── Query berbentuk array ──────────────────────────────────────────────

    public function test_array_query_parameters_never_cause_a_500_on_public_pages(): void
    {
        $this->period();
        $program = $this->program();
        $referrer = $this->referrer();

        $this->get('/ref/' . $referrer->code . '?kampus[]=x')->assertRedirect(route('registration.create'));
        $this->get('/ref/' . $referrer->code . '?kampus[a]=x&kampus[b]=y')->assertRedirect(route('registration.create'));
        $this->get('/prodi/' . $program->slug . '?kampus[]=x')->assertOk();
        $this->get('/daftar?kampus[]=x&prodi[]=1')->assertOk();
        $this->get('/cirebon?x[]=1')->assertOk();
    }

    public function test_array_query_parameters_never_cause_a_500_on_admin_lists(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();

        foreach ([
            '/admin/akun-staf?role[]=x', '/admin/akun-staf?search[]=x', '/admin/akun-staf?campus[]=1',
            '/admin/programs?search[]=x', '/admin/programs?campus[]=1', '/admin/programs?per_page[]=5', '/admin/programs?per_page=abc',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    // ── Dedupe klik afiliasi ───────────────────────────────────────────────

    public function test_repeated_clicks_from_the_same_visitor_count_once_but_keep_the_cookie(): void
    {
        Cache::flush();
        $referrer = $this->referrer();

        for ($i = 0; $i < 5; $i++) {
            $this->get('/ref/' . $referrer->code . '?kampus=cirebon')
                ->assertRedirect(route('campus.landing', 'cirebon'))->assertCookie('ref');
        }

        $this->assertSame(1, (int) $referrer->fresh()->total_clicks);
        $this->assertSame(1, ReferralClick::count());
    }

    public function test_clicks_for_different_campuses_or_referrers_are_counted_separately(): void
    {
        Cache::flush();
        $first = $this->referrer();
        $second = $this->referrer();

        $this->get('/ref/' . $first->code . '?kampus=cirebon');
        $this->get('/ref/' . $first->code . '?kampus=majalengka');
        $this->get('/ref/' . $first->code);
        $this->get('/ref/' . $second->code . '?kampus=cirebon');

        $this->assertSame(3, (int) $first->fresh()->total_clicks);
        $this->assertSame(1, (int) $second->fresh()->total_clicks);

        // Setelah jendela dedupe lewat, klik berikutnya dihitung lagi.
        Cache::flush();
        $this->get('/ref/' . $first->code . '?kampus=cirebon');
        $this->assertSame(4, (int) $first->fresh()->total_clicks);
    }

    // ── Slug lama kampus ───────────────────────────────────────────────────

    public function test_renaming_a_campus_slug_keeps_old_links_working(): void
    {
        $this->period();
        $program = $this->program();
        $referrer = $this->referrer();
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->put('/admin/campuses/' . $this->crb()->id, $this->cirebonPayload(['slug' => 'cirebon-kota']))
            ->assertRedirect(route('admin.campuses.index'));

        $this->assertSame('cirebon-kota', $this->crb()->slug);
        $this->assertDatabaseHas('campus_slug_redirects', ['slug' => 'cirebon', 'campus_id' => $this->crb()->id]);

        auth()->logout();

        // Landing lama dialihkan permanen; landing baru terbuka.
        $this->get('/cirebon')->assertStatus(301)->assertRedirect(route('campus.landing', 'cirebon-kota'));
        $this->get('/cirebon-kota')->assertOk();

        // Tautan afiliasi lama tetap dikenali dan mencatat kampus yang benar.
        $this->get('/ref/' . $referrer->code . '?kampus=cirebon')->assertRedirect(route('campus.landing', 'cirebon-kota'));
        $this->assertSame($this->crb()->id, ReferralClick::first()->campus_id);
    }

    public function test_chained_renames_and_returning_to_an_old_slug(): void
    {
        $this->period();
        $admin = $this->makeUser('admin');
        $id = $this->crb()->id;

        $this->actingAs($admin)->put("/admin/campuses/{$id}", $this->cirebonPayload(['slug' => 'cirebon-kota']));
        $this->actingAs($admin)->put("/admin/campuses/{$id}", $this->cirebonPayload(['slug' => 'ypib-cirebon']));

        // Semua slug lama menunjuk ke slug terbaru.
        $this->get('/cirebon')->assertRedirect(route('campus.landing', 'ypib-cirebon'));
        $this->get('/cirebon-kota')->assertRedirect(route('campus.landing', 'ypib-cirebon'));

        // Kembali ke slug awal: slug itu tidak lagi menjadi alias, dan yang lain tetap mengalihkan.
        $this->actingAs($admin)->put("/admin/campuses/{$id}", $this->cirebonPayload(['slug' => 'cirebon']));
        $this->assertDatabaseMissing('campus_slug_redirects', ['slug' => 'cirebon']);
        $this->get('/cirebon')->assertOk();
        $this->get('/ypib-cirebon')->assertRedirect(route('campus.landing', 'cirebon'));
    }

    public function test_an_old_slug_of_another_campus_cannot_be_reused(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->put('/admin/campuses/' . $this->crb()->id, $this->cirebonPayload(['slug' => 'cirebon-kota']))->assertSessionHasNoErrors();

        // "cirebon" kini alias milik Cirebon: kampus baru tidak boleh mengambilnya.
        $this->actingAs($admin)->post('/admin/campuses', [
            'name' => 'Kampus Baru', 'code' => 'NEW', 'slug' => 'cirebon', 'is_active' => 1,
            'hero_headline' => 'Judul hero kampus baru', 'hero_subtitle' => 'Deskripsi hero kampus baru yang cukup panjang.',
        ])->assertSessionHasErrors('slug');

        $this->assertNull(Campus::where('code', 'NEW')->first());
    }

    public function test_old_slug_of_an_inactive_or_deleted_campus_is_404(): void
    {
        $this->period();
        CampusSlugRedirect::create(['campus_id' => $this->crb()->id, 'slug' => 'cirebon-lama']);

        $this->get('/cirebon-lama')->assertStatus(301);

        $this->crb()->update(['is_active' => false]);
        $this->get('/cirebon-lama')->assertNotFound();
        $this->get('/tidak-pernah-ada')->assertNotFound();
    }
}
