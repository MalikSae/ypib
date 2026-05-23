<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Ruang Kuliah AC & LCD Projector', 'icon' => 'ti-building'],
            ['name' => 'Perpustakaan & Ruang Baca', 'icon' => 'ti-books'],
            ['name' => 'Masjid', 'icon' => 'ti-building-mosque'],
            ['name' => 'Lapangan Olahraga', 'icon' => 'ti-ball-football'],
            ['name' => 'Lab Keperawatan', 'icon' => 'ti-stethoscope'],
            ['name' => 'Lab Kebidanan', 'icon' => 'ti-heart-rate-monitor'],
            ['name' => 'Lab Farmasi', 'icon' => 'ti-flask'],
            ['name' => 'Lab Komputer', 'icon' => 'ti-device-desktop'],
            ['name' => 'Lab CBT Center Nasional', 'icon' => 'ti-clipboard-check'],
            ['name' => 'Lab Psikologi', 'icon' => 'ti-brain'],
            ['name' => 'Dosen Profesional S2 & S3', 'icon' => 'ti-school'],
            ['name' => 'Lahan Parkir Dalam & Luar Negeri', 'icon' => 'ti-parking'],
            ['name' => 'Bus Kampus', 'icon' => 'ti-bus'],
            ['name' => 'Prospek Karir Dalam & Luar Negeri', 'icon' => 'ti-briefcase'],
            ['name' => 'Wifi / Hotspot Dosen & Mahasiswa', 'icon' => 'ti-wifi'],
            ['name' => 'Mobil Inventaris Kampus', 'icon' => 'ti-car'],
        ];

        foreach ($facilities as $index => $facility) {
            Facility::create([
                'name' => $facility['name'],
                'icon' => $facility['icon'],
                'order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
