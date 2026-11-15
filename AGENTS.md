# AGENTS.md — Panduan Kerja untuk AI Coding Agent

Dokumen ini WAJIB dibaca dan dirujuk di setiap sesi kerja pada project PMB YPIB Majalengka. Berisi standar teknis dan pola kerja yang disepakati, hasil dari banyak trial-error di sesi-sesi sebelumnya.

## Tech Stack

Laravel 12, Livewire, Alpine.js, Tailwind CSS 3.4, PHP 8.3, maatwebsite/excel, DomPDF (barryvdh/laravel-dompdf), simplesoftwareio/simple-qrcode, Xendit (payment, belum aktif), Fonnte/Wablas (WA, belum aktif). Hosting: Hostinger shared hosting + Cloudflare DNS.

## Aturan Kerja Umum

1. **Investigasi dulu, eksekusi kemudian.** Jangan langsung mengedit kode berdasarkan asumsi. Baca file yang relevan secara utuh dulu.
2. **Jangan klaim "selesai" tanpa bukti.** Setiap perubahan HARUS benar-benar dites (browser, tinker, atau query database), bukan cuma "seharusnya berfungsi karena kode sudah ditulis sesuai instruksi". Jika verifikasi otomatis (browser subagent) tidak tersedia, katakan dengan jelas bahwa verifikasi belum dilakukan — jangan berpura-pura sudah dites.
3. **Bersihkan file bantu sementara** (script .php/.js/.cjs investigasi, task.md, walkthrough.md) setelah selesai — JANGAN ikut di-commit ke git kecuali diminta eksplisit.
4. **Jangan reka nama class/route/kolom.** Kalau tidak yakin, cek dulu ke file aslinya, jangan menebak.

## Sistem Desain — ADA 2 SISTEM TERPISAH, JANGAN DICAMPUR

### 1. Halaman Admin (prefix `/admin`, layout `layouts.admin`)

WAJIB struktur:
```blade
@extends('layouts.admin')
@section('title', '{Judul} — Admin PMB YPIB')
@section('page-title', '{Judul Singkat}')
@section('content')
... isi ...
@endsection
```

JANGAN PERNAH pakai `<x-app-layout>` (itu layout Breeze default untuk user biasa, BUKAN untuk admin) — kesalahan ini pernah terjadi dan membuat sidebar admin hilang total.

**Warna — HANYA gunakan token berikut**, jangan pernah pakai `gray-*`/`blue-*`/`green-*`/`red-*`/`yellow-*` Tailwind default secara langsung:
- `primary-*` (biru brand, DEFAULT `#0B41CB`) — aksen/CTA utama
- `neutral-*` (abu-abu brand) — teks & border
- `success-*` / `warning-*` / `error-*` / `info-*` — status semantik

**Komponen Blade dasar WAJIB dipakai** (jangan tulis ulang manual dengan Tailwind mentah):
- `<x-card>` — pembungkus card/section
- `<x-button>` — pakai hanya empat bentuk di bagian "Standar tombol" di bawah
- `<x-input-label for="..." value="..." required="true" />`
- `<x-text-input type="..." id="..." name="..." :value="..." :error="$errors->has('field')" />`
- `<x-input-error :messages="$errors->get('field')" />`

**Komponen Blade tambahan (hasil audit lengkap 26 komponen)** — WAJIB dipakai:
- `<x-select-menu>` — **WAJIB untuk semua dropdown**. Dropdown kustom berbasis Alpine (pola ARIA select-only combobox): bisa dipakai dengan mouse, sentuh, dan keyboard (panah, Home/End, Enter, Esc, ketik huruf awal).
  - Form biasa: `<x-select-menu id="jenis" name="jenis" :options="['' => 'Semua jenis'] + $kinds" :value="old('jenis', $x->jenis)" :error="$errors->has('jenis')" />`. Nilai dikirim lewat input tersembunyi bernama `name`.
  - Livewire: `<x-select-menu id="event" wire:model.live="event" :value="$event" :options="..." />`. Tersambung dua arah, termasuk saat server mereset nilainya.
  - `options` berupa array `nilai => label`. Label kosong tidak dipakai; isi pilihan "Semua ..." dengan nilai `''`.
  - Selalu beri `id` tetap. `required` ditahan browser seperti `<select required>`.
  - Daftar pilihan dirender di `<body>` (x-teleport, posisi fixed selebar tombol), jadi aman di dalam card, modal, atau wadah ber-`overflow`.
  - JANGAN pakai `<select>` bawaan browser (termasuk `<x-select>`) di halaman baru. `<x-select>` lama dipertahankan sampai halamannya dikerjakan, lalu diganti `<x-select-menu>`.
- `<x-textarea :error="$errors->has('field')" />` — untuk textarea, JANGAN tulis manual
- `<x-table-action-edit :href="..." />`, `<x-table-action-delete :action="..." />`, `<x-table-action-detail :href="..." />` — untuk tombol aksi kecil (ikon) di baris tabel. Detail dan edit bergaris netral, hapus bergaris merah. JANGAN tulis SVG ikon manual sendiri untuk aksi edit/hapus/detail di tabel — pakai komponen ini supaya ikon dan style konsisten di semua halaman.
  Bila baris tabel punya lebih dari satu aksi yang perlu label teks (misal Chat, Link, Detail di Kelola Prospek), boleh pakai `<x-button size="sm" color="neutral" variant="outline">` dengan teks. Jangan campur ikon dan teks dalam satu baris.
- `<x-button :href="...">` — tombol yang berpindah halaman, membuka tab baru, atau mengunduh dirender sebagai `<a>`. JANGAN pakai `onclick="location.href=..."` atau `window.open` untuk navigasi, dan JANGAN tulis `<a>` dengan class tombol manual.
- `<x-badge-status :active="$item->is_active" :toggle-action="route(...)" />` — untuk badge status Aktif/Nonaktif yang bisa diklik untuk toggle. Pakai ini untuk field boolean status di tabel manapun.
- `<x-filter-tab :active="..." :count="...">` — tab pilihan untuk filter (misal status). Tidak aktif tampil timbul, aktif tampak tertekan. JANGAN tulis tab manual. Hanya untuk pilihan sedikit yang muat satu baris (maksimal sekitar 6, misal status prospek). Bila lebih banyak (misal 10 status pendaftaran), pakai `<x-select-menu>` dengan jumlah di label, contoh "Terdaftar (3)".
- `<x-modal>` — untuk dialog konfirmasi (misal konfirmasi hapus), berbasis Alpine.js, sudah ada state show/toggle bawaan.

**Belum ada component-nya** (kalau perlu, tulis manual dengan Tailwind mengikuti token warna `neutral-*`/`primary-*`/`error-*` yang sudah ditentukan, JANGAN pakai `gray-*`/`blue-*` Tailwind default):
- Checkbox, radio button, toggle/switch, file upload input
- Alert/toast/notification (di admin, flash message sudah ditangani otomatis oleh `layouts/admin.blade.php` sendiri — cukup pakai session flash biasa dari controller, JANGAN bikin banner manual di tiap halaman)

**JANGAN pakai untuk halaman admin baru** (ini komponen lama Breeze, HANYA untuk halaman auth/login/profile bawaan Laravel, bukan untuk CRUD admin):
- `<x-danger-button>`, `<x-primary-button>`, `<x-secondary-button>` — pakai `<x-button color="...">` sebagai gantinya
- `<x-page-header>` — sudah digantikan pola page header manual di `@section('content')` (lihat pola List/Index di bawah)

Pagination pakai bawaan Laravel (`{{ $items->links() }}`). Di daftar Livewire pakai `{{ $items->links('livewire.admin.pagination') }}`.

**Halaman daftar berfilter memakai Livewire** (acuan: `app/Livewire/Admin/ProspectList.php` + `resources/views/livewire/admin/prospect-list.blade.php`):
- Komponen di `app/Livewire/Admin/`, disisipkan dari view halaman dengan `<livewire:admin.nama-komponen />`. Controller `index()` hanya mengembalikan view.
- Semua filter langsung menyaring, TANPA tombol Terapkan: cari pakai `wire:model.live.debounce.300ms`, dropdown pakai `wire:model.live`, tab status pakai `<x-filter-tab wire:click>`.
- Filter disimpan di URL dengan `#[Url(except: '')]`, supaya tautan dari halaman lain (misal `?event=3`) tetap bekerja dan hasil saringan bisa dibagikan.
- Request pembaruan Livewire TIDAK melewati middleware rute. Cek peran di `boot()` komponen.
- Saat memuat: daftar meredup (`wire:loading.class="opacity-60"`), tombol yang ditekan nonaktif (`wire:loading.attr="disabled" wire:target="..."`). Tanpa ikon berputar.
- "Hapus filter" pakai `<x-button color="neutral" variant="ghost" size="sm">`, hanya muncul bila ada filter aktif. Tab status tidak ikut direset.
- Alpine dimuat oleh Livewire (`@livewireScripts` di layout). JANGAN tambah Alpine dari CDN di layout yang memuat Livewire.
- Sudah memakai pola ini: Kelola Prospek, Event dan Kampanye, Data Afiliasi, Data Pendaftar, Arsip Pendaftar, tab Detail di Komisi, Program Studi, Bank Soal, Akun Staf (komponen di `app/Livewire/Admin/`), dan riwayat interview di dashboard Panitia (`app/Livewire/Panitia/`, layout `layouts.panitia` juga memuat Livewire). Halaman daftar baru WAJIB ikut pola ini.

**Pola halaman List/Index** (acuan: `admin/facilities/index.blade.php`):
Page header flex justify-between (judul kiri `text-xl font-bold text-neutral-900` + deskripsi `text-sm text-neutral-400`, tombol aksi kanan `<x-button>`). Tabel: `bg-white rounded-lg border border-neutral-200`, header `bg-neutral-50 text-xs font-semibold uppercase tracking-wide text-neutral-500`, body `divide-y divide-neutral-100`.

**Aturan UI (berlaku untuk seluruh halaman admin, mengacu ke skill ui-craft)**

- **Tulisan**: sentence case untuk judul, tombol, dan label. Uppercase HANYA untuk label kategori kecil (≤13px) dengan `tracking-wide`, misal header tabel. Judul dan tombol tidak boleh uppercase.
- **Radius per elemen** (jangan seragam):
  - Input, select, textarea, dan tombol: `rounded-md` (6px).
  - Card, tabel, dan panel: `rounded-lg` (8px).
  - Modal: `rounded-xl` (12px).
  - Pill (`rounded-full`) hanya untuk badge status.
- **Satu aksen**: `primary` adalah satu-satunya warna aksi.
  - Maksimal 3–5 tombol `primary` per layar, dan satu per baris tabel.
  - Aksi di baris tabel memakai `outline` atau `ghost` (`neutral`). JANGAN pakai `success` atau warna lain sebagai tombol aksi.
  - `success` hanya untuk status positif (badge atau teks).
- **Status**: field boolean pakai `<x-badge-status>`. Field status bertingkat (enum) memakai pill pastel dengan border tipis, gaya yang sama dengan `<x-badge-status>`: `bg-{warna}-50 text-{warna}-700 border-{warna}-200`. Angka tren atau persentase cukup teks biasa, tanpa pill.
- **Ikon**: satu library saja, yaitu Heroicons (outline, `stroke-width` 1.5–2). JANGAN campur dengan library lain. JANGAN pakai emoji sebagai ikon.
- **Gradient teks** dan **glow** tidak dipakai di halaman admin.
- **Aksesibilitas**:
  - Setiap elemen interaktif punya focus ring yang terlihat (`focus-visible:ring-2`). `<x-button>` sudah memilikinya.
  - Semua aksi bisa dijalankan dengan keyboard.
  - Animasi menghormati `prefers-reduced-motion` (`motion-reduce:`).
- **Teks tombol** harus spesifik: "Simpan perubahan", "Kirim link daftar". JANGAN "Klik di sini" atau "Selengkapnya" sendirian.

**Standar tombol (admin dan panitia)**

Tombol dibedakan menurut pentingnya aksi. Hanya empat bentuk ini:

| Kategori | Ditulis | Dipakai untuk |
|---|---|---|
| Utama | `<x-button>` | Aksi yang paling diharapkan. Satu per layar atau per baris tabel. |
| Sekunder | `<x-button color="neutral" variant="outline">` | Aksi lain yang sah tapi bukan tujuan utama. |
| Tersier | `<x-button color="neutral" variant="ghost">` | Jalan keluar dan aksi ringan (Batal, Hapus filter). |
| Berbahaya | `<x-button color="danger" variant="outline">` | Menghapus atau tidak bisa dibatalkan. Selalu dengan konfirmasi. |

- Kombinasi lain (`success`, `warning`, `secondary`, `primary outline`, `primary ghost`, `danger solid`) TIDAK dipakai. Merah penuh (`color="danger"` solid) hanya untuk tombol "Ya, hapus" di dalam dialog konfirmasi.
- Utama, sekunder, dan berbahaya tampil **timbul** (kelas `x-button-raised`, bawaan komponen). Tersier sengaja datar. **Badge selalu datar dan berbentuk pill**: yang timbul bisa ditekan, yang datar hanya keterangan.
- `size="sm"` untuk baris tabel dan aksi kecil: otomatis 44px di mobile dan 36px di desktop. JANGAN tambal tinggi tombol dengan `class="h-11"` atau `md:h-9`.
- Tombol ikon saja: tambahkan `class="!px-2 min-w-11 md:min-w-9"` dan `aria-label`.
- Teks tombol selalu satu baris (`whitespace-nowrap` bawaan komponen). Bila tidak muat, pendekkan teksnya.
- Tombol manual yang tersisa di halaman lama dirapikan ke `<x-button>` saat halamannya dikerjakan.

**Tipografi (admin dan panitia)**

Font: Plus Jakarta Sans (`font-sans`, sudah diatur di `tailwind.config.js`). Hanya empat tingkat ukuran:

| Tingkat | Kelas | Dipakai untuk |
|---|---|---|
| Judul halaman | `text-xl font-bold text-neutral-900` | Satu per halaman |
| Isi utama | `text-sm`, `font-medium` untuk data kunci | Nama, angka, isi sel tabel, isi form |
| Keterangan | `text-xs text-neutral-500` | Sekolah, tanggal, persen, teks bantu |
| Label kategori | `text-xs font-semibold uppercase tracking-wide text-neutral-500` | Header tabel, judul kelompok kecil |

- Ukuran di luar skala (`text-[10px]`, `text-[11px]`, `text-base` untuk isi) TIDAK dipakai. Pengecualian: input dan select `text-base md:text-sm` (aturan iOS, bawaan komponen).
- **Data tabel maksimal dua baris per sel, tanpa pengecualian** (berlaku untuk semua tabel di project, termasuk dashboard affiliator dan halaman pendaftar):
  - Baris utama `text-sm`, baris keterangan `text-xs`. Tidak ada baris ketiga.
  - Teks panjang tidak boleh membungkus jadi baris ketiga. Pakai `<x-truncate :value="$x->nama" class="text-sm font-medium text-neutral-900" />`: dipotong dengan elipsis, teks lengkap di `title`, bawaan `max="max-w-xs"` dan `fallback="—"`. JANGAN tulis ulang pola `truncate` + `title` manual.
  - Peringatan (misal tanda fraud) tampil sebagai badge di kolom status, bukan baris tambahan.
  - Data yang tidak muat pindah ke kolom lain atau ke halaman detail.
  - Dokumen cetak (PDF formulir, SKL) tidak termasuk aturan ini.
- Sel tabel rata tengah vertikal (`align-middle`), supaya badge dan tombol sejajar dengan isi dua baris.
- `font-mono` hanya untuk kode yang disalin (kode referral, URL, token). Nomor WA dan angka biasa memakai font biasa.
- Ukuran huruf di halaman lama yang menyimpang dirapikan saat halamannya dikerjakan.

**Pola halaman Form** (acuan: `admin/programs/form.blade.php`):
Header form: tombol kembali (ikon panah Heroicons, `w-10 h-10`) dan judul dalam SATU baris di semua ukuran layar (`flex items-center gap-3`, bukan `flex-col`). Judul `text-xl font-bold text-neutral-900 tracking-tight`, deskripsi `text-sm text-neutral-500`.
Bungkus `<x-card class="p-4 md:p-8">`. Kelompokkan field terkait dalam `<div class="space-y-5 md:p-6 md:bg-neutral-50 md:rounded-xl md:border md:border-neutral-200">`. Di mobile kotak dalam tidak punya bg, border, atau padding, sehingga hanya ada satu lapis card. Jangan menumpuk card, kotak abu-abu, dan padding di mobile. Tiap field: x-input-label → x-text-input → x-input-error berurutan. Footer: `flex justify-end gap-3 pt-6 border-t border-neutral-200`, tombol Batal (variant ghost) + Simpan (color primary).

**Responsif — halaman admin** (mengacu ke skill ui-craft, `responsive.md`):

- **Breakpoint**: pakai `md` (768px) sebagai satu-satunya titik pindah. Di bawah `md` = mobile, di atas = desktop. Jangan tambah breakpoint lain tanpa alasan konten.
- **Mobile**:
  - Satu kolom, komponen full-width.
  - Filter disembunyikan di balik tombol "Filter" yang bisa dibuka-tutup. Pengecualian: bila filter hanya cari plus satu dropdown (misal Kelola Prospek), tampilkan langsung tanpa tombol buka-tutup.
  - Ringkasan angka (KPI) jadi daftar, bukan grid kartu.
  - Tabel dengan lebih dari 4 kolom ditampilkan sebagai daftar kartu (`md:hidden` untuk kartu, `hidden md:block` untuk tabel). JANGAN biarkan tabel scroll horizontal tanpa indikator.
  - Input dan select `text-base` (16px) untuk mencegah zoom di iOS. Di desktop boleh `md:text-sm`.
  - Zona sentuh minimal 44×44px. `<x-button size="sm">` sudah otomatis 44px di mobile.
  - Aksi destruktif (hapus, nonaktifkan) diletakkan jauh dari jangkauan jempol dan dikonfirmasi dengan `<x-modal>`.
  - Jangan sembunyikan fitur di mobile. Kalau terlalu kompleks, susun ulang layoutnya.
  - Elemen fixed atau sticky wajib memakai `padding-bottom: env(safe-area-inset-bottom)`.
  - Tinggi layar penuh (modal, drawer) pakai `dvh`, bukan `vh` atau `h-screen`.
- **Desktop**:
  - Tabel boleh banyak kolom. Gunakan `max-w` untuk konten yang terlalu lebar.
  - Hover state wajib untuk aksi tambahan. Shortcut keyboard boleh.
  - Form utama dibatasi lebar 640px (`max-w-2xl`) agar mudah dibaca.
- **Tidak boleh**: swipe sebagai satu-satunya cara aksi, scroll horizontal tanpa indikator, dan teks di bawah 16px untuk input di mobile.

### 2. Halaman Publik Pendaftar (prefix `/pendaftaran`, layout `layouts.landing`)

Sistem desain BERBEDA dari admin, jangan dicampur. Class custom didefinisikan inline di `<style>` pada `layouts/landing.blade.php`:
- `.pub-card` — card putih border `#DEE3E9` radius 16px padding 24px
- `.btn-primary` — pill navy `#082e8f` (rounded-full), untuk aksi utama
- `.btn-secondary` — pill outline, untuk aksi sekunder
- `.pub-flash-success` / `.pub-flash-error` / `.pub-flash-warning` — notifikasi/banner
- `.status-badge` — badge status kecil

**Struktur header WAJIB SAMA di semua halaman** (status, detail, documents, exam, interview): breadcrumb kiri-atas ("Kembali ke Status Pendaftaran") → di bawahnya flex kiri-kanan: judul (`text-xl font-semibold`, BUKAN `text-2xl` — dashboard style, bukan landing hero) + subjudul kecil di kiri, elemen aksi (tombol/badge) di kanan, stack vertikal di mobile (`flex-col sm:flex-row`).

Warna: `neutral-*` untuk teks (bukan `gray-*`), `primary-600` untuk aksen (bukan `blue-600`). Badge/pill: `flex-shrink-0 whitespace-nowrap` (supaya tidak wrap 2 baris saat judul/label panjang).

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