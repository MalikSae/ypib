<?php

namespace Tests\Feature\Pmb;

use App\Models\PaymentLog;
use App\Models\ReferralClick;
use App\Models\ReferrerLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class NumberingAndBackfillTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_letter_numbers_are_sequential_and_never_reused_after_soft_delete(): void
    {
        $panitia = $this->makeUser('panitia');
        $admin = $this->makeUser('admin');
        [, $first] = $this->student('menunggu_interview');
        [, $second] = $this->student('menunggu_interview');
        [, $third] = $this->student('menunggu_interview');

        $this->actingAs($panitia)->post("/panitia/scan/{$first->registration_number}/selesai", ['hasil' => 'diterima']);
        $firstNumber = $first->fresh()->letter_number;
        $this->assertStringStartsWith('001/', $firstNumber);

        $first->fresh()->delete();

        $this->actingAs($admin)->post("/admin/pendaftar/{$second->id}/override-status", ['hasil' => 'diterima', 'catatan' => 'uji']);
        $secondNumber = $second->fresh()->letter_number;
        $this->assertNotSame($firstNumber, $secondNumber);
        $this->assertStringStartsWith('002/', $secondNumber);

        $this->actingAs($panitia)->post("/panitia/scan/{$third->registration_number}/selesai", ['hasil' => 'diterima']);
        $this->assertStringStartsWith('003/', $third->fresh()->letter_number);
    }

    public function test_letter_number_uses_wib_month_and_year(): void
    {
        [, $registration] = $this->student('diterima');

        Carbon::setTestNow(Carbon::parse('2026-12-31 18:30:00', 'UTC'));   // 01:30 WIB, 1 Januari 2027
        $this->assertStringEndsWith('/I/2027', $registration->generateLetterNumber());
    }

    public function test_nim_follows_year_program_code_track_and_sequence(): void
    {
        $reguler = $this->program(['kode_prodi' => '12345', 'registration_track' => 'reguler']);
        $non = $this->program(['kode_prodi' => '12345', 'registration_track' => 'non_reguler']);
        $year = substr((string) $this->period()->year, -2);

        [, $a] = $this->student('menunggu_konfirmasi_daftar_ulang', ['first_choice_program_id' => $reguler->id]);
        [, $b] = $this->student('menunggu_konfirmasi_daftar_ulang', ['first_choice_program_id' => $non->id]);
        $admin = $this->makeUser('admin');

        $a->update([]);   // pastikan relasi dimuat segar
        foreach ([$a, $b] as $registration) {
            $registration->forceFill(['re_registration_payment_proof' => 'x.png'])->save();
            $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang")->assertSessionHas('success');
        }

        $nimA = $a->fresh()->nim;
        $nimB = $b->fresh()->nim;
        $this->assertStringStartsWith($year . '12345' . '1', $nimA);
        $this->assertStringStartsWith($year . '12345' . '2', $nimB);
        $this->assertNotSame(substr($nimA, -3), substr($nimB, -3), 'urutan harus berlanjut per tahun');
    }

    // ── registrations:backfill-numbers ────────────────────────────────
    public function test_backfill_dry_run_then_apply_is_sequential_and_idempotent(): void
    {
        $admin = $this->makeUser('admin');
        $withCode = $this->program(['kode_prodi' => '12345']);
        $noCode = $this->program(['kode_prodi' => null]);
        $year = substr((string) $this->period()->year, -2);

        [, $a] = $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $withCode->id]);
        [, $b] = $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $withCode->id]);
        [, $c] = $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $noCode->id]);
        [, $d] = $this->student('diterima', ['first_choice_program_id' => $withCode->id]);
        [, $e] = $this->student('menunggu_interview', ['first_choice_program_id' => $withCode->id]);
        [, $deleted] = $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $withCode->id]);
        $deleted->delete();
        [, $complete] = $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $withCode->id]);
        $complete->forceFill(['nim' => '26999001001', 'letter_number' => '001/UNIV-YPIB/SKL/I/2026'])->save();

        // simulasi
        $this->artisan('registrations:backfill-numbers')->assertSuccessful()->expectsOutputToContain('DRY-RUN');
        foreach ([$a, $b, $c, $d, $e] as $registration) {
            $this->assertNull($registration->fresh()->nim);
            $this->assertNull($registration->fresh()->letter_number);
        }
        $this->assertSame(0, PaymentLog::where('action', 'numbers_backfilled')->count());

        // --apply wajib pencatat audit yang valid
        $this->artisan('registrations:backfill-numbers', ['--apply' => true])->assertFailed();
        $this->artisan('registrations:backfill-numbers', ['--apply' => true, '--acted-by' => $a->user_id])->assertFailed();
        $this->assertNull($a->fresh()->nim);

        $this->artisan('registrations:backfill-numbers', ['--apply' => true, '--acted-by' => $admin->id, '--letter-date' => '2026-06-15'])->assertSuccessful();

        $this->assertSame($year . '12345' . '1' . '400', $a->fresh()->nim);
        $this->assertSame($year . '12345' . '1' . '401', $b->fresh()->nim);
        $this->assertNull($c->fresh()->nim, 'prodi tanpa kode tidak boleh mendapat NIM');
        $this->assertNull($d->fresh()->nim);
        $this->assertNull($e->fresh()->nim);
        $this->assertNull($e->fresh()->letter_number);

        $this->assertSame('002/UNIV-YPIB/SKL/VI/2026', $a->fresh()->letter_number);
        $this->assertSame('003/UNIV-YPIB/SKL/VI/2026', $b->fresh()->letter_number);
        $this->assertSame('004/UNIV-YPIB/SKL/VI/2026', $c->fresh()->letter_number);
        $this->assertSame('005/UNIV-YPIB/SKL/VI/2026', $d->fresh()->letter_number);

        $this->assertSame('26999001001', $complete->fresh()->nim);
        $this->assertNull(\App\Models\Registration::withTrashed()->find($deleted->id)->nim);
        $this->assertSame(4, PaymentLog::where('action', 'numbers_backfilled')->count());

        // idempoten, dan NIM terisi setelah Kode Prodi diisi
        $before = PaymentLog::where('action', 'numbers_backfilled')->count();
        $this->artisan('registrations:backfill-numbers', ['--apply' => true, '--acted-by' => $admin->id])->assertSuccessful();
        $this->assertSame($before, PaymentLog::where('action', 'numbers_backfilled')->count());

        $noCode->update(['kode_prodi' => '54321']);
        $this->artisan('registrations:backfill-numbers', ['--apply' => true, '--acted-by' => $admin->id])->assertSuccessful();
        $this->assertSame($year . '54321' . '1' . '402', $c->fresh()->nim);
    }

    public function test_backfill_validates_options_and_reports_nothing_to_do(): void
    {
        $this->artisan('registrations:backfill-numbers', ['--letter-date' => '15/06/2026'])->assertFailed();
        $this->artisan('registrations:backfill-numbers')->assertSuccessful()->expectsOutputToContain('Tidak ada pendaftar');
    }

    // ── referrers:recount ─────────────────────────────────────────────
    public function test_recount_reconciles_counters_from_rewards_and_clicks(): void
    {
        $admin = $this->makeUser('admin');
        $correct = $this->referrer(['total_conversions' => 1, 'total_clicks' => 2]);
        $low = $this->referrer(['total_conversions' => 0, 'total_clicks' => 0]);
        $high = $this->referrer(['total_conversions' => 5, 'total_clicks' => 9]);
        $negative = $this->referrer(['total_conversions' => -1, 'total_clicks' => 0]);

        [, $r1] = $this->student('terdaftar', ['referrer_id' => $correct->id]);
        $this->reward($correct, $r1, 'approved');
        $this->reward($correct, $r1, 'approved', 're_registration');   // tipe lain tidak dihitung ganda

        [, $r2] = $this->student('terdaftar', ['referrer_id' => $low->id]);
        [, $r3] = $this->student('terdaftar', ['referrer_id' => $low->id]);
        $this->reward($low, $r2, 'disbursed');
        $this->reward($low, $r3, 'approved');

        [, $r4] = $this->student('terdaftar', ['referrer_id' => $high->id]);
        $this->reward($high, $r4, 'cancelled');                         // dibatalkan: tidak dihitung
        [, $r5] = $this->student('terdaftar', ['referrer_id' => $high->id]);
        $this->reward($high, $r5, 'approved');
        $r5->delete();                                                  // soft-delete: tidak dihitung

        ReferralClick::forceCreate(['referrer_id' => $correct->id, 'converted' => false]);
        ReferralClick::forceCreate(['referrer_id' => $correct->id, 'converted' => true]);
        ReferralClick::forceCreate(['referrer_id' => $low->id, 'converted' => false]);

        $counters = fn () => array_map(fn ($r) => (int) $r->fresh()->total_conversions, [$correct, $low, $high, $negative]);

        $this->artisan('referrers:recount', ['--clicks' => true])->assertSuccessful()->expectsOutputToContain('DRY-RUN');
        $this->assertSame([1, 0, 5, -1], $counters());
        $this->assertSame(0, ReferrerLog::where('action', 'counter_recounted')->count());

        $this->artisan('referrers:recount', ['--apply' => true])->assertFailed();

        $this->artisan('referrers:recount', ['--apply' => true, '--acted-by' => $admin->id])->assertSuccessful();
        $this->assertSame([1, 2, 0, 0], $counters());
        $this->assertSame(9, (int) $high->fresh()->total_clicks, 'klik tidak boleh berubah tanpa --clicks');
        $this->assertSame(3, ReferrerLog::where('action', 'counter_recounted')->count());

        $this->artisan('referrers:recount', ['--apply' => true, '--acted-by' => $admin->id])->assertSuccessful();
        $this->assertSame(3, ReferrerLog::where('action', 'counter_recounted')->count(), 'run kedua menulis ulang');

        $this->artisan('referrers:recount', ['--apply' => true, '--acted-by' => $admin->id, '--clicks' => true])->assertSuccessful();
        $this->assertSame([2, 1, 0], [(int) $correct->fresh()->total_clicks, (int) $low->fresh()->total_clicks, (int) $high->fresh()->total_clicks]);
    }
}
