<?php

namespace Tests\Feature\Prospek;

use App\Livewire\Admin\ProspectList;
use App\Models\Campus;
use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\ProspectSource;
use App\Models\Referrer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** Daftar Kelola Prospek (Livewire): tab status, cari, filter event dan affiliator, urutan. */
class ProspectListTest extends TestCase
{
    use RefreshDatabase;

    private Campus $campus;
    private User $admin;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Kampus Uji', 'code' => 'UJI', 'slug' => 'uji', 'city' => 'Majalengka',
            'admin_whatsapp' => '081200000001', 'is_active' => true,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeProspect(string $name, array $overrides = []): Prospect
    {
        $phone = '0812000000' . str_pad((string) ++$this->seq, 2, '0', STR_PAD_LEFT);

        $prospect = new Prospect();
        $prospect->forceFill(array_merge([
            'full_name' => $name, 'phone' => $phone, 'phone_normalized' => $phone, 'school_name' => 'SMA Uji',
            'campus_id' => $this->campus->id, 'status' => Prospect::STATUS_BARU,
        ], $overrides))->save();

        return $prospect;
    }

    private function makeEvent(string $name): ProspectEvent
    {
        return ProspectEvent::create([
            'campus_id' => $this->campus->id, 'kind' => ProspectEvent::KIND_EVENT, 'name' => $name,
            'slug' => 'event-' . uniqid(), 'discount_amount' => 0, 'is_active' => true,
        ]);
    }

    private function makeReferrer(string $code): Referrer
    {
        return Referrer::create([
            'user_id' => User::factory()->create(['name' => 'Affiliator ' . $code])->id, 'code' => $code, 'status' => 'active',
        ]);
    }

    public function test_page_renders_the_list_without_apply_button(): void
    {
        $this->makeProspect('Siti Aminah');

        $this->actingAs($this->admin)->get(route('admin.prospects.index'))
            ->assertOk()
            ->assertSeeLivewire(ProspectList::class)
            ->assertSee('Siti Aminah')
            ->assertDontSee('Terapkan');
    }

    public function test_status_tab_filters_and_counts_follow_search(): void
    {
        $this->makeProspect('Siti Aminah');
        $this->makeProspect('Siti Badriah', ['status' => Prospect::STATUS_TERTARIK, 'last_contacted_at' => now()]);
        $this->makeProspect('Budi Santoso');

        Livewire::actingAs($this->admin)->test(ProspectList::class)
            ->assertViewHas('total', 3)
            ->set('q', 'Siti')
            ->assertViewHas('total', 2)
            ->assertViewHas('counts', fn ($counts) => $counts[Prospect::STATUS_BARU] === 1 && $counts[Prospect::STATUS_TERTARIK] === 1)
            ->assertSee('Siti Aminah')->assertSee('Siti Badriah')->assertDontSee('Budi Santoso')
            ->call('setStatus', Prospect::STATUS_TERTARIK)
            ->assertSee('Siti Badriah')->assertDontSee('Siti Aminah')
            // Angka tab tidak ikut tab status yang dipilih.
            ->assertViewHas('total', 2)
            ->call('setStatus', 'bukan-status')
            ->assertSet('status', '');
    }

    public function test_search_matches_school_and_whatsapp(): void
    {
        $this->makeProspect('Siti Aminah', ['school_name' => 'SMK Harapan']);
        $target = $this->makeProspect('Budi Santoso');

        Livewire::actingAs($this->admin)->test(ProspectList::class)
            ->set('q', 'Harapan')->assertSee('Siti Aminah')->assertDontSee('Budi Santoso')
            ->set('q', $target->phone_normalized)->assertSee('Budi Santoso')->assertDontSee('Siti Aminah');
    }

    public function test_event_and_affiliator_filters_come_from_the_url(): void
    {
        $event = $this->makeEvent('Edu Expo');
        $owner = $this->makeReferrer('AAAAAA');

        $inEvent = $this->makeProspect('Siti Aminah');
        ProspectSource::create(['prospect_id' => $inEvent->id, 'prospect_event_id' => $event->id, 'created_at' => now()]);
        $this->makeProspect('Budi Santoso', ['referrer_id' => $owner->id]);

        Livewire::actingAs($this->admin)->withQueryParams(['event' => $event->id])->test(ProspectList::class)
            ->assertSee('Siti Aminah')->assertDontSee('Budi Santoso');

        Livewire::actingAs($this->admin)->withQueryParams(['referrer' => $owner->id])->test(ProspectList::class)
            ->assertSee('Budi Santoso')->assertDontSee('Siti Aminah')
            ->assertSee('Affiliator AAAAAA (AAAAAA)')
            ->call('clearReferrer')
            ->assertSet('referrer', '')
            ->assertSee('Siti Aminah');
    }

    public function test_clear_filters_keeps_the_status_tab(): void
    {
        $this->makeProspect('Siti Aminah');

        Livewire::actingAs($this->admin)->test(ProspectList::class)
            ->set('q', 'tidak ada')
            ->call('setStatus', Prospect::STATUS_BARU)
            ->assertSee('Tidak ada prospek yang cocok.')
            ->call('clearFilters')
            ->assertSet('q', '')
            ->assertSet('status', Prospect::STATUS_BARU)
            ->assertSee('Siti Aminah');
    }

    public function test_list_puts_prospects_needing_contact_first(): void
    {
        $this->makeProspect('Sudah Daftar', ['status' => Prospect::STATUS_SUDAH_DAFTAR, 'created_at' => now()->subDays(30)]);
        $this->makeProspect('Baru Kemarin', ['created_at' => now()->subDay()]);
        $this->makeProspect('Dihubungi Baru Saja', ['status' => Prospect::STATUS_DIHUBUNGI, 'last_contacted_at' => now()->subHour(), 'created_at' => now()->subDays(20)]);
        $this->makeProspect('Baru Seminggu', ['created_at' => now()->subWeek()]);
        $this->makeProspect('Dihubungi Lama', ['status' => Prospect::STATUS_TERTARIK, 'last_contacted_at' => now()->subDays(5), 'created_at' => now()->subDays(9)]);

        Livewire::actingAs($this->admin)->test(ProspectList::class)
            ->assertViewHas('prospects', fn ($prospects) => $prospects->pluck('full_name')->all() === [
                'Baru Seminggu', 'Baru Kemarin', 'Dihubungi Lama', 'Dihubungi Baru Saja', 'Sudah Daftar',
            ]);
    }

    public function test_non_staff_cannot_use_the_component(): void
    {
        Livewire::actingAs(User::factory()->create(['role' => 'mahasiswa']))->test(ProspectList::class)->assertForbidden();
    }

    public function test_affiliate_list_and_prospect_detail_link_to_filtered_prospects(): void
    {
        $owner = $this->makeReferrer('AAAAAA');
        $prospect = $this->makeProspect('Budi Santoso', ['referrer_id' => $owner->id]);
        $url = route('admin.prospects.index', ['referrer' => $owner->id]);

        $this->actingAs($this->admin)->get(route('admin.referrers.index'))->assertOk()->assertSee('Prospek')->assertSee($url, false);
        $this->actingAs($this->admin)->get(route('admin.prospects.show', $prospect))->assertOk()->assertSee($url, false);
    }
}
