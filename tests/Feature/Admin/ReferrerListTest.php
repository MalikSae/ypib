<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\ReferrerList;
use App\Models\Referrer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Data Afiliasi (Livewire): cari, urutan kolom, jumlah per halaman, dan akses. */
class ReferrerListTest extends TestCase
{
    use RefreshDatabase;

    private function makeReferrer(string $name, string $code, int $clicks): Referrer
    {
        return Referrer::create([
            'user_id' => User::factory()->create(['name' => $name, 'email' => strtolower($code) . '@contoh.test'])->id,
            'code' => $code, 'status' => 'active', 'total_clicks' => $clicks,
        ]);
    }

    public function test_page_renders_component_without_submit_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->makeReferrer('Budi Santoso', 'BUDI01', 5);

        $this->actingAs($admin)->get(route('admin.referrers.index'))
            ->assertOk()
            ->assertSeeLivewire(ReferrerList::class)
            ->assertSee('Budi Santoso');
    }

    public function test_search_sort_and_per_page_apply_live(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->makeReferrer('Budi Santoso', 'BUDI01', 5);
        $this->makeReferrer('Siti Aminah', 'SITI02', 40);

        Livewire::actingAs($admin)->test(ReferrerList::class)
            ->set('search', 'siti')
            ->assertSee('Siti Aminah')->assertDontSee('Budi Santoso')
            ->set('search', 'BUDI01')
            ->assertSee('Budi Santoso')->assertDontSee('Siti Aminah')
            ->set('search', '')
            ->call('sortBy', 'klik')
            ->assertSet('sort', 'klik')->assertSet('dir', 'desc')
            ->assertSeeInOrder(['Siti Aminah', 'Budi Santoso'])
            ->call('sortBy', 'klik')
            ->assertSet('dir', 'asc')
            ->assertSeeInOrder(['Budi Santoso', 'Siti Aminah'])
            ->call('sortBy', 'kolom-tidak-ada')
            ->assertSet('sort', 'klik')
            ->set('perPage', 5)
            ->assertSet('perPage', 5);
    }

    public function test_campus_scoped_admin_cannot_use_the_component(): void
    {
        $campusAdmin = User::factory()->create(['role' => 'admin', 'campus_id' => \App\Models\Campus::create([
            'name' => 'Kampus Uji', 'code' => 'UJI', 'slug' => 'uji', 'city' => 'Majalengka', 'is_active' => true,
        ])->id]);

        Livewire::actingAs($campusAdmin)->test(ReferrerList::class)->assertForbidden();
        Livewire::actingAs(User::factory()->create(['role' => 'mahasiswa']))->test(ReferrerList::class)->assertForbidden();
    }
}
