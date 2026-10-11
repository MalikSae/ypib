<?php

namespace Tests\Feature\Pmb;

use App\Models\PmbPeriod;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T1 (PLAN_MULTI_PERIODE.md): status periode, resolver, fase dari tanggal,
 * sinkronisasi `is_active`, serta unique `active_key` dan `nim`.
 */
class PeriodLifecycleTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function newPeriod(array $attributes = []): PmbPeriod
    {
        $n = $this->nextSeq();

        return PmbPeriod::forceCreate(array_merge([
            'name'       => 'Periode Uji ' . $n,
            'year'       => 2026,
            'open_date'  => '2026-03-01',
            'close_date' => '2026-03-31',
        ], $attributes));
    }

    // ── Sinkronisasi status <-> is_active ────────────────────────────────

    public function test_writing_only_is_active_true_publishes_the_period(): void
    {
        $period = $this->newPeriod(['is_active' => true]);

        $this->assertSame('published', $period->fresh()->status);
        $this->assertTrue($period->fresh()->is_active);
        $this->assertNotNull($period->fresh()->published_at);
    }

    public function test_writing_only_is_active_false_makes_a_draft(): void
    {
        $period = $this->newPeriod(['is_active' => false]);

        $this->assertSame('draft', $period->fresh()->status);
        $this->assertFalse($period->fresh()->is_active);
    }

    public function test_changing_status_drives_is_active_and_timestamps(): void
    {
        $period = $this->newPeriod();
        $this->assertSame('draft', $period->fresh()->status);
        $this->assertFalse($period->fresh()->is_active);

        $period->update(['status' => 'published']);
        $this->assertTrue($period->fresh()->is_active);
        $this->assertNotNull($period->fresh()->published_at);

        $period->update(['status' => 'archived']);
        $this->assertFalse($period->fresh()->is_active);
        $this->assertNotNull($period->fresh()->archived_at);
    }

    public function test_is_active_write_does_not_revive_an_archived_period(): void
    {
        $period = $this->newPeriod(['status' => 'archived']);

        $period->update(['is_active' => true]);

        $this->assertSame('archived', $period->fresh()->status);
        $this->assertFalse($period->fresh()->is_active);
    }

    public function test_status_wins_when_both_attributes_change(): void
    {
        $period = $this->newPeriod(['status' => 'published']);

        $period->update(['status' => 'draft', 'is_active' => true]);

        $this->assertSame('draft', $period->fresh()->status);
        $this->assertFalse($period->fresh()->is_active);
    }

    public function test_active_scope_returns_only_published_periods(): void
    {
        $this->newPeriod(['status' => 'draft']);
        $this->newPeriod(['status' => 'archived']);
        $published = $this->newPeriod(['status' => 'published']);

        $this->assertSame([$published->id], PmbPeriod::active()->pluck('id')->all());
    }

    // ── Jendela waktu (WIB) ──────────────────────────────────────────────

    public function test_registration_window_is_inclusive_through_the_end_of_close_date(): void
    {
        $period = $this->newPeriod(['status' => 'published']);

        Carbon::setTestNow(Carbon::parse('2026-03-31 23:59:00', 'Asia/Jakarta'));
        $this->assertTrue($period->fresh()->isOpen());

        Carbon::setTestNow(Carbon::parse('2026-04-01 00:00:00', 'Asia/Jakarta'));
        $this->assertFalse($period->fresh()->isOpen());
        $this->assertTrue($period->fresh()->isInProgress());

        Carbon::setTestNow(Carbon::parse('2026-02-28 23:59:00', 'Asia/Jakarta'));
        $this->assertFalse($period->fresh()->isOpen());
    }

    public function test_unpublished_periods_are_never_open(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-10 10:00:00', 'Asia/Jakarta'));

        $this->assertFalse($this->newPeriod(['status' => 'draft'])->isOpen());
        $this->assertFalse($this->newPeriod(['status' => 'archived'])->isOpen());
    }

    public function test_payment_window_runs_through_the_last_grace_day(): void
    {
        $period = $this->newPeriod(['status' => 'published', 'payment_grace_days' => 3]);

        Carbon::setTestNow(Carbon::parse('2026-04-03 23:59:00', 'Asia/Jakarta'));
        $this->assertTrue($period->fresh()->isPaymentOpen());

        Carbon::setTestNow(Carbon::parse('2026-04-04 00:00:00', 'Asia/Jakarta'));
        $this->assertFalse($period->fresh()->isPaymentOpen());
    }

    public function test_re_registration_window_follows_the_deadline_and_open_when_empty(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00', 'Asia/Jakarta'));

        $noDeadline = $this->newPeriod(['status' => 'published']);
        $future = $this->newPeriod(['status' => 'published', 're_registration_deadline' => '2026-06-15']);
        $past = $this->newPeriod(['status' => 'published', 're_registration_deadline' => '2026-06-14']);
        $archived = $this->newPeriod(['status' => 'archived', 're_registration_deadline' => '2026-12-31']);

        $this->assertTrue($noDeadline->isReRegistrationOpen());
        $this->assertTrue($future->isReRegistrationOpen());
        $this->assertFalse($past->isReRegistrationOpen());
        $this->assertFalse($archived->isReRegistrationOpen());
    }

    // ── Resolver periode ─────────────────────────────────────────────────

    public function test_current_is_null_outside_any_registration_window(): void
    {
        $this->newPeriod(['status' => 'published']);

        Carbon::setTestNow(Carbon::parse('2026-02-01 10:00:00', 'Asia/Jakarta'));
        $this->assertNull(PmbPeriod::current());

        Carbon::setTestNow(Carbon::parse('2026-05-01 10:00:00', 'Asia/Jakarta'));
        $this->assertNull(PmbPeriod::current());
    }

    public function test_display_period_prefers_the_open_period_over_an_older_in_progress_one(): void
    {
        $old = $this->newPeriod([
            'name' => 'Tahun Lalu', 'year' => 2025, 'status' => 'published',
            'open_date' => '2025-03-01', 'close_date' => '2025-03-31',
        ]);
        $new = $this->newPeriod([
            'name' => 'Tahun Ini', 'status' => 'published',
            'open_date' => '2026-03-01', 'close_date' => '2026-03-31',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-03-10 10:00:00', 'Asia/Jakarta'));

        $this->assertSame($new->id, PmbPeriod::current()->id);
        $this->assertSame($new->id, PmbPeriod::displayPeriod()->id);
        $this->assertNotSame($old->id, PmbPeriod::displayPeriod()->id);
    }

    public function test_display_period_falls_back_to_the_latest_published_period(): void
    {
        $this->newPeriod(['name' => 'Lama', 'year' => 2025, 'status' => 'published', 'open_date' => '2025-03-01', 'close_date' => '2025-03-31']);
        $latest = $this->newPeriod(['name' => 'Terbaru', 'status' => 'published', 'open_date' => '2026-03-01', 'close_date' => '2026-03-31']);
        $this->newPeriod(['name' => 'Arsip', 'year' => 2027, 'status' => 'archived', 'open_date' => '2027-03-01', 'close_date' => '2027-03-31']);

        Carbon::setTestNow(Carbon::parse('2026-08-01 10:00:00', 'Asia/Jakarta'));

        $this->assertNull(PmbPeriod::current());
        $this->assertSame($latest->id, PmbPeriod::displayPeriod()->id);
    }

    public function test_display_period_is_null_without_a_published_period(): void
    {
        $this->newPeriod(['status' => 'draft']);
        $this->newPeriod(['status' => 'archived']);

        $this->assertNull(PmbPeriod::displayPeriod());
    }

    public function test_landing_shows_the_open_period_when_an_older_one_is_still_published(): void
    {
        $this->newPeriod([
            'name' => 'PMB Tahun Lalu', 'year' => 2025, 'status' => 'published',
            'open_date' => '2025-03-01', 'close_date' => '2025-03-31',
        ]);
        $this->newPeriod([
            'name' => 'PMB Tahun Ini', 'status' => 'published',
            'open_date' => '2026-03-01', 'close_date' => '2026-03-31',
        ]);
        $this->program();

        Carbon::setTestNow(Carbon::parse('2026-03-10 10:00:00', 'Asia/Jakarta'));

        $this->get('/')->assertOk()->assertSee('PMB UNIVERSITAS YPIB 2026')->assertDontSee('PMB UNIVERSITAS YPIB 2025');
    }

    // ── Unique multi-periode ─────────────────────────────────────────────

    public function test_one_active_registration_per_account_per_period(): void
    {
        $this->period();
        $user = $this->makeUser();
        $this->registration($user, 'menunggu_pembayaran');

        $this->expectException(QueryException::class);
        $this->registration($user, 'menunggu_pembayaran');
    }

    public function test_soft_deleted_registration_does_not_block_a_new_one_in_the_same_period(): void
    {
        $this->period();
        $user = $this->makeUser();
        $first = $this->registration($user, 'ditolak');
        $first->delete();

        $second = $this->registration($user, 'menunggu_pembayaran');

        $this->assertNotNull($second->id);
    }

    public function test_same_account_can_register_in_different_periods(): void
    {
        $periodA = $this->period();
        $periodB = $this->newPeriod(['status' => 'published', 'year' => 2027, 'open_date' => '2027-03-01', 'close_date' => '2027-03-31']);
        $user = $this->makeUser();

        $a = $this->registration($user, 'ditolak', ['period_id' => $periodA->id]);
        $b = $this->registration($user, 'menunggu_pembayaran', ['period_id' => $periodB->id]);

        $this->assertNotSame($a->period_id, $b->period_id);
    }

    public function test_nim_is_unique_across_registrations(): void
    {
        [, $first] = $this->student('daftar_ulang_selesai', ['nim' => '26123451001']);

        $this->expectException(QueryException::class);
        $this->student('daftar_ulang_selesai', ['nim' => '26123451001']);
    }

    public function test_many_registrations_without_a_nim_are_allowed(): void
    {
        $this->student('menunggu_pembayaran');
        $this->student('menunggu_pembayaran');

        $this->assertSame(2, \App\Models\Registration::whereNull('nim')->count());
    }

    // ── Pra-pemeriksaan ──────────────────────────────────────────────────

    public function test_precheck_command_passes_on_clean_data(): void
    {
        $this->student('menunggu_pembayaran');

        $this->artisan('periods:precheck')->assertExitCode(0);
    }
}
