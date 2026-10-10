<?php

namespace Tests\Feature\Pmb;

use App\Models\Referrer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class AffiliateAndRewardTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    public function test_affiliate_signup_dashboard_bank_and_click_tracking(): void
    {
        $this->post('/afiliasi', [
            'name' => 'Mitra Uji', 'email' => 'mitra@uji.test', 'phone' => '081234567890',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('referrer.dashboard'));

        $user = User::where('email', 'mitra@uji.test')->first();
        $referrer = Referrer::where('user_id', $user->id)->first();
        $this->assertTrue((bool) $user->is_referrer);
        $this->assertNotNull($referrer);
        $this->assertSame(6, strlen($referrer->code));

        $this->actingAs($user)->get('/afiliasi/dashboard')->assertOk();
        $this->actingAs($user)->post('/afiliasi/bank', ['bank_name' => 'BCA', 'bank_account_number' => '123', 'bank_account_name' => 'Mitra'])
            ->assertSessionHas('success');
        $this->actingAs($user)->post('/afiliasi/bank', ['bank_name' => ''])->assertSessionHasErrors();

        auth()->logout();

        $this->get('/ref/' . $referrer->code)->assertRedirect(route('registration.create'))->assertCookie('ref');
        $this->assertSame(1, (int) $referrer->fresh()->total_clicks);

        $referrer->update(['status' => 'inactive']);
        $this->get('/ref/' . $referrer->code)->assertRedirect(route('registration.create'))->assertCookieMissing('ref');
        $this->get('/ref/TIDAKADA')->assertRedirect(route('registration.create'));
    }

    public function test_disbursement_is_not_repeatable_and_needs_bank_data(): void
    {
        $admin = $this->makeUser('admin');
        $referrer = $this->referrer();
        [, $registration] = $this->student('terdaftar', ['referrer_id' => $referrer->id]);
        $reward = $this->reward($referrer, $registration, 'approved');

        $this->actingAs($admin)->post("/admin/reward/{$reward->id}/disburse")->assertSessionHas('success');
        $this->actingAs($admin)->post("/admin/reward/{$reward->id}/disburse")->assertSessionHas('error');
        $this->assertSame(1, $referrer->logs()->where('action', 'disbursed')->count());

        $noBank = $this->referrer(['bank_name' => null, 'bank_account_number' => null, 'bank_account_name' => null]);
        [, $other] = $this->student('terdaftar', ['referrer_id' => $noBank->id]);
        $blocked = $this->reward($noBank, $other, 'approved');

        $this->actingAs($admin)->post("/admin/reward/{$blocked->id}/disburse")->assertSessionHas('error');
        $this->assertSame('approved', $blocked->fresh()->status);
    }

    public function test_reward_csv_export_is_formula_safe(): void
    {
        $admin = $this->makeUser('admin');
        $owner = $this->makeUser('mahasiswa', ['name' => '=cmd|calc!A1']);
        $referrer = $this->referrer(['user_id' => $owner->id, 'bank_name' => '+SUM(1)', 'bank_account_number' => '1234567', 'bank_account_name' => '@x']);
        [, $registration] = $this->student('terdaftar', ['referrer_id' => $referrer->id, 'full_name' => 'Calon Aman']);
        $this->reward($referrer, $registration, 'approved');

        foreach (['/admin/reward/export', '/admin/reward/referrers/export'] as $url) {
            $csv = $this->actingAs($admin)->post($url)->streamedContent();

            foreach (['=cmd', '+SUM', '@x'] as $needle) {
                $this->assertDoesNotMatchRegularExpression('/(^|,)"?' . preg_quote($needle, '/') . '/m', $csv, "$url: sel diawali {$needle}");
            }
            $this->assertStringContainsString("'=cmd|calc!A1", $csv);
            $this->assertStringContainsString('1234567', $csv);
        }
    }

    public function test_affiliate_signup_endpoints_are_throttled(): void
    {
        $statuses = [];
        for ($i = 0; $i < 12; $i++) {
            $statuses[] = $this->post('/afiliasi', [
                'name' => "Spam $i", 'email' => "spam{$i}@uji.test", 'phone' => '08123450' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            ])->getStatusCode();
            auth()->logout();
        }

        $this->assertSame(array_fill(0, 10, 302), array_slice($statuses, 0, 10));
        $this->assertSame(429, $statuses[10]);
    }
}
