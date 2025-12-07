<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@lsp.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status_akun' => 'aktif',
            'phone' => '081234567890',
            'address' => 'Kantor Pusat LSP',
            'email_verified_at' => now(),
        ]);

        // Create Jurusan
        $jurusans = [
            [
                'kode_jurusan' => 'TI',
                'nama_jurusan' => 'Teknik Informatika',
                'deskripsi' => 'Program studi yang mempelajari ilmu komputer, pemrograman, dan pengembangan perangkat lunak.',
                'kuota' => 100,
            ],
            [
                'kode_jurusan' => 'SI',
                'nama_jurusan' => 'Sistem Informasi',
                'deskripsi' => 'Program studi yang menggabungkan ilmu komputer dengan manajemen bisnis.',
                'kuota' => 80,
            ],
            [
                'kode_jurusan' => 'BD',
                'nama_jurusan' => 'Bisnis Digital',
                'deskripsi' => 'Program studi yang fokus pada pengembangan bisnis berbasis teknologi digital.',
                'kuota' => 60,
            ],
        ];

        foreach ($jurusans as $jurusan) {
            Jurusan::create($jurusan);
        }
    }
}
