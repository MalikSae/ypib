# YPIB - Sistem Informasi Penerimaan Mahasiswa Baru (PMB) & Afiliasi

Sistem Informasi PMB terpadu untuk YPIB Majalengka. Selain melayani pendaftaran mahasiswa baru sampai daftar ulang, sistem ini memiliki program Afiliasi/Referral yang memberi reward kepada pihak yang merekomendasikan pendaftar.

## Fitur Utama

### 1. Portal Publik & Pendaftaran (Calon Mahasiswa)
- Landing page: Fakultas, Program Studi (halaman detail `/prodi/{slug}` + galeri), Fasilitas, dan Partner.
- Formulir pendaftaran online bertahap (wizard 5 langkah, Livewire) dengan jalur umum dan **Jalur Alumni YPIB** (tanpa biaya pendaftaran).
- Dashboard pendaftar (`/pendaftaran/status`) dengan timeline tahapan, upload bukti bayar, dan data diri lengkap (`/pendaftaran/detail`).
- Upload dokumen per berkas (`/pendaftaran/dokumen`): Foto, KK, dan Akta Lahir wajib; KTP, transkrip nilai, surat keterangan sehat, dan sertifikat opsional.
- Tes tulis online (CBT) dengan skor otomatis, lalu informasi jadwal interview.
- Daftar ulang: upload bukti bayar daftar ulang.
- Unduhan dokumen: formulir pendaftaran (PDF), Surat Keterangan Lulus (SKL, nomor surat otomatis), dan e-KTM digital.
- PWA: dapat di-install dan punya halaman offline untuk area publik, admin, dan panitia (`public/sw.js`, `manifest-*.json`).

### 2. Program Afiliasi (Referral)
- Pendaftaran mandiri menjadi mitra afiliasi di `/afiliasi`; admin mengaktifkan/menonaktifkan.
- Link referral unik `/ref/{code}`; klik dan konversi dicatat otomatis.
- Dashboard afiliasi: statistik klik, konversi, reward, dan pengaturan rekening bank pencairan.
- Reward dibuat otomatis saat pembayaran pendaftaran dikonfirmasi (`registration`) dan saat daftar ulang dikonfirmasi (`re_registration`). Nominal diatur per prodi.
- Pendaftar yang dihapus (soft delete) menyesuaikan komisi secara otomatis.

### 3. Dashboard Admin & Operator
- **Master data:** Fakultas, Program Studi (kode prodi, jalur reguler/non-reguler, biaya daftar ulang, reward, galeri), Fasilitas, Partner, Bank Soal, akun Panitia.
- **Pengaturan:** periode PMB (rekening bank, WhatsApp admin) dan konfigurasi email.
- **Manajemen pendaftar:**
  - Daftar dengan filter (termasuk program studi), export Excel, dan **rekap per prodi** (+ export).
  - Konfirmasi bayar, konfirmasi daftar ulang (NIM di-generate di tahap ini), override status, catatan internal, ubah referral.
  - Review dokumen per berkas atau massal, upload dokumen/bukti manual, reset tes tulis, ubah data pendaftar.
  - Hapus (soft delete), halaman sampah, dan restore.
- **Afiliasi:** daftar mitra, detail, toggle aktif, ubah rekening, export.
- **Reward:** approve, disburse per reward / per mitra / massal, export CSV.
- Reset password user.

### 4. Panitia PMB
- Dashboard mobile-first di `/panitia` dengan pemindai QR pendaftar.
- Menandai hasil interview (diterima/ditolak); status lanjut ke tahap daftar ulang.

## Alur Status Pendaftaran

```
menunggu_pembayaran -> menunggu_konfirmasi -> terdaftar
  -> (lengkapi data diri) -> upload dokumen wajib, review admin per berkas
  -> menunggu_tes_tulis -> menunggu_interview
  -> diterima / ditolak (Panitia scan QR)
  -> menunggu_konfirmasi_daftar_ulang -> daftar_ulang_selesai
```

Label dan warna status dipusatkan di `Registration::STATUS_LABELS`.

## Role

| Role | Akses |
|---|---|
| `admin`, `operator` | `/admin/*` |
| `panitia` | `/panitia/*` |
| `referrer` / `is_referrer` | `/afiliasi/dashboard` |
| `mahasiswa` (default) | `/pendaftaran/*` |

## Tech Stack

- **Backend:** Laravel 12, PHP >= 8.2 (target produksi 8.3)
- **Frontend:** Livewire 4, Alpine.js 3, Tailwind CSS 3.4, Vite
- **Database:** MySQL/MariaDB (SQLite bawaan `.env.example`)
- **Library:** maatwebsite/excel (export), barryvdh/laravel-dompdf (PDF), simplesoftwareio/simple-qrcode & endroid/qr-code (QR), intervention/image v4 (e-KTM), symfony/resend-mailer
- **Belum aktif:** Xendit (pembayaran online), Fonnte/Wablas (WhatsApp)
- **Hosting:** Hostinger shared hosting + Cloudflare DNS

## Instalasi (Development)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# atur DB_* di .env
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev        # atau: npm run build
php artisan serve
```

Akses di `http://localhost:8000`.

## Akun Seeder

Seeder membuat akun admin dan operator (`admin@ypib.ac.id`, `operator@ypib.ac.id`). Password awal ada di `database/seeders/DatabaseSeeder.php` — **ganti sebelum dipakai di produksi**.

## Dokumentasi Lain

- `AGENTS.md` — standar kerja dan sistem desain untuk AI coding agent.
- `UI_GUIDELINES.md` — komponen Blade dan aturan UI.
- `DEV_REPORT.md` — laporan kondisi codebase (skema, fitur, alur, catatan penting).

## Catatan Deploy: Berkas Privat

Berkas pendaftar (bukti bayar, kartu alumni, bukti daftar ulang, dokumen) kini disimpan privat di `storage/app/private` dan ditayangkan lewat `/berkas/*` (wajib login). Setelah deploy, pindahkan berkas lama dari `public/storage` sekali saja:

```bash
php artisan files:privatize --dry-run   # lihat daftar yang akan dipindah
php artisan files:privatize
```

Frontend di-build ulang (`npm run build`); sertakan `public/build` saat deploy.

## Menjalankan Test

Buat database test sekali (`CREATE DATABASE ypib_test CHARACTER SET utf8mb4;`), lalu `php artisan test`. Test menolak berjalan di database yang namanya tidak berakhiran `_test`. Lihat `DEV_REPORT.md` bagian 11.
