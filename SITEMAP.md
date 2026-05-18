# SITEMAP — YPIB PMB (Penerimaan Mahasiswa Baru)

> Dibuat otomatis berdasarkan investigasi: `routes/web.php`, `routes/auth.php`,
> seluruh controller di `app/Http/Controllers/`, Livewire component di `app/Livewire/`,
> dan semua view di `resources/views/`.
>
> **Tanggal Generate:** 2026-05-18

---

## Ringkasan Sistem

| Kelompok       | Jumlah Halaman |
|----------------|---------------|
| Public         | 6             |
| Auth (Breeze)  | 7             |
| Mahasiswa      | 3             |
| Referrer       | 2             |
| Admin/Operator | 7             |
| **Total**      | **25**        |

---

## 1. PUBLIC (Tanpa Login)

---

### Landing Page

- **URL**        : `/`
- **Method**     : GET
- **Auth**       : Public
- **View**       : `resources/views/landing/index.blade.php`
- **Controller** : `App\Http\Controllers\LandingController@index`
- **Deskripsi**  : Halaman utama website YPIB. Menampilkan daftar program studi yang aktif, informasi periode PMB yang sedang berjalan, serta CTA untuk mendaftar. Data: `$programs` (Program aktif), `$period` (PmbPeriod aktif).

---

### Detail Program Studi

- **URL**        : `/prodi/{slug}`
- **Method**     : GET
- **Auth**       : Public
- **View**       : `resources/views/landing/prodi.blade.php`
- **Controller** : `App\Http\Controllers\LandingController@prodi`
- **Deskripsi**  : Halaman detail program studi berdasarkan slug. Menampilkan informasi lengkap satu program studi dan periode PMB aktif. Mengembalikan 404 jika prodi tidak ditemukan atau tidak aktif.

---

### Formulir Pendaftaran (Publik + Livewire)

- **URL**        : `/daftar`
- **Method**     : GET
- **Auth**       : Public (guest direkomendasikan, namun login juga diperbolehkan; jika login & sudah punya registrasi → redirect ke `/pendaftaran/status`)
- **View**       : `resources/views/registration/create.blade.php` + `resources/views/livewire/registration-form.blade.php`
- **Controller** : `App\Http\Controllers\RegistrationController@create`
- **Livewire**   : `App\Livewire\RegistrationForm`
- **Deskripsi**  : Formulir pendaftaran PMB multi-step (4 langkah): **Step 1** — Data Diri (nama, NIK, tempat/tgl lahir, gender, alamat, HP), **Step 2** — Pilih Program Studi (pilihan 1 & 2), **Step 3** — Asal Sekolah (nama sekolah, tahun lulus, kelas), **Step 4** — Konfirmasi & Submit. Mendukung tracking referral via cookie `ref`. Redirect ke status setelah submit berhasil. Jika tidak ada periode aktif, redirect ke landing dengan pesan error.

---

### Referral Tracking

- **URL**        : `/ref/{code}`
- **Method**     : GET
- **Auth**       : Public
- **View**       : *(tidak ada, langsung redirect)*
- **Controller** : `App\Http\Controllers\ReferralController@track`
- **Deskripsi**  : Endpoint tracking link referral. Mencatat klik (`ReferralClick`), increment `total_clicks` pada referrer, set cookie `ref` berlaku 7 hari, lalu redirect ke `/daftar`. Jika kode tidak valid atau referrer tidak aktif, langsung redirect ke `/daftar` tanpa cookie.

---

### Login

- **URL**        : `/login`
- **Method**     : GET / POST
- **Auth**       : Guest only
- **View**       : `resources/views/auth/login.blade.php`
- **Controller** : `App\Http\Controllers\Auth\AuthenticatedSessionController@create` / `@store`
- **Deskripsi**  : Halaman login dengan email dan password. Setelah berhasil login, sistem akan redirect berdasarkan role: admin/operator → `/admin/dashboard`, is_referrer → `/referrer/dashboard`, mahasiswa → `/pendaftaran/status`.

---

### Register Akun

- **URL**        : `/register`
- **Method**     : GET / POST
- **Auth**       : Guest only
- **View**       : `resources/views/auth/register.blade.php`
- **Controller** : `App\Http\Controllers\Auth\RegisteredUserController@create` / `@store`
- **Deskripsi**  : Halaman registrasi akun baru (nama, email, password). Setelah register, user diarahkan ke dashboard sesuai role (default sebagai mahasiswa).

---

### Lupa Password

- **URL**        : `/forgot-password`
- **Method**     : GET / POST
- **Auth**       : Guest only
- **View**       : `resources/views/auth/forgot-password.blade.php`
- **Controller** : `App\Http\Controllers\Auth\PasswordResetLinkController@create` / `@store`
- **Deskripsi**  : Halaman permintaan reset password via email. Mengirimkan link reset ke email yang terdaftar.

---

### Reset Password

- **URL**        : `/reset-password/{token}`
- **Method**     : GET / POST
- **Auth**       : Guest only
- **View**       : `resources/views/auth/reset-password.blade.php`
- **Controller** : `App\Http\Controllers\Auth\NewPasswordController@create` / `@store`
- **Deskripsi**  : Halaman pengisian password baru menggunakan token dari email reset. Token divalidasi sebelum password diubah.

---

## 2. MAHASISWA (Login Required)

---

### Dashboard Redirect

- **URL**        : `/dashboard`
- **Method**     : GET
- **Auth**       : Auth + Verified
- **View**       : *(tidak ada, langsung redirect)*
- **Controller** : *(closure di `web.php`)*
- **Deskripsi**  : Halaman redirect cerdas berdasarkan role user. Admin/Operator → `/admin/dashboard`, is_referrer = true → `/referrer/dashboard`, mahasiswa biasa → `/pendaftaran/status`. Route ini dipertahankan untuk kompatibilitas dengan Laravel Breeze.

---

### Status Pendaftaran (Index redirect)

- **URL**        : `/pendaftaran`
- **Method**     : GET
- **Auth**       : Auth
- **View**       : *(tidak ada, redirect ke `/pendaftaran/status`)*
- **Controller** : `App\Http\Controllers\RegistrationController@index`
- **Deskripsi**  : Shortcut route yang langsung meredirect ke halaman `/pendaftaran/status`.

---

### Status Pendaftaran

- **URL**        : `/pendaftaran/status`
- **Method**     : GET
- **Auth**       : Auth
- **View**       : `resources/views/registration/status.blade.php`
- **Controller** : `App\Http\Controllers\RegistrationController@status`
- **Deskripsi**  : Halaman utama dashboard mahasiswa. Menampilkan status pendaftaran terkini (menunggu_pembayaran, menunggu_konfirmasi, terdaftar, diterima, ditolak, perlu_revisi). Menampilkan nomor pendaftaran, program studi pilihan, dan histori status. Dilengkapi form upload bukti pembayaran jika status masih `menunggu_pembayaran`.

---

### Upload Bukti Pembayaran (Mahasiswa)

- **URL**        : `/pendaftaran/upload-bukti`
- **Method**     : POST
- **Auth**       : Auth
- **View**       : *(redirect ke `/pendaftaran/status`)*
- **Controller** : `App\Http\Controllers\RegistrationController@uploadProof`
- **Deskripsi**  : Endpoint upload bukti transfer oleh mahasiswa. Validasi: file JPG/PNG/PDF maks 2MB. File disimpan di `storage/app/public/bukti-bayar/`. Setelah upload, status otomatis berubah menjadi `menunggu_konfirmasi`.

---

### Edit Profil

- **URL**        : `/profile`
- **Method**     : GET / PATCH / DELETE
- **Auth**       : Auth
- **View**       : `resources/views/profile/edit.blade.php`
- **Controller** : `App\Http\Controllers\ProfileController@edit` / `@update` / `@destroy`
- **Deskripsi**  : Halaman edit profil user (nama, email). PATCH untuk update data. DELETE untuk menghapus akun (membutuhkan konfirmasi password). Jika email diubah, `email_verified_at` direset.

---

### Verifikasi Email

- **URL**        : `/verify-email`
- **Method**     : GET
- **Auth**       : Auth
- **View**       : `resources/views/auth/verify-email.blade.php`
- **Controller** : `App\Http\Controllers\Auth\EmailVerificationPromptController`
- **Deskripsi**  : Halaman notifikasi untuk user yang belum memverifikasi email. Tombol kirim ulang email verifikasi tersedia dengan throttle 6 kali per menit.

---

### Konfirmasi Password

- **URL**        : `/confirm-password`
- **Method**     : GET / POST
- **Auth**       : Auth
- **View**       : `resources/views/auth/confirm-password.blade.php`
- **Controller** : `App\Http\Controllers\Auth\ConfirmablePasswordController@show` / `@store`
- **Deskripsi**  : Halaman re-konfirmasi password sebelum melakukan aksi sensitif (misal: hapus akun).

---

### Ganti Password

- **URL**        : `/password`
- **Method**     : PUT
- **Auth**       : Auth
- **View**       : *(bagian dari profile/edit)*
- **Controller** : `App\Http\Controllers\Auth\PasswordController@update`
- **Deskripsi**  : Endpoint ganti password dari halaman edit profil. Bagian dari form profil.

---

### Logout

- **URL**        : `/logout`
- **Method**     : POST
- **Auth**       : Auth
- **View**       : *(redirect ke `/`)*
- **Controller** : `App\Http\Controllers\Auth\AuthenticatedSessionController@destroy`
- **Deskripsi**  : Endpoint logout. Menghapus sesi dan redirect ke halaman utama.

---

## 3. REFERRER (is_referrer = true, Login Required)

---

### Daftar Jadi Referrer

- **URL**        : `/referrer/daftar`
- **Method**     : GET / POST
- **Auth**       : Auth (semua role bisa mendaftar)
- **View**       : `resources/views/referrer/create.blade.php`
- **Controller** : `App\Http\Controllers\ReferrerController@create` / `@store`
- **Deskripsi**  : Halaman pendaftaran sebagai referrer. GET menampilkan form pendaftaran (jika sudah punya referrer record, redirect ke dashboard). POST memproses pendaftaran: generate kode unik `REF-XXXXXX`, buat record di tabel `referrers`, set `users.is_referrer = true`, lalu redirect ke dashboard referrer.

---

### Dashboard Referrer

- **URL**        : `/referrer/dashboard`
- **Method**     : GET
- **Auth**       : Auth + is_referrer = true
- **View**       : `resources/views/referrer/dashboard.blade.php`
- **Controller** : `App\Http\Controllers\ReferrerController@dashboard`
- **Deskripsi**  : Dashboard utama referrer. Menampilkan: kode referral unik, statistik (total klik, total konversi, total reward diterima, reward pending), tabel daftar pendaftar yang masuk melalui link referral, dan status reward masing-masing. Jika user belum jadi referrer, redirect ke `/referrer/daftar`.

---

## 4. ADMIN & OPERATOR (role: admin / operator, Login Required)

Semua route admin menggunakan prefix `/admin` dengan middleware `auth` + `role:admin,operator`.

---

### Dashboard Admin

- **URL**        : `/admin/dashboard`
- **Method**     : GET
- **Auth**       : Admin / Operator
- **View**       : `resources/views/admin/dashboard.blade.php`
- **Controller** : `App\Http\Controllers\Admin\DashboardController@index`
- **Deskripsi**  : Halaman dashboard utama admin/operator. Menampilkan statistik ringkasan: total pendaftar, pending pembayaran (`payment_pending`), pending verifikasi dokumen (`payment_confirmed` + `document_pending`), diterima (`accepted`). Tabel 5 pendaftaran terbaru dengan nama user dan prodi pertama.

---

### Daftar Semua Pendaftar

- **URL**        : `/admin/pendaftar`
- **Method**     : GET
- **Auth**       : Admin / Operator
- **View**       : `resources/views/admin/registrations/index.blade.php`
- **Controller** : `App\Http\Controllers\Admin\RegistrationController@index`
- **Deskripsi**  : Halaman daftar semua pendaftar dengan fitur: pencarian (nomor pendaftaran / nama lengkap), filter status, filter program studi, pagination 20 per halaman. Menampilkan status badge, nama referrer jika ada.

---

### Detail Pendaftar

- **URL**        : `/admin/pendaftar/{id}`
- **Method**     : GET
- **Auth**       : Admin / Operator
- **View**       : `resources/views/admin/registrations/show.blade.php`
- **Controller** : `App\Http\Controllers\Admin\RegistrationController@show`
- **Deskripsi**  : Halaman detail pendaftar. Menampilkan semua data diri, pilihan prodi, informasi referrer, bukti pembayaran, histori audit log (`PaymentLog`), dan informasi reward referral. Tombol aksi: konfirmasi bayar, upload bukti (admin), ubah status, tambah catatan internal.

---

### Konfirmasi Pembayaran

- **URL**        : `/admin/pendaftar/{id}/konfirmasi-bayar`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke detail pendaftar)*
- **Controller** : `App\Http\Controllers\Admin\RegistrationController@confirmPayment`
- **Deskripsi**  : Aksi konfirmasi pembayaran. Generate nomor pendaftaran unik format `PMB-{TAHUN}-{4DIGIT}` jika belum ada. Ubah status menjadi `terdaftar`. Catat audit log di `PaymentLog`. Jika pendaftar dari referral, increment `total_conversions` referrer, tandai klik sebagai `converted`, dan buat record `Reward` dengan amount dari `period.referral_reward_amount`.

---

### Upload Bukti Bayar (Admin)

- **URL**        : `/admin/pendaftar/{id}/upload-bukti`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke detail pendaftar)*
- **Controller** : `App\Http\Controllers\Admin\RegistrationController@uploadBukti`
- **Deskripsi**  : Admin dapat mengupload bukti bayar atas nama pendaftar. Validasi: JPG/PNG/PDF maks 2MB. Catat audit log. Jika status masih `menunggu_pembayaran`, otomatis naik ke `menunggu_konfirmasi`.

---

### Update Status Pendaftaran

- **URL**        : `/admin/pendaftar/{id}/status`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke detail pendaftar)*
- **Controller** : `App\Http\Controllers\Admin\RegistrationController@updateStatus`
- **Deskripsi**  : Mengubah status pendaftaran ke: `diterima`, `ditolak`, atau `perlu_revisi`. Hanya bisa dilakukan jika status saat ini adalah `terdaftar`. Perubahan dicatat di `PaymentLog`.

---

### Tambah Catatan Internal

- **URL**        : `/admin/pendaftar/{id}/catatan`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke detail pendaftar)*
- **Controller** : `App\Http\Controllers\Admin\RegistrationController@addNote`
- **Deskripsi**  : Admin/Operator menambahkan catatan internal pada data pendaftar (field `internal_notes`). Maksimal 1000 karakter.

---

### Manajemen Referrer

- **URL**        : `/admin/referrer`
- **Method**     : GET
- **Auth**       : Admin / Operator
- **View**       : `resources/views/admin/referrers/index.blade.php`
- **Controller** : `App\Http\Controllers\Admin\ReferrerController@index`
- **Deskripsi**  : Daftar semua referrer yang terdaftar. Pagination 20 per halaman. Menampilkan nama user, kode referral, total klik, total konversi, total reward, dan status (active/inactive).

---

### Toggle Status Referrer

- **URL**        : `/admin/referrer/{id}/toggle`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke daftar referrer)*
- **Controller** : `App\Http\Controllers\Admin\ReferrerController@toggle`
- **Deskripsi**  : Mengaktifkan atau menonaktifkan referrer. Toggle: `active` ↔ `inactive`.

---

### Manajemen Reward Referral

- **URL**        : `/admin/reward`
- **Method**     : GET
- **Auth**       : Admin / Operator
- **View**       : `resources/views/admin/rewards/index.blade.php`
- **Controller** : `App\Http\Controllers\Admin\RewardController@index`
- **Deskripsi**  : Daftar semua reward referral dengan filter status (pending/approved/disbursed). Pagination 20 per halaman. Menampilkan nama referrer, nama pendaftar, nominal reward, status, dan tombol aksi approve/disburse.

---

### Setujui Reward

- **URL**        : `/admin/reward/{id}/approve`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke daftar reward)*
- **Controller** : `App\Http\Controllers\Admin\RewardController@approve`
- **Deskripsi**  : Menyetujui reward referral yang berstatus `pending`. Mengisi field `approved_by` (id admin) dan `approved_at` (timestamp). Status berubah menjadi `approved`.

---

### Cairkan Reward

- **URL**        : `/admin/reward/{id}/disburse`
- **Method**     : POST
- **Auth**       : Admin / Operator
- **View**       : *(redirect ke daftar reward)*
- **Controller** : `App\Http\Controllers\Admin\RewardController@disburse`
- **Deskripsi**  : Menandai reward yang telah disetujui (`approved`) sebagai sudah dicairkan. Status berubah menjadi `disbursed`.

---

## Alur Status Pendaftaran

```
menunggu_pembayaran
        │
        ▼ (mahasiswa/admin upload bukti)
menunggu_konfirmasi
        │
        ▼ (admin konfirmasi bayar → generate nomor PMB)
   terdaftar
        │
   ┌────┴─────────┐
   ▼              ▼           ▼
diterima       ditolak    perlu_revisi
```

---

## Alur Reward Referral

```
(Admin konfirmasi bayar pendaftar dari referral)
        │
        ▼
  Reward dibuat (status: pending)
        │
        ▼ (Admin approve)
  status: approved
        │
        ▼ (Admin disburse)
  status: disbursed
```

---

## Komponen Livewire

| Komponen                        | File Class                            | File View                                        | Dipakai di Route     |
|---------------------------------|---------------------------------------|--------------------------------------------------|----------------------|
| `registration-form`             | `App\Livewire\RegistrationForm`       | `resources/views/livewire/registration-form.blade.php` | `/daftar`      |

---

## Partial & Layout Views

| File                                                          | Keterangan                                              |
|---------------------------------------------------------------|---------------------------------------------------------|
| `resources/views/layouts/`                                    | Layout utama (app, guest)                               |
| `resources/views/components/`                                 | Komponen Blade reusable                                 |
| `resources/views/admin/registrations/_status_badge.blade.php` | Partial badge status pendaftaran untuk tabel admin      |
| `resources/views/profile/partials/`                           | Partial form update profil & hapus akun                 |
| `resources/views/dashboard.blade.php`                         | View fallback Breeze (tidak dipakai langsung, diganti redirect logic) |
| `resources/views/welcome.blade.php`                           | File welcome default Laravel (kemungkinan tidak dipakai aktif) |
