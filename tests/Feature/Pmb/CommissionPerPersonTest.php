<?php

namespace Tests\Feature\Pmb;

use App\Models\PmbPeriod;
use App\Models\Referrer;
use App\Models\Registration;
use App\Models\Reward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T5 (PLAN_MULTI_PERIODE.md K8, 5.6): satu komisi per orang (NIK) seumur hidup.
 */
class CommissionPerPersonTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private const NIK = '3210000000007777';

    private function lastYearPeriod(): PmbPeriod
    {
        $this->period();

        return PmbPeriod::forceCreate([
            'name' => 'PMB Tahun Lalu', 'year' => (int) now()->subYear()->year, 'status' => 'archived',
            'open_date' => now()->subYear()->toDateString(), 'close_date' => now()->subYear()->addMonths(2)->toDateString(),
        ]);
    }

    private function pending(Referrer $referrer, array $extra = []): Registration
    {
        $this->period();

        return $this->registration(null, 'menunggu_konfirmasi', array_merge([
            'referrer_id' => $referrer->id, 'nik' => self::NIK, 'payment_proof' => 'bukti-bayar/uji.jpg',
        ], $extra));
    }

    private function confirm(Registration $registration): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar", ['note' => 'uji'])
            ->assertSessionHas('success');
    }

    private function rewardCount(string $type = 'registration'): int
    {
        return Reward::where('reward_type', $type)->where('status', '!=', 'cancelled')->count();
    }

    public function test_reward_records_the_nik_and_counts_one_conversion(): void
    {
        $referrer = $this->referrer();
        $registration = $this->pending($referrer);

        $this->confirm($registration);

        $reward = Reward::where('registration_id', $registration->id)->firstOrFail();
        $this->assertSame(self::NIK, $reward->nik);
        $this->assertSame(1, (int) $referrer->fresh()->total_conversions);
    }

    public function test_second_registration_of_the_same_person_earns_no_second_commission(): void
    {
        $referrer = $this->referrer();
        $oldPeriod = $this->lastYearPeriod();

        $first = $this->pending($referrer, ['period_id' => $oldPeriod->id]);
        $this->confirm($first);

        $second = $this->pending($referrer);
        $this->confirm($second);

        $this->assertSame(1, $this->rewardCount());
        $this->assertNull(Reward::where('registration_id', $second->id)->first());
        $this->assertSame(1, (int) $referrer->fresh()->total_conversions, 'counter tidak naik tanpa komisi baru');
        $this->assertSame('terdaftar', $second->fresh()->status, 'konfirmasi pembayaran tetap berjalan');
    }

    public function test_a_different_referrer_does_not_get_a_second_commission_either(): void
    {
        $oldPeriod = $this->lastYearPeriod();
        $firstReferrer = $this->referrer();
        $secondReferrer = $this->referrer();

        $this->confirm($this->pending($firstReferrer, ['period_id' => $oldPeriod->id]));
        $second = $this->pending($secondReferrer);
        $this->confirm($second);

        $this->assertSame(1, $this->rewardCount());
        $this->assertSame(0, (int) $secondReferrer->fresh()->total_conversions);
    }

    public function test_changing_the_nik_afterwards_does_not_reopen_the_commission(): void
    {
        $referrer = $this->referrer();
        $oldPeriod = $this->lastYearPeriod();

        $first = $this->pending($referrer, ['period_id' => $oldPeriod->id]);
        $this->confirm($first);

        // NIK pendaftaran pertama diubah setelah komisi dibuat; komisi tetap terkunci pada NIK lama.
        $first->forceFill(['nik' => '3210000000008888'])->save();

        $second = $this->pending($referrer);
        $this->confirm($second);

        $this->assertSame(1, $this->rewardCount());
        $this->assertSame(self::NIK, Reward::where('registration_id', $first->id)->value('nik'));
    }

    public function test_a_cancelled_commission_does_not_block_a_new_one(): void
    {
        $referrer = $this->referrer();
        $oldPeriod = $this->lastYearPeriod();

        $first = $this->pending($referrer, ['period_id' => $oldPeriod->id]);
        $this->confirm($first);
        Reward::where('registration_id', $first->id)->update(['status' => 'cancelled']);

        $second = $this->pending($referrer);
        $this->confirm($second);

        $this->assertNotNull(Reward::where('registration_id', $second->id)->first());
    }

    public function test_different_people_each_earn_a_commission(): void
    {
        $referrer = $this->referrer();

        $this->confirm($this->pending($referrer, ['nik' => '3210000000000001']));
        $this->confirm($this->pending($referrer, ['nik' => '3210000000000002']));

        $this->assertSame(2, $this->rewardCount());
        $this->assertSame(2, (int) $referrer->fresh()->total_conversions);
    }

    public function test_alumni_registration_still_earns_no_commission(): void
    {
        $referrer = $this->referrer();
        $registration = $this->pending($referrer, ['registration_type' => 'alumni', 'alumni_card_proof' => 'kartu/uji.jpg', 'payment_proof' => null]);

        $this->confirm($registration);

        $this->assertSame(0, $this->rewardCount());
        $this->assertSame(0, (int) $referrer->fresh()->total_conversions);
    }

    public function test_re_registration_commission_is_also_once_per_person(): void
    {
        $referrer = $this->referrer();
        $oldPeriod = $this->lastYearPeriod();
        $program = $this->program(['re_registration_reward_amount' => 500000, 'kode_prodi' => '66666']);
        $admin = $this->makeUser('admin');

        $first = $this->registration(null, 'menunggu_konfirmasi_daftar_ulang', [
            'period_id' => $oldPeriod->id, 'referrer_id' => $referrer->id, 'nik' => self::NIK,
            'first_choice_program_id' => $program->id, 're_registration_payment_proof' => 'x.png',
        ]);
        $this->actingAs($admin)->post("/admin/pendaftar/{$first->id}/konfirmasi-daftar-ulang")->assertSessionHas('success');
        $this->assertSame(1, $this->rewardCount('re_registration'));
        $this->assertSame(self::NIK, Reward::where('reward_type', 're_registration')->value('nik'));

        $other = $this->program(['re_registration_reward_amount' => 500000, 'kode_prodi' => '77777']);
        $second = $this->registration(null, 'menunggu_konfirmasi_daftar_ulang', [
            'referrer_id' => $referrer->id, 'nik' => self::NIK,
            'first_choice_program_id' => $other->id, 're_registration_payment_proof' => 'x.png',
        ]);
        $this->actingAs($admin)->post("/admin/pendaftar/{$second->id}/konfirmasi-daftar-ulang")->assertSessionHas('success');

        $this->assertSame(1, $this->rewardCount('re_registration'));
        $this->assertSame('daftar_ulang_selesai', $second->fresh()->status, 'daftar ulang tetap selesai tanpa komisi kedua');
    }

    // ── restore() ────────────────────────────────────────────────────────

    public function test_restore_is_refused_when_the_account_already_has_an_active_registration_in_that_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $user = $this->makeUser();

        $old = $this->registration($user, 'ditolak');
        $old->delete();
        $this->registration($user, 'menunggu_pembayaran');

        $this->actingAs($admin)->post("/admin/pendaftar/{$old->id}/restore")->assertSessionHas('error');

        $this->assertTrue(Registration::withTrashed()->find($old->id)->trashed());
    }

    public function test_restore_works_when_there_is_no_conflict(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $registration = $this->registration(null, 'ditolak');
        $registration->delete();

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/restore")->assertSessionHas('success');

        $this->assertFalse(Registration::withTrashed()->find($registration->id)->trashed());
    }
}
