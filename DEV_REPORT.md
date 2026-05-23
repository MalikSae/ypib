# DEV REPORT - PMB YPIB

Laporan investigasi kondisi codebase terkini untuk project Penerimaan Mahasiswa Baru (PMB) Yayasan Pendidikan Imam Bonjol (YPIB) Majalengka.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 1 — IDENTITAS PROJECT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. **Nama project dan deskripsi singkat:**
   - **Nama:** PMB YPIB (Laravel)
   - **Deskripsi:** Sistem Informasi Penerimaan Mahasiswa Baru terintegrasi dengan sistem afiliasi/referral untuk tracking pendaftaran.

2. **Framework & versi PHP:**
   - **PHP:** `^8.2`

3. **Versi Laravel:**
   - **Laravel:** `^12.0` (bersama dengan `laravel/breeze` `^2.4` dan `livewire/livewire` `^4.3`)

4. **Node/NPM:**
   - **Node:** `v22.22.0`
   - **NPM:** `10.9.4`

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 2 — STRUKTUR & ARSITEKTUR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

5. **Struktur Folder Utama:**
   - `app/Models/`: Menyimpan semua model Eloquent.
   - `app/Http/Controllers/`: Menyimpan logic controller, terbagi dalam `Admin`, `Auth`, dan root.
   - `app/Http/Middleware/`: Middleware khusus seperti `RoleMiddleware`.
   - `resources/views/`: Blade templates terorganisir di sub-folder.
   - `routes/`: Menyimpan `web.php`, `api.php`, `auth.php`.
   - `database/migrations/`: File skema tabel-tabel DB.
   - `database/seeders/`: Data awal (Dummy & Master data).

6. **Daftar Model & Relasi:**
   - **`User`**: `hasOne/hasMany` `referrer`, `registrations`
   - **`Faculty`**: `hasMany` `programs`
   - **`Program`**: `belongsTo` `faculty`, `hasMany` `galleries`
   - **`ProgramGallery`**: `belongsTo` `program`
   - **`PmbPeriod`**: `hasMany` `registrations`
   - **`Registration`**: `belongsTo` `user`, `referrer`, `period`, `firstChoiceProgram`, `secondChoiceProgram`; `hasMany` `paymentLogs`, `rewards`
   - **`PaymentLog`**: `belongsTo` `registration`, `actor`
   - **`Referrer`**: `belongsTo` `user`; `hasMany` `clicks`, `rewards`, `registrations`
   - **`ReferralClick`**: `belongsTo` `referrer`
   - **`Reward`**: `belongsTo` `referrer`, `registration`, `approvedBy`
   - **`Partner`**: Model mandiri (untuk logo partner)

7. **Daftar Controller:**
   - **Public / Mhs / Afiliasi**: `LandingController`, `ProfileController`, `ReferralController`, `ReferrerController`, `RegistrationController`
   - **Admin/Operator**: `DashboardController`, `FacultyController`, `PartnerController`, `PmbPeriodController`, `ProgramController`, `ReferrerController`, `RegistrationController`, `RewardController`

8. **Middleware yang digunakan:**
   - Bawaan Laravel: `auth`, `verified`
   - Custom: `role` (`App\Http\Middleware\RoleMiddleware`) untuk membedakan `admin`, `operator`.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 3 — DATABASE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

9. **Daftar Tabel & Kolom Utama:**
   - **`users`**: id, name, email, password, role, phone, is_referrer
   - **`faculties`**: id, name, slug, description
   - **`programs`**: id, faculty_id, name, slug, accreditation, quota, registration_fee, re_registration_fee, is_active, description
   - **`pmb_periods`**: id, name, year, open_date, close_date, fees, reward_amounts, bank_details, admin_whatsapp, is_active
   - **`referrers`**: id, user_id, referral_code, bank_details, is_active
   - **`referral_clicks`**: id, referrer_id, ip_address, user_agent, clicked_at
   - **`registrations`**: id, user_id, period_id, referrer_id, status (enum: unpaid, review, paid, dll), proofs, notes
   - **`payment_logs`**: id, registration_id, amount, payment_type, status
   - **`rewards`**: id, referrer_id, registration_id, amount, reward_type, status, approved_by
   - **`program_galleries`**: id, program_id, image_path
   - **`partners`**: id, name, logo_path, url, is_active

10. **Daftar Seeder:**
    - `DatabaseSeeder`: Membuat user admin (`admin@ypib.ac.id`), operator, fakultas (Ilmu Kesehatan, Farmasi, dll), daftar prodi beserta biayanya, dan periode PMB aktif.

11. **Foreign Key/Relasi Utama:**
    - Semua tabel transaksional (`registrations`, `rewards`, `payment_logs`, `referrers`) dihubungkan melalui Foreign Key (cascade/restrict) ke entitas utama (`users`, `pmb_periods`, `programs`).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 4 — FITUR & ROUTES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

12. **Daftar Route (Grouped):**
    - **Public**: `/` (Landing), `/prodi/{slug}`, `/ref/{code}` (Tracking afiliasi)
    - **Auth**: `/login`, `/register`, `/profile`
    - **Mahasiswa**: `/daftar`, `/pendaftaran`, `/pendaftaran/status`, upload bukti bayar/daftar ulang.
    - **Referrer**: `/afiliasi`, register, aktifkan, dashboard, update bank.
    - **Admin (`/admin/*`)**: 
      - `/dashboard`, `/pengaturan` (PMB Period)
      - CRUD Master: `/faculties`, `/programs`, `/partners`
      - Transaksi: `/pendaftar` (konfirmasi bayar, daftar ulang, ubah status)
      - Afiliasi: `/afiliasi` (toggle status)
      - Reward: `/reward` (approve, disburse, mass-disburse, export)

13. **Fitur yang SELESAI:**
    - Landing page dengan dinamis prodi dan pendaftaran
    - Sistem Afiliasi lengkap (Klik, Registrasi dari link, generate Reward, Disburse)
    - Dashboard Mahasiswa (Upload bukti bayar, daftar ulang)
    - Panel Admin komprehensif (Pengaturan PMB, Program, Pendaftar, Reward)
    - Integrasi Partner/Sponsor logo

14. **Fitur BELUM SELESAI / Placeholder:**
    - Sebagian besar logic inti sudah ada. Fitur Notifikasi Realtime / Email murni masih log-based (`MAIL_MAILER=log`), belum terintegrasi ke SMTP/Layanan Email sungguhan untuk tahap produksi.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 5 — VIEWS & UI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

15. **Daftar View (Folder):**
    - `admin/`: `dashboard`, `programs/*`, `faculties/*`, `registrations/*`, `rewards/*`, `partners/*`
    - `landing/`: `prodi`
    - `referrer/`: `dashboard`, `index`
    - `registration/`: `form`, `status`, `index`
    - Root: `welcome.blade.php`, `dashboard.blade.php`

16. **Layout yang digunakan:**
    - Terdapat dalam folder `layouts/` (Blade template inheritance, seperti `app.blade.php`, `guest.blade.php`).

17. **Frontend Stack:**
    - CSS: **Tailwind CSS ^3.4** (via Vite)
    - JS/Reactivity: **Alpine.js** dan **Livewire ^4.3**
    - Forms/Typography: Plugin Tailwind (Forms & Typography)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 6 — DEPENDENCIES & KONFIGURASI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

18. **Package PHP Utama:**
    - `laravel/framework` (^12.0)
    - `laravel/breeze` (^2.4)
    - `livewire/livewire` (^4.3)

19. **Kondisi `.env`:**
    - `APP_KEY`: **Configured**
    - `DB_*`: **Configured** (MySQL `ypib` lokal)
    - `MAIL_*`: **Configured** (namun masih `log` driver)
    - `AWS_*`: Not set (belum pakai S3)

20. **Storage link:**
    - **Sudah ada:** `public/storage` terpaut (symlink) ke folder `storage/app/public`.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 7 — CATATAN & REKOMENDASI
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

21. **Hal yang perlu diperhatikan:**
    - Terdapat percampuran antara Livewire dan Alpine.js dalam codebase ini, perhatikan alur state management saat modifikasi form/UI.
    - Status enum pada tabel `registrations` sangat sentral dalam mengelola alur pendaftar. Pengubahan status harus ekstra hati-hati karena berdampak pada `Reward` dan `PaymentLog`.

22. **Potensi Bug/Inkonsistensi:**
    - Pengiriman Email/Notifikasi masih tersimpan di log, harus dikonfigurasi ke SMTP di server Production, jika tidak peserta tidak akan mendapatkan info.
    - Pada `run_command` saat setup awal Node berjalan sangat panjang (`npm run dev` running for >2 hours). Perlu diwaspadai jika terjadi memory leak pada environment lokal.

23. **Estimasi % Progress Keseluruhan:**
    - **~85%**. Keseluruhan alur utama (CRUD Master, Pendaftaran, Pembayaran, Sistem Afiliasi, Reward, Dashboard) sudah siap. Tinggal penyempurnaan deployment, pengecekan bugs edge-cases, dan implementasi email gateway.
