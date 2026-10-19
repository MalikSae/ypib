<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Jalur pemulihan lewat CLI bila semua admin terkunci: membuat (atau mengaktifkan kembali dan
 * mereset) admin semua kampus dengan password acak yang ditampilkan sekali.
 *
 *   php artisan users:create-admin pemulihan@ypib.ac.id "Admin Pemulihan"
 */
class CreateAdminUser extends Command
{
    protected $signature = 'users:create-admin {email : Email akun} {name : Nama lengkap}';

    protected $description = 'Buat atau pulihkan akun admin semua kampus dengan password acak';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email tidak valid.');

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing && !in_array($existing->role, ['admin', 'operator', 'panitia'], true)) {
            $this->error("Email ini milik akun '{$existing->role}', bukan staf. Gunakan email lain.");

            return self::FAILURE;
        }

        $password = Str::password(16, symbols: false);

        $user = $existing ?? new User();
        $user->forceFill([
            'name'              => $existing?->name ?? $this->argument('name'),
            'email'             => $email,
            'password'          => $password,
            'role'              => 'admin',
            'campus_id'         => null,
            'is_active'         => true,
            'email_verified_at' => $existing?->email_verified_at ?? now(),
        ])->save();

        $this->info(($existing ? 'Akun dipulihkan' : 'Akun dibuat') . ": {$email} (admin semua kampus)");
        $this->line("Password sementara (hanya tampil sekali): {$password}");
        $this->warn('Ganti password setelah login.');

        return self::SUCCESS;
    }
}
