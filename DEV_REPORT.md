# Laporan Kondisi Project YPIB

Laporan ini murni investigasi tanpa melakukan perubahan file apa pun pada project (kecuali pembuatan file laporan ini dan skrip pembantu sementara).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 1 — INFO DASAR
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. Framework & Versi**
- **Laravel**: 12.59.0
- **PHP**: 8.3.30
- **Livewire**: ^4.3
- **Alpine.js**: ^3.4.2
- **Tailwind CSS**: ^3.4.19
- **Vite**: ^6.0.11

**2. Isi composer.json (Package Utama)**
```json
"require": {
    "php": "^8.2",
    "intervention/image": "^4.1",
    "laravel/breeze": "^2.4",
    "laravel/framework": "^12.0",
    "laravel/tinker": "^2.10.1",
    "livewire/livewire": "^4.3",
    "symfony/resend-mailer": "^7.4"
}
```

**3. Isi package.json (Dependency Frontend)**
```json
"devDependencies": {
    "@tailwindcss/forms": "^0.5.11",
    "@tailwindcss/typography": "^0.5.19",
    "@tailwindcss/vite": "^4.0.0",
    "alpinejs": "^3.4.2",
    "autoprefixer": "^10.4.2",
    "axios": "^1.7.4",
    "concurrently": "^9.0.1",
    "laravel-vite-plugin": "^1.2.0",
    "postcss": "^8.4.31",
    "tailwindcss": "^3.4.19",
    "vite": "^6.0.11"
}
```

**4. Struktur Folder Utama (2-3 Level)**
- `app/`
  - `Http/`
    - `Controllers/` (Admin/, Auth/, dll)
  - `Models/`
  - `Providers/`
- `resources/views/`
  - `admin/`
  - `components/`
  - `landing/`
  - `layouts/`
  - `livewire/`
  - `profile/`
  - `referrer/`
  - `registration/`
- `routes/`
  - `auth.php`
  - `console.php`
  - `web.php`
- `database/`
  - `factories/`
  - `migrations/`
  - `seeders/`
  - `database.sqlite`

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 2 — STATUS GIT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- **git status**: `On branch master. Your branch is up to date. nothing to commit, working tree clean.`
- **git diff --stat**: Kosong (Tidak ada perubahan yang belum di-commit).

**File Modified / Untracked:**
- **TIDAK ADA** file yang modified maupun untracked. Working tree dalam kondisi bersih.
- **config/filesystems.php**: Telah di-commit di masa lalu. Isinya saat ini untuk disk `public` sudah dikonfigurasi dengan benar menggunakan `public_path('storage')`:
  ```php
  'public' => [
      'driver' => 'local',
      'root' => public_path('storage'),
      'url' => env('APP_URL').'/storage',
      'visibility' => 'public',
      'throw' => false,
      'report' => false,
  ],
  ```

**git log --oneline -15 (Sebagian besar commit terakhir):**
- `f619eb4` feat: add admin dashboard, referrer management, and registration listing views with status metrics
- `1a8789e` feat: implement registration system with admin settings management and mail configuration
- `c90e78f` feat: create ReferrerController and affiliate program landing page view
- `e9afabb` feat: implement admin registration management system with database schema updates and controller logic
- `0cfe2ea` feat: add landing page preview layout and styling assets
- `61135e9` feat: add program study detail page with fee structure and gallery display
- `93a4eb7` feat: create landing page preview and associated build assets
- `2df8a9c` feat: install intervention/image and implement landing page views and admin controllers
- `8349b92` feat: create administrative program management form and landing page views
- `d45f506` feat: initialize filesystems configuration with local, public, and s3 disk support
- `66db4ea` feat: implement facility, program, and referrer management modules
- `4916fa3` feat: scaffold complete admin panel and landing page infrastructure
- `c864399` feat: initialize blade layout templates
- `f03b402` feat: implement admin dashboard with partner management
- `5dff2be` chore: build assets and update manifest file

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 3 — DATABASE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. Tabel PMB & Affiliate Utama**
- **pmb_periods**: id, name, year, open_date, close_date, university_bank_name, dll.
- **registrations**: id, period_id, user_id, referrer_id, registration_number, admission_path, first_choice_program_id, status, payment_proof, dll.
- **payment_logs**: id, registration_id, action, note.

- **referrers**: id, user_id, code, status, bank_name, bank_account_number, bank_account_name.
- **referral_clicks**: id, referrer_id, ip_address, converted, converted_at.
- **rewards**: id, referrer_id, registration_id, amount, status, notes.

- **users**: id, name, email, role, is_referrer, referrer_id, phone.
- **facilities**: id, name, image_path, icon, description, is_active, order.
- **partners**: id, name, logo_path, url, is_active.
- **programs**: id, name, faculty_id, registration_fee, referral_reward_amount, re_registration_reward_amount, dll.

**2. Record Count:**
- `facilities`: **16**
- `partners`: **6**
*(Seeder telah berjalan dengan sukses)*

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 4 — ROUTES & FITUR YANG SUDAH ADA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. Landing Page:**
- `GET /` -> `LandingController@preview`
- `GET /brand` -> (Closure return view 'brand')
- `GET /prodi/{slug}` -> `LandingController@prodi`

**2. PMB (Pendaftaran):**
- `GET /daftar` -> `RegistrationController@create`
- `GET /pendaftaran` -> `RegistrationController@index`
- `GET /pendaftaran/status` -> `RegistrationController@status`
- `POST /pendaftaran/upload-bukti` -> `RegistrationController@uploadProof`
- `POST /pendaftaran/upload-berkas` -> `RegistrationController@uploadDocument`
- `POST /pendaftaran/upload-daftar-ulang-bukti` -> `RegistrationController@uploadReRegistrationProof`

**3. Admin / CRUD:**
- `GET admin/dashboard` -> `Admin\DashboardController@index`
- `GET admin/pengaturan` -> `Admin\PmbPeriodController@index`
- Resource routes: `admin/faculties`, `admin/programs`, `admin/partners`, `admin/facilities`.
- Manajemen Pendaftar: `admin/pendaftar`, `admin/pendaftar/{id}`, `admin/pendaftar/{id}/konfirmasi-bayar`, dll (Controller: `Admin\RegistrationController`).

**4. Sistem Referral / Affiliate:**
- Tracking: `GET /ref/{code}` -> `ReferralController@track`
- Referrer Dashboard: `GET /afiliasi/dashboard` -> `ReferrerController@dashboard`
- Landing Afiliasi: `GET /afiliasi` -> `ReferrerController@index`
- Admin Manajemen Afiliasi: `admin/afiliasi` & `admin/reward` (Termasuk fitur export & mass disburse ditangani oleh `Admin\RewardController`).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 5 — LANDING PAGE STATUS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. Route /landing-preview:**
Route `/landing-preview` **TIDAK ADA**. Berdasarkan kode saat ini, landing page preview diakses langsung melalui route `/` (`LandingController@preview`).

**2. File View yang Terlibat:**
- `resources/views/layouts/landing.blade.php`
- `resources/views/landing/preview.blade.php`
- `resources/views/landing/prodi.blade.php`

**3. Komparasi Section di `preview.blade.php`:**
**SUDAH ADA:**
- Section 1: Hero
- Section 3: Program Studi (Pilih Program Studimu)
- Section 4: Cara Mendaftar
- Section 5: Jalur Penerimaan
- Section 6: Mitra Kerja Sama (Dipercaya Institusi Terkemuka)
- Section 7: Gallery Image (Momen & Aktivitas)
- Section 8: Final CTA

**GAP (YANG BELUM ADA):**
- **Section 2 (Trust Builder / Statistik)**: Section ini belum ada / terlewat di file `preview.blade.php`. Setelah Section 1 (Hero) langsung lompat ke Section 3 (Program Studi).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 6 — KONFIGURASI ENVIRONMENT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. Status .env:**
- `APP_ENV`: `local`
- `APP_URL`: `http://ypib.test`
- `DB_DATABASE`: `ypib`
*(Value penting telah terisi, file ada dan tersambung).*

**2. Status config/filesystems.php:**
Sudah dikonfigurasi dengan benar sesuai instruksi "Hostinger fix". Disk `public` mengarah ke `public_path('storage')`.

**3. Identifikasi Environment:**
Ini adalah environment **development lokal (Laragon)**. Dilihat dari `APP_ENV=local` dan `APP_URL=http://ypib.test`. Belum disetting untuk environment `dev.univypib.ac.id` maupun production `daftar.univypib.ac.id`.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
## BAGIAN 7 — CATATAN UNTUK PENGEMBANGAN LANJUTAN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. **Potensi Konflik:**
   - Karena landing page saat ini di set pada route root `/` (bukan `/landing-preview`), pastikan pengembangan fitur selanjutnya tidak menimpa root path ini.
   - Database connection di env adalah `mysql` (`DB_DATABASE=ypib`), namun ada file `database.sqlite` di folder database. Hal ini tidak menjadi masalah karena database yang berjalan adalah MySQL, namun pastikan tidak tertukar di server production.
2. **Status File / Folder:**
   - Git Tree is Clean. Semua file dan direktori aman untuk dikerjakan dan tidak ada perubahan menggantung (*uncommitted changes*).
3. **Ketidaksesuaian Terdeteksi:**
   - Ada "Gap" desain di Landing Page (`preview.blade.php`) di mana "Section 2" (Trust Builder / Statistik) dilewati begitu saja.
   - Menambahkan sebuah script `get_schema.php` untuk melihat struktur database. File ini bebas Anda hapus karena hanya digunakan sementara untuk generate laporan ini.
