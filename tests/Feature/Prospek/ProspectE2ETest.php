<?php

namespace Tests\Feature\Prospek;

use App\Livewire\RegistrationForm;
use App\Models\Campus;
use App\Models\Faculty;
use App\Models\PeriodProgram;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Prospect;
use App\Models\ProspectEvent;
use App\Models\ProspectLink;
use App\Models\ReferralClick;
use App\Models\Referrer;
use App\Models\Registration;
use App\Models\Reward;
use App\Models\User;
use App\Support\ProspectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Uji ujung ke ujung: form minat → link daftar → buat akun → form pendaftaran → konfirmasi bayar
 * (komisi pendaftaran) → diterima → tagihan daftar ulang dengan potongan → konfirmasi daftar
 * ulang (komisi daftar ulang).
 */
class ProspectE2ETest extends TestCase
{
    use RefreshDatabase;

    private const REG_FEE = 250000;
    private const RE_REG_FEE = 5000000;
    private const REFERRAL_REWARD = 75000;
    private const RE_REG_REWARD = 300000;
    private const DISCOUNT = 500000;

    private Campus $campus;
    private PmbPeriod $period;
    private Program $program;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Kampus Uji', 'code' => 'UJI', 'slug' => 'uji', 'city' => 'Majalengka',
            'admin_whatsapp' => '081200000001', 'is_active' => true,
        ]);
        $this->period = PmbPeriod::create([
            'name' => 'PMB Uji', 'year' => 2027,
            'open_date' => now()->subDay()->toDateString(), 'close_date' => now()->addMonth()->toDateString(),
            'is_active' => true, 'status' => PmbPeriod::STATUS_PUBLISHED,
        ]);
        $faculty = Faculty::create(['name' => 'Fakultas Uji', 'slug' => 'fakultas-uji']);
        $this->program = Program::create([
            'name' => 'S1 Uji', 'slug' => 's1-uji', 'kode_prodi' => '13201', 'registration_track' => 'reguler',
            'faculty_id' => $faculty->id, 'is_active' => true,
        ]);
        PeriodProgram::create([
            'period_id' => $this->period->id, 'program_id' => $this->program->id, 'campus_id' => $this->campus->id,
            'is_offered' => true, 'quota' => 0,
            'registration_fee' => self::REG_FEE, 're_registration_fee' => self::RE_REG_FEE,
            're_registration_minimum_payment' => 0,
            'referral_reward_amount' => self::REFERRAL_REWARD, 're_registration_reward_amount' => self::RE_REG_REWARD,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeEvent(string $kind): ProspectEvent
    {
        return ProspectEvent::create([
            'campus_id' => $this->campus->id, 'period_id' => $this->period->id, 'kind' => $kind,
            'name' => 'Uji ' . $kind, 'slug' => 'uji-' . $kind . '-' . uniqid(),
            'discount_amount' => self::DISCOUNT, 'is_active' => true,
        ]);
    }

    private function makeAffiliate(string $code): Referrer
    {
        $user = User::factory()->create(['phone' => '081377777777', 'is_referrer' => true]);

        return Referrer::create(['user_id' => $user->id, 'code' => $code, 'status' => 'active']);
    }

    private function submitInterest(ProspectEvent $event, ?string $ref = null): Prospect
    {
        $this->post(route('prospect.store', [$event->slug] + ($ref ? ['ref' => $ref] : [])), [
            'full_name' => 'Siswa Ujung', 'school_name' => 'SMA Ujung', 'education_level' => 'sma_smk_ma',
            'phone' => '0812-3456-7890', 'program_id' => $this->program->id,
        ]);

        return Prospect::where('phone_normalized', '081234567890')->firstOrFail();
    }

    /** Isi form pendaftaran Livewire lengkap lalu submit. Nama dan WA sengaja diisi beda. */
    private function submitRegistration(User $student, ?string $refCookie = null): void
    {
        $this->actingAs($student);

        $component = $refCookie ? Livewire::withCookie('ref', $refCookie) : Livewire::getFacadeRoot();
        $component->test(RegistrationForm::class)
            ->set('full_name', 'Nama Palsu')
            ->set('nik', '3210012345678901')
            ->set('birth_place', 'Majalengka')
            ->set('birth_date', '2008-05-01')
            ->set('gender', 'male')
            ->set('religion', 'Islam')
            ->set('address', 'Jl. Uji No. 1')
            ->set('phone', '089999999999')
            ->set('campus_id', (string) $this->campus->id)
            ->set('first_choice_program_id', (string) $this->program->id)
            ->set('admission_path', 'umum')
            ->set('education_level', 'sma_smk_ma')
            ->set('school_name', 'SMA Ujung')
            ->set('school_major', 'IPA')
            ->set('graduation_year', '2026')
            ->set('father_name', 'Ayah Uji')
            ->set('mother_name', 'Ibu Uji')
            ->set('father_occupation', 'Petani')
            ->set('mother_occupation', 'Guru')
            ->call('submit')
            ->assertHasNoErrors();
    }

    public function test_online_campaign_full_flow_with_discount_and_affiliate_commissions(): void
    {
        $event = $this->makeEvent(ProspectEvent::KIND_ONLINE);
        $owner = $this->makeAffiliate('OWNER1');
        $other = $this->makeAffiliate('OTHER1');
        ReferralClick::create(['referrer_id' => $owner->id, 'campus_id' => $this->campus->id, 'ip_address' => '1.1.1.1', 'converted' => false]);

        // 1. Form minat lewat link affiliator → langsung ke WA admin.
        $prospect = $this->submitInterest($event, $owner->code);
        $this->assertSame($owner->id, $prospect->referrer_id);
        $link = ProspectLink::firstOrFail();
        $this->assertSame(self::DISCOUNT, $link->discount_amount);

        // 2. Potongan event diubah setelah link dibuat: link tetap memakai snapshot.
        $event->update(['discount_amount' => 100000]);

        // 3. Buka link → buat akun (nama/WA terkunci ke prospek).
        $this->get(route('prospect.link', $link->token))->assertRedirect(route('registration.create'));
        $this->post(route('register'), [
            'name' => 'Orang Lain', 'email' => 'siswa@example.test', 'phone' => '089999999999',
            'password' => 'RahasiaUji2026!', 'password_confirmation' => 'RahasiaUji2026!',
        ]);
        $student = User::where('email', 'siswa@example.test')->firstOrFail();
        $this->assertSame('Siswa Ujung', $student->name);

        // 4. Form pendaftaran: cookie affiliator lain diabaikan, data dikunci ke prospek.
        $this->submitRegistration($student, $other->code);

        $registration = Registration::where('user_id', $student->id)->firstOrFail();
        $this->assertSame('Siswa Ujung', $registration->full_name);
        $this->assertSame('081234567890', $registration->phone);
        $this->assertSame($owner->id, $registration->referrer_id);
        $this->assertSame($prospect->id, $registration->prospect_id);
        $this->assertSame(self::DISCOUNT, (int) $registration->event_discount_amount);
        $this->assertSame('menunggu_pembayaran', $registration->status);
        $this->assertNotNull($link->fresh()->used_at);
        $this->assertSame(Prospect::STATUS_SUDAH_DAFTAR, $prospect->fresh()->status);
        $this->assertNull(session(ProspectService::SESSION_KEY));

        // 5. Admin konfirmasi bayar pendaftaran → komisi pendaftaran untuk pemilik prospek.
        $this->actingAs($this->admin)
            ->post(route('admin.registrations.confirm-payment', $registration->id), ['note' => 'Tunai di kantor'])
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame('terdaftar', $registration->status);
        $this->assertSame(self::REG_FEE, (int) $registration->registration_fee_paid);
        $reward = Reward::where('registration_id', $registration->id)->where('reward_type', 'registration')->firstOrFail();
        $this->assertSame($owner->id, $reward->referrer_id);
        $this->assertSame(self::REFERRAL_REWARD, (int) $reward->amount);
        $this->assertSame(1, $owner->fresh()->total_conversions);
        $this->assertSame(0, Reward::where('referrer_id', $other->id)->count());
        // Alur prospek tidak menandai klik referral sebagai konversi.
        $this->assertFalse((bool) ReferralClick::where('referrer_id', $owner->id)->first()->converted);

        // 6. Diterima (simulasi tahap interview, lalu keputusan admin).
        $registration->forceFill(['status' => 'menunggu_interview'])->save();
        $this->actingAs($this->admin)
            ->post(route('admin.registrations.override-status', $registration->id), ['hasil' => 'diterima'])
            ->assertSessionHas('success');
        $this->assertSame('diterima', $registration->fresh()->status);

        // 7. Tagihan daftar ulang sudah dipotong.
        $expectedBill = self::RE_REG_FEE - self::DISCOUNT;
        $this->assertSame($expectedBill, $registration->fresh()->reRegistrationRemaining());

        // 8. Admin konfirmasi daftar ulang sebesar tagihan → selesai, NIM terbit, komisi daftar ulang.
        $this->actingAs($this->admin)
            ->post(route('admin.registrations.confirm-re-registration', $registration->id), [
                'amount' => (string) $expectedBill, 'note' => 'Transfer bank',
            ])
            ->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame('daftar_ulang_selesai', $registration->status);
        $this->assertNotEmpty($registration->nim);
        $this->assertSame($expectedBill, $registration->reRegistrationPaid());
        $this->assertSame(0, $registration->reRegistrationRemaining());

        $reReward = Reward::where('registration_id', $registration->id)->where('reward_type', 're_registration')->firstOrFail();
        $this->assertSame($owner->id, $reReward->referrer_id);
        $this->assertSame(self::RE_REG_REWARD, (int) $reReward->amount);
    }

    public function test_graduation_year_must_be_a_plausible_four_digit_year(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']));

        foreach (['20266777777' => 'digits', '1900' => 'between', (string) (now()->year + 2) => 'between'] as $value => $rule) {
            Livewire::test(RegistrationForm::class)
                ->set('step', 3)
                ->set('education_level', 'sma_smk_ma')
                ->set('school_name', 'SMA Ujung')
                ->set('school_major', 'IPA')
                ->set('graduation_year', $value)
                ->call('nextStep')
                ->assertHasErrors(['graduation_year' => $rule]);
        }

        Livewire::test(RegistrationForm::class)
            ->set('step', 3)
            ->set('education_level', 'sma_smk_ma')
            ->set('school_name', 'SMA Ujung')
            ->set('school_major', 'IPA')
            ->set('graduation_year', '2026')
            ->call('nextStep')
            ->assertHasNoErrors('graduation_year');
    }

    public function test_campus_event_link_ignores_referral_cookie_and_pays_no_commission(): void
    {
        $event = $this->makeEvent(ProspectEvent::KIND_EVENT);
        $cookieAffiliate = $this->makeAffiliate('COOKIE1');

        $prospect = $this->submitInterest($event, $cookieAffiliate->code); // ref diabaikan untuk event kampus
        $this->assertNull($prospect->referrer_id);

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $this->get(route('prospect.link', ProspectLink::firstOrFail()->token));
        $this->submitRegistration($student, $cookieAffiliate->code);

        $registration = Registration::where('user_id', $student->id)->firstOrFail();
        $this->assertNull($registration->referrer_id);
        $this->assertSame(self::DISCOUNT, (int) $registration->event_discount_amount);

        $this->actingAs($this->admin)
            ->post(route('admin.registrations.confirm-payment', $registration->id), ['note' => 'Tunai'])
            ->assertSessionHas('success');

        $this->assertSame(0, Reward::count());
    }

    public function test_registration_without_link_matching_whatsapp_credits_owner_without_discount(): void
    {
        $event = $this->makeEvent(ProspectEvent::KIND_ONLINE);
        $owner = $this->makeAffiliate('OWNER2');
        $cookieAffiliate = $this->makeAffiliate('COOKIE2');

        $prospect = $this->submitInterest($event, $owner->code);

        // Daftar biasa (tanpa membuka link), nomor WA sama dengan prospek, cookie affiliator lain.
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($student);
        Livewire::withCookie('ref', $cookieAffiliate->code)
            ->test(RegistrationForm::class)
            ->set('full_name', 'Siswa Ujung')->set('nik', '3210012345678902')->set('birth_place', 'Majalengka')
            ->set('birth_date', '2008-05-01')->set('gender', 'male')->set('religion', 'Islam')
            ->set('address', 'Jl. Uji No. 2')->set('phone', '081234567890')
            ->set('campus_id', (string) $this->campus->id)->set('first_choice_program_id', (string) $this->program->id)
            ->set('admission_path', 'umum')->set('education_level', 'sma_smk_ma')
            ->set('school_name', 'SMA Ujung')->set('school_major', 'IPA')->set('graduation_year', '2026')
            ->set('father_name', 'Ayah')->set('mother_name', 'Ibu')
            ->set('father_occupation', 'Petani')->set('mother_occupation', 'Guru')
            ->call('submit')
            ->assertHasNoErrors();

        $registration = Registration::where('user_id', $student->id)->firstOrFail();
        $this->assertSame($owner->id, $registration->referrer_id);
        $this->assertSame($prospect->id, $registration->prospect_id);
        $this->assertSame(0, (int) $registration->event_discount_amount);
        $this->assertSame(Prospect::STATUS_SUDAH_DAFTAR, $prospect->fresh()->status);
    }
}
