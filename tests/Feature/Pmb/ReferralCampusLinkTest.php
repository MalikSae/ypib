<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\Campus;
use App\Models\ReferralClick;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Tautan afiliasi per kampus: /ref/KODE?kampus=cirebon mengarah ke landing kampus. */
class ReferralCampusLinkTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    public function test_campus_link_redirects_to_campus_landing_sets_cookie_and_records_campus(): void
    {
        $referrer = $this->referrer();

        $this->get('/ref/' . $referrer->code . '?kampus=cirebon')
            ->assertRedirect(route('campus.landing', 'cirebon'))
            ->assertCookie('ref');

        $click = ReferralClick::where('referrer_id', $referrer->id)->firstOrFail();
        $this->assertSame($this->crb()->id, $click->campus_id);
        $this->assertSame(1, (int) $referrer->fresh()->total_clicks);
    }

    public function test_default_campus_link_goes_to_main_landing(): void
    {
        $referrer = $this->referrer();

        $this->get('/ref/' . $referrer->code . '?kampus=majalengka')
            ->assertRedirect(route('landing'))
            ->assertCookie('ref');

        $this->assertSame(Campus::defaultId(), ReferralClick::first()->campus_id);
    }

    public function test_general_link_and_bad_campus_fall_back_to_the_registration_form(): void
    {
        $referrer = $this->referrer();

        $this->get('/ref/' . $referrer->code)->assertRedirect(route('registration.create'))->assertCookie('ref');
        $this->get('/ref/' . $referrer->code . '?kampus=tidak-ada')->assertRedirect(route('registration.create'))->assertCookie('ref');
        $this->get('/ref/' . $referrer->code . '?kampus=')->assertRedirect(route('registration.create'));

        $this->crb()->update(['is_active' => false]);
        $this->get('/ref/' . $referrer->code . '?kampus=cirebon')->assertRedirect(route('registration.create'))->assertCookie('ref');

        // Keempat permintaan dari IP yang sama tanpa kampus valid dihitung satu klik (dedupe 5 menit).
        $this->assertSame(1, ReferralClick::count());
        $this->assertNull(ReferralClick::whereNotNull('campus_id')->first(), 'klik tanpa kampus valid tidak mencatat kampus');
    }

    public function test_inactive_or_unknown_referrer_never_gets_a_cookie_or_click(): void
    {
        $inactive = $this->referrer(['status' => 'inactive']);

        $this->get('/ref/' . $inactive->code . '?kampus=cirebon')->assertRedirect(route('registration.create'))->assertCookieMissing('ref');
        $this->get('/ref/TIDAKADA?kampus=cirebon')->assertRedirect(route('registration.create'))->assertCookieMissing('ref');

        $this->assertSame(0, ReferralClick::count());
    }

    public function test_dashboard_lists_a_link_per_extra_campus_plus_the_general_link(): void
    {
        $referrer = $this->referrer();
        $referrer->user->forceFill(['is_referrer' => true])->save();

        $html = $this->actingAs($referrer->user)->get('/afiliasi/dashboard')->assertOk()->getContent();

        $base = rtrim(config('app.url'), '/') . '/ref/' . $referrer->code;

        $this->assertStringContainsString($base . '?kampus=cirebon', $html);
        // Kampus bawaan tidak punya kartu: halamannya sama dengan tautan umum.
        $this->assertStringNotContainsString('?kampus=majalengka', $html);
        $this->assertStringContainsString('Link Umum', $html);
        $this->assertStringContainsString(urlencode('Daftar kuliah di YPIB Kampus Cirebon lewat link ini: ' . $base . '?kampus=cirebon'), $html);

        // Kampus nonaktif tidak punya tautan; tinggal tautan umum.
        $this->crb()->update(['is_active' => false]);
        $this->actingAs($referrer->user)->get('/afiliasi/dashboard')->assertOk()->assertDontSee('?kampus=cirebon')->assertSee('Link Umum');
    }

    public function test_commission_attribution_is_unchanged_by_the_campus_in_the_link(): void
    {
        $this->period();
        $program = $this->program();
        $referrer = $this->referrer();

        $this->get('/ref/' . $referrer->code . '?kampus=cirebon')->assertCookie('ref');

        // Pendaftar membuka form dengan cookie dari tautan Cirebon dan memilih kampus bawaan.
        $user = $this->makeUser();
        Livewire::actingAs($user)->withCookie('ref', $referrer->code)->test(RegistrationForm::class)
            ->set('campus_id', (string) Campus::defaultId())
            ->set('first_choice_program_id', (string) $program->id)
            ->set('admission_path', 'umum')
            ->set('full_name', 'Calon Uji')->set('nisn', '1234567890')->set('nik', '3210000000000001')
            ->set('birth_place', 'Majalengka')->set('birth_date', '2005-01-01')->set('gender', 'male')
            ->set('religion', 'Islam')->set('address', 'Jl. Uji')->set('phone', '081234567890')
            ->set('school_name', 'SMA Uji')->set('school_major', 'IPA')->set('graduation_year', '2024')
            ->set('certificate_number', 'IJ-1')->set('father_name', 'Ayah')->set('mother_name', 'Ibu')
            ->set('father_occupation', 'Petani')->set('mother_occupation', 'IRT')
            ->call('submit')->assertHasNoErrors();

        $registration = Registration::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($referrer->id, (int) $registration->referrer_id);
        $this->assertSame(Campus::defaultId(), $registration->campus_id);
    }
}
