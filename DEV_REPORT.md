# Project Condition Report - PMB YPIB

Laporan investigasi codebase ini dibuat secara otomatis berdasarkan pemindaian pada file konfigurasi, struktur direktori, skema database (migration), route, dan controller.

## 1. Framework & Versi
- **Framework:** Laravel Framework `^12.0`
- **PHP Version:** `^8.2` (dari `composer.json`)
- **Database Connection:** MySQL (berdasarkan file `.env` aktual: `DB_CONNECTION=mysql`, `DB_DATABASE=ypib`)

## 2. Struktur Folder Utama
Berikut adalah struktur folder root utama (2 level):
- `app/`
  - `Exports/`, `Http/`, `Livewire/`, `Models/`, `Providers/`, `View/`
- `bootstrap/`
- `config/`
- `database/`
  - `factories/`, `migrations/`, `seeders/`
- `public/`
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
- **programs**: `id`, `name`, `faculty_id`, `accreditation`, `quota`, `registration_fee`, `is_active`, `kode_prodi`, `registration_track`, `re_registration_minimum_payment`, `re_registration_fee`, `icon`, `slug`.
- **referrers**: `id`, `user_id`, `referral_code`, `status`, `bank_name`, `bank_account`, `bank_account_name`.
- **referral_clicks**: `id`, `referrer_id`, `ip_address`, `user_agent`.
- **registrations**: `id`, `registration_number`, `nim`, `letter_number`, `user_id`, `period_id`, `first_choice_program_id`, `second_choice_program_id`, `referrer_id`, `status` (enum panjang pendaftaran), `registration_type` (umum/alumni), `payment_proof`, `re_registration_payment_proof`, (dan field data diri lengkap).
- **registration_documents**: `id`, `registration_id`, `document_type`, `file_path`, `status`, `review_note`, `reviewed_by`.
- **payment_logs**: `id`, `registration_id`, `acted_by`, `action`, `note`.
- **rewards**: `id`, `referrer_id`, `registration_id`, `amount`, `status`, `disbursed_at`, `approved_at`.
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
  - Tampilan beranda, detail prodi (`/prodi/{slug}`).
  - Referral tracking (`/ref/{code}`).
- **Pendaftaran (Mahasiswa):**
  - Pembuatan pendaftaran (memilih prodi).
  - Status pendaftaran dan pengunggahan bukti bayar / kartu alumni / daftar ulang.
  - Pengisian detail data diri, sekolah, ortu.
  - Upload dokumen berkas-berkas pendaftaran.
  - Download formulir pendaftaran, SKL, dan e-KTM.
  - Pelaksanaan tes tulis online secara langsung (CBT sederhana).
  - Halaman informasi jadwal/hasil interview.
- **Admin & Operator:**
  - Pengaturan periode PMB dan email.
  - Master data CRUD (Fakultas, Prodi, Mitra, Fasilitas, Bank Soal, Panitia).
  - Manajemen Pendaftar (Approval, update data, export, restore, review dokumen per berkas, upload bukti manual, reset tes tulis).
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
1. **Pendaftaran:** Pendaftar mengisi formulir awal dari landing page dan masuk ke sistem dengan status awal (biasanya `menunggu_pembayaran` atau `draft`).
2. **Pembayaran:** Pendaftar mengunggah bukti bayar pendaftaran. Status berubah menjadi `menunggu_konfirmasi`.
3. **Konfirmasi Admin:** Admin memvalidasi pembayaran. Jika sah, status menjadi `terdaftar`.
4. **Lengkapi Form & Upload Dokumen:** Mahasiswa harus melengkapi form detail (jika belum lengkap). Setelah lengkap, mereka mengunggah 6 tipe dokumen pendaftaran (beberapa opsional). Dokumen berstatus `menunggu_review`. Admin mereview *per dokumen* (terima/tolak revisi).
5. **Tes Tulis:** Setelah dokumen lengkap & disetujui otomatis lanjut ke status `menunggu_tes_tulis`. Peserta menjawab bank soal di sistem, disubmit, dihitung skor otomatis (`sangat_baik` atau `baik`).
6. **Interview:** Setelah tes tulis, peserta diarahkan ke status `menunggu_interview`.
7. **Persetujuan Akhir (Panitia):** Role Panitia menyeken QR pendaftar dan menandai hasil kelulusan. Status berubah ke `menunggu_konfirmasi_daftar_ulang`.
8. **Daftar Ulang:** Pendaftar mengunggah bukti bayar daftar ulang. Admin menyetujui, dan alur pendaftaran selesai dengan status `daftar_ulang_selesai`.

**Alur Afiliasi (Referrer):**
1. User (umumnya mahasiswa lama/pihak luar) mendaftar di `/afiliasi`.
2. Admin mengaktifkan status Referrer.
3. Referrer mendapat link unik (`/ref/{code}`). Setiap klik dicatat di `referral_clicks`.
4. Jika klik berujung pada Registrasi (session), `referrer_id` disimpan di tabel `registrations`.
5. Jika pendaftar mencapai status tertentu (sudah lulus daftar ulang), sistem (via command/logic tertentu) men-generate reward di tabel `rewards`.
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
- Konfigurasi Email (`MAIL_*`) yang perlu disesuaikan server.
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
