<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\PmbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@ypib.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '08123456789',
        ]);

        // ── Operator ───────────────────────────────────────────────────────
        User::create([
            'name'     => 'Operator PMB',
            'email'    => 'operator@ypib.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'operator',
            'phone'    => '08129999999',
        ]);

        // ── Fakultas ───────────────────────────────────────────────────────
        $facultiesData = [
            'Fakultas Ilmu Kesehatan',
            'Fakultas Farmasi',
            'Fakultas Psikologi, Bisnis, dan Teknologi',
        ];

        $faculties = [];
        foreach ($facultiesData as $fName) {
            $faculties[$fName] = Faculty::create([
                'name' => $fName,
                'slug' => Str::slug($fName),
                'description' => 'Deskripsi ' . $fName
            ]);
        }

        // ── Program Studi ──────────────────────────────────────────────────
        $programs = [
            // Fakultas Ilmu Kesehatan
            [
                'name'          => 'S1 Ilmu Keperawatan',
                'faculty_name'  => 'Fakultas Ilmu Kesehatan',
                'accreditation' => 'B',
                'quota'         => 100,
                'registration_fee' => 250000,
                're_registration_fee' => 25000000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Studi S1 Ilmu Keperawatan YPIB Majalengka berkomitmen untuk menghasilkan lulusan ners yang profesional, kompeten, dan humanis. Kami memadukan teori sains keperawatan modern dengan praktik klinis nyata di berbagai rumah sakit terkemuka.</p><p style="margin-bottom: 8px; font-weight: 600;">Fokus Keahlian Utama Kami:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Keperawatan Gawat Darurat & Kritis</li><li>Keperawatan Komunitas & Keluarga</li><li>Manajemen Keperawatan & Leadership</li><li>Pelayanan Kesehatan Holistik</li></ul>',
            ],
            [
                'name'          => 'Profesi Ners',
                'faculty_name'  => 'Fakultas Ilmu Kesehatan',
                'accreditation' => 'B',
                'quota'         => 50,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Pendidikan Profesi Ners merupakan kelanjutan langsung setelah menyelesaikan gelar S.Kep. Program ini bertujuan mengasah keterampilan klinis, pengambilan keputusan klinis yang tepat, serta nilai-nilai profesionalisme ners di lapangan kerja sebenarnya.</p><p style="margin-bottom: 8px; font-weight: 600;">Program Praktik Klinis Meliputi:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Praktik Keperawatan Medikal Bedah (KMB)</li><li>Keperawatan Anak & Maternitas</li><li>Keperawatan Jiwa & Gerontik</li><li>Manajemen Keperawatan Rumah Sakit</li></ul>',
            ],
            [
                'name'          => 'D3 Kebidanan',
                'faculty_name'  => 'Fakultas Ilmu Kesehatan',
                'accreditation' => 'B',
                'quota'         => 80,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Studi D3 Kebidanan YPIB Majalengka mencetak bidan ahli madya yang mandiri, sigap, dan berdedikasi tinggi dalam memberikan pelayanan kebidanan bagi ibu dan anak di tingkat masyarakat maupun fasilitas kesehatan primer.</p><p style="margin-bottom: 8px; font-weight: 600;">Kompetensi Utama Lulusan:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Asuhan Kebidanan Ibu Hamil & Bersalin</li><li>Perawatan Neonatus, Bayi & Balita</li><li>Penyuluhan Keluarga Berencana (KB)</li><li>Mitigasi Kegawatdaruratan Maternal</li></ul>',
            ],
            [
                'name'          => 'S1 Kebidanan',
                'faculty_name'  => 'Fakultas Ilmu Kesehatan',
                'accreditation' => 'B',
                'quota'         => 80,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Studi S1 Kebidanan membekali mahasiswa dengan dasar keilmuan kebidanan yang mendalam, riset klinis, serta kepemimpinan klinis guna melahirkan bidan profesional yang mampu bersaing global dan memimpin asuhan kesehatan maternal secara holistik.</p><p style="margin-bottom: 8px; font-weight: 600;">Fokus Pembelajaran Utama:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Metodologi Riset Kebidanan Modern</li><li>Asuhan Kebidanan Holistik & Komplementer</li><li>Kebijakan & Manajemen Layanan Kesehatan</li><li>Etika Profesi & Hukum Kesehatan Ibu-Anak</li></ul>',
            ],
            [
                'name'          => 'Pendidikan Profesi Bidan',
                'faculty_name'  => 'Fakultas Ilmu Kesehatan',
                'accreditation' => 'B',
                'quota'         => 50,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Pendidikan Profesi Bidan mengantarkan lulusan S1 Kebidanan menjadi bidan profesional dengan lisensi praktek mandiri. Berfokus pada integrasi teori asuhan komprehensif, advokasi kesehatan perempuan, dan kepemimpinan di komunitas.</p><p style="margin-bottom: 8px; font-weight: 600;">Pengalaman Belajar Lapangan:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Praktik Asuhan Kebidanan Fisiologis Komprehensif</li><li>Praktik Mandiri Bidan (PMB)</li><li>Asuhan Komplementer dalam Kebidanan</li><li>Manajemen Pelayanan Kebidanan Komunitas</li></ul>',
            ],
            // Fakultas Farmasi
            [
                'name'          => 'S1 Farmasi Majalengka',
                'faculty_name'  => 'Fakultas Farmasi',
                'accreditation' => 'B',
                'quota'         => 80,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Studi S1 Farmasi YPIB Majalengka menyiapkan apoteker masa depan yang unggul di bidang sains farmasi, teknologi farmasi bahan alam, serta pelayanan kefarmasian klinis-komunitas yang berbasis keselamatan pasien.</p><p style="margin-bottom: 8px; font-weight: 600;">Bidang Kajian Utama:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Sains & Formulasi Teknologi Sediaan Obat</li><li>Farmasi Klinis, Komunitas & Manajemen</li><li>Analisis Kimia Farmasi & Toksikologi</li><li>Fitofarmaka & Pemanfaatan Bahan Alam Lokal</li></ul>',
            ],
            // Fakultas Psikologi, Bisnis, dan Teknologi
            [
                'name'          => 'S1 Psikologi',
                'faculty_name'  => 'Fakultas Psikologi, Bisnis, dan Teknologi',
                'accreditation' => 'B',
                'quota'         => 100,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Studi S1 Psikologi YPIB menawarkan pemahaman mendalam tentang perilaku manusia, dinamika mental, serta penerapan ilmu psikologi di bidang industri, pendidikan, klinis dasar, dan pemberdayaan komunitas sosial.</p><p style="margin-bottom: 8px; font-weight: 600;">Konsentrasi Pembelajaran:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Psikologi Industri & Organisasi (PIO)</li><li>Psikologi Pendidikan & Tumbuh Kembang Anak</li><li>Konseling Dasar & Kesehatan Mental</li><li>Psikometri & Pengukuran Perilaku</li></ul>',
            ],
            [
                'name'          => 'S1 Perdagangan Internasional',
                'faculty_name'  => 'Fakultas Psikologi, Bisnis, dan Teknologi',
                'accreditation' => 'B',
                'quota'         => 80,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Mempersiapkan profesional muda untuk menguasai dinamika bisnis lintas batas, logistik global, hukum perdagangan internasional, serta ekspor-impor. Dirancang untuk menjawab tantangan ekonomi digital dan globalisasi yang berkembang pesat.</p><p style="margin-bottom: 8px; font-weight: 600;">Fokus Karir & Keahlian:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Manajemen Ekspor, Impor & Logistik Global</li><li>Analisis Pasar Internasional & Investasi Asing</li><li>Negosiasi Bisnis & Diplomasi Perdagangan</li><li>E-commerce Lintas Batas (Cross-border)</li></ul>',
            ],
            [
                'name'          => 'S1 Ilmu Komputer',
                'faculty_name'  => 'Fakultas Psikologi, Bisnis, dan Teknologi',
                'accreditation' => 'B',
                'quota'         => 100,
                'registration_fee' => 250000,
                'is_active'     => true,
                'description'   => '<p style="margin-top: 0; margin-bottom: 16px;">Program Studi S1 Ilmu Komputer YPIB mencetak praktisi IT, software engineer, dan data scientist yang kreatif dan adaptif. Kurikulum kami fokus pada pengembangan perangkat lunak modern, kecerdasan buatan (AI), serta keamanan siber.</p><p style="margin-bottom: 8px; font-weight: 600;">Pilar Teknologi Utama:</p><ul class="list-disc pl-5" style="margin-bottom: 0; padding-left: 20px; line-height: 1.8;"><li>Pemrograman Aplikasi Web & Mobile (Fullstack)</li><li>Rekayasa Kecerdasan Buatan (AI) & Machine Learning</li><li>Manajemen Basis Data & Big Data Analytics</li><li>Keamanan Siber & Administrasi Jaringan</li></ul>',
            ],
        ];

        foreach ($programs as $program) {
            $facultyName = $program['faculty_name'];
            unset($program['faculty_name']);
            
            $program['faculty_id'] = $faculties[$facultyName]->id;
            $program['slug'] = Str::slug($program['name']);
            Program::create($program);
        }

        // ── Periode PMB Aktif ──────────────────────────────────────────────
        PmbPeriod::create([
            'name'                  => 'PMB 2025/2026',
            'year'                  => 2025,
            'open_date'             => '2025-06-01',
            'close_date'            => '2025-09-30',
            'registration_fee'      => 250000,
            'referral_reward_amount'=> 50000,
            're_registration_fee'   => 25000000,
            're_registration_reward_amount' => 500000,
            'is_active'             => true,
        ]);
    }
}
