# LSP2025 - Sistem Informasi Penerimaan Mahasiswa Baru

LSP2025 adalah aplikasi web manajemen pendaftaran mahasiswa baru yang modern dan responsif, dibangun menggunakan **Laravel 12**. Sistem ini dirancang untuk memfasilitasi seluruh rangkaian proses penerimaan mahasiswa baru, mulai dari pendaftaran akun, pengisian formulir biodata, verifikasi dokumen, pembayaran, hingga pengumuman kelulusan secara digital.

Aplikasi ini membagi akses menjadi dua peran utama: **Administrator** yang mengelola sistem secara keseluruhan, dan **Mahasiswa** (calon pendaftar) yang menggunakan sistem untuk mendaftar.

## 🛠️ Teknologi yang Digunakan

Project ini dibangun di atas stack teknologi modern untuk performa dan pengalaman pengguna yang optimal:

*   **Backend Framework:** [Laravel 12](https://laravel.com) (PHP ^8.2)
*   **Database:** MySQL
*   **Frontend:**
    *   Blade Templates
    *   [Tailwind CSS v3](https://tailwindcss.com) (Utility-first CSS framework)
    *   [Alpine.js](https://alpinejs.dev) (Lightweight JavaScript framework)
*   **Authentication:** Laravel Breeze / Sanctum
*   **Build Tool:** Vite

## ✨ Fitur Utama

### 👑 Administrator
*   **Dashboard Analitik:** Menampilkan ringkasan statistik pendaftar, status pembayaran, dan grafik pertumbuhan pendaftar.
*   **Manajemen Jurusan:** Menambah, mengedit, dan menghapus program studi serta mengelola kuota penerimaan.
*   **Manajemen Mahasiswa:**
    *   Melihat daftar calon mahasiswa.
    *   Memverifikasi biodata dan berkas pendaftaran.
    *   Mengubah status kelulusan (Diterima/Ditolak/Cadangan).
*   **Validasi Pembayaran:** Memeriksa bukti transfer yang diunggah mahasiswa dan mengubah status pembayaran.
*   **Portal Pengumuman:** Membuat berita atau pengumuman penting dengan dukungan gambar cover dan kategori (Umum, Penerimaan, Pembayaran).
*   **Manajemen Akun:** Mengelola data pengguna sistem.

### 🎓 Mahasiswa (Calon Pendaftar)
*   **Registrasi & Login:** Pembuatan akun mandiri yang aman.
*   **Dashboard Personal:** Pusat informasi status pendaftaran dan notifikasi penting.
*   **Formulir Pendaftaran:** Pengisian data diri, orang tua, sekolah asal, dan pemilihan jurusan.
*   **Upload Dokumen:** Antarmuka pengunggahan berkas persyaratan (Ijazah, KK, Pas Foto, dll).
*   **Pembayaran:** Fitur upload bukti pembayaran biaya pendaftaran.
*   **Cek Pengumuman:** Melihat informasi terbaru seputar PMB dengan tampilan visual yang menarik.
*   **Cetak Kartu:** (Opsional/Future) Mencetak kartu tanda peserta ujian.

## 📸 Galeri & Tangkapan Layar

Berikut adalah gambaran visual dari aplikasi LSP2025.

### Diagram Use Case
![Use Case Diagram](usecase-lsp.jpg)
*Gambaran alur interaksi pengguna dengan sistem*

### 1. Dashboard Mahasiswa
![Dashboard Mahasiswa](https://via.placeholder.com/800x450.png?text=Dashboard+Mahasiswa+Preview)
*Tampilan dashboard mahasiswa dengan status pendaftaran real-time dan pengumuman terbaru*

### 2. Pusat Informasi & Pengumuman
![Halaman Pengumuman](https://via.placeholder.com/800x450.png?text=Halaman+Pengumuman)
*Daftar pengumuman dengan filter kategori dan tampilan kartu bergambar*

### 3. Formulir Pendaftaran
![Formulir Pendaftaran](https://via.placeholder.com/800x450.png?text=Formulir+Pendaftaran)
*Antarmuka pengisian biodata calon mahasiswa*

### 4. Dashboard Administrator
![Dashboard Admin](https://via.placeholder.com/800x450.png?text=Dashboard+Admin+Preview)
*Panel kontrol admin untuk memantau statistik dan verifikasi data*

*(Catatan: Gambar di atas adalah placeholder. Silakan ganti URL gambar dengan screenshot asli dari aplikasi Anda setelah instalasi.)*

## 🚀 Panduan Instalasi & Penggunaan

Ikuti langkah-langkah berikut untuk menjalankan project ini di lingkungan pengembangan lokal (Localhost).

### Prasyarat Sistem
Pastikan komputer Anda telah terinstall:
*   **PHP** >= 8.2
*   **Composer** (PHP Package Manager)
*   **Node.js** & **NPM**
*   **MySQL** Database Server

### Langkah-langkah Instalasi

1.  **Clone Repository**
    Unduh source code project ke komputer Anda:
    ```bash
    git clone https://github.com/username/LSP2025.git
    cd LSP2025
    ```

2.  **Install Dependencies**
    Install library PHP dan JavaScript yang dibutuhkan:
    ```bash
    # Install dependency PHP
    composer install

    # Install dependency Frontend
    npm install
    ```

3.  **Konfigurasi Environment**
    Salin file konfigurasi contoh `.env.example` menjadi `.env`:
    ```bash
    cp .env.example .env
    ```
    Buka file `.env` dengan text editor dan sesuaikan konfigurasi database:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=lsp2025_db  # Sesuaikan dengan nama database Anda
    DB_USERNAME=root        # Sesuaikan dengan user database Anda
    DB_PASSWORD=            # Sesuaikan dengan password database Anda
    ```

4.  **Generate Application Key**
    Buat kunci enkripsi aplikasi Laravel:
    ```bash
    php artisan key:generate
    ```

5.  **Migrasi Database & Seeding**
    Jalankan perintah ini untuk membuat tabel-tabel di database dan mengisi data awal (akun Admin default & Data Jurusan):
    ```bash
    php artisan migrate --seed
    ```

6.  **Build Assets Frontend**
    Kompilasi file CSS dan JavaScript (Tailwind & Alpine):
    ```bash
    npm run build
    ```

7.  **Jalankan Server**
    Jalankan server pengembangan Laravel:
    ```bash
    php artisan serve
    ```
    Akses aplikasi melalui browser di alamat: `http://127.0.0.1:8000`

## 🔐 Akun Demo (Default)

Untuk pengujian awal, Anda dapat menggunakan akun Administrator yang dibuat otomatis oleh seeder:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@lsp.com` | `password` |
| **Mahasiswa** | *(Silakan Register Akun Baru)* | - |

## 🤝 Kontribusi

Kontribusi selalu diterima! Silakan buat **Pull Request** baru atau buka **Issue** jika Anda menemukan bug atau memiliki saran fitur baru.

## 📝 Lisensi

Project ini bersifat open-source dan dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT).