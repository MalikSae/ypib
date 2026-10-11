<?php

namespace Tests\Feature\Pmb;

use App\Models\Faculty;
use App\Models\PaymentLog;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class PlatformSecurityAndConfigTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ── Header & proxy ────────────────────────────────────────────────
    public function test_security_headers_on_pages_and_on_404(): void
    {
        foreach (['/', '/login', '/register', '/halaman-tidak-ada'] as $url) {
            $headers = $this->get($url)->headers;

            $this->assertSame('SAMEORIGIN', $headers->get('X-Frame-Options'), $url);
            $this->assertSame('nosniff', $headers->get('X-Content-Type-Options'));
            $this->assertSame('strict-origin-when-cross-origin', $headers->get('Referrer-Policy'));
            $this->assertStringContainsString('camera=(self)', (string) $headers->get('Permissions-Policy'), 'kamera dipakai pemindai QR panitia');
            $this->assertNull($headers->get('Strict-Transport-Security'));
        }
    }

    public function test_hsts_only_in_production_over_https(): void
    {
        $this->app['env'] = 'production';

        $this->assertNotNull($this->get('https://localhost/login')->headers->get('Strict-Transport-Security'));
        $this->assertNull($this->get('http://localhost/login')->headers->get('Strict-Transport-Security'));
    }

    public function test_client_ip_is_read_from_trusted_proxies_only(): void
    {
        $probe = fn (string $remote, ?string $forwarded) => (function () use ($remote, $forwarded) {
            $path = '/_ip_' . md5($remote . $forwarded);
            Route::get($path, fn () => response(request()->ip()));
            $request = $this->withServerVariables(['REMOTE_ADDR' => $remote]);
            if ($forwarded) {
                $request = $request->withHeaders(['X-Forwarded-For' => $forwarded]);
            }

            return $request->get($path)->getContent();
        })();

        // edge Cloudflare (172.64.0.0/13): IP asli dibaca dari X-Forwarded-For
        $this->assertSame('203.0.113.9', $probe('172.69.10.1', '203.0.113.9'));
        // klien memalsukan XFF: yang dipakai tetap IP asli di kanan
        $this->assertSame('203.0.113.9', $probe('172.69.10.1', '1.2.3.4, 203.0.113.9'));
        // sumber bukan proxy tepercaya: XFF diabaikan
        $this->assertSame('198.51.100.7', $probe('198.51.100.7', '1.2.3.4'));
    }

    // ── Pembatas laju ─────────────────────────────────────────────────
    public function test_signup_endpoints_share_a_per_ip_limit_and_429_page_is_indonesian(): void
    {
        $statuses = [];
        for ($i = 0; $i < 12; $i++) {
            $statuses[] = $this->post('/register', [
                'name' => "Spam $i", 'email' => "spam{$i}@uji.test", 'phone' => '08123450' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            ])->getStatusCode();
            auth()->logout();
        }

        $this->assertSame(array_fill(0, 10, 302), array_slice($statuses, 0, 10));
        $this->assertSame(429, $statuses[10]);

        $resp = $this->post('/forgot-password', ['email' => 'siapa@uji.test']);   // bucket yang sama
        $resp->assertStatus(429);
        $this->assertStringContainsString('Terlalu banyak permintaan', $resp->getContent());
    }

    public function test_each_visitor_behind_one_edge_has_their_own_bucket(): void
    {
        $statuses = [];
        for ($i = 1; $i <= 12; $i++) {
            $statuses[] = $this->withServerVariables(['REMOTE_ADDR' => '172.69.10.1'])
                ->withHeaders(['X-Forwarded-For' => "203.0.113.$i"])
                ->post('/forgot-password', ['email' => "p{$i}@uji.test"])->getStatusCode();
        }

        $this->assertNotContains(429, $statuses);
    }

    public function test_referral_click_endpoint_is_throttled(): void
    {
        $codes = [];
        for ($i = 0; $i < 62; $i++) {
            $codes[] = $this->get('/ref/KODE' . $i)->getStatusCode();
        }

        $this->assertSame(302, $codes[0]);
        $this->assertContains(429, $codes);
    }

    // ── Zona waktu & bahasa ───────────────────────────────────────────
    public function test_timezone_and_locale_are_indonesian(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('id', app()->getLocale());
        $this->assertSame('Maret', Carbon::parse('2026-03-01')->translatedFormat('F'));
        $this->assertSame('Nama lengkap wajib diisi.', trans('validation.required', ['attribute' => trans('validation.attributes.full_name')]));

        $user = $this->makeUser();
        $this->actingAs($user)->patch('/profile', ['name' => '', 'email' => $user->email, 'phone' => ''])->assertSessionHasErrors(['name', 'phone']);
        $this->assertSame('Nama wajib diisi.', session('errors')->first('name'));
    }

    public function test_login_failure_message_is_indonesian(): void
    {
        $this->post('/login', ['email' => 'tidak@ada.test', 'password' => 'x'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('tidak cocok', session('errors')->first('email'));
    }

    // ── Seeder ────────────────────────────────────────────────────────
    private function seed_(): void
    {
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
    }

    public function test_seeder_refuses_production_without_strong_password(): void
    {
        $this->app['env'] = 'production';

        foreach ([null, '', 'password'] as $weak) {
            config(['app.seed_admin_password' => $weak]);
            $this->seed_();

            $this->assertSame(0, User::whereIn('email', ['admin@ypib.ac.id', 'operator@ypib.ac.id'])->count());
            $this->assertSame(0, Program::count());
        }

        config(['app.seed_admin_password' => 'Str0ng#Secret-1']);
        $this->seed_();

        $admin = User::where('email', 'admin@ypib.ac.id')->first();
        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('Str0ng#Secret-1', $admin->password));
        $this->assertFalse(Hash::check('password', $admin->password));
    }

    public function test_seeder_uses_default_outside_production_and_creates_open_period(): void
    {
        config(['app.seed_admin_password' => null]);
        $this->seed_();

        $this->assertTrue(Hash::check('password', User::where('email', 'admin@ypib.ac.id')->first()->password));

        $period = PmbPeriod::first();
        $this->assertTrue($period->isOpen(), 'periode hasil seed langsung tertutup');
        $this->assertSame((int) now()->year, (int) $period->year);
    }

    // ── Sanitasi HTML ─────────────────────────────────────────────────
    public function test_html_sanitizer_blocks_script_vectors_but_keeps_formatting(): void
    {
        $bad = '<p>Halo</p><script>alert(1)</script><img src=x onerror="alert(2)"><a href="javascript:alert(3)">x</a>'
            . '<iframe src="//e"></iframe><svg onload=alert(4)></svg>';
        $clean = HtmlSanitizer::clean($bad);

        $this->assertStringContainsString('<p>Halo</p>', $clean);
        foreach (['<script', 'alert(1)', 'onerror', 'javascript:', '<iframe', 'onload', '<svg', '<img'] as $needle) {
            $this->assertStringNotContainsString($needle, $clean, "lolos: {$needle}");
        }

        $good = HtmlSanitizer::clean('<p style="margin-top: 0;">Isi <strong>tebal</strong></p><ul class="list-disc"><li>Satu</li></ul><a href="https://contoh.id">tautan</a>');
        foreach (['<strong>tebal</strong>', '<li>Satu</li>', 'class="list-disc"', 'href="https://contoh.id"'] as $needle) {
            $this->assertStringContainsString($needle, $good);
        }

        $this->assertStringNotContainsString('url(', HtmlSanitizer::clean('<p style="background:url(http://e/x.png);color:red">x</p>'));
        $this->assertStringContainsString('noopener', HtmlSanitizer::clean('<a href="https://a.test" target="_blank">x</a>'));
        $this->assertSame('', HtmlSanitizer::clean(null));
    }

    public function test_program_description_is_sanitised_on_save_and_on_render(): void
    {
        $admin = $this->makeUser('admin');
        $faculty = $this->faculty();

        $this->actingAs($admin)->post('/admin/programs', [
            'name' => 'Prodi XSS', 'kode_prodi' => '44444', 'registration_track' => 'reguler', 'faculty_id' => $faculty->id,
            'accreditation' => 'B', 'quota' => 0, 'registration_fee' => 0, 'referral_reward_amount' => 0,
            're_registration_reward_amount' => 0, 're_registration_minimum_payment' => 0, 'is_active' => 1,
            'description' => '<p>Halo</p><script>alert(1)</script><img src=x onerror="alert(2)">',
        ])->assertSessionHasNoErrors();

        $program = Program::where('name', 'Prodi XSS')->first();
        $this->assertStringNotContainsString('<script', (string) $program->description);

        $html = $this->get('/prodi/' . $program->slug)->assertOk()->getContent();
        $this->assertStringContainsString('Halo', $html);
        $this->assertStringNotContainsString('alert(1)', $html);

        // data lama yang kotor tetap disaring saat render
        $program->forceFill(['description' => '<p>Lama</p><script>alert(9)</script>'])->save();
        $html = $this->get('/prodi/' . $program->slug)->assertOk()->getContent();
        $this->assertStringContainsString('Lama', $html);
        $this->assertStringNotContainsString('alert(9)', $html);
    }

    // ── Master data ───────────────────────────────────────────────────
    public function test_faculty_names_must_be_unique_by_slug(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin)->post('/admin/faculties', ['name' => 'Fakultas Teknik'])->assertSessionHasNoErrors();

        foreach (['Fakultas Teknik', 'fakultas  teknik', 'Fakultas Teknik!'] as $duplicate) {
            $resp = $this->actingAs($admin)->post('/admin/faculties', ['name' => $duplicate]);
            $this->assertLessThan(500, $resp->getStatusCode());
            $resp->assertSessionHasErrors('name');
        }
        $this->assertSame(1, Faculty::where('slug', 'fakultas-teknik')->count());

        $this->actingAs($admin)->post('/admin/faculties', ['name' => 'Fakultas Ekonomi']);
        $economics = Faculty::where('name', 'Fakultas Ekonomi')->first();
        $this->actingAs($admin)->put("/admin/faculties/{$economics->id}", ['name' => 'Fakultas Teknik'])->assertSessionHasErrors('name');
        $this->actingAs($admin)->put("/admin/faculties/{$economics->id}", ['name' => 'Fakultas Ekonomi', 'description' => 'baru'])->assertSessionHasNoErrors();
    }

    public function test_program_slug_is_unique_and_stable_and_delete_is_guarded(): void
    {
        $admin = $this->makeUser('admin');
        $faculty = $this->faculty();
        $payload = fn (array $extra = []) => array_merge([
            'name' => 'Prodi Uji', 'kode_prodi' => '22222', 'registration_track' => 'reguler', 'faculty_id' => $faculty->id,
            'accreditation' => 'B', 'quota' => 10, 'registration_fee' => 250000, 'referral_reward_amount' => 50000,
            're_registration_reward_amount' => 500000, 're_registration_minimum_payment' => 0, 'is_active' => 1, 'description' => '<p>x</p>',
        ], $extra);

        $this->actingAs($admin)->post('/admin/programs', $payload())->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/programs', $payload(['kode_prodi' => '33333', 'registration_track' => 'non_reguler']))
            ->assertSessionHasNoErrors();

        $first = Program::where('kode_prodi', '22222')->first();
        $second = Program::where('kode_prodi', '33333')->first();
        $this->assertSame('prodi-uji', $first->slug);
        $this->assertSame('prodi-uji-2', $second->slug);

        $this->actingAs($admin)->put("/admin/programs/{$second->id}", $payload(['kode_prodi' => '33333', 'registration_track' => 'non_reguler', 'quota' => 99]))
            ->assertSessionHasNoErrors();
        $this->assertSame('prodi-uji-2', $second->fresh()->slug, 'slug berubah padahal nama sama');

        $this->actingAs($admin)->put("/admin/programs/{$second->id}", $payload(['name' => 'Prodi Baru', 'kode_prodi' => '33333', 'registration_track' => 'non_reguler']))
            ->assertSessionHasNoErrors();
        $this->assertSame('prodi-baru', $second->fresh()->slug);

        // prodi yang dipakai pendaftar (termasuk soft-delete) tidak bisa dihapus
        $registration = $this->registration(null, 'terdaftar', ['first_choice_program_id' => $first->id]);
        $this->actingAs($admin)->delete("/admin/programs/{$first->id}")->assertSessionHas('error');
        $registration->delete();
        $this->actingAs($admin)->delete("/admin/programs/{$first->id}")->assertSessionHas('error');
        $this->assertNotNull(Program::find($first->id));

        $this->actingAs($admin)->delete("/admin/programs/{$second->id}")->assertSessionHas('success');
    }

    public function test_period_update_ignores_unvalidated_fields(): void
    {
        $admin = $this->makeUser('admin');
        $period = $this->period();

        $this->actingAs($admin)->put("/admin/periode/{$period->id}", [
            'name' => 'PMB Baru', 'year' => 2026, 'open_date' => '2026-01-01', 'close_date' => '2026-12-31',
            're_registration_deadline' => '2027-02-28', 'payment_grace_days' => 3, 'nim_sequence_start' => 1,
            'admin_whatsapp' => '08123', 'is_active' => 0, 'id' => 999,
        ])->assertRedirect(route('admin.periods.index'));

        $period->refresh();
        $this->assertTrue((bool) $period->is_active);
        $this->assertSame('PMB Baru', $period->name);
        $this->assertSame('08123', $period->admin_whatsapp);
    }

    // ── Rahasia & akun ────────────────────────────────────────────────
    public function test_mail_api_key_is_encrypted_and_never_echoed(): void
    {
        $admin = $this->makeUser('admin');
        $base = ['mail_from_address' => 'no-reply@kampus.test', 'mail_from_name' => 'PMB'];

        $this->actingAs($admin)->put('/admin/pengaturan-email', $base + ['resend_api_key' => 're_RAHASIA_123456'])->assertSessionHas('success');
        $this->assertSame('re_RAHASIA_123456', Setting::getSecret('resend_api_key'));
        $this->assertStringStartsWith('enc:', Setting::get('resend_api_key'));

        $html = $this->actingAs($admin)->get('/admin/pengaturan-email')->assertOk()->getContent();
        $this->assertStringNotContainsString('re_RAHASIA_123456', $html);
        $this->assertStringContainsString('tersimpan', $html);

        $this->actingAs($admin)->put('/admin/pengaturan-email', $base + ['resend_api_key' => ''])->assertSessionHas('success');
        $this->assertSame('re_RAHASIA_123456', Setting::getSecret('resend_api_key'), 'kosong harus mempertahankan key');

        $this->actingAs($admin)->put('/admin/pengaturan-email', $base + ['clear_resend_api_key' => '1'])->assertSessionHas('success');
        $this->assertNull(Setting::getSecret('resend_api_key'));

        Setting::set('resend_api_key', 're_lama_plaintext');
        $this->assertSame('re_lama_plaintext', Setting::getSecret('resend_api_key'), 'nilai lama (plaintext) tetap terbaca');
    }

    public function test_account_deletion_rules(): void
    {
        [$withHistory] = $this->student('terdaftar');
        $response = $this->actingAs($withHistory)->from('/profile')->delete('/profile', ['password' => 'password-lama']);
        $this->assertLessThan(500, $response->getStatusCode());
        $response->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($withHistory->id));
        $this->assertAuthenticatedAs($withHistory);

        $staff = $this->makeUser('operator');
        [, $registration] = $this->student('terdaftar');
        PaymentLog::create(['registration_id' => $registration->id, 'acted_by' => $staff->id, 'action' => 'x', 'note' => 'y']);
        $this->actingAs($staff)->from('/profile')->delete('/profile', ['password' => 'password-lama'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($staff->id));

        $clean = $this->makeUser('mahasiswa', ['remember_token' => 'token-uji']);
        $this->actingAs($clean)->from('/profile')->delete('/profile', ['password' => 'salah'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull(User::find($clean->id));

        $this->actingAs($clean)->delete('/profile', ['password' => 'password-lama'])->assertRedirect('/');
        $this->assertNull(User::find($clean->id), 'akun yang bersih harus terhapus dan tidak muncul kembali setelah logout');
        $this->assertGuest();
    }
}
