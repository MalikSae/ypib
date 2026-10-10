<?php

namespace Tests\Concerns;

use App\Models\Faculty;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\Referrer;
use App\Models\Registration;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Pembangun data untuk test PMB. Memakai forceCreate agar tidak bergantung pada
 * $fillable dan tidak memerlukan factory (model PMB belum punya factory).
 */
trait BuildsPmbData
{
    private int $pmbSeq = 0;

    protected function nextSeq(): int
    {
        return ++$this->pmbSeq;
    }

    protected function makeUser(string $role = 'mahasiswa', array $extra = []): User
    {
        $n = $this->nextSeq();

        return User::forceCreate(array_merge([
            'name'              => 'Pengguna ' . $n,
            'email'             => "pengguna{$n}@uji.test",
            'password'          => Hash::make('password-lama'),
            'role'              => $role,
            'phone'             => '08123456' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'email_verified_at' => now(),
        ], $extra));
    }

    protected function faculty(): Faculty
    {
        return Faculty::firstOrCreate(['slug' => 'fakultas-uji'], ['name' => 'Fakultas Uji']);
    }

    /** Periode aktif yang sedang terbuka (dibuat sekali per test). */
    protected function period(array $extra = []): PmbPeriod
    {
        return PmbPeriod::first() ?? PmbPeriod::forceCreate(array_merge([
            'name'       => 'PMB Uji',
            'year'       => (int) now()->year,
            'open_date'  => now()->subDays(3)->toDateString(),
            'close_date' => now()->addDays(30)->toDateString(),
            'is_active'  => true,
        ], $extra));
    }

    protected function program(array $extra = []): Program
    {
        $n = $this->nextSeq();

        return Program::forceCreate(array_merge([
            'name'               => 'Prodi Uji ' . $n,
            'faculty_id'         => $this->faculty()->id,
            'kode_prodi'         => str_pad((string) (10000 + $n), 5, '0', STR_PAD_LEFT),
            'registration_track' => 'reguler',
            'slug'               => 'prodi-uji-' . $n,
            'is_active'          => true,
            'quota'              => 0,
        ], $extra));
    }

    protected function registration(?User $user, string $status, array $extra = []): Registration
    {
        $n = $this->nextSeq();
        $user ??= $this->makeUser();
        $period = $this->period();
        $programId = $extra['first_choice_program_id'] ?? $this->program()->id;
        unset($extra['first_choice_program_id']);

        return Registration::forceCreate(array_merge([
            'period_id'               => $period->id,
            'user_id'                 => $user->id,
            'first_choice_program_id' => $programId,
            'registration_number'     => 'PMB-UJI-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'registration_type'       => 'reguler',
            'admission_path'          => 'umum',
            'full_name'               => 'Calon Mahasiswa ' . $n,
            'nisn'                    => '1234567890',
            'nik'                     => '3210' . str_pad((string) $n, 12, '0', STR_PAD_LEFT),
            'birth_place'             => 'Majalengka',
            'birth_date'              => '2005-01-01',
            'gender'                  => 'male',
            'religion'                => 'Islam',
            'address'                 => 'Jl. Uji No. 1',
            'phone'                   => '081234567890',
            'school_name'             => 'SMA Uji',
            'school_major'            => 'IPA',
            'graduation_year'         => 2024,
            'certificate_number'      => 'IJ-' . $n,
            'father_name'             => 'Ayah Uji',
            'mother_name'             => 'Ibu Uji',
            'father_occupation'       => 'Petani',
            'mother_occupation'       => 'Ibu Rumah Tangga',
            'status'                  => $status,
        ], $extra));
    }

    /** Pendaftar sekaligus akunnya: [User, Registration]. */
    protected function student(string $status = 'terdaftar', array $extra = []): array
    {
        $user = $this->makeUser();

        return [$user, $this->registration($user, $status, $extra)];
    }

    protected function referrer(array $extra = []): Referrer
    {
        $n = $this->nextSeq();

        return Referrer::forceCreate(array_merge([
            'user_id'             => $this->makeUser()->id,
            'code'                => 'RF' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status'              => 'active',
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567' . $n,
            'bank_account_name'   => 'Pemilik ' . $n,
        ], $extra));
    }

    protected function reward(Referrer $referrer, Registration $registration, string $status = 'approved', string $type = 'registration', int $amount = 50000): Reward
    {
        return Reward::forceCreate([
            'referrer_id'     => $referrer->id,
            'registration_id' => $registration->id,
            'amount'          => $amount,
            'reward_type'     => $type,
            'status'          => $status,
        ]);
    }
}
