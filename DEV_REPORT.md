# Project Condition Report - PMB YPIB

Laporan kondisi codebase berdasarkan pemindaian file konfigurasi, struktur direktori, skema database (migration), route, dan controller. Terakhir diperbarui: 2026-10-10 (commit `a981cd8`).

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
- **pmb_periods**: `id`, `name`, `start_date`, `end_date`, `is_active`, `bank_name`, `bank_account`, `bank_account_name`, `admin_whatsapp`.
- **faculties**: `id`, `name`, `description`, `is_active`.
- **programs**: `id`, `name`, `faculty_id`, `accreditation`, `quota`, `registration_fee`, `is_active`, `kode_prodi`, `registration_track` (reguler/non_reguler; `kode_prodi` unik komposit dengan track), `re_registration_minimum_payment`, `re_registration_fee`, `re_registration_fee_details`, `referral_reward_amount`, `re_registration_reward_amount`, `icon`, `slug`.
- **program_galleries**: galeri foto per prodi.
- **referrers**: `id`, `user_id`, `referral_code`, `status`, `bank_name`, `bank_account`, `bank_account_name`.
- **referral_clicks**: `id`, `referrer_id`, `ip_address`, `user_agent`.
- **registrations**: `id`, `registration_number`, `nim`, `letter_number`, `user_id`, `period_id`, `first_choice_program_id`, `second_choice_program_id`, `referrer_id`, `status` (enum panjang pendaftaran), `registration_type` (reguler/alumni), `admission_path`, `payment_proof`, `alumni_card_proof`, `re_registration_payment_proof`, `document_proof` (legacy, tidak dibaca kode), `internal_notes`, `deleted_at` (soft delete), (dan field data diri lengkap).
- **registration_documents**: `id`, `registration_id`, `document_type`, `file_path`, `status`, `review_note`, `reviewed_by`.
- **payment_logs**: `id`, `registration_id`, `acted_by`, `action`, `note`.
- **rewards**: `id`, `referrer_id`, `registration_id`, `reward_type` (`registration`/`re_registration`), `amount`, `status`, `approved_by`, `approved_at`, `disbursed_at`.
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
8. **Daftar Ulang:** Pendaftar mengunggah bukti bayar daftar ulang. Admin menyetujui (`confirmReRegistration`); NIM di-generate di tahap ini (`Registration::generateNim()`, format `YY` + `kode_prodi` + track + urut 3 digit; 2026 mulai dari 400), dan alur selesai dengan status `daftar_ulang_selesai`.
9. **Dokumen keluaran:** Nomor surat SKL (`generateLetterNumber()`) dibuat saat status diterima (override admin atau konfirmasi panitia). Pendaftar dapat mengunduh formulir PDF, SKL, dan e-KTM.

**Alur Afiliasi (Referrer):**
1. User (umumnya mahasiswa lama/pihak luar) mendaftar di `/afiliasi`.
2. Admin mengaktifkan status Referrer.
3. Referrer mendapat link unik (`/ref/{code}`). Setiap klik dicatat di `referral_clicks`.
4. Jika klik berujung pada Registrasi (session), `referrer_id` disimpan di tabel `registrations`.
5. Reward di tabel `rewards` dibuat langsung oleh `AdminRegistrationController`: tipe `registration` saat admin mengonfirmasi pembayaran (nominal `programs.referral_reward_amount`, default 50.000) dan tipe `re_registration` saat daftar ulang dikonfirmasi (`programs.re_registration_reward_amount`, dilewati bila 0 atau jalur alumni). Status awal `approved`. Pendaftar yang di-soft-delete menyesuaikan komisi otomatis.
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
13. **Test:** hanya test bawaan Breeze (`tests/Feature/Auth`, `ProfileTest`); belum ada test untuk alur PMB.
14. **Berkas Sensitif Privat:** bukti bayar, kartu alumni, bukti daftar ulang, dan dokumen pendaftaran disimpan di disk `local` (`storage/app/private`) lewat `App\Support\PrivateFile` dan hanya ditayangkan oleh `FileController` (rute `/berkas/*`, wajib login; pemilik, admin, operator; panitia hanya dokumen). Jangan memakai `Storage::url()` atau disk `public` untuk berkas ini. Berkas lama di disk `public` tetap terbaca (fallback) sampai dipindah dengan `php artisan files:privatize` (dukung `--dry-run`); jalankan sekali di server setelah deploy.
15. **Hak Akses Akun:** `/admin/panitia/*` hanya boleh menyentuh akun berperan `panitia` (selain itu 404); reset password akun `admin`/`operator` hanya oleh admin atau pemilik akun. Jangan memakai route model binding `User` tanpa cek role di area admin.
16. **Rahasia & Ekspor:** API key Resend disimpan terenkripsi (`enc:` + `Crypt`) lewat `Setting::setSecret()/getSecret()` dan tidak pernah dikirim ke browser (kosong = pertahankan, ada opsi hapus). Semua class export Excel memakai `SafeValueBinder` (teks berawalan `=` dan deretan digit >= 15 ditulis sebagai teks); CSV memakai `CsvSanitizer::row()`.
17. **Komisi & Afiliator:** pergantian afiliator pendaftar ditolak bila ada komisi berstatus `disbursed`; komisi `pending`/`approved` dipindah atau dibatalkan (`cancelled`, bukan dihapus); `total_conversions` tidak pernah di bawah 0. Catatan internal diedit di tempat dengan optimistic lock (`notes_checksum`) dan isi lama dicatat di `payment_logs` (`internal_note_edited`). Reset tes tulis hanya sebelum keputusan interview.
18. **Hapus Akun & Validasi:** akun dengan riwayat pendaftaran/aktivitas tidak bisa dihapus mandiri (pesan di bag `userDeletion`); penghapusan memakai query agar `Auth::logout()` tidak meng-INSERT ulang baris user. NIK wajib `digits:16`, `school_grade` wajib `numeric|between:0,100` di form publik, ubah data pendaftar, dan edit admin.
19. **Zona Waktu & Bahasa:** `app.timezone` = `Asia/Jakarta` (env `APP_TIMEZONE`) dan `app.locale` = `id` (sengaja bukan env; terjemahan di `lang/id`). Timestamp yang tersimpan sebelum perubahan ini berupa jam UTC dan tidak digeser otomatis. Penambahan berkas bahasa baru harus mengikuti `lang/id/validation.php` (termasuk `attributes`).
20. **Kuota & Konten:** `quota` prodi ditegakkan (`Program::isFull()`, `0` = tanpa batas; dihitung per periode, tidak termasuk `ditolak`/soft-delete) di form publik (langkah 2 dan submit dengan `lockForUpdate`) dan saat pendaftar pindah prodi. Slug prodi dibuat unik (`Program::uniqueSlug`) dan hanya berubah saat nama berubah. HTML deskripsi prodi disaring `App\Support\HtmlSanitizer` (HTMLPurifier) saat simpan dan render; jangan merender HTML mentah dari input admin.
21. **Pembatasan & Header:** `POST /register`, `/afiliasi`, `/forgot-password` memakai limiter `public-signup` (10/menit dan 60/jam per IP, satu bucket bersama; atur di `AppServiceProvider`), `/ref/{code}` 60/menit. `App\Http\Middleware\SecurityHeaders` (global) memasang `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` (kamera `self` untuk pemindai QR panitia) dan HSTS hanya di produksi lewat HTTPS; CSP belum dipasang. `/admin/komponen` hanya di lingkungan `local`; rute `/brand` dihapus. Set `SESSION_SECURE_COOKIE=true` di produksi.
22. **IP Klien di Balik Proxy:** `bootstrap/app.php` mengonfigurasi `trustProxies` (rentang Cloudflare + jaringan privat; override `TRUSTED_PROXIES` di `.env`). Tanpa itu semua pengunjung berbagi satu bucket pembatas laju (login, `public-signup`) dan `isSecure()` selalu false. Setelah deploy, verifikasi `request()->ip()` di server adalah IP pengunjung, bukan IP proxy hosting.
