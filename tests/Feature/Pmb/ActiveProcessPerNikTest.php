<?php

namespace Tests\Feature\Pmb;

use App\Livewire\RegistrationForm;
use App\Models\PaymentLog;
use App\Models\PmbPeriod;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T3 (PLAN_MULTI_PERIODE.md K7, 5.5): satu proses aktif per orang (NIK).
 */
class ActiveProcessPerNikTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private const NIK = '3210000000009999';

    /** Periode lain (sudah tutup) milik pendaftaran yang sudah ada. */
    private function otherPeriod(array $extra = []): PmbPeriod
    {
        return PmbPeriod::forceCreate(array_merge([
            'name'                         => 'PMB Lain',
            'year'                         => (int) now()->subYear()->year,
            'open_date'                    => now()->subYear()->toDateString(),
            'close_date'                   => now()->subYear()->addMonths(2)->toDateString(),
            're_registration_deadline'     => now()->addDays(10)->toDateString(),
            'university_bank_name'         => 'BRI',
            'university_bank_account'      => '1234567890',
            'university_bank_account_name' => 'YPIB',
            'admin_whatsapp'               => '081234567890',
            'status'                       => 'published',
        ], $extra));
    }

    /** Pendaftaran lain dengan NIK yang sama pada periode lain. */
    private function existing(string $status, array $periodExtra = [], array $registrationExtra = [], ?int $programId = null): Registration
    {
        $this->period(['admin_whatsapp' => '081234567890']);
        $period = $this->otherPeriod($periodExtra);

        return $this->registration(null, $status, array_merge([
            'period_id'               => $period->id,
            'nik'                     => self::NIK,
            'first_choice_program_id' => $programId ?? $this->program()->id,
        ], $registrationExtra));
    }

    private function submit(int $programId, ?string $nik = null): \Livewire\Features\SupportTesting\Testable
    {
        $this->period();

        $component = Livewire::actingAs($this->makeUser())->test(RegistrationForm::class);

        foreach ([
            'full_name' => 'Calon Uji', 'nisn' => '1234567890', 'nik' => $nik ?? self::NIK, 'birth_place' => 'Majalengka',
            'birth_date' => '2005-01-01', 'gender' => 'male', 'religion' => 'Islam', 'address' => 'Jl. Uji', 'phone' => '081234567890',
            'first_choice_program_id' => (string) $programId, 'admission_path' => 'umum',
            'school_name' => 'SMA Uji', 'school_major' => 'IPA', 'graduation_year' => '2024', 'certificate_number' => 'IJ-1',
            'father_name' => 'Ayah', 'mother_name' => 'Ibu', 'father_occupation' => 'Petani', 'mother_occupation' => 'IRT',
        ] as $field => $value) {
            $component->set($field, $value);
        }

        return $component->call('submit');
    }

    private function enforce(bool $on = true): void
    {
        config(['pmb.enforce_nik_block' => $on]);
    }

    private function newRegistrationCount(): int
    {
        return Registration::where('nik', self::NIK)->count();
    }

    // ── Saklar mati: hanya tanda ─────────────────────────────────────────

    public function test_with_the_switch_off_nothing_is_blocked_and_admin_sees_the_flag(): void
    {
        $this->enforce(false);
        $program = $this->program(['name' => 'Prodi Sama']);
        $other = $this->existing('menunggu_tes_tulis', [], [], $program->id);

        $this->submit($program->id)->assertHasNoErrors();
        $this->assertSame(2, $this->newRegistrationCount());

        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->get("/admin/pendaftar/{$other->id}")->assertOk()->assertSee('NIK ganda');
        $this->actingAs($admin)->get('/admin/pendaftar')->assertOk()->assertSee('NIK ganda');
    }

    // ── Saklar hidup: kondisi yang memblokir ─────────────────────────────

    public function test_verified_registration_blocks_a_new_one(): void
    {
        $this->enforce();

        foreach (['menunggu_tes_tulis', 'menunggu_interview'] as $status) {
            Registration::query()->forceDelete();
            $this->existing($status);

            $this->submit($this->program()->id)->assertHasErrors(['nik']);
            $this->assertSame(1, $this->newRegistrationCount(), "status {$status} seharusnya memblokir");
        }
    }

    public function test_accepted_within_re_registration_deadline_blocks(): void
    {
        $this->enforce();

        foreach (['diterima', 'menunggu_konfirmasi_daftar_ulang'] as $status) {
            Registration::query()->forceDelete();
            $this->existing($status);

            $this->submit($this->program()->id)->assertHasErrors(['nik']);
            $this->assertSame(1, $this->newRegistrationCount(), "status {$status} seharusnya memblokir");
        }
    }

    public function test_numbered_student_blocks_the_same_program_only(): void
    {
        $this->enforce();
        $same = $this->program();
        $this->existing('daftar_ulang_selesai', [], ['nim' => '26123451001'], $same->id);

        $this->submit($same->id)->assertHasErrors(['nik']);
        $this->assertSame(1, $this->newRegistrationCount());

        // Prodi berbeda: boleh (mis. lanjut jenjang).
        $this->submit($this->program()->id)->assertHasNoErrors();
        $this->assertSame(2, $this->newRegistrationCount());
    }

    public function test_reapply_flag_lets_the_same_program_through(): void
    {
        $this->enforce();
        $program = $this->program();
        $this->existing('daftar_ulang_selesai', [], ['nim' => '26123451001', 'reapply_allowed' => true], $program->id);

        $this->submit($program->id)->assertHasNoErrors();
        $this->assertSame(2, $this->newRegistrationCount());
    }

    // ── Saklar hidup: kondisi yang TIDAK memblokir ───────────────────────

    public function test_early_statuses_rejected_and_lapsed_cases_do_not_block(): void
    {
        $this->enforce();

        $cases = [
            'menunggu_pembayaran' => [],
            'menunggu_konfirmasi' => [],
            'terdaftar'           => [],
            'ditolak'             => [],
            // Tenggat daftar ulang kosong tidak memblokir (K7(b)).
            'diterima'            => ['re_registration_deadline' => null],
            // Tenggat sudah lewat.
            'menunggu_konfirmasi_daftar_ulang' => ['re_registration_deadline' => now()->subDay()->toDateString()],
        ];

        foreach ($cases as $status => $periodExtra) {
            Registration::query()->forceDelete();
            PmbPeriod::query()->where('name', 'PMB Lain')->delete();
            $this->existing($status, $periodExtra);

            $this->submit($this->program()->id)->assertHasNoErrors();
            $this->assertSame(2, $this->newRegistrationCount(), "status {$status} tidak boleh memblokir");
        }
    }

    public function test_archived_period_releases_the_block(): void
    {
        $this->enforce();
        $this->existing('menunggu_interview', ['status' => 'archived']);

        $this->submit($this->program()->id)->assertHasNoErrors();
        $this->assertSame(2, $this->newRegistrationCount());
    }

    public function test_soft_deleted_registrations_do_not_block(): void
    {
        $this->enforce();
        $this->existing('menunggu_interview')->delete();

        $this->submit($this->program()->id)->assertHasNoErrors();
    }

    // ── Pesan generik dan tidak ada celah enumerasi ──────────────────────

    public function test_block_message_is_generic(): void
    {
        $this->enforce();
        $program = $this->program(['name' => 'Prodi Sangat Rahasia']);
        $this->existing('menunggu_interview', ['name' => 'PMB Nama Rahasia'], [], $program->id);

        $component = $this->submit($this->program()->id)->assertHasErrors(['nik']);
        $message = $component->errors()->first('nik');

        $this->assertStringContainsString('proses pendaftaran lain', $message);
        $this->assertStringContainsString('081234567890', $message);
        foreach (['Prodi Sangat Rahasia', 'PMB Nama Rahasia', 'Menunggu Interview', 'menunggu_interview'] as $leak) {
            $this->assertStringNotContainsString($leak, $message);
        }
    }

    public function test_guests_cannot_probe_a_nik_through_the_wizard_steps(): void
    {
        $this->enforce();
        $this->existing('daftar_ulang_selesai', [], ['nim' => '26123451001']);

        // Tamu mengisi langkah 1 dengan NIK terblokir: tidak ada umpan balik NIK sama sekali.
        $component = Livewire::test(RegistrationForm::class)
            ->set('full_name', 'Penguji')->set('nisn', '1234567890')->set('nik', self::NIK)
            ->set('birth_place', 'Majalengka')->set('birth_date', '2005-01-01')->set('gender', 'male')
            ->set('religion', 'Islam')->set('address', 'Jl. Uji')->set('phone', '081234567890')
            ->set('step', 1)->call('nextStep');

        $component->assertHasNoErrors()->assertSet('step', 2);
    }

    // ── Ubah NIK setelah daftar ──────────────────────────────────────────

    public function test_changing_nik_to_a_blocked_one_is_refused_for_the_registrant(): void
    {
        $this->enforce();
        $program = $this->program();
        $this->existing('menunggu_interview', [], [], $program->id);

        $own = $this->registration(null, 'terdaftar', ['nik' => '3210000000001111', 'first_choice_program_id' => $program->id]);

        $payload = [
            'section' => 'data_diri', 'full_name' => $own->full_name, 'nisn' => '1234567890', 'nik' => self::NIK,
            'birth_place' => 'Majalengka', 'birth_date' => '2005-01-01', 'gender' => 'male', 'religion' => 'Islam',
            'address' => 'Jl. Uji', 'phone' => '081234567890',
        ];

        $this->actingAs($own->user)->post('/pendaftaran/detail/update', $payload)->assertSessionHasErrors('nik');
        $this->assertSame('3210000000001111', $own->fresh()->nik);

        // Saklar mati: perubahan diizinkan.
        $this->enforce(false);
        $this->actingAs($own->user)->post('/pendaftaran/detail/update', $payload)->assertSessionHasNoErrors();
        $this->assertSame(self::NIK, $own->fresh()->nik);
    }

    public function test_unchanged_nik_is_not_rechecked_on_data_update(): void
    {
        $this->enforce();
        $program = $this->program();
        $this->existing('menunggu_interview', [], [], $program->id);
        $own = $this->registration(null, 'terdaftar', ['nik' => self::NIK, 'first_choice_program_id' => $program->id]);

        $this->actingAs($own->user)->post('/pendaftaran/detail/update', [
            'section' => 'data_diri', 'full_name' => 'Nama Diperbarui', 'nisn' => '1234567890', 'nik' => self::NIK,
            'birth_place' => 'Majalengka', 'birth_date' => '2005-01-01', 'gender' => 'male', 'religion' => 'Islam',
            'address' => 'Jl. Uji', 'phone' => '081234567890',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Nama Diperbarui', $own->fresh()->full_name);
    }

    public function test_admin_nik_change_is_never_blocked_but_warns(): void
    {
        $this->enforce();
        $program = $this->program();
        $this->existing('menunggu_interview', [], [], $program->id);
        $own = $this->registration(null, 'terdaftar', ['nik' => '3210000000002222', 'first_choice_program_id' => $program->id]);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post("/admin/pendaftar/{$own->id}/update-data", [
            'full_name' => $own->full_name, 'nik' => self::NIK, 'birth_place' => 'Majalengka', 'birth_date' => '2005-01-01',
            'gender' => 'male', 'phone' => '081234567890', 'address' => 'Jl. Uji', 'school_name' => 'SMA Uji',
            'graduation_year' => '2024', 'admission_path' => 'umum', 'email' => $own->user->email,
            'first_choice_program_id' => $program->id,
        ])->assertSessionHas('success', fn ($m) => str_contains($m, 'NIK ganda'));

        $this->assertSame(self::NIK, $own->fresh()->nik);
    }

    // ── Penanda izin daftar lagi ─────────────────────────────────────────

    public function test_admin_can_set_and_revoke_the_reapply_flag_with_a_logged_reason(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $numbered = $this->registration(null, 'daftar_ulang_selesai', ['nim' => '26123451001']);

        $this->actingAs($admin)->post("/admin/pendaftar/{$numbered->id}/izin-daftar-lagi", ['allowed' => 1, 'note' => ''])
            ->assertSessionHasErrors('note');
        $this->assertFalse((bool) $numbered->fresh()->reapply_allowed);

        $this->actingAs($admin)->post("/admin/pendaftar/{$numbered->id}/izin-daftar-lagi", ['allowed' => 1, 'note' => 'Cuti selesai'])
            ->assertSessionHas('success');
        $numbered->refresh();
        $this->assertTrue((bool) $numbered->reapply_allowed);
        $this->assertSame('Cuti selesai', $numbered->reapply_note);
        $this->assertTrue(PaymentLog::where('registration_id', $numbered->id)->where('action', 'reapply_allowed')->exists());

        $this->actingAs($admin)->post("/admin/pendaftar/{$numbered->id}/izin-daftar-lagi", ['allowed' => 0, 'note' => 'Salah input'])
            ->assertSessionHas('success');
        $this->assertFalse((bool) $numbered->fresh()->reapply_allowed);
        $this->assertTrue(PaymentLog::where('registration_id', $numbered->id)->where('action', 'reapply_revoked')->exists());
    }

    public function test_reapply_flag_needs_a_numbered_registration_and_staff(): void
    {
        $admin = $this->makeUser('admin');
        $this->period();
        $plain = $this->registration(null, 'terdaftar');
        $numbered = $this->registration(null, 'daftar_ulang_selesai', ['nim' => '26123451002']);

        $this->actingAs($admin)->post("/admin/pendaftar/{$plain->id}/izin-daftar-lagi", ['allowed' => 1, 'note' => 'x'])
            ->assertSessionHas('error');
        $this->assertFalse((bool) $plain->fresh()->reapply_allowed);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->post("/admin/pendaftar/{$numbered->id}/izin-daftar-lagi", ['allowed' => 1, 'note' => 'x'])
            ->assertForbidden();
    }
}
