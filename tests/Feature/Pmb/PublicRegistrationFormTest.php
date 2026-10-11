<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\PmbPeriod;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class PublicRegistrationFormTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    /** Isi seluruh langkah wizard dengan data valid; override per field lewat $override. */
    private function fill($component, int $programId, array $override = [])
    {
        $data = array_merge([
            'full_name' => 'Calon Uji', 'nisn' => '1234567890', 'nik' => '3210000000000001', 'birth_place' => 'Majalengka',
            'birth_date' => '2005-01-01', 'gender' => 'male', 'religion' => 'Islam', 'address' => 'Jl. Uji', 'phone' => '081234567890',
            'first_choice_program_id' => (string) $programId, 'admission_path' => 'umum',
            'school_name' => 'SMA Uji', 'school_major' => 'IPA', 'graduation_year' => '2024', 'certificate_number' => 'IJ-1',
            'father_name' => 'Ayah', 'mother_name' => 'Ibu', 'father_occupation' => 'Petani', 'mother_occupation' => 'IRT',
        ], $override);

        foreach ($data as $field => $value) {
            $component->set($field, $value);
        }

        return $component;
    }

    private function openForm(?\App\Models\User $user = null)
    {
        $this->period();

        return Livewire::actingAs($user ?? $this->makeUser())->test(RegistrationForm::class);
    }

    public function test_valid_submission_creates_pending_payment_registration(): void
    {
        $program = $this->program();
        $component = $this->fill($this->openForm(), $program->id);

        $component->call('submit')->assertHasNoErrors();

        $registration = Registration::first();
        $this->assertNotNull($registration);
        $this->assertSame('menunggu_pembayaran', $registration->status);
        $this->assertSame($program->id, $registration->first_choice_program_id);
        $this->assertNull($registration->registration_number);
    }

    public function test_submit_revalidates_every_step(): void
    {
        $this->period();
        $component = Livewire::actingAs($this->makeUser())->test(RegistrationForm::class)->call('submit');

        $component->assertHasErrors(['full_name']);
        $component->assertSet('step', 1);
        $this->assertSame(0, Registration::count());
    }

    public function test_nik_must_be_sixteen_digits(): void
    {
        $program = $this->program();

        foreach (['abcdefghijklmnop', '12345678901234567', '123'] as $bad) {
            $this->fill($this->openForm(), $program->id, ['nik' => $bad])->set('step', 1)->call('nextStep')->assertHasErrors(['nik']);
        }
        $this->fill($this->openForm(), $program->id)->set('step', 1)->call('nextStep')->assertHasNoErrors();
    }

    public function test_school_grade_is_validated_between_zero_and_hundred(): void
    {
        $program = $this->program();

        foreach (['abc', '100.01', '-1', '1000000'] as $bad) {
            $this->fill($this->openForm(), $program->id, ['school_grade' => $bad])->call('submit')->assertHasErrors(['school_grade']);
        }
        $this->assertSame(0, Registration::count());

        foreach (['', '0', '100', '85.5'] as $good) {
            Registration::query()->forceDelete();
            $this->fill($this->openForm(), $program->id, ['school_grade' => $good])->call('submit')->assertHasNoErrors();
            $this->assertSame(1, Registration::count(), "nilai '{$good}' seharusnya diterima");
        }
    }

    public function test_inactive_program_cannot_be_chosen(): void
    {
        $inactive = $this->program(['is_active' => false]);

        $this->fill($this->openForm(), $inactive->id)->call('submit')->assertHasErrors(['first_choice_program_id']);
        $this->assertSame(0, Registration::count());
    }

    public function test_quota_is_enforced_in_step_two_and_on_direct_submit(): void
    {
        $period = $this->period();
        $program = $this->program(['quota' => 1]);
        $this->registration(null, 'terdaftar', ['first_choice_program_id' => $program->id]);            // mengisi kursi
        $this->registration(null, 'ditolak', ['first_choice_program_id' => $program->id]);             // tidak dihitung

        $stepTwo = $this->fill($this->openForm(), $program->id)->set('step', 2);
        $stepTwo->call('nextStep')->assertHasErrors(['first_choice_program_id'])->assertSet('step', 2);

        $this->fill($this->openForm(), $program->id)->call('submit')->assertHasErrors(['first_choice_program_id']);
        $this->assertSame(2, Registration::where('first_choice_program_id', $program->id)->count());

        // kuota 0 = tanpa batas
        $program->update(['quota' => 0]);
        $this->fill($this->openForm(), $program->id)->call('submit')->assertHasNoErrors();
        $this->assertSame(3, Registration::where('first_choice_program_id', $program->id)->count());
        $this->assertSame($period->id, Registration::latest('id')->first()->period_id);
    }

    public function test_closed_period_blocks_new_registrations_but_not_existing_registrants(): void
    {
        $program = $this->program();
        $this->period(['open_date' => now()->subDays(30)->toDateString(), 'close_date' => now()->subDay()->toDateString()]);

        $this->fill(Livewire::actingAs($this->makeUser())->test(RegistrationForm::class), $program->id)->call('submit');
        $this->assertSame(0, Registration::count());

        $newcomer = $this->makeUser();
        $this->actingAs($newcomer)->get('/daftar?prodi=' . $program->id)->assertRedirect(route('landing'));

        [$existing] = $this->student('terdaftar');
        $this->actingAs($existing)->get('/daftar?prodi=' . $program->id)->assertRedirect(route('registration.status'));
    }

    public function test_period_window_follows_wib_calendar_days(): void
    {
        $period = new PmbPeriod(['status' => 'published', 'open_date' => '2026-04-01', 'close_date' => '2026-04-30']);

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-03-31 18:30:00', 'UTC'));   // 01:30 WIB 1 April
        $this->assertTrue($period->isOpen());

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-04-30 23:30:00', 'Asia/Jakarta'));
        $this->assertTrue($period->isOpen());

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-05-01 00:30:00', 'Asia/Jakarta'));
        $this->assertFalse($period->isOpen());

        \Carbon\Carbon::setTestNow();
    }

    public function test_referrer_is_resolved_server_side_and_locked_from_client(): void
    {
        $program = $this->program();
        $inactive = $this->referrer(['status' => 'inactive']);
        $active = $this->referrer();

        // properti terkunci: klien tidak bisa mengubahnya
        $component = $this->openForm();
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        $component->set('referrer_id', $inactive->id);
    }

    public function test_referrer_comes_from_cookie_or_account_only_when_valid(): void
    {
        $program = $this->program();
        $inactive = $this->referrer(['status' => 'inactive']);
        $active = $this->referrer();
        $this->period();

        // tanpa cookie: tidak ada referrer
        $this->fill($this->openForm(), $program->id)->call('submit');
        $this->assertNull(Registration::first()->referrer_id);
        Registration::query()->forceDelete();

        // cookie referrer tidak aktif: diabaikan
        $this->fill(Livewire::actingAs($this->makeUser())->withCookie('ref', $inactive->code)->test(RegistrationForm::class), $program->id)->call('submit');
        $this->assertNull(Registration::first()->referrer_id);
        Registration::query()->forceDelete();

        // cookie referrer aktif: dipakai
        $this->fill(Livewire::actingAs($this->makeUser())->withCookie('ref', $active->code)->test(RegistrationForm::class), $program->id)->call('submit');
        $this->assertSame($active->id, Registration::first()->referrer_id);
        Registration::query()->forceDelete();

        // referrer terkunci di akun
        $locked = $this->makeUser('mahasiswa', ['referrer_id' => $active->id]);
        $this->fill(Livewire::actingAs($locked)->test(RegistrationForm::class), $program->id)->call('submit');
        $this->assertSame($active->id, Registration::first()->referrer_id);
    }

    public function test_one_registration_per_account_per_period(): void
    {
        $program = $this->program();
        $user = $this->makeUser();
        $this->period();

        $this->fill(Livewire::actingAs($user)->test(RegistrationForm::class), $program->id)->call('submit');
        $this->fill(Livewire::actingAs($user)->test(RegistrationForm::class), $program->id)->call('submit');

        $this->assertSame(1, Registration::where('user_id', $user->id)->count());
    }
}
