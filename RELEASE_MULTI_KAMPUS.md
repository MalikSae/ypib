# Rilis Produksi: Multi-Kampus PMB (Majalengka dan Cirebon)

Dokumen ini melengkapi `RELEASE_MULTI_PERIODE.md`. Ikuti langkah gladi dan rilis yang sama (staging dengan salinan data produksi, lalu produksi).

## 1. Ringkasan

- **Kampus** menjadi data: Majalengka (bawaan) dan Cirebon, dikelola di Admin → Master Data → Kampus.
- **Prodi per kampus**: biaya pendaftaran, kuota, dan fee affiliate kini per periode × prodi × kampus (`period_programs.campus_id`). Mengaturnya di form Program Studi (kartu "Dibuka di Kampus Mana?") atau di Periode → Biaya & Kuota (tab kampus).
- **Pendaftar** memilih kampus dulu, lalu prodi yang dibuka di kampus itu. `registrations.campus_id` menyimpan pilihannya.
- **Staf per kampus**: akun admin, operator, dan panitia dapat dibatasi ke satu kampus (`users.campus_id`). Staf kampus hanya melihat pendaftar kampusnya.
- **Akun Staf** (Pengaturan → Akun Staf, hanya admin semua kampus) menggantikan menu Kelola Panitia. Akun dinonaktifkan, tidak dihapus (`users.is_active`).
- **Landing per kampus**: `/{slug}` (mis. `/cirebon`) dengan teks hero, alamat, WA, biaya, dan kuota kampus itu.

## 2. Migrasi (urutan)

| Migrasi | Isi |
|---|---|
| `2026_10_15_000001_create_campuses_table` | Tabel `campuses`; membuat **Kampus Majalengka (bawaan)** dan **Kampus Cirebon** bila belum ada. |
| `2026_10_15_000002_add_campus_id_to_period_programs_and_registrations` | `campus_id` di `period_programs` (unik `periode + prodi + kampus`) dan `registrations`. **Semua data lama dimasukkan ke Majalengka.** |
| `2026_10_15_000003_add_campus_id_to_users_table` | `users.campus_id` (kosong = semua kampus). |
| `2026_10_19_000001_add_is_active_to_users_table` | `users.is_active`, semua akun lama tetap aktif. |
| `2026_10_19_000002_add_hero_copy_to_campuses_table` | Teks hero kampus; mengisi awal Cirebon dengan teks Farmasi. |

Idempoten: aman dijalankan ulang bila berhenti di tengah. `down()` migrasi `000002` **menghapus baris biaya/kuota kampus non-bawaan** (tidak bisa dipulihkan), jadi rollback hanya bila belum ada data Cirebon yang diisi.

## 3. Sebelum rilis (H-1)

1. Backup database produksi.
2. Pastikan `APP_URL` di `.env` produksi sudah domain asli (tautan afiliasi di dashboard dibangun darinya); nilai lokal seperti `http://ypib.test` membuat semua tautan yang dibagikan salah.
3. Aset `public/build` yang ada sudah memuat semua kelas Tailwind yang dipakai view multi-kampus (diperiksa dengan membandingkan selector build dengan kelas di view). Bila ada view baru yang memakai kelas yang belum pernah dipakai, jalankan `npm run build` dan commit `public/build`; kelas yang tidak ada di build tidak berefek (contoh: `mb-10` di hero landing, yang diganti inline style).
4. Pastikan SMTP/Integrasi Email tidak diperlukan: pembuatan akun staf memakai password manual, tanpa email.

## 4. Setelah migrasi: langkah manual (wajib)

Data lama sengaja tidak diubah otomatis:

1. **Prodi Cirebon yang sudah ada** ("S1 Farmasi Cirebon", "Profesi Apoteker Cirebon") masih ditawarkan di **kampus bawaan**. Buka Admin → Program Studi → Edit untuk masing-masing, lalu di kartu **Dibuka di Kampus Mana?**: centang **Kampus Cirebon**, isi biaya dan kuota (mis. Rp 350.000 dan Rp 750.000), dan **cabut centang Kampus Majalengka**. Simpan.
2. Lengkapi **Kampus Cirebon** (Admin → Kampus → Edit): alamat, **WhatsApp admin** (footer landing memakainya; tanpa isian, nomornya tidak tampil), rekening pembayaran bila berbeda, dan periksa **teks hero**.
3. Di Periode → Biaya & Kuota (tab Kampus Cirebon), atur biaya daftar ulang dan fee affiliate tiap prodi Cirebon. Baris baru meminjam nilai kampus bawaan sebagai titik awal.
4. Buat akun staf Cirebon di Akun Staf (peran + kampus Cirebon), bila diperlukan.
5. Cek `https://<domain>/cirebon`.

## 5. Smoke test setelah rilis

- Landing `/` tampil seperti biasa; `/cirebon` menampilkan hanya prodi Cirebon dengan biaya dan kuota Cirebon; `/majalengka` mengarah ke `/`.
- Form pendaftaran: pilih kampus, daftar prodi ikut berubah, biaya ringkasan benar.
- Daftar pendaftar lama tetap muncul untuk admin semua kampus (tercatat di Kampus Majalengka).
- Login admin semua kampus; menu Akun Staf, Kampus, dan Periode tampil. Admin yang dibatasi kampus tidak melihatnya dan hanya melihat pendaftar kampusnya.
- Nonaktifkan akun uji lewat Akun Staf: akun tidak bisa login dan sesi aktifnya putus.

## 6. Perilaku yang perlu diketahui

- Rute `/{slug}` berada paling akhir; slug kampus yang sama dengan rute sistem (`admin`, `daftar`, `login`, dst.) ditolak di form Kampus. Mengganti slug kampus tidak memutus tautan lama: slug lama dialihkan 301 ke yang baru (tabel `campus_slug_redirects`), termasuk tautan afiliasi `?kampus=slug-lama`.
- Galeri dan mitra bersifat global, jadi disembunyikan di halaman kampus non-bawaan.
- Jalur pemulihan bila semua admin terkunci: `php artisan users:create-admin email@contoh.com "Nama"` (password acak ditampilkan sekali). Membatasi akun ke kampus: `php artisan users:set-campus email KODE`.
- Seeder data uji `CirebonDummySeeder` hanya untuk lingkungan lokal dan menolak berjalan di production.

## 7. Rollback

Aplikasi lama tidak mengenal `campus_id`, jadi rollback kode tanpa rollback database aman selama kolom baru dibiarkan (semuanya nullable atau punya default). Rollback database (`migrate:rollback`) menghapus data biaya kampus non-bawaan (lihat bagian 2).

## 8. Tautan afiliasi per kampus

- Migrasi `2026_10_19_000003_add_campus_id_to_referral_clicks_table` menambah `referral_clicks.campus_id` (kosong untuk klik lama dan klik tautan umum).
- `/ref/KODE?kampus=cirebon` mencatat klik, menyimpan cookie `ref` (7 hari, klik terakhir menang), lalu membuka landing kampus (`/cirebon`). `?kampus=majalengka` membuka `/`. Tanpa `?kampus=`, atau dengan kampus tidak dikenal/nonaktif, perilaku lama berlaku (form pendaftaran).
- Dashboard afiliasi menampilkan satu tautan per kampus aktif ditambah "Link Umum". Afiliator yang sudah menyebarkan `/ref/KODE` tidak perlu mengganti tautan.
- Komisi tidak berubah: tetap mengikuti kampus tempat pendaftar mendaftar, bukan kampus di tautan.

## 9. Perilaku tambahan

- Klik tautan afiliasi dihitung maksimal sekali per 5 menit untuk kombinasi afiliator + kampus + IP (cookie tetap dipasang pada setiap klik). Ini memakai cache aplikasi (`CACHE_STORE`).
- Migrasi `2026_10_19_000004_create_campus_slug_redirects_table` menyimpan slug lama kampus. Slug lama kampus lain tidak bisa dipakai ulang.
- Rute landing kampus dibatasi 120 permintaan per menit per IP.
- Parameter query berbentuk array (`?kampus[]=x`) pada halaman yang diubah di fitur ini diperlakukan sebagai kosong, bukan error 500.
