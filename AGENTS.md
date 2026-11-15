# AGENTS.md — Panduan Kerja untuk AI Coding Agent

Dokumen ini WAJIB dibaca dan dirujuk di setiap sesi kerja pada project PMB YPIB Majalengka. Berisi standar teknis dan pola kerja yang disepakati, hasil dari banyak trial-error di sesi-sesi sebelumnya.

## Tech Stack

Laravel 12, Livewire, Alpine.js, Tailwind CSS 3.4, PHP 8.3, maatwebsite/excel, DomPDF (barryvdh/laravel-dompdf), simplesoftwareio/simple-qrcode, Xendit (payment, belum aktif), Fonnte/Wablas (WA, belum aktif). Hosting: Hostinger shared hosting + Cloudflare DNS.

## Aturan Kerja Umum

1. **Investigasi dulu, eksekusi kemudian.** Jangan langsung mengedit kode berdasarkan asumsi. Baca file yang relevan secara utuh dulu.
2. **Jangan klaim "selesai" tanpa bukti.** Setiap perubahan HARUS benar-benar dites (browser, tinker, atau query database), bukan cuma "seharusnya berfungsi karena kode sudah ditulis sesuai instruksi". Jika verifikasi otomatis (browser subagent) tidak tersedia, katakan dengan jelas bahwa verifikasi belum dilakukan — jangan berpura-pura sudah dites.
3. **Bersihkan file bantu sementara** (script .php/.js/.cjs investigasi, task.md, walkthrough.md) setelah selesai — JANGAN ikut di-commit ke git kecuali diminta eksplisit.
4. **Jangan reka nama class/route/kolom.** Kalau tidak yakin, cek dulu ke file aslinya, jangan menebak.

## Sistem Desain

Semua standar tampilan ada di **`DESIGN.md`**. WAJIB dibaca sebelum mengerjakan UI apa pun: komponen Blade, warna, tombol, tipografi, field, card, tabel, filter Livewire, responsif.

Ringkasnya: ada 2 sistem terpisah yang tidak boleh dicampur, yaitu halaman admin dan panitia (`layouts.admin`, `layouts.panitia`) dan halaman publik pendaftar (`layouts.landing`). Keputusan desain baru ditulis di `DESIGN.md`, bukan di sini.

## Alur Status Registrasi (Single Path, Tanpa Cabang)

```
menunggu_pembayaran → menunggu_konfirmasi → terdaftar
  → [upload dokumen via /pendaftaran/dokumen bersifat OPSIONAL, tidak menahan tahap berikutnya; gerbang isFormComplete() hanya untuk halaman dokumen]
  → [tes tulis dan interview sama-sama terbuka sejak terdaftar (lihat Registration::EXAM_OPEN_STATUSES / INTERVIEW_OPEN_STATUSES): tes tulis tidak wajib sebelum interview]
  → menunggu_tes_tulis (hanya muncul bila admin mereset tes; persetujuan dokumen TIDAK mengubah status; dokumen boleh menyusul kapan saja, tidak mengunci daftar ulang)
  → menunggu_interview (otomatis setelah submit tes tulis)
  → diterima / ditolak (via Panitia PMB scan QR di /panitia)
  → menunggu_konfirmasi_daftar_ulang → daftar_ulang_selesai
```

Sistem dokumen tunggal lama (`document_proof`, `menunggu_review_berkas`, `perlu_revisi_berkas`, method `updateStatus()`) SUDAH DIHAPUS TOTAL dari kode. Kolom `document_proof` di database dibiarkan sebagai arsip historis saja, TIDAK dibaca kode manapun lagi. JANGAN bangun ulang sistem lama ini.

Label & warna status disentralisasi di `Registration::STATUS_LABELS` (constant) + method `getStatusLabel()` / `getStatusColor()`. JANGAN buat array mapping status baru di file manapun — selalu tambahkan status baru ke `STATUS_LABELS` ini saja.

## Role & Akses

Kolom `role` di tabel `users` (enum): `admin`, `operator`, `referrer`, `mahasiswa`, `panitia`.

- `admin`/`operator` → akses penuh `/admin/*`
- `referrer` → `/afiliasi/dashboard`
- `panitia` → `/panitia/*` (dashboard scan QR untuk interview), layout TERPISAH (`layouts.panitia`, mobile-first, BUKAN `layouts.admin`)
- `mahasiswa` (default) → `/pendaftaran/*` (pendaftar/calon mahasiswa)

## Git & Commit

- Commit terpisah per fitur JIKA file tidak saling tumpang tindih. Jika overlap terlalu rumit untuk dipisah rapi tanpa risiko salah potong, gabung jadi satu commit utuh — LAPORKAN dulu ke user sebelum memutuskan mana yang dipilih.
- Jangan pernah force push atau merge otomatis jika push gagal.
- Setiap deploy produksi: tambahkan entri baru PALING ATAS di `resources/data/changelog.php` (tanggal, judul, commit, daftar update per kelompok). Isi ditampilkan di `/admin/changelog`. Tulis dari sudut pandang pengguna, bukan istilah teknis.

---

*Dokumen ini hidup — update setiap kali ada keputusan arsitektur/desain baru yang disepakati, supaya tidak perlu direkonstruksi manual berulang-ulang di sesi berikutnya.*