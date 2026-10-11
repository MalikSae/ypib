<?php

namespace Tests\Feature\Pmb;

use App\Models\PmbPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/**
 * Multi-periode T2 (PLAN_MULTI_PERIODE.md): kelola periode di admin.
 */
class PeriodManagementTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function readyPeriod(array $extra = []): PmbPeriod
    {
        $n = $this->nextSeq();

        return PmbPeriod::forceCreate(array_merge([
            'name'                         => 'Periode Uji ' . $n,
            'year'                         => 2026,
            'open_date'                    => '2026-03-01',
            'close_date'                   => '2026-03-31',
            're_registration_deadline'     => '2026-06-30',
            'university_bank_name'         => 'BRI',
            'university_bank_account'      => '1234567890',
            'university_bank_account_name' => 'YPIB Majalengka',
            'admin_whatsapp'               => '081234567890',
            'status'                       => 'draft',
        ], $extra));
    }

    private function validPayload(array $extra = []): array
    {
        return array_merge([
            'name'                     => 'PMB 2027/2028',
            'year'                     => 2027,
            'open_date'                => '2027-03-01',
            'close_date'               => '2027-03-31',
            're_registration_deadline' => '2027-06-30',
            'payment_grace_days'       => 3,
            'nim_sequence_start'       => 1,
            'admin_whatsapp'           => '081234567890',
        ], $extra);
    }

    // ── Akses ────────────────────────────────────────────────────────────

    public function test_only_staff_can_manage_periods(): void
    {
        $period = $this->readyPeriod();
        $student = $this->makeUser('mahasiswa');
        $operator = $this->makeUser('operator');

        $this->actingAs($student)->get('/admin/periode')->assertForbidden();
        $this->actingAs($student)->post("/admin/periode/{$period->id}/tayangkan")->assertForbidden();
        $this->assertSame('draft', $period->fresh()->status);

        $this->actingAs($operator)->get('/admin/periode')->assertOk();
    }

    public function test_legacy_settings_url_redirects_to_period_list(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/pengaturan')->assertRedirect('/admin/periode');
    }

    // ── Buat dan ubah ────────────────────────────────────────────────────

    public function test_new_period_is_created_as_draft_and_never_auto_published(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/periode', $this->validPayload(['status' => 'published', 'is_active' => 1]))
            ->assertRedirect('/admin/periode');

        $period = PmbPeriod::firstOrFail();
        $this->assertSame('draft', $period->status);
        $this->assertFalse($period->is_active);
        $this->assertSame(2027, (int) $period->year);
    }

    public function test_index_does_not_create_a_period_implicitly(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/periode')->assertOk()->assertSee('Belum ada periode');

        $this->assertSame(0, PmbPeriod::count());
    }

    public function test_validation_rejects_bad_dates_and_grace_days(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/periode', $this->validPayload(['close_date' => '2027-02-01']))
            ->assertSessionHasErrors('close_date');
        $this->actingAs($admin)->post('/admin/periode', $this->validPayload(['re_registration_deadline' => '2027-03-15']))
            ->assertSessionHasErrors('re_registration_deadline');
        $this->actingAs($admin)->post('/admin/periode', $this->validPayload(['payment_grace_days' => 31]))
            ->assertSessionHasErrors('payment_grace_days');

        $this->assertSame(0, PmbPeriod::count());
    }

    public function test_update_cannot_change_status_through_the_form(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod();

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validPayload([
            'status' => 'published', 'is_active' => 1, 'id' => 999,
        ]))->assertRedirect('/admin/periode');

        $period->refresh();
        $this->assertSame('draft', $period->status);
        $this->assertFalse($period->is_active);
        $this->assertSame('PMB 2027/2028', $period->name);
    }

    public function test_year_is_locked_once_the_period_has_registrations(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['status' => 'published']);
        $this->registration(null, 'terdaftar', ['period_id' => $period->id]);

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validPayload(['year' => 2030]))
            ->assertSessionHasErrors('year');

        $this->assertSame(2026, (int) $period->fresh()->year);

        // Tahun yang sama tetap boleh disimpan bersama perubahan lain.
        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validPayload([
            'year' => 2026, 'name' => 'Nama Baru', 'open_date' => '2026-03-01', 'close_date' => '2026-03-31',
        ]))->assertRedirect('/admin/periode');
        $this->assertSame('Nama Baru', $period->fresh()->name);
    }

    public function test_published_period_requires_deadline_and_non_overlapping_window(): void
    {
        $admin = $this->makeUser('admin');
        $this->readyPeriod(['name' => 'Tayang A', 'status' => 'published', 'open_date' => '2027-03-01', 'close_date' => '2027-03-31']);
        $period = $this->readyPeriod(['name' => 'Tayang B', 'status' => 'published', 'open_date' => '2027-05-01', 'close_date' => '2027-05-31']);

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validPayload([
            'open_date' => '2027-05-01', 'close_date' => '2027-05-31', 're_registration_deadline' => null,
        ]))->assertSessionHasErrors('re_registration_deadline');

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validPayload([
            'open_date' => '2027-03-20', 'close_date' => '2027-05-31',
        ]))->assertSessionHasErrors('open_date');
    }

    public function test_archived_period_cannot_be_edited(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['status' => 'archived']);

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", $this->validPayload())
            ->assertRedirect('/admin/periode')->assertSessionHas('error');

        $this->assertNotSame('PMB 2027/2028', $period->fresh()->name);
    }

    // ── Tayang ───────────────────────────────────────────────────────────

    public function test_publish_requires_deadline_bank_and_whatsapp(): void
    {
        $admin = $this->makeUser('admin');

        $noDeadline = $this->readyPeriod(['re_registration_deadline' => null]);
        $noBank = $this->readyPeriod(['university_bank_account' => null, 'open_date' => '2027-03-01', 'close_date' => '2027-03-31']);
        $noWhatsapp = $this->readyPeriod(['admin_whatsapp' => '', 'open_date' => '2028-03-01', 'close_date' => '2028-03-31']);

        foreach ([$noDeadline, $noBank, $noWhatsapp] as $period) {
            $this->actingAs($admin)->post("/admin/periode/{$period->id}/tayangkan")->assertSessionHas('error');
            $this->assertSame('draft', $period->fresh()->status);
        }
    }

    public function test_publish_succeeds_when_complete_and_records_the_transition(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod();

        $this->actingAs($admin)->post("/admin/periode/{$period->id}/tayangkan")->assertSessionHas('success');

        $period->refresh();
        $this->assertSame('published', $period->status);
        $this->assertTrue($period->is_active);
        $this->assertNotNull($period->published_at);
    }

    public function test_publish_is_refused_when_the_window_overlaps_a_published_period(): void
    {
        $admin = $this->makeUser('admin');
        $this->readyPeriod(['name' => 'Sudah Tayang', 'status' => 'published']);
        $period = $this->readyPeriod(['name' => 'Beririsan', 'open_date' => '2026-03-15', 'close_date' => '2026-04-15']);

        $this->actingAs($admin)->post("/admin/periode/{$period->id}/tayangkan")
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'beririsan') && str_contains($m, 'Sudah Tayang'));

        $this->assertSame('draft', $period->fresh()->status);
    }

    public function test_adjacent_windows_do_not_overlap(): void
    {
        $admin = $this->makeUser('admin');
        $this->readyPeriod(['status' => 'published', 'open_date' => '2026-03-01', 'close_date' => '2026-03-31']);
        $next = $this->readyPeriod(['open_date' => '2026-04-01', 'close_date' => '2026-04-30']);

        $this->actingAs($admin)->post("/admin/periode/{$next->id}/tayangkan")->assertSessionHas('success');
    }

    public function test_unpublish_only_without_registrations(): void
    {
        $admin = $this->makeUser('admin');
        $empty = $this->readyPeriod(['status' => 'published']);
        $used = $this->readyPeriod(['status' => 'published', 'open_date' => '2027-03-01', 'close_date' => '2027-03-31']);
        $this->registration(null, 'terdaftar', ['period_id' => $used->id]);

        $this->actingAs($admin)->post("/admin/periode/{$empty->id}/batalkan-tayang")->assertSessionHas('success');
        $this->assertSame('draft', $empty->fresh()->status);

        $this->actingAs($admin)->post("/admin/periode/{$used->id}/batalkan-tayang")->assertSessionHas('error');
        $this->assertSame('published', $used->fresh()->status);
    }

    // ── Arsip ────────────────────────────────────────────────────────────

    public function test_archive_confirmation_shows_running_registrations(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['status' => 'published']);
        $this->registration(null, 'menunggu_tes_tulis', ['period_id' => $period->id]);
        $this->registration(null, 'daftar_ulang_selesai', ['period_id' => $period->id]);

        $response = $this->actingAs($admin)->get("/admin/periode/{$period->id}/arsip")->assertOk();

        $response->assertViewHas('summary', fn ($s) => $s['total'] === 2 && $s['running'] === 1 && $s['awaiting_re_registration'] === 0);
    }

    public function test_archive_needs_acknowledgement_when_re_registration_is_pending(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['status' => 'published']);
        $this->registration(null, 'menunggu_konfirmasi_daftar_ulang', ['period_id' => $period->id]);

        $this->actingAs($admin)->post("/admin/periode/{$period->id}/arsip")->assertSessionHas('error');
        $this->assertSame('published', $period->fresh()->status);

        $this->actingAs($admin)->post("/admin/periode/{$period->id}/arsip", ['acknowledge' => 1])
            ->assertRedirect('/admin/periode')->assertSessionHas('success');

        $period->refresh();
        $this->assertSame('archived', $period->status);
        $this->assertFalse($period->is_active);
        $this->assertNotNull($period->archived_at);
    }

    public function test_archived_period_drops_out_of_public_resolver_and_can_be_reopened(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['status' => 'published']);

        $this->actingAs($admin)->post("/admin/periode/{$period->id}/arsip")->assertSessionHas('success');
        $this->assertNull(PmbPeriod::displayPeriod());

        $this->actingAs($admin)->post("/admin/periode/{$period->id}/buka-kembali")->assertSessionHas('success');
        $this->assertSame($period->id, PmbPeriod::displayPeriod()->id);
    }

    public function test_reopen_is_refused_when_it_would_overlap(): void
    {
        $admin = $this->makeUser('admin');
        $archived = $this->readyPeriod(['status' => 'archived']);
        $this->readyPeriod(['name' => 'Penggantinya', 'status' => 'published', 'open_date' => '2026-03-10', 'close_date' => '2026-04-10']);

        $this->actingAs($admin)->post("/admin/periode/{$archived->id}/buka-kembali")->assertSessionHas('error');
        $this->assertSame('archived', $archived->fresh()->status);
    }

    // ── Salin ────────────────────────────────────────────────────────────

    public function test_duplicate_creates_next_year_draft_with_bank_and_contact(): void
    {
        $admin = $this->makeUser('admin');
        $source = $this->readyPeriod(['status' => 'published', 'payment_grace_days' => 5, 'nim_sequence_start' => 400]);

        $this->actingAs($admin)->post("/admin/periode/{$source->id}/salin")->assertSessionHas('success');

        $copy = PmbPeriod::where('id', '!=', $source->id)->firstOrFail();
        $this->assertSame('draft', $copy->status);
        $this->assertFalse($copy->is_active);
        $this->assertSame(2027, (int) $copy->year);
        $this->assertSame('2027-03-01', $copy->open_date->toDateString());
        $this->assertSame('2027-03-31', $copy->close_date->toDateString());
        $this->assertSame('2027-06-30', $copy->re_registration_deadline->toDateString());
        $this->assertSame('1234567890', $copy->university_bank_account);
        $this->assertSame('081234567890', $copy->admin_whatsapp);
        $this->assertSame(5, $copy->payment_grace_days);
        $this->assertSame(1, $copy->nim_sequence_start);
        $this->assertSame('published', $source->fresh()->status);
    }

    // ── Halaman ──────────────────────────────────────────────────────────

    public function test_create_and_edit_forms_render_with_current_values(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['name' => 'Periode Form Uji']);
        $this->registration(null, 'terdaftar', ['period_id' => $period->id]);

        $this->actingAs($admin)->get('/admin/periode/buat')
            ->assertOk()
            ->assertSee('Tambah Periode PMB')
            ->assertSee('Masa Bayar Setelah Tutup');

        $this->actingAs($admin)->get("/admin/periode/{$period->id}/edit")
            ->assertOk()
            ->assertSee('Periode Form Uji')
            ->assertSee('1234567890')
            ->assertSee('Terkunci karena sudah ada pendaftar');
    }

    public function test_sidebar_links_to_period_management(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee('Periode PMB');
    }

    // ── Daftar ───────────────────────────────────────────────────────────

    public function test_list_shows_counts_and_phase(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->readyPeriod(['name' => 'PMB Daftar Uji', 'status' => 'published']);
        $this->registration(null, 'terdaftar', ['period_id' => $period->id]);
        $this->registration(null, 'daftar_ulang_selesai', ['period_id' => $period->id]);

        $this->actingAs($admin)->get('/admin/periode')
            ->assertOk()
            ->assertSee('PMB Daftar Uji')
            ->assertSee('(1 berjalan)');
    }
}
