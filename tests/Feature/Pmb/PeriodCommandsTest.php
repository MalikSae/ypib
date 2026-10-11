<?php

namespace Tests\Feature\Pmb;

use App\Models\PaymentLog;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T8 (PLAN_MULTI_PERIODE.md): periods:assign dan periods:readiness.
 */
class PeriodCommandsTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function periodFor(string $name, int $yearsAgo, string $status = 'published'): PmbPeriod
    {
        return PmbPeriod::forceCreate([
            'name' => $name, 'year' => (int) now()->subYears($yearsAgo)->year, 'status' => $status,
            'open_date' => now()->subYears($yearsAgo)->toDateString(),
            'close_date' => now()->subYears($yearsAgo)->addMonths(2)->toDateString(),
            're_registration_deadline' => now()->subYears($yearsAgo)->addMonths(8)->toDateString(),
            'university_bank_name' => 'BRI', 'university_bank_account' => '1234567890',
            'university_bank_account_name' => 'YPIB', 'admin_whatsapp' => '081234567890',
        ]);
    }

    // ── periods:assign ───────────────────────────────────────────────────

    public function test_assign_is_a_dry_run_by_default(): void
    {
        $from = $this->period();
        $to = $this->periodFor('PMB Tujuan', 1);
        [, $registration] = $this->student('terdaftar', ['period_id' => $from->id]);

        $this->artisan('periods:assign', ['--to' => $to->id, '--from' => $from->id])
            ->assertSuccessful()->expectsOutputToContain('DRY-RUN');

        $this->assertSame($from->id, (int) $registration->fresh()->period_id);
        $this->assertSame(0, PaymentLog::where('action', 'period_reassigned')->count());
    }

    public function test_assign_apply_moves_logs_and_keeps_numbers(): void
    {
        $admin = $this->makeUser('admin');
        $from = $this->period();
        $to = $this->periodFor('PMB Tujuan', 1);
        [, $registration] = $this->student('daftar_ulang_selesai', [
            'period_id' => $from->id, 'nim' => '26123451001', 'registration_number' => 'PMB-2026-1111',
        ]);

        $this->artisan('periods:assign', ['--to' => $to->id, '--from' => $from->id, '--apply' => true, '--acted-by' => $admin->id])
            ->assertSuccessful();

        $registration->refresh();
        $this->assertSame($to->id, (int) $registration->period_id);
        $this->assertSame('26123451001', $registration->nim);
        $this->assertSame('PMB-2026-1111', $registration->registration_number);

        $log = PaymentLog::where('registration_id', $registration->id)->where('action', 'period_reassigned')->firstOrFail();
        $this->assertSame($admin->id, (int) $log->acted_by);
    }

    public function test_assign_apply_requires_a_valid_actor(): void
    {
        $from = $this->period();
        $to = $this->periodFor('PMB Tujuan', 1);
        [$student, $registration] = $this->student('terdaftar', ['period_id' => $from->id]);

        $this->artisan('periods:assign', ['--to' => $to->id, '--from' => $from->id, '--apply' => true])->assertFailed();
        $this->artisan('periods:assign', ['--to' => $to->id, '--from' => $from->id, '--apply' => true, '--acted-by' => $student->id])->assertFailed();

        $this->assertSame($from->id, (int) $registration->fresh()->period_id);
    }

    public function test_assign_validates_target_and_requires_a_selector(): void
    {
        $from = $this->period();
        $archived = $this->periodFor('PMB Arsip', 1, 'archived');
        $to = $this->periodFor('PMB Tujuan', 2);

        $this->artisan('periods:assign', ['--to' => 99999, '--from' => $from->id])->assertFailed();
        $this->artisan('periods:assign', ['--to' => $archived->id, '--from' => $from->id])->assertFailed();
        $this->artisan('periods:assign', ['--to' => $to->id])->assertFailed();
        $this->artisan('periods:assign', ['--to' => $to->id, '--created-from' => 'bukan-tanggal'])->assertFailed();
    }

    public function test_assign_skips_accounts_that_already_have_a_registration_in_the_target(): void
    {
        $admin = $this->makeUser('admin');
        $from = $this->period();
        $to = $this->periodFor('PMB Tujuan', 1);
        $user = $this->makeUser();

        $inFrom = $this->registration($user, 'terdaftar', ['period_id' => $from->id]);
        $this->registration($user, 'terdaftar', ['period_id' => $to->id]);
        [, $other] = $this->student('terdaftar', ['period_id' => $from->id]);

        $this->artisan('periods:assign', ['--to' => $to->id, '--from' => $from->id, '--apply' => true, '--acted-by' => $admin->id])
            ->assertSuccessful()->expectsOutputToContain('DILEWATI');

        $this->assertSame($from->id, (int) $inFrom->fresh()->period_id, 'bentrok tidak dipindah');
        $this->assertSame($to->id, (int) $other->fresh()->period_id);
    }

    public function test_assign_can_select_by_ids_and_creation_date(): void
    {
        $admin = $this->makeUser('admin');
        $from = $this->period();
        $to = $this->periodFor('PMB Tujuan', 1);
        [, $old] = $this->student('terdaftar', ['period_id' => $from->id]);
        [, $recent] = $this->student('terdaftar', ['period_id' => $from->id]);
        $old->forceFill(['created_at' => now()->subYears(2)])->save();

        $this->artisan('periods:assign', [
            '--to' => $to->id, '--created-until' => now()->subYear()->toDateString(), '--apply' => true, '--acted-by' => $admin->id,
        ])->assertSuccessful();

        $this->assertSame($to->id, (int) $old->fresh()->period_id);
        $this->assertSame($from->id, (int) $recent->fresh()->period_id);

        $this->artisan('periods:assign', ['--to' => $to->id, '--ids' => (string) $recent->id, '--apply' => true, '--acted-by' => $admin->id])
            ->assertSuccessful();
        $this->assertSame($to->id, (int) $recent->fresh()->period_id);
    }

    // ── periods:readiness ────────────────────────────────────────────────

    public function test_readiness_passes_for_a_complete_period(): void
    {
        $period = $this->period([
            're_registration_deadline' => now()->addMonths(6)->toDateString(),
            'university_bank_name' => 'BRI', 'university_bank_account' => '1234567890',
            'university_bank_account_name' => 'YPIB', 'admin_whatsapp' => '081234567890',
        ]);
        $program = $this->program(['registration_fee' => 250000]);
        PeriodProgram::updateOrCreate(
            ['period_id' => $period->id, 'program_id' => $program->id],
            ['is_offered' => true, 'registration_fee' => 250000]
        );

        $this->artisan('periods:readiness')->assertSuccessful()->expectsOutputToContain('Tidak ada temuan GAGAL');
    }

    public function test_readiness_fails_without_a_deadline_or_account_details(): void
    {
        $this->period(['re_registration_deadline' => null, 'university_bank_account' => null, 'admin_whatsapp' => null]);

        $this->artisan('periods:readiness')
            ->assertFailed()
            ->expectsOutputToContain('tenggat daftar ulang belum diisi')
            ->expectsOutputToContain('nomor rekening kosong')
            ->expectsOutputToContain('WhatsApp admin kosong');
    }

    public function test_readiness_fails_without_any_published_period_and_on_overlap(): void
    {
        $this->periodFor('PMB Draft', 0, 'draft');
        $this->artisan('periods:readiness')->assertFailed()->expectsOutputToContain('Tidak ada periode berstatus tayang');

        $this->periodFor('PMB Satu', 0);
        $this->periodFor('PMB Dua', 0);
        $this->artisan('periods:readiness')->assertFailed()->expectsOutputToContain('beririsan');
    }

    public function test_readiness_warns_about_missing_codes_zero_fees_and_stranded_registrations(): void
    {
        $period = $this->periodFor('PMB Aktif', 0);
        $archived = $this->periodFor('PMB Arsip', 3, 'archived');
        $noCode = $this->program(['name' => 'Prodi Tanpa Kode', 'kode_prodi' => null]);
        PeriodProgram::updateOrCreate(
            ['period_id' => $period->id, 'program_id' => $noCode->id],
            ['is_offered' => true, 'registration_fee' => 0]
        );
        $this->registration(null, 'terdaftar', ['period_id' => $archived->id]);

        $this->artisan('periods:readiness')
            ->assertSuccessful()
            ->expectsOutputToContain('tanpa kode_prodi')
            ->expectsOutputToContain('biaya pendaftaran Rp 0')
            ->expectsOutputToContain('periode non-tayang');
    }
}
