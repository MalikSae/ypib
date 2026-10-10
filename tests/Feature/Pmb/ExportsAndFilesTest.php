<?php

namespace Tests\Feature\Pmb;

use App\Exports\ReferrerExport;
use App\Exports\RegistrationExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class ExportsAndFilesTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    // ── Excel ─────────────────────────────────────────────────────────
    private function sheetCells($export): array
    {
        $raw = Excel::raw($export, ExcelType::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'xl') . '.xlsx';
        file_put_contents($path, $raw);
        $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();
        @unlink($path);

        $cells = [];
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell;
            }
        }

        return $cells;
    }

    public function test_excel_exports_never_contain_formulas_and_keep_long_numbers_as_text(): void
    {
        $this->student('terdaftar', [
            'full_name' => '=HYPERLINK("http://jahat.test","klik")', 'nik' => '3210000000000099', 'school_name' => 'SMA Aman',
        ]);
        $owner = $this->makeUser('mahasiswa', ['name' => '=cmd|calc!A1']);
        $this->referrer(['user_id' => $owner->id]);

        foreach ([new RegistrationExport(), new ReferrerExport()] as $export) {
            foreach ($this->sheetCells($export) as $cell) {
                $this->assertNotSame('f', $cell->getDataType(), 'sel formula: ' . $cell->getValue());
            }
        }

        $cells = $this->sheetCells(new RegistrationExport());
        $values = array_map(fn ($c) => (string) $c->getValue(), $cells);
        $this->assertContains('=HYPERLINK("http://jahat.test","klik")', $values, 'teks asli harus utuh');
        $this->assertContains('SMA Aman', $values);

        $nik = collect($cells)->first(fn ($c) => (string) $c->getValue() === '3210000000000099');
        $this->assertNotNull($nik);
        $this->assertSame('s', $nik->getDataType(), 'NIK 16 digit harus teks agar tidak dibulatkan Excel');
    }

    public function test_recap_and_affiliate_exports_download(): void
    {
        $admin = $this->makeUser('admin');
        $this->student('terdaftar');
        $this->referrer();

        $this->actingAs($admin)->get('/admin/pendaftar/rekap/export')->assertOk();
        $this->actingAs($admin)->get('/admin/afiliasi/export')->assertOk();
        $this->actingAs($admin)->get('/admin/pendaftar/export')->assertOk();
    }

    // ── CSV streaming ─────────────────────────────────────────────────
    private function parseCsv(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = array_values(array_filter(preg_split('/\r\n|\n/', $content), fn ($l) => $l !== ''));

        return array_map('str_getcsv', $lines);
    }

    private function bulkRegistrations(int $count): void
    {
        $program = $this->program(['name' => 'Prodi Massal']);
        $period = $this->period();
        $now = now()->toDateTimeString();

        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = ['name' => "Massal $i", 'email' => "massal{$i}@uji.test", 'password' => 'x', 'role' => 'mahasiswa',
                'phone' => '0813' . str_pad((string) $i, 7, '0', STR_PAD_LEFT), 'created_at' => $now, 'updated_at' => $now];
        }
        foreach (array_chunk($users, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $ids = DB::table('users')->where('email', 'like', 'massal%@uji.test')->orderBy('id')->pluck('id')->all();
        $rows = [];
        foreach ($ids as $i => $id) {
            $rows[] = [
                'period_id' => $period->id, 'user_id' => $id, 'registration_number' => 'MS-' . $i, 'first_choice_program_id' => $program->id,
                'full_name' => "Calon $i", 'nik' => '32' . str_pad((string) $i, 14, '0', STR_PAD_LEFT), 'birth_place' => 'Majalengka',
                'birth_date' => '2005-01-01', 'gender' => 'male', 'address' => 'x', 'phone' => '0813' . str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'school_name' => 'SMA', 'graduation_year' => 2024, 'status' => $i % 3 ? 'terdaftar' : 'diterima',
                'registration_type' => 'reguler', 'admission_path' => 'umum', 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('registrations')->insert($chunk);
        }
    }

    public function test_csv_export_streams_every_row_with_bom_and_consistent_columns(): void
    {
        $admin = $this->makeUser('admin');
        $this->bulkRegistrations(120);

        $response = $this->actingAs($admin)->get('/admin/pendaftar/export?format=csv');
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));

        $raw = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $raw);

        $rows = $this->parseCsv($raw);
        $this->assertCount(121, $rows);
        $this->assertSame('No. Registrasi', $rows[0][0]);
        foreach ($rows as $row) {
            $this->assertCount(14, $row);
        }
        $this->assertSame('MS-119', $rows[1][0], 'urutan harus menurun berdasarkan id');
        $this->assertSame('MS-0', $rows[120][0]);
    }

    public function test_csv_applies_filters_and_is_spreadsheet_safe(): void
    {
        $admin = $this->makeUser('admin');
        $program = $this->program(['name' => 'Prodi Saring']);
        $other = $this->program(['name' => 'Prodi Lain']);

        $this->registration(null, 'terdaftar', [
            'first_choice_program_id' => $program->id, 'registration_number' => 'SAFE-1', 'full_name' => '=HYPERLINK("http://e.test","x")',
            'phone' => '081234567890', 'nik' => '3210000000000099', 'school_name' => '+cmd|calc',
        ]);
        $this->registration(null, 'diterima', ['first_choice_program_id' => $program->id, 'registration_number' => 'FILTER-STATUS']);
        $this->registration(null, 'terdaftar', ['first_choice_program_id' => $other->id, 'registration_number' => 'FILTER-PRODI']);

        $rows = $this->parseCsv($this->actingAs($admin)->get("/admin/pendaftar/export?format=csv&prodi={$program->id}&status=terdaftar")->streamedContent());
        array_shift($rows);

        $this->assertCount(1, $rows, 'filter prodi/status diabaikan');
        $row = $rows[0];
        $this->assertSame('SAFE-1', $row[0]);
        $this->assertSame("'=HYPERLINK(\"http://e.test\",\"x\")", $row[1], 'formula tidak dinetralkan');
        $this->assertSame("'+cmd|calc", $row[5]);
        $this->assertSame('="3210000000000099"', $row[3], 'NIK harus dibungkus agar tidak dibulatkan Excel');
        $this->assertSame('="081234567890"', $row[4], 'nol di depan harus dipertahankan');
    }

    public function test_xlsx_is_refused_above_limit_but_csv_still_works(): void
    {
        $admin = $this->makeUser('admin');
        $this->assertSame(5000, RegistrationExport::XLSX_MAX_ROWS);
        $this->bulkRegistrations(RegistrationExport::XLSX_MAX_ROWS + 1);

        $response = $this->from('/admin/pendaftar')->actingAs($admin)->get('/admin/pendaftar/export');
        $response->assertRedirect('/admin/pendaftar');
        $this->assertStringContainsString('Export CSV', session('error'));
        $this->assertStringContainsString('5001', session('error'));

        $raw = $this->actingAs($admin)->get('/admin/pendaftar/export?format=csv')->streamedContent();
        $this->assertSame(5002, substr_count($raw, "\n"));
    }

    public function test_export_format_is_validated_and_buttons_exist(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin/pendaftar/export?format=pdf')->assertSessionHasErrors('format');

        $html = $this->actingAs($admin)->get('/admin/pendaftar')->assertOk()->getContent();
        $this->assertStringContainsString('Export Excel', $html);
        $this->assertStringContainsString('name="format" value="csv"', $html);
    }

    // ── Berkas privat ─────────────────────────────────────────────────
    public function test_admin_uploads_are_stored_privately_and_replaced_files_are_deleted(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $admin = $this->makeUser('admin');
        [, $registration] = $this->student('menunggu_konfirmasi', ['registration_number' => null]);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/konfirmasi-bayar", ['bukti_bayar' => UploadedFile::fake()->image('b.png')])
            ->assertSessionHas('success');
        $first = $registration->fresh()->payment_proof;
        Storage::disk('local')->assertExists($first);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/upload-bukti", ['bukti_bayar' => UploadedFile::fake()->image('b2.png')]);
        $second = $registration->fresh()->payment_proof;
        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertExists($second);
        Storage::disk('local')->assertMissing($first);

        $this->actingAs($admin)->post("/admin/pendaftar/{$registration->id}/document/upload", ['document_type' => 'kk', 'file' => UploadedFile::fake()->image('k.png')]);
        $document = $registration->documents()->where('document_type', 'kk')->first();
        Storage::disk('local')->assertExists($document->file_path);
        Storage::disk('public')->assertMissing($document->file_path);
    }

    public function test_re_registration_proof_is_private_and_old_one_is_removed(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$user, $registration] = $this->student('diterima');

        $this->actingAs($user)->post('/pendaftaran/upload-daftar-ulang-bukti', ['re_registration_payment_proof' => UploadedFile::fake()->image('d.png')])
            ->assertSessionHas('success');
        $first = $registration->fresh()->re_registration_payment_proof;
        Storage::disk('local')->assertExists($first);
        Storage::disk('public')->assertMissing($first);
        $this->assertSame('menunggu_konfirmasi_daftar_ulang', $registration->fresh()->status);

        $this->actingAs($user)->post('/pendaftaran/upload-daftar-ulang-bukti', ['re_registration_payment_proof' => UploadedFile::fake()->image('e.png')]);
        Storage::disk('local')->assertMissing($first);
    }

    public function test_legacy_public_files_are_served_and_privatize_command_moves_them(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        [$owner, $registration] = $this->student('terdaftar', ['payment_proof' => 'bukti-bayar/lama.png']);

        Storage::disk('public')->put('bukti-bayar/lama.png', 'LAMA');
        Storage::disk('public')->put('program-galleries/tetap.png', 'GALERI');

        $this->actingAs($owner)->get("/berkas/registrasi/{$registration->id}/payment_proof")->assertOk();

        $this->artisan('files:privatize', ['--dry-run' => true])->assertSuccessful();
        Storage::disk('public')->assertExists('bukti-bayar/lama.png');
        Storage::disk('local')->assertMissing('bukti-bayar/lama.png');

        $this->artisan('files:privatize')->assertSuccessful();
        Storage::disk('public')->assertMissing('bukti-bayar/lama.png');
        Storage::disk('local')->assertExists('bukti-bayar/lama.png');
        $this->assertSame('LAMA', Storage::disk('local')->get('bukti-bayar/lama.png'));
        Storage::disk('public')->assertExists('program-galleries/tetap.png');     // bukan berkas sensitif

        $this->actingAs($owner)->get("/berkas/registrasi/{$registration->id}/payment_proof")->assertOk();
        $this->artisan('files:privatize')->assertSuccessful();                      // idempoten
    }

    public function test_image_uploads_become_webp_and_are_cleaned_up(): void
    {
        Storage::fake('public');
        $admin = $this->makeUser('admin');
        $faculty = $this->faculty();

        $this->actingAs($admin)->post('/admin/partners', [
            'name' => 'Mitra', 'logo' => UploadedFile::fake()->image('logo.png', 800, 400), 'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $partner = \App\Models\Partner::first();
        $this->assertStringEndsWith('.webp', $partner->logo_path);
        Storage::disk('public')->assertExists($partner->logo_path);

        $old = $partner->logo_path;
        $this->actingAs($admin)->put("/admin/partners/{$partner->id}", [
            'name' => 'Mitra', 'logo' => UploadedFile::fake()->image('baru.png', 300, 300), 'is_active' => 1,
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);

        $this->actingAs($admin)->post('/admin/programs', [
            'name' => 'Prodi Galeri', 'kode_prodi' => '98765', 'registration_track' => 'reguler', 'faculty_id' => $faculty->id,
            'accreditation' => 'B', 'quota' => 0, 'registration_fee' => 250000, 'referral_reward_amount' => 50000,
            're_registration_reward_amount' => 500000, 're_registration_minimum_payment' => 0, 'is_active' => 1,
            'gallery' => [UploadedFile::fake()->image('g1.jpg', 2000, 1000), UploadedFile::fake()->image('g2.png', 600, 600)],
        ])->assertSessionHasNoErrors();

        $program = \App\Models\Program::where('name', 'Prodi Galeri')->first();
        $paths = $program->galleries->pluck('image_path')->all();
        $this->assertCount(2, $paths);

        $this->actingAs($admin)->delete("/admin/programs/{$program->id}")->assertSessionHas('success');
        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }
}
