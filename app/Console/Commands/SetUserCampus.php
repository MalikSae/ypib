<?php

namespace App\Console\Commands;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Batasi akun admin/operator/panitia ke satu kampus, atau lepaskan batasannya.
 * Contoh: php artisan users:set-campus admin.cirebon@ypib.ac.id CRB
 *         php artisan users:set-campus admin.cirebon@ypib.ac.id --all
 */
class SetUserCampus extends Command
{
    protected $signature = 'users:set-campus {email : Email akun} {code? : Kode kampus (mis. CRB)} {--all : Lepaskan batasan kampus}';

    protected $description = 'Batasi akun staf ke satu kampus, atau beri akses semua kampus';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('Akun tidak ditemukan.');

            return self::FAILURE;
        }

        if (!in_array($user->role, ['admin', 'operator', 'panitia'], true)) {
            $this->error("Peran '{$user->role}' tidak mendukung pembatasan kampus (hanya admin, operator, panitia).");

            return self::FAILURE;
        }

        if ($this->option('all')) {
            $user->campus_id = null;
            $user->save();
            $this->info("{$user->email}: akses semua kampus.");

            return self::SUCCESS;
        }

        $campus = Campus::where('code', strtoupper((string) $this->argument('code')))->first();

        if (!$campus) {
            $this->error('Kode kampus tidak ditemukan. Tersedia: ' . Campus::pluck('code')->implode(', '));

            return self::FAILURE;
        }

        $user->campus_id = $campus->id;
        $user->save();
        $this->info("{$user->email}: dibatasi ke {$campus->name}.");

        return self::SUCCESS;
    }
}
