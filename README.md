# YPIB - Sistem Informasi Penerimaan Mahasiswa Baru (PMB) & Afiliasi

Project ini adalah Sistem Informasi Penerimaan Mahasiswa Baru (PMB) terpadu untuk YPIB (Yayasan Pendidikan Imam Bonjol) Majalengka. Selain melayani pendaftaran mahasiswa, sistem ini dilengkapi dengan program Afiliasi/Referral untuk memberikan reward kepada pihak yang berhasil merekomendasikan pendaftar baru.

## 🚀 Fitur Utama

### 1. Portal Publik & Pendaftaran (Calon Mahasiswa)
- Halaman Landing informatif dengan daftar Fakultas, Program Studi, dan Fasilitas.
- Formulir pendaftaran mahasiswa baru secara online.
- Dashboard Calon Mahasiswa untuk melihat status kelulusan, upload bukti pembayaran registrasi awal, dan upload bukti daftar ulang.

### 2. Program Afiliasi (Referral System)
- Pendaftaran mandiri untuk menjadi Mitra Afiliasi (Referrer).
- Link referral unik untuk setiap mitra (`/ref/{code}`).
- Pencatatan otomatis (tracking) pengunjung dan pendaftar yang mendaftar melalui tautan afiliasi.
- Dashboard Afiliasi untuk memantau trafik klik, jumlah konversi (pendaftar), serta mengatur rekening bank tujuan pencairan reward.

### 3. Dashboard Admin & Operator
- **Manajemen Master Data:** Pengaturan detail Fakultas, Program Studi, Galeri Prodi, Fasilitas Kampus, dan Partner kerja sama.
- **Manajemen Pendaftar:** Verifikasi validitas pembayaran, persetujuan status pendaftaran, penambahan catatan (notes), serta konfirmasi daftar ulang.
- **Manajemen Periode PMB:** Konfigurasi gelombang PMB (aktif/non-aktif), batas tanggal, biaya registrasi, serta nominal komisi afiliasi.
- **Manajemen Pencairan Reward:** 
  - Verifikasi dan persetujuan payout untuk para affiliate.
  - Fitur *Mass-Disburse* (Pencairan Massal) untuk kemudahan operasional.
  - Export laporan pencairan dalam format CSV.

## 🛠️ Tech Stack

Sistem ini dikembangkan dengan kerangka kerja modern **TALL Stack**:
- **Backend:** [Laravel 12.x](https://laravel.com)
- **Frontend Logic:** [Livewire 4.x](https://livewire.laravel.com) & [Alpine.js](https://alpinejs.dev)
- **Styling:** [Tailwind CSS 3.4](https://tailwindcss.com) (dengan plugin Forms & Typography)
- **Bundler:** [Vite](https://vitejs.dev/)
- **Database ORM:** Eloquent ORM

## 📦 Kebutuhan Sistem Dasar
- PHP >= 8.2
- Composer
- Node.js & NPM
- Database Server (MySQL/MariaDB) atau ekstensial SQLite

## ⚙️ Panduan Instalasi (Development)

1. **Persiapkan Repositori**
   Pastikan Anda sudah berada di root direktori project.

2. **Install Dependensi Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   Ganti nama `.env.example` menjadi `.env`, lalu generate App Key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Jangan lupa atur konfigurasi `DB_*` di dalam file `.env` sesuai database Anda.*

4. **Jalankan Migrasi & Seeder**
   Membangun struktur database sekaligus mengisi data fundamental (Fakultas, Prodi, Periode PMB, dll).
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Build Aset Frontend**
   ```bash
   # Mode kompilasi satu kali:
   npm run build
   
   # Atau mode watch untuk development:
   npm run dev
   ```

6. **Jalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Akses project melalui URL lokal, umumnya `http://localhost:8000`.

## 🔐 Kredensial Pengguna Akses (Seeder)

Setelah proses seed database selesai, Anda dapat mencoba login pada rute `/login` menggunakan akun berikut:

**Super Administrator:**
- **Email:** `admin@ypib.ac.id`
- **Password:** `password`

**Operator PMB:**
- **Email:** `operator@ypib.ac.id`
- **Password:** `password`

*(Gunakan role operator untuk melihat batasan fitur yang tidak memerlukan otorisasi manajerial penuh).*
