# Sistem Penerimaan Mahasiswa Baru (SPMB)

Aplikasi web untuk manajemen pendaftaran mahasiswa baru menggunakan Laravel 12. Menyediakan fitur lengkap untuk proses penerimaan dari registrasi hingga verifikasi pendaftaran.

## Tech Stack

-   **Backend:** Laravel 12 (PHP 8.2+)
-   **Database:** MySQL
-   **Frontend:** Blade, Tailwind CSS, Alpine.js
-   **Auth:** Laravel Breeze
-   **Build Tool:** Vite

## Fitur

### Administrator

-   Dashboard dengan statistik dan grafik pendaftar
-   Manajemen jurusan dan kuota
-   Verifikasi biodata dan dokumen mahasiswa
-   Validasi pembayaran
-   Manajemen pengumuman dengan kategori
-   Kelola status kelulusan (Diterima/Ditolak)

### Mahasiswa

-   Registrasi dan login
-   Dashboard status pendaftaran
-   Formulir pendaftaran online
-   Upload dokumen persyaratan
-   Upload bukti pembayaran
-   Lihat pengumuman dan informasi PMB

## Screenshots

### Use Case Diagram

![Use Case Diagram](https://github.com/user-attachments/assets/bd734549-bcb3-4788-99e1-a358214b6ebb)

### Registrasi

![Register](https://github.com/user-attachments/assets/2f79309d-7b59-472d-93c3-7600a039da47)

### Login

![Login](https://github.com/user-attachments/assets/62293726-9209-4909-ac36-75916daf3af8)

> **Note:** Setelah login berhasil, akun harus diverifikasi admin terlebih dahulu sebelum dapat mengakses dashboard.

### Dashboard Mahasiswa

![Dashboard Mahasiswa](https://github.com/user-attachments/assets/37bb97d4-4532-4e5b-b674-079392411ecd)

### Formulir Pendaftaran

![Form Pendaftaran 1](https://github.com/user-attachments/assets/b5ed61be-c635-469a-a9b4-aa82154aa705)
![Form Pendaftaran 2](https://github.com/user-attachments/assets/f0636867-b4bc-4ce0-bf62-bfbe5a63fbf7)

### Pembayaran

![Pembayaran](https://github.com/user-attachments/assets/d8cdcc7d-7650-4f78-9d0d-ea3ff9808066)

### Halaman Pengumuman

![Pengumuman](https://github.com/user-attachments/assets/85667018-5dad-4662-952b-b8cad1c2d3ad)

### Dashboard Admin

![Dashboard Admin](https://github.com/user-attachments/assets/99d89bc1-cba6-4596-add9-90785fae08c8)

### Verifikasi Akun Mahasiswa

![Verifikasi Akun](https://github.com/user-attachments/assets/9391b99a-9393-4092-b5ae-c7c7f4b33a0b)

### Daftar Pendaftar

![Daftar Pendaftar](https://github.com/user-attachments/assets/841e44b8-cc15-4b88-ac9d-3d0a2f0dd4dc)

### Verifikasi Pendaftaran

![Verifikasi Pendaftaran](https://github.com/user-attachments/assets/4b2f2e0f-4fd6-4547-836c-ad55b33f86e3)

### Verifikasi Pembayaran

![Verifikasi Pembayaran](https://github.com/user-attachments/assets/3c8c720c-90e4-4058-b5d3-48fc7c31ef1d)

### Daftar Pengumuman Admin

![Daftar Pengumuman](https://github.com/user-attachments/assets/27f18b2f-9070-43a8-aaa5-ad8255e9bbf4)

### Tambah Pengumuman

![Tambah Pengumuman](https://github.com/user-attachments/assets/7a62d44c-a9f7-4868-92e8-dc715dd9979f)

### Detail Pengumuman

![Detail Pengumuman](https://github.com/user-attachments/assets/fa0b7caf-5fbb-4ae6-99f3-f2308c0bf4ef)

### Daftar Jurusan

![Daftar Jurusan](https://github.com/user-attachments/assets/87dd4ef7-e78a-4ecc-a64a-b97332298f6a)

### Tambah Jurusan

![Tambah Jurusan](https://github.com/user-attachments/assets/071f9b23-a055-48dc-9170-0431bf4fb902)

## Instalasi

### Prasyarat

-   PHP >= 8.2
-   Composer
-   Node.js & NPM
-   MySQL

### Langkah Instalasi

1. Clone repository

```bash
git clone (url repository)
cd LSP2025
```

2. Install dependencies

```bash
composer install
npm install
```

3. Setup environment

```bash
cp .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lsp2025_db
DB_USERNAME=root
DB_PASSWORD=
```

4. Generate key

```bash
php artisan key:generate
```

5. Migrasi database

```bash
php artisan migrate --seed
```

6. Build assets

```bash
npm run build
```

7. Jalankan server

```bash
php artisan serve
```

Akses aplikasi di `http://127.0.0.1:8000`

## Akun Default

| Role      | Email               | Password |
| --------- | ------------------- | -------- |
| Admin     | admin@lsp.com       | password |
| Mahasiswa | _(Register manual)_ | -        |

## Lisensi

MIT License
