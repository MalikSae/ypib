<?php

namespace Tests\Feature\Prospek;

use App\Models\Campus;
use App\Models\PmbPeriod;
use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\ProspectLink;
use App\Models\ProspectSource;
use App\Models\Referrer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): ProspectEvent
    {
        $campus = Campus::create([
            'name' => 'Kampus Uji', 'code' => 'UJI', 'slug' => 'uji', 'city' => 'Majalengka',
            'admin_whatsapp' => '081200000001', 'is_active' => true,
        ]);

        $period = PmbPeriod::create([
            'name' => 'Periode Uji', 'year' => 2027,
            'open_date' => now()->subDay()->toDateString(),
            'close_date' => now()->addMonth()->toDateString(),
            'is_active' => true, 'status' => PmbPeriod::STATUS_PUBLISHED,
        ]);

        return ProspectEvent::create(array_merge([
            'campus_id' => $campus->id,
            'kind' => ProspectEvent::KIND_ONLINE,
            'period_id' => $period->id,
            'name' => 'Sosialisasi Uji',
            'slug' => 'sosialisasi-uji-' . uniqid(),
            'discount_amount' => 500000,
            'is_active' => true,
        ], $overrides));
    }

    private function makeReferrer(string $code, string $status = 'active'): Referrer
    {
        $user = User::factory()->create(['phone' => '081355555555']);

        return Referrer::create(['user_id' => $user->id, 'code' => $code, 'status' => $status]);
    }

    public function test_phone_normalization_accepts_common_formats(): void
    {
        $this->assertSame('081234567890', Prospect::normalizePhone('+62 812-3456-7890'));
        $this->assertSame('081234567890', Prospect::normalizePhone('6281234567890'));
        $this->assertSame('081234567890', Prospect::normalizePhone('081234567890'));
        $this->assertNull(Prospect::normalizePhone('123'));
    }

    public function test_submit_creates_prospect_source_and_link_with_first_touch_owner(): void
    {
        $event = $this->makeEvent();
        $owner = $this->makeReferrer('AAAAAA');

        $this->post(route('prospect.store', [$event->slug, 'ref' => $owner->code]), [
            'full_name' => 'Siswa Uji',
            'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma',
            'phone' => '0812-3456-7890',
            'program_id' => '',
        ])->assertRedirectContains('https://wa.me/6281200000001');

        $prospect = Prospect::where('phone_normalized', '081234567890')->firstOrFail();
        $this->assertSame($owner->id, $prospect->referrer_id);
        $this->assertSame(Prospect::STATUS_BARU, $prospect->status);
        $this->assertSame(1, ProspectSource::where('prospect_id', $prospect->id)->count());
        $this->assertSame(1, ProspectLink::where('prospect_id', $prospect->id)->count());
    }

    public function test_second_interest_from_other_referrer_does_not_move_ownership(): void
    {
        $event = $this->makeEvent();
        $first = $this->makeReferrer('AAAAAA');
        $second = $this->makeReferrer('BBBBBB');

        $this->post(route('prospect.store', [$event->slug, 'ref' => $first->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);
        $this->post(route('prospect.store', [$event->slug, 'ref' => $second->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);

        $prospect = Prospect::where('phone_normalized', '081234567890')->firstOrFail();
        $this->assertSame($first->id, $prospect->referrer_id);
        // Satu sumber per (prospek, event): submit kedua di event yang sama tidak menambah baris.
        $this->assertSame(1, ProspectSource::where('prospect_id', $prospect->id)->count());
    }

    public function test_event_kind_ignores_affiliator_ref(): void
    {
        $event = $this->makeEvent(['kind' => ProspectEvent::KIND_EVENT]);
        $owner = $this->makeReferrer('AAAAAA');

        $this->post(route('prospect.store', [$event->slug, 'ref' => $owner->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);

        $this->assertNull(Prospect::where('phone_normalized', '081234567890')->firstOrFail()->referrer_id);
    }

    public function test_inactive_referrer_code_is_ignored_for_ownership(): void
    {
        $event = $this->makeEvent();
        $inactive = $this->makeReferrer('CCCCCC', 'inactive');

        $this->post(route('prospect.store', [$event->slug, 'ref' => $inactive->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);

        $this->assertNull(Prospect::where('phone_normalized', '081234567890')->firstOrFail()->referrer_id);
    }

    public function test_link_opens_once_and_is_refused_after_use(): void
    {
        $event = $this->makeEvent();
        $owner = $this->makeReferrer('AAAAAA');

        $this->post(route('prospect.store', [$event->slug, 'ref' => $owner->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);

        $link = ProspectLink::firstOrFail();

        $this->get(route('prospect.link', $link->token))
            ->assertRedirect(route('registration.create'));
        $this->assertSame($link->token, session('prospect_token'));

        $link->forceFill(['used_at' => now()])->save();

        $this->get(route('prospect.link', $link->token))
            ->assertRedirect(route('landing'));
    }

    public function test_public_form_renders_for_active_event(): void
    {
        $event = $this->makeEvent();

        $this->get(route('prospect.show', $event->slug) . '?ref=AAAAAA')
            ->assertOk()
            ->assertSee('Form Minat')
            ->assertSee('Hemat Rp 500.000 saat daftar ulang');
    }

    public function test_admin_prospect_pages_render(): void
    {
        $event = $this->makeEvent();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.prospects.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.prospects.events.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.prospects.events.edit', $event))->assertOk();
    }

    public function test_referrer_dashboard_renders_prospects_section(): void
    {
        $event = $this->makeEvent();
        $owner = $this->makeReferrer('AAAAAA');
        $owner->user->forceFill(['is_referrer' => true])->save();

        $this->post(route('prospect.store', [$event->slug, 'ref' => $owner->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);

        $this->actingAs($owner->user)->get(route('referrer.dashboard'))
            ->assertOk()
            ->assertSee('Prospek dari Form Minat')
            ->assertSee('0812xxxxxx90', false)
            ->assertDontSee('081234567890');
    }

    public function test_admin_follow_up_logs_contact_note_and_status_change(): void
    {
        $event = $this->makeEvent();
        $owner = $this->makeReferrer('AAAAAA');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post(route('prospect.store', [$event->slug, 'ref' => $owner->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);
        $prospect = Prospect::where('phone_normalized', '081234567890')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.prospects.contact', $prospect), ['note' => 'Minta info biaya', 'dihubungi' => 1])
            ->assertSessionHas('success');

        $prospect->refresh();
        $this->assertSame(Prospect::STATUS_DIHUBUNGI, $prospect->status);
        $this->assertNotNull($prospect->last_contacted_at);

        // Catatan saja (tanpa centang) tidak mengubah status.
        $this->actingAs($admin)->post(route('admin.prospects.contact', $prospect), ['note' => 'Akan konfirmasi minggu depan']);
        $this->actingAs($admin)->post(route('admin.prospects.contact', $prospect), ['note' => '']) // kosong tanpa centang ditolak
            ->assertSessionHasErrors('note');

        $this->actingAs($admin)->post(route('admin.prospects.update-status', $prospect), ['status' => Prospect::STATUS_TERTARIK]);

        $types = $prospect->activities()->pluck('type')->all();
        $this->assertEqualsCanonicalizing(['kontak', 'catatan', 'status'], $types);

        $this->actingAs($admin)->get(route('admin.prospects.show', $prospect))
            ->assertOk()
            ->assertSee('Minta info biaya');
    }

    public function test_account_signup_from_link_uses_prospect_name_and_phone(): void
    {
        $event = $this->makeEvent();

        $this->post(route('prospect.store', [$event->slug]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);
        $this->get(route('prospect.link', ProspectLink::firstOrFail()->token));

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Siswa Uji')
            ->assertSee('Bukan kamu?');

        // Input klien diabaikan: akun memakai nama dan WA prospek.
        $this->post(route('register'), [
            'name' => 'Orang Lain', 'email' => 'lain@example.test', 'phone' => '089999999999',
            'password' => 'RahasiaUji2026!', 'password_confirmation' => 'RahasiaUji2026!',
        ]);

        $user = User::where('email', 'lain@example.test')->firstOrFail();
        $this->assertSame('Siswa Uji', $user->name);
        $this->assertSame('081234567890', $user->phone);
    }

    public function test_release_drops_prospect_link_from_session(): void
    {
        $event = $this->makeEvent();

        $this->post(route('prospect.store', [$event->slug]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);
        $this->get(route('prospect.link', ProspectLink::firstOrFail()->token));
        $this->assertNotNull(session('prospect_token'));

        $this->post(route('prospect.release'))->assertSessionMissing('prospect_token');

        $this->get(route('register'))->assertOk()->assertDontSee('Bukan kamu?');
    }

    public function test_ownerless_prospect_is_not_claimed_by_later_affiliate(): void
    {
        $campusEvent = $this->makeEvent(['kind' => ProspectEvent::KIND_EVENT]);
        $online = ProspectEvent::create([
            'campus_id' => $campusEvent->campus_id, 'period_id' => $campusEvent->period_id, 'kind' => ProspectEvent::KIND_ONLINE,
            'name' => 'Kampanye', 'slug' => 'kampanye-' . uniqid(), 'discount_amount' => 200000, 'is_active' => true,
        ]);
        $affiliate = $this->makeReferrer('AAAAAA');
        $data = ['full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890'];

        $this->post(route('prospect.store', [$campusEvent->slug]), $data);
        $this->post(route('prospect.store', [$online->slug, 'ref' => $affiliate->code]), $data);

        $this->assertNull(Prospect::where('phone_normalized', '081234567890')->firstOrFail()->referrer_id);
    }

    public function test_link_snapshots_discount_and_new_event_replaces_old_link(): void
    {
        $first = $this->makeEvent(['discount_amount' => 500000]);
        $second = ProspectEvent::create([
            'campus_id' => $first->campus_id, 'period_id' => $first->period_id, 'kind' => ProspectEvent::KIND_EVENT,
            'name' => 'Event Kedua', 'slug' => 'event-kedua-' . uniqid(), 'discount_amount' => 300000, 'is_active' => true,
        ]);
        $data = ['full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890'];

        $this->post(route('prospect.store', [$first->slug]), $data);
        $firstLink = ProspectLink::firstOrFail();
        $this->assertSame(500000, $firstLink->discount_amount);

        // Potongan event diubah setelah link dibuat: link lama tetap memakai snapshot.
        $first->update(['discount_amount' => 100000]);
        $this->assertSame(500000, $firstLink->fresh()->discount_amount);

        // Form minat dari event lain: link lama ditutup, link baru memakai potongan event kedua.
        $this->post(route('prospect.store', [$second->slug]), $data);
        $this->assertFalse($firstLink->fresh()->isUsable());
        $newLink = ProspectLink::where('id', '!=', $firstLink->id)->firstOrFail();
        $this->assertTrue($newLink->isUsable());
        $this->assertSame(300000, $newLink->discount_amount);
    }

    public function test_affiliate_dashboard_is_read_only_for_prospects(): void
    {
        $event = $this->makeEvent();
        $owner = $this->makeReferrer('AAAAAA');
        $owner->user->forceFill(['is_referrer' => true])->save();

        $this->post(route('prospect.store', [$event->slug, 'ref' => $owner->code]), [
            'full_name' => 'Siswa Uji', 'school_name' => 'SMA Uji', 'education_level' => 'sma_smk_ma', 'phone' => '081234567890',
        ]);
        $prospect = Prospect::where('phone_normalized', '081234567890')->firstOrFail();

        $this->actingAs($owner->user)->get(route('referrer.dashboard'))
            ->assertOk()
            ->assertSee('Siswa Uji')
            ->assertSee('Baru')
            ->assertDontSee('Tandai sudah dihubungi')
            ->assertDontSee('Catatan hasil kontak');

        // Tidak ada endpoint bagi affiliator untuk mengubah prospek.
        $this->actingAs($owner->user)->post('/afiliasi/prospek/' . $prospect->id . '/dihubungi')->assertNotFound();
        $this->assertSame(Prospect::STATUS_BARU, $prospect->fresh()->status);
    }

    public function test_closed_event_refuses_interest_form(): void
    {
        $event = $this->makeEvent(['is_active' => false]);

        $this->get(route('prospect.show', $event->slug))->assertRedirect(route('landing'));
    }
}
