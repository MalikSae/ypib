<?php

namespace Tests\Feature\Pmb;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

/** Halaman Akun Staf: akses, validasi, pengaman, nonaktifkan, reset password, command pemulihan. */
class StaffAccountTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    private function crb(): Campus
    {
        return Campus::where('code', 'CRB')->firstOrFail();
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'name' => 'Staf Baru', 'email' => 'staf.baru@uji.test', 'role' => 'operator', 'campus_id' => '',
            'password' => 'Rahasia#12345', 'password_confirmation' => 'Rahasia#12345',
        ], $extra);
    }

    // ── Akses ──────────────────────────────────────────────────────────────

    public function test_only_global_admin_can_open_staff_pages(): void
    {
        $target = $this->makeUser('panitia');

        $this->actingAs($this->makeUser('admin'))->get('/admin/akun-staf')->assertOk()->assertSee($target->email);

        foreach ([
            $this->makeUser('operator'),
            $this->makeUser('admin', ['campus_id' => $this->crb()->id]),
            $this->makeUser('panitia'),
            $this->makeUser('mahasiswa'),
        ] as $blocked) {
            $this->actingAs($blocked)->get('/admin/akun-staf')->assertForbidden();
            $this->actingAs($blocked)->post('/admin/akun-staf', $this->payload())->assertForbidden();
            $this->actingAs($blocked)->patch("/admin/akun-staf/{$target->id}/toggle")->assertForbidden();
        }

        $this->assertNull(User::where('email', 'staf.baru@uji.test')->first());
    }

    public function test_endpoints_only_touch_staff_accounts(): void
    {
        $admin = $this->makeUser('admin');

        foreach ([$this->makeUser('mahasiswa'), $this->makeUser('mahasiswa', ['is_referrer' => true])] as $victim) {
            $this->actingAs($admin)->get("/admin/akun-staf/{$victim->id}/edit")->assertNotFound();
            $this->actingAs($admin)->put("/admin/akun-staf/{$victim->id}", $this->payload(['email' => $victim->email]))->assertNotFound();
            $this->actingAs($admin)->patch("/admin/akun-staf/{$victim->id}/toggle")->assertNotFound();
            $this->actingAs($admin)->post("/admin/akun-staf/{$victim->id}/reset-password", [
                'password' => 'Hacked#12345', 'password_confirmation' => 'Hacked#12345',
            ])->assertNotFound();

            $this->assertTrue(Hash::check('password-lama', $victim->fresh()->password));
            $this->assertSame('mahasiswa', $victim->fresh()->role);
        }
    }

    // ── Tambah dan ubah ────────────────────────────────────────────────────

    public function test_create_staff_with_role_and_campus(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/admin/akun-staf', $this->payload(['role' => 'panitia', 'campus_id' => $this->crb()->id]))
            ->assertRedirect(route('admin.staff.index'));

        $user = User::where('email', 'staf.baru@uji.test')->firstOrFail();
        $this->assertSame('panitia', $user->role);
        $this->assertSame($this->crb()->id, $user->campus_id);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Rahasia#12345', $user->password));
    }

    public function test_create_rejects_invalid_role_duplicate_email_and_weak_password(): void
    {
        $admin = $this->makeUser('admin');
        $existing = $this->makeUser('operator');

        $this->actingAs($admin)->post('/admin/akun-staf', $this->payload(['role' => 'mahasiswa']))->assertSessionHasErrors('role');
        $this->actingAs($admin)->post('/admin/akun-staf', $this->payload(['role' => 'referrer']))->assertSessionHasErrors('role');
        $this->actingAs($admin)->post('/admin/akun-staf', $this->payload(['email' => $existing->email]))->assertSessionHasErrors('email');
        $this->actingAs($admin)->post('/admin/akun-staf', $this->payload(['password' => 'abc', 'password_confirmation' => 'abc']))->assertSessionHasErrors('password');
        $this->actingAs($admin)->post('/admin/akun-staf', $this->payload(['campus_id' => 99999]))->assertSessionHasErrors('campus_id');
    }

    public function test_update_changes_role_and_campus_but_not_password(): void
    {
        $admin = $this->makeUser('admin');
        $staff = $this->makeUser('panitia');

        $this->actingAs($admin)->put("/admin/akun-staf/{$staff->id}", [
            'name' => 'Nama Baru', 'email' => $staff->email, 'role' => 'operator', 'campus_id' => $this->crb()->id,
            'password' => 'Disusupkan#123', 'password_confirmation' => 'Disusupkan#123',
        ])->assertRedirect(route('admin.staff.index'));

        $fresh = $staff->fresh();
        $this->assertSame('Nama Baru', $fresh->name);
        $this->assertSame('operator', $fresh->role);
        $this->assertSame($this->crb()->id, $fresh->campus_id);
        $this->assertTrue(Hash::check('password-lama', $fresh->password), 'edit tidak boleh mengganti password');
    }

    // ── Pengaman ───────────────────────────────────────────────────────────

    public function test_cannot_change_or_deactivate_own_account(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeUser('admin'); // admin global lain, supaya yang menolak adalah aturan "akun sendiri"

        $this->actingAs($admin)->put("/admin/akun-staf/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'operator', 'campus_id' => '',
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->put("/admin/akun-staf/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'campus_id' => $this->crb()->id,
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->patch("/admin/akun-staf/{$admin->id}/toggle")->assertSessionHas('error');

        $fresh = $admin->fresh();
        $this->assertSame('admin', $fresh->role);
        $this->assertNull($fresh->campus_id);
        $this->assertTrue($fresh->is_active);

        // Nama dan email sendiri tetap boleh diubah.
        $this->actingAs($admin)->put("/admin/akun-staf/{$admin->id}", [
            'name' => 'Nama Saya', 'email' => $admin->email, 'role' => 'admin', 'campus_id' => '',
        ])->assertRedirect(route('admin.staff.index'));
        $this->assertSame('Nama Saya', $admin->fresh()->name);
    }

    /**
     * Lewat UI, pelaku selalu admin global aktif lain, jadi aturan "admin global terakhir" hanya
     * terpicu bila datanya berubah di antara dua request. Diuji langsung sebagai lapis pengaman.
     */
    public function test_last_global_admin_guard_blocks_demotion_restriction_and_deactivation(): void
    {
        $last = $this->makeUser('admin')->fresh(); // fresh: muat default DB (is_active)
        $this->makeUser('admin', ['campus_id' => $this->crb()->id]); // admin kampus tidak dihitung
        $this->makeUser('operator');
        $this->actingAs($this->makeUser('operator')); // pelaku bukan target

        $guard = new \ReflectionMethod(\App\Http\Controllers\Admin\StaffController::class, 'violation');
        $controller = new \App\Http\Controllers\Admin\StaffController();

        $this->assertNotNull($guard->invoke($controller, $last, 'operator', null, true), 'turunkan peran');
        $this->assertNotNull($guard->invoke($controller, $last, 'admin', $this->crb()->id, true), 'batasi kampus');
        $this->assertNotNull($guard->invoke($controller, $last, 'admin', null, false), 'nonaktifkan');
        $this->assertNull($guard->invoke($controller, $last, 'admin', null, true), 'tanpa perubahan akses');

        // Dengan admin global aktif lain, perubahan diizinkan.
        $this->makeUser('admin');
        $this->assertNull($guard->invoke($controller, $last, 'operator', null, true));
    }
    // ── Nonaktifkan ────────────────────────────────────────────────────────

    public function test_deactivate_blocks_login_and_ends_sessions(): void
    {
        $admin = $this->makeUser('admin');
        $staff = $this->makeUser('operator', ['email' => 'operator.nonaktif@uji.test']);

        DB::table('sessions')->insert([
            'id' => 'sesi-staf-1', 'user_id' => $staff->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->patch("/admin/akun-staf/{$staff->id}/toggle")->assertSessionHas('success');

        $this->assertFalse($staff->fresh()->is_active);
        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-staf-1']);

        auth()->logout();
        $this->post('/login', ['email' => $staff->email, 'password' => 'password-lama'])->assertSessionHasErrors('email');
        $this->assertGuest();

        // Aktifkan kembali.
        $this->actingAs($admin)->patch("/admin/akun-staf/{$staff->id}/toggle");
        $this->assertTrue($staff->fresh()->is_active);
        auth()->logout();
        $this->post('/login', ['email' => $staff->email, 'password' => 'password-lama']);
        $this->assertAuthenticatedAs($staff->fresh());
    }

    public function test_deactivated_user_with_open_session_is_logged_out_on_next_request(): void
    {
        $staff = $this->makeUser('operator', ['is_active' => false]);

        $this->actingAs($staff)->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ── Reset password ─────────────────────────────────────────────────────

    public function test_reset_password_changes_it_and_ends_sessions(): void
    {
        $admin = $this->makeUser('admin');
        $staff = $this->makeUser('panitia');
        DB::table('sessions')->insert([
            'id' => 'sesi-panitia-1', 'user_id' => $staff->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)->post("/admin/akun-staf/{$staff->id}/reset-password", [
            'password' => 'Baru#1234567', 'password_confirmation' => 'Baru#1234567',
        ])->assertRedirect(route('admin.staff.index'));

        $this->assertTrue(Hash::check('Baru#1234567', $staff->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'sesi-panitia-1']);

        $this->actingAs($admin)->post("/admin/akun-staf/{$staff->id}/reset-password", [
            'password' => 'pendek', 'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');
    }

    // ── Daftar ─────────────────────────────────────────────────────────────

    public function test_list_filters_by_role_campus_and_search_and_hides_non_staff(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeUser('panitia', ['name' => 'Budi Panitia', 'email' => 'budi@panitia.test', 'campus_id' => $this->crb()->id]);
        $this->makeUser('operator', ['name' => 'Citra Operator', 'email' => 'citra@operator.test']);
        $this->makeUser('mahasiswa', ['name' => 'Siswa Budi', 'email' => 'budi-siswa@siswa.test']);

        $this->actingAs($admin)->get('/admin/akun-staf?search=budi')
            ->assertSee('budi@panitia.test')->assertDontSee('budi-siswa@siswa.test')->assertDontSee('citra@operator.test');
        $this->actingAs($admin)->get('/admin/akun-staf?role=operator')
            ->assertSee('citra@operator.test')->assertDontSee('budi@panitia.test');
        $this->actingAs($admin)->get('/admin/akun-staf?campus=' . $this->crb()->id)
            ->assertSee('budi@panitia.test')->assertDontSee('citra@operator.test');
        $this->actingAs($admin)->get('/admin/akun-staf?campus=none')
            ->assertSee('citra@operator.test')->assertDontSee('budi@panitia.test');
    }

    public function test_sidebar_shows_staff_menu_only_to_global_admin(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/dashboard')->assertSee('Akun Staf');
        $this->actingAs($this->makeUser('operator'))->get('/admin/dashboard')->assertDontSee('Akun Staf');
        $this->actingAs($this->makeUser('admin', ['campus_id' => $this->crb()->id]))->get('/admin/dashboard')->assertDontSee('Akun Staf');
    }

    // ── Command pemulihan ──────────────────────────────────────────────────

    public function test_create_admin_command_creates_and_recovers_global_admin(): void
    {
        $this->artisan('users:create-admin', ['email' => 'pemulihan@uji.test', 'name' => 'Admin Pemulihan'])
            ->assertSuccessful();

        $user = User::where('email', 'pemulihan@uji.test')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertNull($user->campus_id);
        $this->assertTrue($user->is_active);

        // Akun yang terkunci (nonaktif dan dibatasi kampus) dipulihkan.
        $user->forceFill(['is_active' => false, 'campus_id' => $this->crb()->id, 'role' => 'operator'])->save();
        $this->artisan('users:create-admin', ['email' => 'pemulihan@uji.test', 'name' => 'x'])->assertSuccessful();

        $fresh = $user->fresh();
        $this->assertTrue($fresh->is_active);
        $this->assertSame('admin', $fresh->role);
        $this->assertNull($fresh->campus_id);

        // Akun pendaftar tidak boleh dibajak lewat command ini.
        $student = $this->makeUser('mahasiswa');
        $this->artisan('users:create-admin', ['email' => $student->email, 'name' => 'x'])->assertFailed();
        $this->assertSame('mahasiswa', $student->fresh()->role);
    }
}
