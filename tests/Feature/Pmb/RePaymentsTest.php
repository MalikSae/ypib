<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\PaymentLog;
use App\Models\PeriodProgram;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class RePaymentsTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function terms(int $programId, int $fee = 10_000_000, int $min = 5_000_000, int $registrationFee = 250_000): void
    {
        PeriodProgram::create([
            'period_id' => $this->period()->id, 'program_id' => $programId, 'campus_id' => Campus::defaultId(), 'is_offered' => true,
            'registration_fee' => $registrationFee, 're_registration_fee' => $fee, 're_registration_minimum_payment' => $min,
        ]);
    }

    /** @return array{0: \App\Models\User, 1: Registration} */
    private function acceptedStudent(string $status = 'diterima'): array
    {
        $program = $this->program();
        $this->terms($program->id);

        return $this->student($status, ['first_choice_program_id' => $program->id]);
    }

    private function submit($user, string $amount)
    {
        return $this->actingAs($user)->post('/pendaftaran/upload-daftar-ulang-bukti', [
            're_registration_payment_proof' => UploadedFile::fake()->image('b.png'),
            'amount'                        => $amount,
        ]);
    }

    public function test_student_amount_is_bounded_by_minimum_and_remaining(): void
    {
        Storage::fake('local');
        [$user, $registration] = $this->acceptedStudent();

        $this->submit($user, '')->assertSessionHasErrors('amount');
        $this->submit($user, '4.000.000')->assertSessionHasErrors('amount');
        $this->submit($user, '12.000.000')->assertSessionHasErrors('amount');
        $this->assertSame(0, $registration->payments()->count());

        $this->submit($user, '5.000.000')->assertSessionHas('success');

        $payment = $registration->payments()->first();
        $this->assertSame(5_000_000, $payment->amount);
        $this->assertSame(5_000_000, $payment->claimed_amount);
        $this->assertSame('menunggu_verifikasi', $payment->status);
        $this->assertSame('menunggu_konfirmasi_daftar_ulang', $registration->fresh()->status);
    }

    public function test_admin_verifies_with_corrected_amount_and_nim_issues_at_minimum(): void
    {
        Storage::fake('local');
        $admin = $this->makeUser('admin');
        [$user, $registration] = $this->acceptedStudent();
        $this->submit($user, '6000000');
        $url = "/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang";

        // dikoreksi admin ke bawah batas minimal: terverifikasi, tetapi belum selesai
        $this->actingAs($admin)->post($url, ['amount' => '3000000'])->assertSessionHas('success');
        $registration->refresh();
        $payment = $registration->payments()->first();
        $this->assertSame('terverifikasi', $payment->status);
        $this->assertSame(3_000_000, $payment->amount);
        $this->assertSame(6_000_000, $payment->claimed_amount);
        $this->assertSame('menunggu_konfirmasi_daftar_ulang', $registration->status);
        $this->assertNull($registration->nim);

        // pembayaran lain dicatat admin sampai total mencapai minimal
        $this->actingAs($admin)->post($url, ['amount' => '2000000', 'note' => 'Tunai di kantor'])->assertSessionHas('success');
        $registration->refresh();
        $this->assertSame('daftar_ulang_selesai', $registration->status);
        $this->assertNotNull($registration->nim);
        $this->assertSame(5_000_000, $registration->reRegistrationPaid());
        $this->assertSame(5_000_000, $registration->reRegistrationRemaining());
    }

    public function test_additional_payment_after_completion_only_adds_to_the_total(): void
    {
        $admin = $this->makeUser('admin');
        [, $registration] = $this->acceptedStudent('menunggu_konfirmasi_daftar_ulang');
        $registration->forceFill(['re_registration_payment_proof' => 'x.png'])->save();
        $url = "/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang";

        $this->actingAs($admin)->post($url, ['amount' => '5000000'])->assertSessionHas('success');
        $nim = $registration->fresh()->nim;
        $this->assertSame('daftar_ulang_selesai', $registration->fresh()->status);

        $this->actingAs($admin)->post($url)->assertSessionHas('error');
        $this->actingAs($admin)->post($url, ['amount' => '5000000'])->assertSessionHas('success');

        $registration->refresh();
        $this->assertSame($nim, $registration->nim);
        $this->assertSame(10_000_000, $registration->reRegistrationPaid());
        $this->assertSame(0, $registration->reRegistrationRemaining());
        $this->assertSame(1, PaymentLog::where('registration_id', $registration->id)->where('action', 're_registration_confirmed')->count());
    }

    public function test_fully_paid_student_cannot_submit_more(): void
    {
        $admin = $this->makeUser('admin');
        [$user, $registration] = $this->acceptedStudent('menunggu_konfirmasi_daftar_ulang');
        $registration->forceFill(['re_registration_payment_proof' => 'x.png'])->save();

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang", ['amount' => '10000000'])->assertSessionHas('success');
        $this->submit($user, '1000')->assertSessionHas('error');
        $this->assertSame(1, $registration->payments()->count());
    }

    public function test_rejecting_a_claim_returns_the_student_to_the_payment_step(): void
    {
        Storage::fake('local');
        $admin = $this->makeUser('admin');
        [$user, $registration] = $this->acceptedStudent();
        $this->submit($user, '5000000');
        $payment = $registration->payments()->first();

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/pembayaran/{$payment->id}/tolak")->assertSessionHasErrors('note');
        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/pembayaran/{$payment->id}/tolak", ['note' => 'Bukti buram'])->assertSessionHas('success');

        $this->assertSame('ditolak', $payment->fresh()->status);
        $this->assertSame('diterima', $registration->fresh()->status);
        $this->assertSame(0, $registration->fresh()->reRegistrationPaid());
        $this->submit($user, '5000000')->assertSessionHas('success');
    }

    public function test_payment_proof_is_only_visible_to_owner_and_staff(): void
    {
        Storage::fake('local');
        [$user, $registration] = $this->acceptedStudent();
        $this->submit($user, '5000000');
        $payment = $registration->payments()->first();

        $this->actingAs($user)->get("/berkas/pembayaran/{$payment->id}")->assertOk();
        $this->actingAs($this->makeUser('admin'))->get("/berkas/pembayaran/{$payment->id}")->assertOk();
        $this->actingAs($this->makeUser())->get("/berkas/pembayaran/{$payment->id}")->assertForbidden();
    }

    public function test_confirming_registration_payment_snapshots_the_fee(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program();
        $this->terms($program->id, registrationFee: 300_000);
        [, $registration] = $this->student('menunggu_konfirmasi', ['first_choice_program_id' => $program->id, 'registration_number' => null, 'payment_proof' => 'x.png']);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar")->assertSessionHas('success');
        $this->assertSame(300_000, (int) $registration->fresh()->registration_fee_paid);

        // tarif berubah kemudian: angka yang sudah tercatat tidak ikut berubah
        PeriodProgram::where('program_id', $program->id)->update(['registration_fee' => 999_000]);
        $this->assertSame(300_000, (int) $registration->fresh()->registration_fee_paid);
    }

    public function test_dashboard_reports_verified_amounts_per_program(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['name' => 'Prodi Laporan']);
        $this->terms($program->id);
        [, $paid] = $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $program->id]);
        $paid->forceFill(['registration_fee_paid' => 250_000])->save();
        RegistrationPayment::create(['registration_id' => $paid->id, 'type' => 're_registration', 'amount' => 7_000_000, 'status' => 'terverifikasi']);
        RegistrationPayment::create(['registration_id' => $paid->id, 'type' => 're_registration', 'amount' => 900_000, 'status' => 'menunggu_verifikasi']);
        $this->student('daftar_ulang_selesai', ['first_choice_program_id' => $program->id]);

        $response = $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        $response->assertSee('Prodi Laporan')
            ->assertSee('Rp 250.000')
            ->assertSee('Rp 7.000.000')
            ->assertSee('Rp 3.000.000')
            ->assertDontSee('Rp 900.000')
            ->assertSee('1 pendaftar sudah selesai daftar ulang tetapi nominalnya belum tercatat');
    }

    public function test_legacy_confirmation_without_amount_still_completes(): void
    {
        $admin = $this->makeUser('admin');
        [, $registration] = $this->acceptedStudent('menunggu_konfirmasi_daftar_ulang');
        $registration->forceFill(['re_registration_payment_proof' => 'x.png'])->save();

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-daftar-ulang")->assertSessionHas('success');

        $this->assertSame('daftar_ulang_selesai', $registration->fresh()->status);
        $this->assertSame(0, $registration->payments()->count());
    }
}
