# Project Condition Report - PMB YPIB

Laporan kondisi codebase berdasarkan pemindaian file konfigurasi, struktur direktori, skema database (migration), route, dan controller. Terakhir diperbarui: 2026-10-10 (commit `09998bc`; lihat bagian 10 untuk daftar periksa deploy).

## 1. Framework & Versi
- **Framework:** Laravel Framework `^12.0`
- **PHP Version:** `^8.2` (dari `composer.json`; target produksi 8.3)
- **Frontend:** Livewire `^4.3`, Alpine.js, Tailwind CSS `^3.4`, Vite
- **Hosting:** Hostinger shared hosting + Cloudflare DNS
- **Database Connection:** MySQL (berdasarkan file `.env` aktual: `DB_CONNECTION=mysql`, `DB_DATABASE=ypib`)

## 2. Struktur Folder Utama
Berikut adalah struktur folder root utama (2 level):
- `app/`
  - `Console/`, `Exports/`, `Http/` (Controllers: `Admin/`, `Auth/`, `Panitia/`; Middleware `RoleMiddleware`), `Livewire/`, `Models/`, `Providers/`, `View/`
- `bootstrap/`
- `config/`
- `database/`
  - `factories/`, `migrations/`, `seeders/`
- `public/` (termasuk `sw.js`, `offline.html`, `manifest-public|admin|panitia.json` untuk PWA)
- `resources/`
  - `css/`, `js/`, `views/`
- `routes/`
  - `auth.php`, `console.php`, `web.php`
- `storage/`
- `tests/`
- `vendor/`

## 3. Database (Tabel & Kolom Utama)
Berdasarkan file migrations, berikut adalah tabel-tabel utama di sistem dan field pentingnya:

- **users**: `id`, `name`, `email`, `password`, `role` (enum: admin, operator, referrer, mahasiswa, panitia), `phone`, `is_referrer`, `referrer_id`.
- **pmb_periods**: `id`, `name`, `year`, `open_date`, `close_date`, `status` (`draft`/`published`/`archived`; sumber kebenaran), `is_active` (usang, disinkronkan dari `status`), `re_registration_deadline`, `payment_grace_days` (bawaan 3), `nim_sequence_start`, `published_at`, `archived_at`, `university_bank_name`, `university_bank_account`, `university_bank_account_name`, `admin_whatsapp`.
- **period_programs**: biaya, kuota, dan komisi per prodi per periode: `period_id`, `program_id` (unik bersama), `is_offered`, `quota`, `registration_fee`, `re_registration_fee`, `re_registration_fee_details` (json), `re_registration_minimum_payment`, `referral_reward_amount`, `re_registration_reward_amount`. Kolom serupa di `programs` tinggal cadangan baca.
- **faculties**: `id`, `name`, `description`, `is_active`.
- **programs**: `id`, `name`, `faculty_id`, `accreditation`, `quota`, `registration_fee`, `is_active`, `kode_prodi`, `registration_track` (reguler/non_reguler; `kode_prodi` unik komposit dengan track), `re_registration_minimum_payment`, `re_registration_fee`, `re_registration_fee_details`, `referral_reward_amount`, `re_registration_reward_amount`, `icon`, `slug`.
- **program_galleries**: galeri foto per prodi.
- **referrers**: `id`, `user_id`, `referral_code`, `status`, `bank_name`, `bank_account`, `bank_account_name`.
- **referral_clicks**: `id`, `referrer_id`, `ip_address`, `user_agent`.
- **registrations**: `id`, `registration_number`, `nim`, `letter_number`, `user_id`, `period_id`, `first_choice_program_id`, `second_choice_program_id`, `referrer_id`, `status` (enum panjang pendaftaran), `registration_type` (reguler/alumni), `admission_path`, `payment_proof`, `alumni_card_proof`, `re_registration_payment_proof`, `document_proof` (legacy, tidak dibaca kode), `internal_notes`, `deleted_at` (soft delete), `active_key` (kolom turunan: satu pendaftaran aktif per akun per periode, unik), `reapply_allowed`/`reapply_note` (izin admin untuk NIK ber-NIM mendaftar lagi pada prodi yang sama), (dan field data diri lengkap). `nim` unik; indeks pada `nik`.
- **registration_documents**: `id`, `registration_id`, `document_type`, `file_path`, `status`, `review_note`, `reviewed_by`.
- **payment_logs**: `id`, `registration_id`, `acted_by`, `action`, `note`.
- **rewards**: `id`, `referrer_id`, `registration_id`, `nik` (NIK saat komisi dibuat; dasar satu komisi per orang), `reward_type` (`registration`/`re_registration`), `amount`, `status`, `approved_by`, `approved_at`, `disbursed_at`.
- **partners**: `id`, `name`, `logo_path`, `url`, `is_active`.
- **facilities**: `id`, `name`, `description`, `image_path`, `icon`, `is_active`.
- **exam_questions**: `id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `is_active`.
- **exam_sessions**: `id`, `registration_id`, `status`, `score`, `result_label`, `started_at`, `completed_at`.
- **exam_answers**: `id`, `exam_session_id`, `exam_question_id`, `selected_option`, `is_correct`.
- **settings**: `id`, `key`, `value`, `type`, `description`.
- **referrer_logs**: `id`, `referrer_id`, `acted_by`, `action`, `note`.

## 4. Fitur-Fitur yang Sudah Ada
Berdasarkan `routes/web.php` dan `routes/auth.php`:

- **Public / Landing Page:**
  - Tampilan beranda, detail prodi (`/prodi/{slug}`) dengan galeri, fasilitas, partner.
  - PWA installable + halaman offline (`sw.js`, `offline.html`).
  - Referral tracking (`/ref/{code}`).
- **Pendaftaran (Mahasiswa):**
  - Pembuatan pendaftaran (wizard 5 langkah via Livewire `RegistrationForm`; jalur umum / alumni).
  - Status pendaftaran dan pengunggahan bukti bayar / kartu alumni / daftar ulang.
  - Pengisian detail data diri, sekolah, ortu.
  - Upload dokumen berkas-berkas pendaftaran.
  - Download formulir pendaftaran, SKL, dan e-KTM.
  - Pelaksanaan tes tulis online secara langsung (CBT sederhana).
  - Halaman informasi jadwal/hasil interview.
- **Admin & Operator:**
  - Pengaturan periode PMB dan email.
  - Master data CRUD (Fakultas, Prodi, Mitra, Fasilitas, Bank Soal, Panitia).
  - Manajemen Pendaftar (filter prodi, konfirmasi bayar & daftar ulang, override status, update data, catatan, ubah referral, export, soft delete + sampah + restore, review dokumen per berkas / massal, upload bukti manual, reset tes tulis).
  - Rekap pendaftar per prodi + export (`/admin/pendaftar/rekap`).
  - Manajemen Afiliasi (Approval referrer, update rekening, export).
  - Pencairan komisi/reward (Approve, disburse per orang atau massal, export).
  - Reset password user.
- **Afiliasi (Referrer):**
  - Mendaftar sebagai afiliator, aktivasi, dan melengkapi data bank.
  - Dashboard performa (klik & pendaftaran berhasil).
- **Panitia (Interview):**
  - Dashboard khusus (mobile-friendly).
  - Scan QR pendaftaran dan konfirmasi selesai/lulus interview.
- **Autentikasi (Breeze):**
  - Login, register, forgot/reset password, email verification.

## 5. Alur Bisnis Utama
**Alur Pendaftaran Mahasiswa Baru (PMB):**
1. **Pendaftaran:** Pendaftar mengisi formulir awal dari landing page dan masuk ke sistem dengan status awal `menunggu_pembayaran` (jalur alumni: upload kartu alumni, tanpa biaya pendaftaran).
2. **Pembayaran:** Pendaftar mengunggah bukti bayar pendaftaran. Status berubah menjadi `menunggu_konfirmasi`.
3. **Konfirmasi Admin:** Admin memvalidasi pembayaran. Jika sah, status menjadi `terdaftar`.
4. **Lengkapi Form & Upload Dokumen:** Mahasiswa harus melengkapi form detail (jika belum lengkap). Setelah lengkap, mereka mengunggah dokumen pendaftaran: Foto, KK, dan Akta Lahir wajib (`RegistrationDocument::MANDATORY_TYPES`); KTP, transkrip nilai, surat keterangan sehat, dan sertifikat opsional. Dokumen berstatus `menunggu_review`. Admin mereview *per dokumen* (terima/tolak revisi).
5. **Tes Tulis:** Setelah dokumen lengkap & disetujui otomatis lanjut ke status `menunggu_tes_tulis`. Peserta menjawab bank soal di sistem, disubmit, dihitung skor otomatis (`sangat_baik` atau `baik`).
6. **Interview:** Setelah tes tulis, peserta diarahkan ke status `menunggu_interview`.
7. **Persetujuan Akhir (Panitia):** Role Panitia memindai QR pendaftar dan memutuskan `diterima` atau `ditolak` (nomor SKL dibuat saat `diterima`). Status menjadi `menunggu_konfirmasi_daftar_ulang` setelah pendaftar mengunggah bukti daftar ulang.
8. **Daftar Ulang:** Pendaftar mengunggah bukti bayar daftar ulang. Admin menyetujui (`confirmReRegistration`); NIM di-generate di tahap ini (`Registration::generateNim()`, format `YY` + `kode_prodi` + track + urut 3 digit; urutan dihitung **per awalan** `YY+kode_prodi+track`, lantai dari `pmb_periods.nim_sequence_start`, 2026 mulai dari 400; di atas 999 ditolak dengan pesan jelas), dan alur selesai dengan status `daftar_ulang_selesai`.
9. **Dokumen keluaran:** Nomor surat SKL (`generateLetterNumber()`) dibuat saat status diterima (override admin atau konfirmasi panitia). Pendaftar dapat mengunduh formulir PDF, SKL, dan e-KTM.

**Alur Afiliasi (Referrer):**
1. User (umumnya mahasiswa lama/pihak luar) mendaftar di `/afiliasi`.
2. Admin mengaktifkan status Referrer.
3. Referrer mendapat link unik (`/ref/{code}`). Setiap klik dicatat di `referral_clicks`.
4. Jika klik berujung pada Registrasi (session), `referrer_id` disimpan di tabel `registrations`.
5. Reward di tabel `rewards` dibuat langsung oleh `AdminRegistrationController`: tipe `registration` saat admin mengonfirmasi pembayaran (nominal dari `period_programs.referral_reward_amount` periode pendaftarannya, default 50.000) dan tipe `re_registration` saat daftar ulang dikonfirmasi (`period_programs.re_registration_reward_amount`, dilewati bila 0 atau jalur alumni). **Satu komisi per orang (NIK) seumur hidup** untuk tiap tipe, lintas periode, prodi, dan afiliator; reward `cancelled` tidak dihitung. Status awal `approved`. Pendaftar yang di-soft-delete menyesuaikan komisi otomatis.
6. Admin melakukan *approve* lalu *disburse* komisi/reward yang bisa dilakukan massal.

## 6. Package/Library yang Digunakan
**Backend (Composer):**
- `barryvdh/laravel-dompdf` (^3.1): Cetak PDF (Formulir pendaftaran, SKL).
- `endroid/qr-code` & `simplesoftwareio/simple-qrcode`: Generator QR Code untuk verifikasi e-KTM / Formulir.
- `intervention/image` (^4.1): Manipulasi gambar (khususnya untuk e-KTM komposit foto & QR).
- `laravel/breeze` (^2.4): Autentikasi starter kit.
- `livewire/livewire` (^4.3): Komponen interaktif (dipakai kemungkinan untuk list data table).
- `maatwebsite/excel` (^3.1): Export data ke Excel/CSV.
- `symfony/resend-mailer`: Mailer.

**Frontend (NPM):**
- `tailwindcss` (^3.4) & plugin turunannya (`@tailwindcss/forms`, dll): Framework CSS utama.
- `alpinejs` (^3.4): Javascript ringan untuk interaktivitas UI.
- `axios`: HTTP client.
- `vite`: Build tool.

## 7. Kondisi .env.example
File `.env.example` terstruktur secara default Laravel dengan beberapa kunci khusus yang menonjol:
- Konfigurasi basic `APP_*` (URL, Name, Key, Env).
- Konfigurasi `DB_*` menggunakan SQLite secara bawaan (konfigurasi MySQL di-comment).
- Konfigurasi Email (`MAIL_*`); default `MAIL_MAILER=log` (email tidak terkirim sampai diubah; paket `symfony/resend-mailer` tersedia). Pengaturan email juga bisa diubah dari admin (`/admin/pengaturan-email`, tabel `settings`).
- `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `FILESYSTEM_DISK=local`.
- Saklar multi-periode (keduanya `false` bawaan): `PMB_ENFORCE_WINDOW` (gerbang jendela pendaftaran) dan `PMB_ENFORCE_NIK_BLOCK` (blokir satu proses aktif per NIK). Lihat bagian 10.G.
- Terdapat blok konfigurasi `AWS_*` (S3). Ini menandakan project memiliki kapabilitas/disiapkan untuk upload file ke S3 Storage.

## 8. Role & Auth System
Sistem otorisasi menggunakan Guard standar (`web`), dilengkapi dengan role-based akses sederhana berbasis Enum di tabel `users`.
- Role terdiri dari: `admin`, `operator`, `referrer`, `mahasiswa`, dan `panitia`.
- Middleware khusus: `role:admin,operator` melindungi route `/admin/*`. `role:panitia` melindungi route `/panitia/*`.
- Breeze mengatur akses `/login` dan validasi sesi. Logic redirect dashboard custom terdapat di `Route::get('/dashboard')`, me-redirect user ke panel yang sesuai rolenya saat berhasil login.

## 9. Catatan Penting & Hal yang Perlu Diperhatikan (Watch Out)
1. **Sistem Desain Ganda:** Layout admin (`layouts.admin`) dan layout publik (`layouts.landing`) menggunakan sistem UI yang benar-benar terpisah. **Jangan pernah memakai Breeze `x-app-layout` pada admin.**
2. **Standardisasi Tailwind Warna Admin:** Halaman admin dilarang menggunakan `gray-*` atau `blue-*` default Tailwind. Harus menggunakan color token custom `neutral-*` dan `primary-*`.
3. **Komponen Blade Custom Admin:** Form admin sangat bergantung pada komponen kustom (misal: `<x-card>`, `<x-button>`, `<x-text-input>`, `<x-select>`). Jangan buat elemen form mentah tanpa komponen ini.
4. **Sistem Berkas Pendaftaran:** Alur dokumen menggunakan arsitektur tabel relasional tunggal yang baru (`registration_documents`). Kolom lama `document_proof` di tabel `registrations` adalah *deprecated/legacy* dan di-ignore dari logic saat ini. Jangan pernah membangun fitur di atas `document_proof` lama.
5. **Image Processing:** Terdapat kerumitan di fitur E-KTM (`downloadEktm`), dimana sistem merakit pas foto, template PNG kosong, text overlay, dan QR code (Intervention + GD Native). Hati-hati jika menyentuh dependensi image pada sistem karena font/pallete issue rentan terjadi di GD.
6. **Unique Constraints Prodi:** Constraint di database untuk `kode_prodi` adalah composite (terikat dengan `registration_track`).
7. **Soft Delete Pendaftar:** `registrations` memakai `SoftDeletes`. Query yang menyentuh relasi registrasi (mis. dashboard panitia) harus menangani data terhapus (lihat fix `b9906b5`). Pembuatan NIM memakai `withTrashed()` agar urutan tidak bentrok.
8. **NIM & Nomor SKL:** NIM dibuat saat konfirmasi daftar ulang, bukan saat pendaftaran. Nomor SKL dibuat saat status `diterima` (override admin atau konfirmasi panitia).
9. **Batas Upload:** bukti bayar/kartu alumni/bukti daftar ulang maks 2MB; dokumen pendaftaran maks 15MB; gambar galeri prodi & fasilitas maks 5MB.
10. **Intervention Image v4:** API berbeda dari v2/v3 (lihat fix `a981cd8`); cek nama method di dokumentasi v4 sebelum mengubah kode e-KTM.
11. **Integrasi Belum Aktif:** Xendit (pembayaran) dan Fonnte/Wablas (WhatsApp) belum terhubung; konfirmasi pembayaran masih manual oleh admin.
12. **PWA:** service worker `public/sw.js` meng-cache area publik; ubah aset/rute penting bisa butuh bump versi cache.
13. **Test:** suite PMB lengkap di `tests/Feature/Pmb/` (lihat bagian 11); jalankan `php artisan test` sebelum dan sesudah mengubah alur PMB.
14. **Berkas Sensitif Privat:** bukti bayar, kartu alumni, bukti daftar ulang, dan dokumen pendaftaran disimpan di disk `local` (`storage/app/private`) lewat `App\Support\PrivateFile` dan hanya ditayangkan oleh `FileController` (rute `/berkas/*`, wajib login; pemilik, admin, operator; panitia hanya dokumen). Jangan memakai `Storage::url()` atau disk `public` untuk berkas ini. Berkas lama di disk `public` tetap terbaca (fallback) sampai dipindah dengan `php artisan files:privatize` (dukung `--dry-run`); jalankan sekali di server setelah deploy.
15. **Hak Akses Akun:** `/admin/panitia/*` hanya boleh menyentuh akun berperan `panitia` (selain itu 404); reset password akun `admin`/`operator` hanya oleh admin atau pemilik akun. Jangan memakai route model binding `User` tanpa cek role di area admin.
16. **Rahasia & Ekspor:** API key Resend disimpan terenkripsi (`enc:` + `Crypt`) lewat `Setting::setSecret()/getSecret()` dan tidak pernah dikirim ke browser (kosong = pertahankan, ada opsi hapus). Semua class export Excel memakai `SafeValueBinder` (teks berawalan `=` dan deretan digit >= 15 ditulis sebagai teks); CSV memakai `CsvSanitizer::row()`.
17. **Komisi & Afiliator:** pergantian afiliator pendaftar ditolak bila ada komisi berstatus `disbursed`; komisi `pending`/`approved` dipindah atau dibatalkan (`cancelled`, bukan dihapus); `total_conversions` tidak pernah di bawah 0. Catatan internal diedit di tempat dengan optimistic lock (`notes_checksum`) dan isi lama dicatat di `payment_logs` (`internal_note_edited`). Reset tes tulis hanya sebelum keputusan interview.
18. **Hapus Akun & Validasi:** akun dengan riwayat pendaftaran/aktivitas tidak bisa dihapus mandiri (pesan di bag `userDeletion`); penghapusan memakai query agar `Auth::logout()` tidak meng-INSERT ulang baris user. NIK wajib `digits:16`, `school_grade` wajib `numeric|between:0,100` di form publik, ubah data pendaftar, dan edit admin.
19. **Zona Waktu & Bahasa:** `app.timezone` = `Asia/Jakarta` (env `APP_TIMEZONE`) dan `app.locale` = `id` (sengaja bukan env; terjemahan di `lang/id`). Timestamp yang tersimpan sebelum perubahan ini berupa jam UTC dan tidak digeser otomatis. Penambahan berkas bahasa baru harus mengikuti `lang/id/validation.php` (termasuk `attributes`).
20. **Kuota & Konten:** `quota` prodi ditegakkan (`Program::isFull()`, `0` = tanpa batas; dihitung per periode, tidak termasuk `ditolak`/soft-delete) di form publik (langkah 2 dan submit dengan `lockForUpdate`) dan saat pendaftar pindah prodi. Slug prodi dibuat unik (`Program::uniqueSlug`) dan hanya berubah saat nama berubah. HTML deskripsi prodi disaring `App\Support\HtmlSanitizer` (HTMLPurifier) saat simpan dan render; jangan merender HTML mentah dari input admin.
21. **Pembatasan & Header:** `POST /register`, `/afiliasi`, `/forgot-password` memakai limiter `public-signup` (10/menit dan 60/jam per IP, satu bucket bersama; atur di `AppServiceProvider`), `/ref/{code}` 60/menit. `App\Http\Middleware\SecurityHeaders` (global) memasang `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` (kamera `self` untuk pemindai QR panitia) dan HSTS hanya di produksi lewat HTTPS; CSP belum dipasang. `/admin/komponen` hanya di lingkungan `local`; rute `/brand` dihapus. Set `SESSION_SECURE_COOKIE=true` di produksi.
22. **IP Klien di Balik Proxy:** `bootstrap/app.php` mengonfigurasi `trustProxies` (rentang Cloudflare + jaringan privat; override `TRUSTED_PROXIES` di `.env`). Tanpa itu semua pengunjung berbagi satu bucket pembatas laju (login, `public-signup`) dan `isSecure()` selalu false. Setelah deploy, verifikasi `request()->ip()` di server adalah IP pengunjung, bukan IP proxy hosting.
23. **Seeder, NIM, Rollback:** `DatabaseSeeder` menolak jalan di production kecuali `SEED_ADMIN_PASSWORD` diisi (bukan kosong/"password"); periode seed memakai tanggal relatif hari ini. Konfirmasi daftar ulang ditolak bila prodi belum punya `kode_prodi` (NIM tidak boleh null). `migrate:rollback` penuh sudah diuji sampai kosong dan `migrate` ulang bersih; jaga `down()` agar tidak bergantung pada kolom yang baru dikembalikan migrasi sebelumnya.
24. **Backfill & Recount (command, dry-run default):** `php artisan registrations:backfill-numbers [--apply --acted-by=ID] [--letter-date=YYYY-MM-DD]` mengisi NIM (status `daftar_ulang_selesai`, butuh `kode_prodi`) dan nomor SKL pendaftar lama; `php artisan referrers:recount [--clicks] [--apply --acted-by=ID]` menghitung ulang `total_conversions` (dan klik) dari `rewards`/`referral_clicks`. Tanpa `--apply` hanya simulasi (transaksi di-rollback). Perubahan tercatat di `payment_logs` (`numbers_backfilled`) dan `referrer_logs` (`counter_recounted`). Cadangkan DB sebelum `--apply`.
25. **Formulir PDF:** `RegistrationController::downloadPdf` membuat QR hanya bila `registration_number` ada (nomor baru diberikan saat pembayaran dikonfirmasi); sebelum itu formulir tetap bisa diunduh tanpa QR.

## 10. Daftar Periksa Deploy

Hasil QA berlapis (kode, data nyata, UI, dependensi, konfigurasi). Centang berurutan; langkah bertanda **[DATA]** mengubah data produksi dan wajib didahului cadangan.

### A. Sebelum deploy
- [ ] **Cadangkan** database dan folder berkas: `public/storage/` (berkas lama) dan `storage/app/private/`.
- [ ] **Perbarui tanggal periode aktif** di `/admin/periode`. Sejak `PmbPeriod::isOpen()` ditegakkan, periode dengan `close_date` lewat (di DB dev: `2026-09-30`) menolak pendaftaran baru. Nama periode dan tanggal harus konsisten.
- [ ] **Isi `kode_prodi`** semua prodi aktif di `/admin/programs`. Saat QA 5 dari 9 prodi masih kosong; tanpa kode, konfirmasi daftar ulang ditolak (NIM tidak bisa dibuat).
- [ ] **Tinjau `quota` prodi.** Kuota > 0 kini ditegakkan per periode (`0` = tanpa batas). Pendaftar `menunggu_pembayaran` ikut menghitung kursi.
- [ ] Siapkan `.env` produksi:

| Kunci | Nilai | Catatan |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | HSTS dan guard seeder hanya aktif di `production` |
| `APP_URL` | URL HTTPS situs | |
| `SESSION_SECURE_COOKIE` | `true` | cookie sesi hanya lewat HTTPS |
| `TRUSTED_PROXIES` | kosong (rentang Cloudflare + jaringan privat) atau daftar IP/CIDR proxy hosting | lihat verifikasi IP di bagian C |
| `SEED_ADMIN_PASSWORD` | hanya bila menjalankan `db:seed` | seeder menolak jalan di production tanpa nilai kuat |
| `MAIL_MAILER` / Resend API key | isi key di `/admin/pengaturan-email` | bawaan `.env.example` adalah `log`: email reset password tidak terkirim |

  `APP_TIMEZONE` tidak perlu diisi (default `Asia/Jakarta`); `app.locale` dikunci `id`.
- [ ] Pastikan folder ini **dapat ditulis**: `storage/framework/cache/htmlpurifier`, `storage/app/private`, `bootstrap/cache`.

### B. Deploy
- [ ] `composer install --no-dev --optimize-autoloader` (`composer.lock` berubah: 34 advisory ditutup; `composer audit` bersih).
- [ ] Aset frontend: sertakan `public/build` hasil `npm run build` (tidak ada build di server).
- [ ] `php artisan migrate --force` (QA tidak menambah migrasi baru; hanya `down()` migrasi `2026_05_24_080000` yang diperbaiki).
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache` (sudah diuji berhasil).
- [ ] **[DATA]** `php artisan files:privatize --dry-run`, tinjau daftar, lalu `php artisan files:privatize`. Berkas lama tetap terbaca lewat fallback sampai dipindah.

### C. Verifikasi setelah deploy
- [ ] **IP asli pengunjung**: `request()->ip()` di server harus IP pengunjung, bukan IP proxy. Bila bukan, isi `TRUSTED_PROXIES`. Gejala bila salah: semua pengunjung berbagi satu batas laju (429 massal pada `/register`, `/forgot-password`, login).
- [ ] Header respons: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, dan `Strict-Transport-Security` (hanya HTTPS).
- [ ] Alur utama: daftar akun → wizard → upload bukti → admin konfirmasi → dokumen → tes → interview (panitia, kamera pemindai QR berfungsi) → daftar ulang.
- [ ] Unduhan: formulir PDF (juga sebelum nomor pendaftaran ada), SKL, e-KTM, dan tautan `/berkas/*` (pemilik/admin/operator/panitia sesuai aturan).
- [ ] Tidak bisa mengakses `/storage/bukti-bayar/...`, `/storage/dokumen-pendaftaran/...` secara langsung setelah `files:privatize`.
- [ ] `/brand` 404 dan `/admin/komponen` 404 (hanya di `local`).
- [ ] Email reset password terkirim (setelah Resend API key diisi).

### D. Perapian data (setelah verifikasi, semuanya **[DATA]**)
Semua command berjalan sebagai simulasi tanpa `--apply`; hasil simulasi sama persis dengan yang akan ditulis.
- [ ] `php artisan registrations:backfill-numbers --letter-date=YYYY-MM-DD` → tinjau → ulangi dengan `--apply --acted-by=<ID admin>`. Di DB dev: 3 pendaftar lama tanpa NIM/SKL. Tentukan `--letter-date` agar bulan/tahun SKL mencerminkan tanggal keputusan, bukan hari ini.
- [ ] `php artisan referrers:recount --clicks` → tinjau → `--apply --acted-by=<ID admin>`. Di DB dev: 3 dari 5 afiliator menyimpang (termasuk `TESTX` yang kemungkinan data uji).
- [ ] Hapus atau nonaktifkan akun/afiliator uji, dan ganti password default akun seed bila ada.

### E. Keputusan terbuka (belum dikerjakan)
- Geser timestamp lama +7 jam: data sebelum perubahan zona waktu berupa jam UTC (baris baru sudah WIB).
- Kedaluwarsa pendaftar `menunggu_pembayaran` agar tidak menahan kuota.
- NIK unik antar pendaftar (saat ini hanya satu registrasi per akun per periode).
- `public/.htaccess` meng-cache gambar `public/images` selama 1 tahun tanpa versi nama berkas.
- Content-Security-Policy penuh (Alpine/Livewire dan inline style perlu dirancang terpisah).
- Batas laju `public-signup` (10/menit dan 60/jam per IP) dapat disesuaikan di `AppServiceProvider`.
- Batas ekspor XLSX pendaftar `RegistrationExport::XLSX_MAX_ROWS` = 5.000 baris (XLSX ±25 MB memori per 1.000 baris). Di atas itu admin diarahkan ke tombol **Export CSV** (streaming, memori konstan). Sesuaikan angka ini dengan `memory_limit` dan `max_execution_time` hosting; di hosting bersama 256 MB, XLSX mulai berisiko di kisaran 8.000–9.000 baris.

### F. Rollback
- Kode: kembalikan commit sebelumnya, lalu `php artisan config:clear route:clear view:clear`.
- Berkas: `files:privatize` memindahkan (bukan menyalin) berkas; pemulihan memerlukan cadangan folder `public/storage/`.
- Data: `backfill-numbers`/`recount` tercatat di `payment_logs` (`numbers_backfilled`) dan `referrer_logs` (`counter_recounted`); pemulihan nilai lama memerlukan cadangan DB.
- `migrate:rollback` penuh sudah diuji sampai kosong dan `migrate` ulang bersih.
### G. Multi-periode (urutan rilis; lihat `PLAN_MULTI_PERIODE.md`)

Semua langkah migrasi bersifat penambahan dan dapat di-rollback sampai T9 (pembersihan, belum dikerjakan). Dua saklar baru **mati** secara bawaan sehingga rilis tidak mengubah perilaku pendaftar sampai dinyalakan.

- [ ] **Cadangkan** database. Migrasi baru: `add_multi_period_columns`, `create_period_programs_table`, `add_reapply_columns_to_registrations`, `add_nik_to_rewards_table`.
- [ ] `php artisan periods:precheck` harus bersih (tanpa pendaftaran aktif ganda per akun+periode, tanpa NIM ganda). Migrasi menolak jalan bila tidak.
- [ ] `php artisan migrate --force`. Backfill otomatis: periode aktif menjadi `published`, selebihnya `archived`; `nim_sequence_start = 400` untuk tahun 2026; `period_programs` diisi dari nilai `programs`; `rewards.nik` dari pendaftarannya.
- [ ] **[DATA]** Buka `/admin/periode`: isi **tenggat daftar ulang**, rekening, dan WhatsApp periode berjalan; tinjau **masa bayar** (`payment_grace_days`, bawaan 3) dan `nim_sequence_start`. Periode tayang tanpa tenggat tidak membatasi daftar ulang.
- [ ] **[DATA]** Buka `Biaya & Kuota` pada periode berjalan dan tinjau tiap prodi (ditawarkan, kuota, biaya, komisi, rincian daftar ulang).
- [ ] Putuskan bila perlu memecah pendaftaran lama per tahun ajaran: `php artisan periods:assign --to=<ID> --from=<ID> [--created-until=YYYY-MM-DD]` (simulasi), lalu `--apply --acted-by=<ID admin>`. Jika seluruh pendaftar lama satu tahun ajaran, lewati.
- [ ] `php artisan periods:readiness` harus tanpa temuan **GAGAL**. Tinjau semua **PERIKSA** (prodi tanpa `kode_prodi`, biaya Rp 0, pendaftaran di periode non-tayang, NIK ganda).
- [ ] Rilis dengan `PMB_ENFORCE_WINDOW=false` dan `PMB_ENFORCE_NIK_BLOCK=false`; verifikasi alur utama dan pemilih periode di header admin.
- [ ] Satu siklus pendaftaran: tinjau tanda **NIK ganda** di `/admin/pendaftar` dan detail pendaftar; selesaikan yang basi.
- [ ] Nyalakan `PMB_ENFORCE_WINDOW=true` (lalu `php artisan config:cache`) setelah tenggat dan masa bayar terisi. Nyalakan `PMB_ENFORCE_NIK_BLOCK=true` terpisah, setelah tanda NIK ganda ditinjau.
- [ ] Jangan mengarsipkan periode lama sebelum melihat jumlah pendaftar yang masih berjalan di halaman konfirmasi arsip; arsip memutus unggahan baru pendaftarnya (unduhan tetap bisa).

**Rollback multi-periode:** matikan kedua saklar (tanpa rilis ulang) bila gerbang atau blokir menolak pendaftar sah; kembalikan kode bila perlu (`migrate:rollback` tiap migrasi baru reversibel). Kolom biaya/kuota di `programs` dan `is_active` sengaja dipertahankan sebagai cadangan sampai pembersihan.

**Keputusan yang masih terbuka** (default di `PLAN_MULTI_PERIODE.md` bagian 3): T-1 kalender reguler dan non-reguler (default: satu kalender), T-2 tenggat kelengkapan untuk pendaftar yang berhenti (default: tidak ada), T-3 lantai urutan NIM 400 global atau per prodi (default: per awalan, dari `nim_sequence_start` periode).

26. **Ekspor Pendaftar:** `GET /admin/pendaftar/export` mendukung `format=xlsx|csv` (+ filter `prodi`, `status`). XLSX menyimpan seluruh sel di memori (±25 MB per 1.000 baris), sehingga ditolak di atas `RegistrationExport::XLSX_MAX_ROWS` (5.000) dengan pesan mengarahkan ke CSV. CSV di-stream (`chunkByIdDesc(500)` + `fputcsv`, BOM UTF-8, memori konstan: 20.000 baris ±9 detik) dan memakai `RegistrationExport::csvRow()` yang menetralkan injeksi formula serta membungkus digit rawan Excel (nol di depan / >= 15 digit) sebagai `="..."`.

27. **Periode (multi-periode):** satu tahun ajaran = satu baris `pmb_periods`; `status` sumber kebenaran (hook `saving` menyelaraskan `is_active` dari atribut yang berubah, jangan menulis keduanya lewat query massal). Fase diturunkan dari tanggal (`registration_open`, masa bayar `close_date + payment_grace_days`, `in_progress`, daftar ulang sampai `re_registration_deadline`), tanpa scheduler. Satu periode hanya boleh tayang bila jendela tanggalnya tidak beririsan dan tenggat daftar ulang, rekening, serta WhatsApp terisi. Semua pembaca periode memakai `PmbPeriod::current()` (pendaftaran baru) atau `PmbPeriod::displayPeriod()` (tampilan); jangan memakai `PmbPeriod::active()->first()`.
28. **Biaya per periode:** SKL, halaman bayar, dan komisi membaca `period_programs` milik periode pendaftarannya (`Registration::programTerms()`, `Program::termsFor($period)`); prodi tanpa baris memakai nilai `programs` sebagai cadangan. Prodi baru dibuat `is_offered = false` sampai biayanya diatur di `/admin/periode/{id}/biaya`. Periode arsip hanya baca. Form prodi tidak lagi mengedit biaya/kuota/komisi.
29. **Satu proses aktif per NIK:** `App\Support\NikGuard`, diperiksa hanya di `RegistrationForm::submit()` dan saat pendaftar mengubah NIK (langkah wizard terbuka untuk tamu dan sengaja tidak memeriksa NIK: tidak boleh menjadi alat enumerasi). Pesan penolakan generik. Selama `PMB_ENFORCE_NIK_BLOCK=false` hanya tanda "NIK ganda" di daftar/detail admin. Pendaftaran ber-NIM pada prodi sama diblokir kecuali admin menyalakan `reapply_allowed` (alasan wajib, tercatat di `payment_logs`).
30. **Gerbang jendela:** `App\Support\RegistrationWindow` menolak (hanya bila `PMB_ENFORCE_WINDOW=true`) unggah bukti bayar pertama di luar masa bayar, bukti daftar ulang setelah tenggat, dan aksi ubah data di periode arsip. Staf, halaman status, riwayat, dan unduhan tidak digerbangi. Pemberitahuan batas waktu di halaman status tampil terlepas dari saklar.
31. **Riwayat dan filter:** pendaftar memilih pendaftaran lewat `?periode=ID` (disimpan di sesi, terikat ke akun; `?periode=reset` menghapus) sehingga halaman dan unduhan mengikuti. Staf memilih periode di header admin (`App\Support\AdminPeriod`, sesi per staf, bawaan periode tampilan, "Semua periode" tersedia): memengaruhi dashboard, daftar, sampah, rekap, dan ekspor, tidak pernah aksi.
32. **Command periode:** `php artisan periods:precheck` (pelanggar unique sebelum migrasi), `periods:readiness` (kesiapan data sebelum menyalakan saklar; kode keluar 1 bila ada temuan GAGAL), `periods:assign --to=ID [--from=ID|--ids=|--created-from=|--created-until=] [--apply --acted-by=ID]` (memindahkan pendaftaran antarperiode; dry-run bawaan; tidak mengubah NIM/nomor yang terbit).

## 11. Pengujian Otomatis

Suite berada di `tests/Feature/Pmb/` (259 test, ±1.530 assertion bersama test bawaan Breeze; ±2 menit) dengan pembangun data di `tests/Concerns/BuildsPmbData.php`.

| Berkas | Cakupan |
|---|---|
| `AccessControlTest` | matriks role, endpoint panitia, reset password staf, akses berkas privat, ekspor, halaman dev |
| `StudentFlowTest` | upload bukti/dokumen + gerbang status, kunci data, ujian, keputusan panitia, PDF/SKL/e-KTM |
| `AdminActionsTest` | konfirmasi bayar, daftar ulang + NIM, reset tes, afiliator, catatan internal, review dokumen, hapus/pulihkan |
| `PublicRegistrationFormTest` | wizard Livewire: validasi, kuota, periode, referrer |
| `AffiliateAndRewardTest` | pendaftaran afiliasi, pelacakan klik, pencairan, CSV reward |
| `NumberingAndBackfillTest` | nomor SKL/NIM, command `registrations:backfill-numbers` dan `referrers:recount` |
| `ExportsAndFilesTest` | ekspor XLSX/CSV, berkas privat, `files:privatize`, unggah gambar |
| `PlatformSecurityAndConfigTest` | header, pembatas laju, IP proxy, zona waktu/bahasa, seeder, sanitasi HTML, master data, rahasia, hapus akun |
| `PeriodLifecycleTest` | status periode, hook `is_active`, jendela WIB, resolver `current()`/`displayPeriod()`, unique `active_key` dan `nim` |
| `PeriodManagementTest` | CRUD periode, tayang/batal/arsip/buka kembali/salin, validasi dan pengaman |
| `PeriodProgramPricingTest` | biaya, kuota, dan komisi per periode, prodi tidak ditawarkan, matriks admin |
| `MultiPeriodRegistrationTest` | daftar lagi, resolver pendaftaran, tombol "daftar di periode berjalan", banner landing |
| `ActiveProcessPerNikTest` | aturan K7, saklar, pesan generik, ubah NIK, penanda `reapply_allowed` |
| `CommissionPerPersonTest` | satu komisi per NIK, `restore()` dengan konflik |
| `RegistrationWindowGateTest` | gerbang bukti bayar/daftar ulang/arsip, saklar, K3 dan K5 |
| `PeriodScopedAdminTest` | pemilih periode global, dashboard/daftar/rekap/ekspor |
| `RegistrationHistoryTest` | riwayat pendaftaran, `?periode=`, banner langkah tertunda |
| `PeriodCommandsTest` | `periods:assign` dan `periods:readiness` |

**Wajib sebelum menjalankan:** buat database khusus test sekali: `CREATE DATABASE ypib_test CHARACTER SET utf8mb4;` (nama diatur di `phpunit.xml`). `tests/TestCase.php` menolak berjalan bila nama database tidak berakhiran `_test`, karena `RefreshDatabase` menghapus seluruh tabel. Jalankan dengan `php artisan test`.

Catatan: test memakai MySQL (bukan SQLite) karena beberapa migrasi dan query memakai fitur MySQL.
