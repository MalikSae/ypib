<?php

namespace Tests\Feature\Pmb;

use App\Models\PmbPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T7 (PLAN_MULTI_PERIODE.md): riwayat pendaftaran dan pemilih periode untuk pendaftar.
 */
class RegistrationHistoryTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private PmbPeriod $current;
    private PmbPeriod $old;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->current = $this->period(['name' => 'PMB Berjalan']);
        $this->old = PmbPeriod::forceCreate([
            'name' => 'PMB Tahun Lalu', 'year' => (int) now()->subYear()->year, 'status' => 'published',
            'open_date' => now()->subYear()->toDateString(), 'close_date' => now()->subYear()->addMonths(2)->toDateString(),
            'university_bank_name' => 'BRI', 'university_bank_account' => '1', 'university_bank_account_name' => 'YPIB',
            'admin_whatsapp' => '081234567890',
        ]);
    }

    /** Akun dengan satu pendaftaran di periode lama dan satu di periode berjalan. */
    private function twoRegistrations(string $oldStatus = 'ditolak', string $currentStatus = 'menunggu_pembayaran'): array
    {
        $user = $this->makeUser();
        $old = $this->registration($user, $oldStatus, [
            'period_id' => $this->old->id, 'full_name' => 'Nama Tahun Lalu',
            'first_choice_program_id' => $this->program(['name' => 'Prodi Lama'])->id,
        ]);
        $new = $this->registration($user, $currentStatus, [
            'period_id' => $this->current->id, 'full_name' => 'Nama Tahun Ini',
            'first_choice_program_id' => $this->program(['name' => 'Prodi Baru'])->id,
        ]);

        return [$user, $old, $new];
    }

    // ── Daftar dan pemilih ───────────────────────────────────────────────

    public function test_history_list_appears_only_with_more_than_one_registration(): void
    {
        [$user] = $this->twoRegistrations();
        $this->actingAs($user)->get('/pendaftaran/status')
            ->assertOk()->assertSee('Pendaftaran Saya')->assertSee('PMB Tahun Lalu')->assertSee('Sedang dilihat');

        [$single] = $this->student('terdaftar', ['period_id' => $this->current->id]);
        $this->actingAs($single)->get('/pendaftaran/status')->assertOk()->assertDontSee('Pendaftaran Saya');
    }

    public function test_selecting_a_period_persists_across_pages_until_reset(): void
    {
        [$user] = $this->twoRegistrations();

        $this->actingAs($user)->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Ini');

        $this->get('/pendaftaran/status?periode=' . $this->old->id)->assertOk()->assertSee('Prodi Lama');
        $this->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Lalu')->assertDontSee('Nama Tahun Ini');

        $this->get('/pendaftaran/status?periode=reset')->assertOk();
        $this->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Ini');
    }

    public function test_period_choice_of_another_account_or_without_registration_is_ignored(): void
    {
        [$user] = $this->twoRegistrations();

        $stranger = $this->makeUser();
        $this->registration($stranger, 'terdaftar', ['period_id' => $this->old->id, 'full_name' => 'Orang Lain']);

        // Akun ini punya pendaftaran di kedua periode; periode yang tidak dikenal diabaikan.
        $this->actingAs($user)->get('/pendaftaran/status?periode=999999')->assertOk()->assertSee('Prodi Baru');
        $this->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Ini')->assertDontSee('Orang Lain');
    }

    public function test_the_choice_is_per_session_not_global(): void
    {
        [$user] = $this->twoRegistrations();
        $this->actingAs($user)->get('/pendaftaran/status?periode=' . $this->old->id)->assertOk();

        [$other] = $this->twoRegistrations();
        $this->actingAs($other)->get('/pendaftaran/detail')->assertOk()->assertSee('Nama Tahun Ini');
    }

    public function test_actions_follow_the_registration_being_viewed(): void
    {
        [$user, $old, $new] = $this->twoRegistrations('menunggu_pembayaran', 'menunggu_pembayaran');

        $this->actingAs($user)->get('/pendaftaran/status?periode=' . $this->old->id)->assertOk();
        $this->post('/pendaftaran/upload-bukti', ['payment_proof' => UploadedFile::fake()->image('b.png')])
            ->assertSessionHas('success');

        $this->assertSame('menunggu_konfirmasi', $old->fresh()->status);
        $this->assertSame('menunggu_pembayaran', $new->fresh()->status);
    }

    public function test_downloads_follow_the_viewed_registration(): void
    {
        // Pendaftaran lama sudah diterima; pendaftaran berjalan belum.
        [$user] = $this->twoRegistrations('diterima', 'menunggu_pembayaran');

        // Tanpa pilihan, yang dilihat adalah pendaftaran berjalan: belum lulus, SKL ditolak.
        $this->actingAs($user)->get('/pendaftaran/surat-kelulusan')->assertSessionHas('error');

        // Setelah memilih pendaftaran lama, SKL miliknya yang diunduh.
        $this->get('/pendaftaran/status?periode=' . $this->old->id)->assertOk();
        $response = $this->get('/pendaftaran/surat-kelulusan')->assertOk();
        $this->assertStringContainsString('nama-tahun-lalu', (string) $response->headers->get('content-disposition'));

        // Kembali ke pendaftaran utama: SKL tidak tersedia lagi.
        $this->get('/pendaftaran/status?periode=reset')->assertOk();
        $this->get('/pendaftaran/surat-kelulusan')->assertSessionHas('error');
    }

    // ── Langkah tertunda di periode lain ─────────────────────────────────

    public function test_pending_step_in_another_period_is_surfaced(): void
    {
        [$user] = $this->twoRegistrations('menunggu_tes_tulis', 'menunggu_pembayaran');

        $this->actingAs($user)->get('/pendaftaran/status')
            ->assertOk()
            ->assertSee('menunggu tindakan Anda')
            ->assertSee('PMB Tahun Lalu')
            ->assertSee('Menunggu Tes Tulis');
    }

    public function test_finished_or_archived_registrations_do_not_raise_the_banner(): void
    {
        [$userA] = $this->twoRegistrations('ditolak', 'menunggu_pembayaran');
        $this->actingAs($userA)->get('/pendaftaran/status')->assertOk()->assertDontSee('menunggu tindakan Anda');

        [$userB] = $this->twoRegistrations('menunggu_tes_tulis', 'menunggu_pembayaran');
        $this->old->update(['status' => 'archived']);
        $this->actingAs($userB)->get('/pendaftaran/status')->assertOk()->assertDontSee('menunggu tindakan Anda');
    }

    public function test_the_banner_links_to_the_pending_registration(): void
    {
        [$user] = $this->twoRegistrations('terdaftar', 'menunggu_pembayaran');

        $this->actingAs($user)->get('/pendaftaran/status')
            ->assertOk()
            ->assertSee('/pendaftaran/status?periode=' . $this->old->id, false);
    }
}
