<?php

namespace Tests\Feature\Pmb;

use App\Models\RegistrationDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsPmbData;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use BuildsPmbData;
    use RefreshDatabase;

    public function test_role_matrix_for_admin_and_panitia_areas(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');

        $this->actingAs($this->makeUser('mahasiswa'))->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->makeUser('panitia'))->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->makeUser('admin'))->get('/panitia/dashboard')->assertForbidden();
        $this->actingAs($this->makeUser('mahasiswa'))->get('/panitia/dashboard')->assertForbidden();

        $this->actingAs($this->makeUser('operator'))->get('/admin/dashboard')->assertOk();
        $this->actingAs($this->makeUser('panitia'))->get('/panitia/dashboard')->assertOk();
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->makeUser('panitia'))->get('/dashboard')->assertRedirect(route('panitia.dashboard'));
        $this->actingAs($this->makeUser('mahasiswa', ['is_referrer' => true]))->get('/dashboard')->assertRedirect(route('referrer.dashboard'));
        $this->actingAs($this->makeUser())->get('/dashboard')->assertRedirect(route('registration.status'));
    }

    public function test_register_cannot_set_role_or_referrer_flag(): void
    {
        $this->post('/register', [
            'name' => 'Calon', 'email' => 'calon@uji.test', 'phone' => '081234567890',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
            'role' => 'admin', 'is_referrer' => 1,
        ]);

        $user = User::where('email', 'calon@uji.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('mahasiswa', $user->role);
        $this->assertFalse((bool) $user->is_referrer);
    }

    public function test_staff_password_reset_rules(): void
    {
        $operator = $this->makeUser('operator');
        $otherOperator = $this->makeUser('operator');
        $admin = $this->makeUser('admin');
        $otherAdmin = $this->makeUser('admin');
        $student = $this->makeUser();
        $new = ['password' => 'Changed#12345', 'password_confirmation' => 'Changed#12345'];

        $this->actingAs($operator)->post("/admin/users/{$admin->id}/reset-password", $new)->assertForbidden();
        $this->actingAs($operator)->post("/admin/users/{$otherOperator->id}/reset-password", $new)->assertForbidden();
        $this->assertTrue(Hash::check('password-lama', $admin->fresh()->password));

        $this->actingAs($operator)->post("/admin/users/{$student->id}/reset-password", $new)->assertSessionHas('success');
        $this->actingAs($operator)->post("/admin/users/{$operator->id}/reset-password", $new)->assertSessionHas('success');
        $this->actingAs($admin)->post("/admin/users/{$otherOperator->id}/reset-password", $new)->assertSessionHas('success');
        $this->actingAs($admin)->post("/admin/users/{$otherAdmin->id}/reset-password", $new)->assertSessionHas('success');
    }

    public function test_private_file_access_matrix(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$owner, $registration] = $this->student('terdaftar', ['payment_proof' => 'bukti-bayar/a.png']);
        $other = $this->makeUser();
        $admin = $this->makeUser('admin');
        $operator = $this->makeUser('operator');
        $panitia = $this->makeUser('panitia');

        Storage::disk('local')->put('bukti-bayar/a.png', 'BUKTI');
        $document = RegistrationDocument::forceCreate([
            'registration_id' => $registration->id, 'document_type' => 'kk',
            'file_path' => 'dokumen-pendaftaran/kk.png', 'status' => 'menunggu_review',
        ]);
        Storage::disk('local')->put('dokumen-pendaftaran/kk.png', 'DOKUMEN');

        $proof = "/berkas/registrasi/{$registration->id}/payment_proof";
        $doc = "/berkas/dokumen/{$document->id}";

        $this->get($proof)->assertRedirect('/login');
        $this->get($doc)->assertRedirect('/login');

        foreach ([$owner, $admin, $operator] as $allowed) {
            $this->actingAs($allowed)->get($proof)->assertOk();
            $this->actingAs($allowed)->get($doc)->assertOk();
        }

        $this->actingAs($other)->get($proof)->assertForbidden();
        $this->actingAs($other)->get($doc)->assertForbidden();
        $this->actingAs($panitia)->get($proof)->assertForbidden();   // panitia hanya dokumen
        $this->actingAs($panitia)->get($doc)->assertOk();

        $this->assertSame('nosniff', $this->actingAs($owner)->get($proof)->headers->get('X-Content-Type-Options'));

        $this->actingAs($owner)->get("/berkas/registrasi/{$registration->id}/password")->assertNotFound();
        $this->actingAs($owner)->get("/berkas/registrasi/{$registration->id}/alumni_card_proof")->assertNotFound();
        $this->actingAs($owner)->get('/berkas/dokumen/999999')->assertNotFound();
    }

    public function test_export_requires_staff(): void
    {
        $this->get('/admin/pendaftar/export?format=csv')->assertRedirect('/login');
        $this->actingAs($this->makeUser())->get('/admin/pendaftar/export?format=csv')->assertForbidden();
        $this->actingAs($this->makeUser('panitia'))->get('/admin/pendaftar/export?format=csv')->assertForbidden();
        $this->actingAs($this->makeUser('operator'))->get('/admin/pendaftar/export?format=csv')->assertOk();
    }

    public function test_development_pages_are_not_public(): void
    {
        $admin = $this->makeUser('admin');

        $this->get('/brand')->assertNotFound();
        $this->actingAs($admin)->get('/admin/komponen')->assertNotFound();
        $this->assertStringNotContainsString('Komponen UI', $this->actingAs($admin)->get('/admin/dashboard')->getContent());
    }
}
