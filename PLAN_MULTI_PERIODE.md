# Rencana Implementasi: Multi-Periode PMB (Revisi 7)

Status: **usulan, belum dikerjakan**. Dokumen ini hanya perencanaan; tidak ada kode yang diubah.
Revisi 7 memasukkan hasil audit keenam (V1-V5) dan dua keputusan pemilik produk: override ber-NIM dengan
penanda `reapply_allowed` oleh admin (V3, opsi A) dan blokir keras K7 di belakang saklar default mati (V4, ya).
Revisi 6 memasukkan hasil audit kelima (S1-S4) dan keputusan komisi (opsi A: satu komisi per NIK, tidak per prodi).
Revisi 5 memasukkan hasil audit keempat (temuan R1-R6) dan keputusan pemilik produk atas pendaftar yang sudah
ber-NIM (opsi A: boleh bila prodi berbeda). Revisi 4 memasukkan audit ketiga (P1-P9) dan masa bayar (opsi A).
Butir yang bertanda **[ASUMSI]** memakai rekomendasi saya dan belum dikonfirmasi; butir **[TERBUKA]** belum diputuskan.

## 0. Perubahan dari Revisi 6

| Temuan audit | Perubahan di dokumen ini |
|---|---|
| V1 periode arsip memblokir unduhan e-KTM/SKL/formulir | Arsip hanya menolak aksi yang **mengubah** data; halaman status, riwayat, dan unduhan tetap boleh (5.2, 5.3, T4) |
| V2 K7(b) memblokir tanpa batas sampai T8 mengisi tenggat | K7(b) tidak berlaku bila `re_registration_deadline` kosong; mengisi tenggat periode berjalan menjadi prasyarat rilis 3 (K7, 5.5, bagian 7) |
| V3 blokir ber-NIM prodi sama tanpa jalur penanganan | **Diputuskan opsi A**: penanda `reapply_allowed` pada pendaftaran lama, diatur admin dengan alasan dan log (5.1, 5.5, T3) |
| V4 blokir K7 langsung menyala di rilis 3 | **Diputuskan ya**: blokir keras di belakang saklar `PMB_ENFORCE_NIK_BLOCK` (default `false`); sebelum dinyalakan hanya tanda "NIK ganda" (5.5, T3, bagian 7) |
| V5 catatan riwayat P6 bertentangan dengan R2 | Diberi catatan "digantikan R2" |

### Riwayat: perubahan Revisi 6 dari Revisi 5

| Temuan audit | Perubahan di dokumen ini |
|---|---|
| S1 pengaman konkurensi `generateNim()` belum ditegaskan | 5.7 dan T5: tetap `withTrashed()` + `lockForUpdate()` di dalam transaksi; test NIM soft-delete tidak dipakai ulang |
| S2 nilai cadangan komisi berbeda untuk dua jenis | 5.6: pendaftaran 50.000, daftar ulang 0 (tidak dibuat bila 0); test T5 |
| S3 komisi untuk alumni yang lanjut jenjang | **Diputuskan opsi A**: tetap satu komisi per NIK seumur hidup, tidak per prodi (K8). Afiliator tidak mendapat komisi kedua bila orang yang sama mendaftar prodi lain |
| S4 backfill T1 mengarsipkan periode non-aktif | Pra-pemeriksaan T1: tinjau periode non-aktif yang punya pendaftaran non-final sebelum migrasi |

### Riwayat: perubahan Revisi 5 dari Revisi 4

| Temuan audit | Perubahan di dokumen ini |
|---|---|
| R1 K7(a) memblokir permanen siapa pun yang ber-NIM, termasuk alumni yang lanjut jenjang | **K7(a) diubah** (keputusan pemilik, opsi A): blokir ber-NIM hanya bila prodi pilihan **sama**; prodi berbeda boleh (K7, 5.5) |
| R2 sinkronisasi satu arah merusak penulis lama yang hanya menulis `is_active` | Aturan hook berdasarkan atribut yang diubah (5.1, T1) |
| R3 tombol "daftar di periode berjalan" tidak bisa langsung ke form (`?prodi=` wajib) | Tombol mengarah ke pilihan prodi di landing atau ke `/daftar?prodi=<prodi terakhir>` bila ditawarkan (T3) |
| R4 salin periode harus ikut menyalin rekening dan WA admin | `duplicate` menyalin kolom bank dan WA; tayang ditolak bila kosong (T2) |
| R5 batas hari masa bayar belum tegas | `payment_open` sampai akhir hari `close_date + payment_grace_days` (WIB) (5.2) |
| R6 nomor pendaftaran acak 4 digit per tahun | Dicatat sebagai batas kapasitas, tidak diubah (bagian 9) |

### Riwayat: perubahan Revisi 4 dari Revisi 3

| Temuan audit | Perubahan di dokumen ini |
|---|---|
| P1 pemeriksaan NIK di langkah 1 dapat diuji tamu (`/daftar` publik) | K7 diperiksa **hanya di `submit()`**; tidak ada umpan balik NIK di langkah pengisian (5.5, T3) |
| P2 K7/K8 dilewati dengan mengubah NIK setelah daftar | K7 diulang saat NIK berubah (pendaftar: diblokir; admin: peringatan); NIK disimpan pada reward saat dibuat (5.5, 5.6, T5) |
| P3 bukti bayar pertama terkunci tepat setelah daftar di hari terakhir | **K2 diubah**: masa bayar `payment_grace_days` (default 3) setelah `close_date` (keputusan pemilik: opsi A) |
| P4 gerbang dokumen/tes sudah ada di alur status | 5.3 dan T4 dipersempit ke empat aksi yang benar-benar baru |
| P5 pindah prodi tidak diperhitungkan | `updateDetail` section `program_jalur` masuk titik baca T3b dan test |
| P6 sinkronisasi dua arah `is_active` rapuh | Satu arah: `status` sumber kebenaran; `scopeActive` dan `isOpen()` pindah ke `status` di T1. **Digantikan R2**: hook berbasis atribut yang diubah (5.1) |
| P7 komisi alumni dan nilai cadangan tidak disebut | 5.6 mempertahankan keduanya |
| P8 T-8 sudah terjawab | Dihapus dari butir terbuka (kartu alumni juga ke `menunggu_konfirmasi`) |
| P9 filter periode afiliator memuat draft/arsip | Urut `open_date` menurun dengan label status (T6) |

## 1. Tujuan

Mengubah "tahun ajaran" dari satu baris periode yang diedit ulang tiap tahun menjadi objek yang dikelola
(dibuat, disalin, ditayangkan, diarsipkan), dengan pendaftar, biaya, kuota, rekening, dan penomoran yang
terikat ke periodenya masing-masing.

## 2. Keputusan

| # | Topik | Keputusan | Sumber |
|---|---|---|---|
| K1 | Daftar lagi | Boleh, dengan syarat K7 | Pemilik produk |
| K2 | Pintu masuk | **Pendaftaran baru** hanya selama `open_date`..`close_date`. **Bukti bayar pertama** (atau kartu alumni) boleh diunggah sampai `close_date + payment_grace_days` (default 3 hari); setelah itu ditolak | Pemilik produk (D1); masa bayar: opsi A |
| K3 | Pendaftar yang sudah melewati tahap bayar | Pendaftaran dengan status **selain `menunggu_pembayaran`** dianggap berjalan dan **boleh lanjut** (dokumen, ubah data, tes, daftar ulang) walaupun konfirmasi staf selesai setelah `close_date`. Penentunya status, bukan berkas, karena staf boleh mengonfirmasi hanya dengan catatan. Alur status yang ada sudah mengunci dokumen/tes sebelum `terdaftar`, jadi K3 tidak butuh gerbang baru | Pemilik produk (D1); N1, P4 |
| K4 | Daftar ulang | Tenggat sendiri per periode (`re_registration_deadline`), **wajib diisi saat periode ditayangkan** | Pemilik produk + audit C1, N4 |
| K5 | Aksi staf | Selalu boleh, termasuk memproses pendaftaran di periode arsip. Yang terkunci pada arsip hanya `period_programs` | Pemilik produk; N11 |
| K6 | Biaya dan kuota | **Berbeda tiap periode** (tabel `period_programs`) | Pemilik produk (D2) |
| K7 | Satu proses aktif per orang (NIK) | Pendaftaran baru atau perubahan NIK oleh pendaftar diblokir hanya bila ada pendaftaran lain dengan NIK sama yang (a) sudah ber-NIM **pada prodi pilihan yang sama** dan tidak bertanda `reapply_allowed` (prodi berbeda boleh, mis. lanjut jenjang), atau (b) berstatus `diterima` / `menunggu_konfirmasi_daftar_ulang` dan masih dalam tenggat daftar ulang (tidak berlaku bila tenggat kosong), atau (c) berstatus `menunggu_tes_tulis` / `menunggu_interview` di periode yang belum arsip. Status sebelum itu hanya memberi tanda peringatan admin. Diperiksa **hanya pada `submit()` dan simpan data diri** (5.5). Blokir keras aktif hanya bila `PMB_ENFORCE_NIK_BLOCK=true`; selain itu hanya tanda "NIK ganda" | **[ASUMSI]** rekomendasi D3; N2, P1, P2; (a) per prodi: keputusan pemilik (R1, opsi A); `reapply_allowed` dan saklar: keputusan pemilik (V3, V4) |
| K8 | Komisi afiliator | Satu komisi pendaftaran dan satu komisi daftar ulang **per orang (NIK)** seumur hidup, tidak per prodi; NIK dicatat pada reward saat dibuat. Pendaftar `alumni` tetap tanpa komisi | **[ASUMSI]** rekomendasi D6; P2, P7; tidak per prodi: keputusan pemilik (S3, opsi A) |
| K9 | Format NIM | Tetap `yy + kode_prodi(5) + jalur(1) + urutan(3)`. Urutan dihitung **per awalan** (`yy+kode_prodi+jalur`), bukan global | Format: kode saat ini. Cakupan urutan: **[ASUMSI]** |
| K10 | Fase periode | Diturunkan dari tanggal; `status` hanya untuk `draft` / `published` / `archived`. Tidak butuh scheduler | Audit C3 |

## 3. Hal yang masih terbuka

| # | Pertanyaan | Default yang dipakai dokumen ini |
|---|---|---|
| T-1 **[TERBUKA]** | Kalender reguler dan non-reguler (`registration_track`) sama atau berbeda? | Satu kalender bersama |
| T-2 **[TERBUKA]** | Perlu tenggat kelengkapan (`completion_deadline`) agar pendaftar yang berhenti tidak menahan kuota selamanya? | Tidak ada tenggat (kolom opsional, tidak dibangun) |
| T-3 **[TERBUKA]** | Lantai urutan NIM `400` (tahun 2026) berlaku global (peninggalan sistem lama) atau per prodi? | Disimpan sebagai `nim_sequence_start` per periode, berlaku per awalan |
| T-4 **[ASUMSI]** | Nomor SKL memakai tahun saat diterbitkan, bukan tahun periode | Tidak diubah |
| T-5 **[ASUMSI]** | Ubah data diri tetap boleh bagi pendaftar yang sudah melewati tahap bayar, sampai `isDetailLocked()` | Mengikuti K3 |
| T-6 **[ASUMSI]** | Seluruh pendaftar yang ada (`period_id = 1`) satu tahun ajaran yang sama | Dipetakan ke satu periode (T8) |
| T-7 **[ASUMSI]** | Ambang blokir K7 pada `menunggu_tes_tulis` (dokumen terverifikasi) | Seperti tertulis di K7 |
| T-8 **[ASUMSI]** | `payment_grace_days = 3` cukup sebagai nilai awal; dapat diubah per periode | Default 3 |

## 4. Kondisi saat ini (titik sentuh kode, terverifikasi)

- Periode aktif dibaca `PmbPeriod::active()->first()` tanpa urutan di `LandingController` (3x),
  `RegistrationController::create` (1x), `RegistrationForm` (3x: `mount`, `programHasSeat`, `submit`),
  `PmbPeriodController::index` (1x). `PmbPeriod::scopeActive` dan `isOpen()` membaca `is_active`.
- `PmbPeriod::isOpen()` hanya dipakai di `RegistrationController::create` dan `RegistrationForm::submit`.
- `/daftar` (`routes/web.php:47`) **publik**; `RegistrationForm::nextStep()` memvalidasi tanpa login, hanya
  `submit()` yang mewajibkan login. Rute tersebut mengalihkan akun yang punya pendaftaran **apa pun**.
- `RegistrationController` pendaftar: 17 query `Registration::where('user_id', Auth::id())`, 15 memakai `latest()`.
- `updateDetail` (`RegistrationController`) memperbolehkan pendaftar mengubah `nik` dan prodi sampai
  `isDetailLocked()` (`nim` terisi, atau status `diterima`/`ditolak`/`menunggu_konfirmasi_daftar_ulang`/
  `daftar_ulang_selesai`). `Admin\RegistrationController::updateData` juga mengubah `nik`.
- `/admin/pengaturan` hanya `index` dan `update`; `is_active` tidak bisa diubah dari UI.
- `Admin\RegistrationController::confirmPayment` menerima `note` sebagai pengganti bukti
  (`required_without`), sehingga pendaftaran `terdaftar` bisa tanpa `payment_proof` maupun `alumni_card_proof`;
  melewati komisi untuk `registration_type = 'alumni'`; memakai nilai cadangan `?? 50000`.
- Alur kartu alumni mengubah `menunggu_pembayaran` menjadi `menunggu_konfirmasi`, sama dengan bukti bayar.
- Biaya, kuota, dan nominal reward ada di tabel **`programs`**, dibaca di:
  `registration/status.blade.php:150`, `re-registration.blade.php`, `livewire/registration-form.blade.php:474`,
  `registration/pdf-skl.blade.php:70-74` (SKL), `landing/*`, `admin/registrations/show.blade.php`,
  `Program::isFull()`, `RegistrationController::updateDetail` (pindah prodi, memeriksa `isFull`),
  `Admin\RegistrationController` (reward, baris 158 dan 434), `Admin\ProgramController`.
- Form prodi memuat editor dinamis `fee_names` / `fee_amounts` untuk `re_registration_fee_details` (JSON).
- `Program::isFull(?int $periodId)` tidak pernah dipanggil tanpa periode.
- `Registration::generateNim()`: urutan dari `MAX(SUBSTRING(nim,-3))` untuk seluruh NIM berawalan `yy`
  (global per tahun, lebar 3 digit, maksimum 999 per tahun), lantai `YEAR_SEQUENCE_START['26'] = 400`.
- `users.referrer_id` melekat pada akun dan dipakai `RegistrationForm::resolveReferrer()`.
- `DatabaseSeeder` dan `PmbPeriodController::index` menulis `is_active` langsung.
- `Registration` memakai `SoftDeletes`.
- Tidak ada unique `user_id + period_id`, tidak ada unique pada `registrations.nim`, tidak ada index `registrations.nik`.
- `nik` hanya divalidasi `digits:16`; tidak diverifikasi pihak ketiga.
- `admin/referrers/index.blade.php:93` memuat `PmbPeriod::all()` untuk filter.

## 5. Desain target

### 5.1 Skema

`pmb_periods` (tambahan):

| Kolom | Tipe | Keterangan |
|---|---|---|
| `status` | string, default `draft` | `draft` / `published` / `archived` |
| `re_registration_deadline` | date, nullable | K4; wajib bila `status=published` (divalidasi aplikasi, bukan constraint, supaya data lama tetap valid) |
| `payment_grace_days` | unsigned tinyint, default 3 | K2 |
| `nim_sequence_start` | unsigned smallint, default 1 | lantai urutan per awalan (menggantikan konstanta) |
| `published_at`, `archived_at` | timestamp, nullable | jejak transisi |

`is_active` dipertahankan sementara; `status` adalah sumber kebenaran. Hook `saving` model memakai
**atribut yang diubah** sebagai penentu (menjaga penulis lama yang hanya menulis `is_active`, termasuk
`PmbPeriodController::index`, `DatabaseSeeder`, dan helper test):
- bila `status` diubah: `is_active = (status = published)`;
- bila hanya `is_active` diubah dan `status` bukan `archived`: `status = published` (true) atau `draft` (false);
- bila keduanya diubah: `status` menang.

`update()` massal (query builder) tidak memicu hook, jadi tidak ada kode yang boleh menulis kedua kolom lewat
query massal. Penulis lama diganti di T1. `scopeActive` dan `isOpen()` membaca `status`.

`period_programs` (baru, K6):

| Kolom | Keterangan |
|---|---|
| `period_id`, `program_id` | unique bersama |
| `is_offered` | prodi ditawarkan pada periode ini |
| `quota` | 0 = tanpa batas |
| `registration_fee`, `re_registration_fee`, `re_registration_fee_details` (json), `re_registration_minimum_payment` | biaya |
| `referral_reward_amount`, `re_registration_reward_amount` | nominal komisi |

Kolom serupa di `programs` ditandai usang dan dipakai sebagai cadangan baca selama transisi (hapus di T9).
Master prodi (nama, kode, jalur, ikon, deskripsi, galeri) tetap di `programs`.

`registrations`:

| Perubahan | Keterangan |
|---|---|
| Index `period_id`, index `nik` | filter periode; pencarian K7 dan K8 |
| Kolom turunan `active_key` (stored generated) `IF(deleted_at IS NULL, CONCAT(user_id,'-',period_id), NULL)` + unique | satu pendaftaran aktif per akun per periode. Cadangan bila hosting menolak generated column: unique biasa + pemeriksaan di aplikasi (audit C6) |
| Unique pada `nim` (nullable) | menjamin NIM tidak ganda; pra-pemeriksaan data wajib |
| Kolom `reapply_allowed` (boolean, default false) dan `reapply_note` (text, nullable) | penanda admin bahwa NIK pendaftaran ber-NIM ini boleh mendaftar lagi pada prodi yang sama (K7(a)); alasan wajib, dan perubahannya dicatat di log pendaftaran (periksa tipe kolom `action` pada `PaymentLog` sebelum menambah jenis aksi) |

`rewards`:

| Perubahan | Keterangan |
|---|---|
| Kolom `nik` (nullable, index) | NIK pendaftar pada saat reward dibuat; dasar K8. Diisi ulang untuk reward lama dari `registrations.nik` (T5) |

### 5.2 Fase periode (diturunkan dari tanggal)

| Fase | Syarat |
|---|---|
| `registration_open` | `status=published` dan `open_date <= hari ini <= close_date` (WIB) |
| `payment_open` | `status=published` dan `open_date <= hari ini <= close_date + payment_grace_days`, inklusif sampai akhir hari terakhir (WIB), sama seperti `isOpen()` |
| `in_progress` | `status=published` dan hari ini setelah `close_date` |
| `re_registration_open` | `status=published` dan (`re_registration_deadline` null atau hari ini <= tenggat) |
| diarsipkan | `status=archived`: hanya baca bagi pendaftar. Aksi yang **mengubah** data ditolak; halaman status, riwayat, dan unduhan (e-KTM, SKL, formulir) tetap boleh. Staf tetap dapat memproses (K5) |

Aturan penayangan:
- Satu periode hanya boleh `published` bila jendela `open_date..close_date`-nya **tidak beririsan** dengan
  periode `published` lain (menjamin satu "periode berjalan" tanpa langkah aktivasi).
- `re_registration_deadline` wajib terisi dan `>= close_date` (K4).

Resolver periode (menggantikan semua `active()->first()` sejak T1):
- `PmbPeriod::current()` = periode `published` yang `registration_open`, atau `null`.
- `PmbPeriod::displayPeriod()` = `current()`; bila tidak ada, periode `published` dengan `open_date`
  terbaru; bila tidak ada, `null`. Dipakai landing, form, dan semua pembaca lama. Periode `in_progress`
  milik tahun lalu tidak lagi mengalahkan periode baru karena urutan `open_date` menurun.

Pengaman arsip: sebelum `archived`, halaman konfirmasi menampilkan jumlah pendaftaran yang belum final
(bukan `daftar_ulang_selesai` / `ditolak`). Arsip ditolak bila ada yang berstatus
`menunggu_konfirmasi_daftar_ulang` kecuali admin mencentang pernyataan eksplisit; semua transisi dicatat
(siapa, kapan) dan dapat dikembalikan ke `published` selama tanggal tidak beririsan.

### 5.3 Aturan gerbang (K2, K4, K5)

Hanya empat aksi pendaftar yang mendapat gerbang baru. Aksi lain sudah dikunci alur status yang ada.

| Aksi | Periode | Syarat |
|---|---|---|
| Daftar baru (`create`, `submit`) | `current()` | `registration_open` dan lolos K7 |
| Upload bukti bayar pertama / kartu alumni | periode milik pendaftaran | status `menunggu_pembayaran` dan `payment_open`. Mengganti bukti saat status sudah `menunggu_konfirmasi` tidak dibatasi tanggal |
| Upload bukti daftar ulang | periode milik pendaftaran | `re_registration_open` dan periode belum `archived` |
| Aksi pendaftar yang **mengubah** data (dokumen, ubah data, tes) | periode milik pendaftaran | periode belum `archived` |
| Aksi staf, halaman status, riwayat, dan semua unduhan (e-KTM, SKL, formulir) | - | selalu boleh, termasuk di periode arsip |

Gerbang memakai `registration->period`, bukan periode berjalan, supaya pendaftar tahun lalu tidak
terpengaruh pergantian periode.

### 5.4 Biaya, kuota, SKL, dan tampilan publik (K6)

- Semua pembacaan biaya/kuota/komisi memakai baris `period_programs` milik **periode pendaftarannya**.
- SKL dan halaman bayar tidak lagi berubah saat harga tahun berikutnya diedit.
- Periode `archived` menjadikan `period_programs`-nya hanya baca.
- Membuat periode baru = **salin matriks** dari periode sebelumnya, lalu diedit.
- Pindah prodi oleh pendaftar (`updateDetail`, section `program_jalur`) memakai kuota `period_programs` periode
  pendaftarannya dan menolak prodi `is_offered = false` pada periode itu.
- Landing, halaman prodi, dan formulir memakai `displayPeriod()`:
  - Prodi dengan `is_offered=false` pada periode itu **disembunyikan** dari formulir dan landing.
  - Bila `displayPeriod()` bukan `registration_open`, landing menampilkan tanggal buka/tutup, tombol daftar
    dinonaktifkan, dan harga tetap tampil dari periode tersebut.
  - Bila `displayPeriod()` `null`, landing menampilkan "Pendaftaran belum dibuka" tanpa harga.
- Pembacaan harga memuat relasi dengan `with()` terfilter periode untuk menghindari N+1 di landing.

### 5.5 Satu proses aktif per NIK (K7)

NIK diketik pendaftar dan tidak diverifikasi, dan `/daftar` publik. Karena itu:
(1) blokir keras dibatasi pada pendaftaran yang **sudah diverifikasi staf**;
(2) pemeriksaan hanya terjadi di tempat yang sudah mewajibkan login, dengan pesan generik.

Pendaftaran baru ditolak bila ada pendaftaran lain (tidak soft-delete) dengan NIK sama dan:

| Kondisi | Hasil |
|---|---|
| Sudah ber-NIM (`daftar_ulang_selesai`) dan prodi pilihan **sama** dengan pendaftaran baru, tanpa `reapply_allowed` | Diblokir; admin dapat membuka lewat penanda `reapply_allowed` pada pendaftaran lama (alasan wajib, tercatat) |
| Sudah ber-NIM, prodi sama, **`reapply_allowed = true`** | Boleh daftar (mis. cuti/DO yang kembali); NIM lama tidak diubah dan pendaftaran baru diberi tanda "ber-NIM sebelumnya" |
| Sudah ber-NIM, prodi pilihan **berbeda** | Boleh daftar (mis. alumni lanjut jenjang); diberi tanda "sudah pernah ber-NIM" untuk admin. Kondisi (b) dan (c) di bawah tetap berlaku bila ada proses lain yang berjalan |
| Status `diterima` / `menunggu_konfirmasi_daftar_ulang`, periodenya belum arsip, tenggat daftar ulang **terisi** dan belum lewat | Diblokir sampai selesai atau tenggat lewat. Tenggat kosong: tidak memblokir (hanya tanda "NIK ganda") |
| Status `menunggu_tes_tulis` / `menunggu_interview` pada periode yang belum arsip | Diblokir (dokumen sudah diverifikasi) |
| Status `menunggu_pembayaran` / `menunggu_konfirmasi` / `terdaftar` | **Tidak diblokir**; pendaftaran baru diberi tanda "NIK ganda" di daftar dan detail admin |
| `ditolak`, periode sudah arsip, tenggat daftar ulang lewat | Boleh daftar lagi |

Ketentuan:
- Pesan penolakan **generik** ("data Anda sudah terdaftar pada proses lain, hubungi admin"), tanpa nama
  prodi, periode, atau status. Kontak diambil dari `admin_whatsapp` periode.
- Titik pemeriksaan: `RegistrationForm::submit()` (sudah wajib login) dan simpan data diri pendaftar
  (`updateDetail`, section `data_diri`, hanya bila `nik` berubah). **Tidak** diperiksa di `nextStep()` atau
  `RegistrationController::create` (tamu dapat mengakses keduanya, sehingga akan menjadi alat enumerasi NIK).
  `create` hanya memeriksa akun (unique per periode).
- Perubahan NIK oleh **admin** (`updateData`) tidak diblokir (K5), tetapi menampilkan peringatan dan menandai
  "NIK ganda".
- Pemeriksaan memakai index `registrations.nik`; rate limiter `public-signup` tetap berlaku.
- **Saklar `PMB_ENFORCE_NIK_BLOCK` (default `false`)**: selama mati, seluruh baris "Diblokir" di atas tidak
  menolak pendaftaran, hanya memberi tanda "NIK ganda" (dan "ber-NIM sebelumnya"). Dinyalakan setelah tanda
  ditinjau admin selama satu siklus pendaftaran. Saklar ini terpisah dari `PMB_ENFORCE_WINDOW` (T4).
- Penanda `reapply_allowed`: tombol admin di detail pendaftar ber-NIM; alasan wajib; hanya `admin`/`operator`.
- Pendaftaran dengan tanda "NIK ganda" diselesaikan admin (hapus yang basi atau tolak). Tidak ada
  penghapusan otomatis.

### 5.6 Komisi per orang (K8)

- `confirmPayment`: komisi `registration` dan penambahan `total_conversions` hanya bila **belum ada** reward
  `registration` berstatus selain `cancelled` untuk NIK yang sama (lintas periode), dibaca dari kolom
  `rewards.nik`, bukan dari NIK pendaftaran saat ini (menutup pergantian NIK).
- `confirmReRegistration`: sama untuk komisi `re_registration`.
- Dipertahankan: tidak ada komisi untuk `registration_type = 'alumni'`, dan nilai cadangan nominal bila
  `period_programs` kosong: komisi pendaftaran **50.000**; komisi daftar ulang **0** (tidak dibuat bila 0).
- Satu komisi per NIK berlaku walau orang yang sama mendaftar prodi lain (R1): komisi kedua tidak dibuat.
- Perbaikan sampingan: `total_conversions` hari ini bertambah **sebelum** pemeriksaan reward ada; pindahkan agar hanya
  bertambah saat reward benar-benar dibuat.

### 5.7 Penomoran (K9)

- `generateNim()`: `MAX(urutan)` hanya untuk NIM berawalan `yy+kode_prodi+jalur`, lantai `nim_sequence_start`
  periode, lebar 3 digit (999 per awalan per tahun). NIM yang sudah terbit tidak diubah.
- Pengaman yang sudah ada **dipertahankan**: `MAX` tetap `withTrashed()` (NIM milik pendaftar yang dihapus
  tidak dipakai ulang), `lockForUpdate()`, dan dipanggil di dalam `DB::transaction`.
- Unique `nim` mencegah duplikat; bentrok menghasilkan pesan jelas dan percobaan ulang di dalam transaksi.
- `restore()` registrasi menolak bila akun sudah punya pendaftaran aktif di periode yang sama (audit C5).

## 6. Tahapan kerja

Setiap tahap berdiri sendiri, ditutup test, dan bisa dirilis terpisah. Estimasi hari kerja satu pengembang,
termasuk test dan perbaikan test yang ada.

### T1. Skema, model, resolver, dan penulis lama (1,25 hari)
- Migrasi: kolom baru `pmb_periods` (termasuk `payment_grace_days`); backfill `status` (`published` bila
  `is_active`, selain itu `archived`), `re_registration_deadline` dibiarkan null untuk data lama (audit C1),
  `nim_sequence_start = 400` untuk periode tahun 2026; kolom `active_key` + unique; index `nik`
  (`period_id` sudah ber-index lewat foreign key). **Status: T1 diimplementasi** (lihat riwayat git/sesi).
- **Pra-pemeriksaan wajib** (jalankan sebelum unique): duplikat `user_id+period_id` aktif, NIM ganda, dan
  periode non-aktif yang punya pendaftaran non-final (backfill akan mengarsipkannya; tinjau dulu).
- `PmbPeriod`: fase (5.2), `current()`, `displayPeriod()`; `scopeActive` dan `isOpen()` pindah ke `status`;
  hook `saving` berbasis atribut yang diubah (5.1).
- **Ganti seluruh pembaca `active()->first()`** (8 titik) dengan `displayPeriod()`; ganti penulis lama.
  Ini yang membuat T2 aman dirilis sebelum T3 (N3).
- Test: model (jendela WIB, batas hari terakhir masa bayar, irisan tanggal, sinkronisasi: hanya `is_active`
  diubah, hanya `status` diubah, keduanya, periode `archived`; `create(['is_active'=>true])` menghasilkan
  `published`; `displayPeriod()` dengan dua periode `published`), migrasi naik/turun, `migrate:rollback` bersih.

### T2. Kelola periode di admin (1,5 hari) — **diimplementasi**
- Rute `admin/periode`: `index`, `create`, `store`, `edit`, `update`, plus `publish`, `archive`, `duplicate`.
- `PmbPeriodController` ditulis ulang: tidak lagi membuat "PMB Baru" otomatis; validasi tanggal
  (`close_date >= open_date`, `re_registration_deadline >= close_date` dan wajib saat tayang,
  `payment_grace_days` 0-30, tidak beririsan saat tayang); `year` terkunci bila sudah ada pendaftar;
  konfirmasi dan log pada tiap transisi.
- `duplicate` menyalin juga `university_bank_*` dan `admin_whatsapp` (dibaca halaman bayar, daftar ulang,
  SKL, dan interview dari periode pendaftaran); tayang ditolak bila rekening atau WA kosong.
- Pengaman arsip (5.2).
- View `admin/periods/index` dan `form` (pola `admin/programs`), sidebar, `/admin/pengaturan` diarahkan ke daftar.
- Test: akses staf, validasi, irisan tanggal, tenggat wajib, pengaman arsip, salin periode (termasuk
  rekening dan WA), tayang ditolak bila rekening kosong.

### T3b. Biaya, kuota, dan komisi per periode (3 hari) — **diimplementasi**
- Migrasi `period_programs` + isi untuk periode yang ada dari nilai `programs`.
- Halaman "Biaya & kuota per periode" (matriks prodi x kolom) + salin dari periode sebelumnya.
  Editor rincian biaya daftar ulang (`fee_names`/`fee_amounts`) dibangun ulang pada halaman ini;
  form prodi tidak lagi mengedit biaya/kuota/komisi.
- Ganti seluruh pembacaan di bagian 4, termasuk `Program::isFull()`, pindah prodi di `updateDetail`, SKL,
  halaman bayar/daftar ulang, landing (aturan 5.4), seeder, dan perhitungan reward. Muat relasi dengan
  `with()` terfilter.
- Test: tiap periode menampilkan biaya sendiri; mengubah harga periode B tidak mengubah SKL periode A;
  kuota per periode; prodi tidak ditawarkan tersembunyi dan ditolak saat pindah prodi; fallback ke `programs`
  selama transisi; jumlah query landing tidak bertambah per prodi.

### T3. Pendaftaran per periode dan resolver (2,5 hari) — **diimplementasi**
- `create()` dan `RegistrationForm`: pakai `PmbPeriod::current()`; unique per periode; K7 **hanya di `submit()`**
  dan di `updateDetail` (section `data_diri`, saat `nik` berubah), dengan pesan generik (5.5).
- Satu resolver `CurrentRegistration` menggantikan 17 query pendaftar: prioritas pendaftaran di periode
  berjalan, jika tidak ada maka yang terbaru, menerima `?periode=` untuk riwayat.
- Landing: aturan 5.4.
- Halaman status: tombol "Daftar di periode berjalan" bila ada `current()` dan akun/NIK lolos K7
  (menutup celah pendaftar `ditolak` tanpa jalan kembali, N10). `create()` mewajibkan `?prodi=`, jadi tombol
  mengarah ke `/daftar?prodi=<prodi terakhir>` bila prodi itu `is_offered` di `current()`, selain itu ke
  daftar prodi di landing. Pemeriksaan K7 yang menyangkut prodi memakai prodi yang dipilih saat `submit()`.
- Tanda "NIK ganda" di daftar dan detail pendaftar admin; peringatan pada `updateData` admin.
- Migrasi `reapply_allowed`/`reapply_note`; tombol dan log penanda `reapply_allowed` di detail admin.
- Seluruh blokir K7 di belakang `PMB_ENFORCE_NIK_BLOCK` (default `false`).
- Test: akun tahun lalu dapat daftar lagi (K1); blokir K7 pada tiap baris 5.5 termasuk yang tidak diblokir;
  pesan generik tidak memuat data pendaftaran lain; **`nextStep` tamu tidak membocorkan status NIK**;
  mengubah NIK menjadi NIK terblokir ditolak; ber-NIM + prodi sama diblokir, ber-NIM + prodi berbeda boleh
  dan bertanda; ber-NIM + prodi sama + `reapply_allowed` boleh; tenggat kosong tidak memblokir K7(b);
  saklar mati hanya menandai, saklar hidup memblokir; tombol "Daftar di periode berjalan" memilih tujuan yang
  benar; dua pendaftaran tidak saling mengganggu; unique `active_key` menolak duplikat.

### T5. Penomoran dan komisi (1,25 hari) — **diimplementasi**
- `generateNim()` per awalan (5.7), unique `nim`, penyesuaian `registrations:backfill-numbers`.
- Migrasi `rewards.nik` + isi ulang; aturan komisi per orang (5.6) di `confirmPayment` dan
  `confirmReRegistration`; perbaiki penghitungan `total_conversions`; sesuaikan `referrers:recount`.
- Test: NIM per awalan, dua prodi berurutan sendiri-sendiri; satu komisi per NIK lintas periode;
  ganti NIK tidak membuka komisi kedua; ber-NIM + prodi lain tidak menghasilkan komisi kedua; alumni tetap
  tanpa komisi; nilai cadangan 50.000 (pendaftaran) dan 0 (daftar ulang, tidak dibuat); NIM soft-delete tidak
  dipakai ulang; `total_conversions` benar.

### T4. Gerbang pintu masuk (0,5 hari) — **diimplementasi**
- Guard `EnsureRegistrationWindow` (periode milik pendaftaran) untuk empat aksi di 5.3; aksi lain yang
  mengubah data hanya guard `archived`. **Unduhan dan halaman status tidak digerbangi.** Tidak menulis ulang
  gerbang status yang sudah ada.
- Saklar `PMB_ENFORCE_WINDOW` (default `false`) pada konfigurasi; diaktifkan setelah T8 selesai (N9).
- Banner di halaman status: batas masa pendaftaran, masa bayar, dan tenggat daftar ulang.
- Test: setiap baris tabel 5.3, termasuk pendaftar yang dikonfirmasi staf (tanpa bukti, hanya catatan)
  setelah `close_date`, unggah bukti di hari ke-3 dan hari ke-4 masa bayar, kartu alumni, saklar mati/hidup,
  dan **unduh e-KTM, SKL, formulir di periode arsip tetap berhasil**.

### T6. Filter periode di admin (1 hari) — **diimplementasi**
- Pemilih periode global di header admin (default `displayPeriod()`, disimpan di sesi).
- Terapkan ke dashboard, daftar pendaftar, rekap per prodi + ekspornya, ekspor pendaftar/afiliasi.
- Filter periode di halaman afiliator: urut `open_date` menurun dengan label status.

### T7. Riwayat pendaftaran untuk pendaftar (0,5 hari) — **diimplementasi**
- Pemilih periode dan daftar "pendaftaran saya" di halaman status; unduhan mengikuti pendaftaran terpilih.
- Banner bila ada langkah tertunda di pendaftaran periode lain (audit D7).

### T8. Migrasi data lama dan deploy (0,5 hari) — **diimplementasi (perangkat dan dokumen; pengisian data produksi ada di tangan Anda)**
- Konfirmasi T-6; command `periods:assign` (default `--dry-run`, `--apply --acted-by=ID`) bila perlu memecah.
- **Isi `re_registration_deadline` periode berjalan** (prasyarat K4/N4), tinjau `nim_sequence_start` dan
  `payment_grace_days`, isi matriks `period_programs`, lalu aktifkan `PMB_ENFORCE_WINDOW`.
- Perbarui `DEV_REPORT.md` dan daftar periksa deploy.

### T9. Pembersihan (0,5 hari, opsional)
- Hapus `is_active`, kolom biaya/kuota usang di `programs`, konstanta `YEAR_SEQUENCE_START`, dan saklar gerbang.

**Total perkiraan: 10-12 hari kerja** (T1-T8, jumlah per tahap 12 hari). Perkiraan kasar.

## 7. Urutan rilis dan rollback

| Rilis | Isi | Rollback |
|---|---|---|
| 1 | **T1 + T2** (T1 sudah mengganti semua pembaca periode) | Migrasi `down`; kolom baru tidak dipakai kode lama |
| 2 | **T3b** (nilai awal identik dengan sekarang) | Kembalikan kode; `programs` masih berisi nilai asli (cadangan baca) |
| 3 | **T3 + T5 + T7**, dengan `PMB_ENFORCE_NIK_BLOCK=false` (hanya tanda "NIK ganda"). **Prasyarat:** `re_registration_deadline` periode berjalan sudah terisi | Kembalikan kode; unique `active_key` dan `nim` dilepas lewat migrasi `down`; saklar tetap mati |
| 4 | **T4** di balik `PMB_ENFORCE_WINDOW=false`; nyalakan `PMB_ENFORCE_NIK_BLOCK` setelah tanda "NIK ganda" ditinjau satu siklus | Matikan saklar; tidak perlu rilis ulang |
| 5 | **T6** | Kembalikan kode |
| 6 | **T8**, lalu **T9** | T8 memakai `--dry-run` lebih dulu; T9 tidak dapat dibatalkan, jadi dirilis terakhir dan hanya setelah satu siklus stabil |

Semua migrasi bersifat penambahan dan reversibel sampai T9. Sebelum tiap rilis: cadangan basis data,
`php artisan test`, dan pra-pemeriksaan data (T1).

## 8. Strategi test

- Berkas baru di `tests/Feature/Pmb/`: `PeriodLifecycleTest`, `PeriodProgramPricingTest`,
  `MultiPeriodRegistrationTest`, `ActiveProcessPerNikTest`, `RegistrationWindowGateTest`,
  `CommissionPerPersonTest`, `PeriodScopedAdminTest`.
- `BuildsPmbData::period()` menerima status dan tanggal; tambahkan `periodProgram()` dan helper pendaftar
  dengan NIK tertentu.
- Test yang ikut berubah: `PublicRegistrationFormTest` (periode tutup, kuota), `PlatformSecurityAndConfigTest`
  (rute pengaturan periode), `NumberingAndBackfillTest` (urutan per awalan), `AdminActionsTest` (komisi),
  `StudentFlowTest` (harga/kuota dari `period_programs`).
- Seluruh suite dijalankan di tiap tahap (saat ini 113 test, ±75 detik).

## 9. Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| `/daftar` jalur kritis konversi | T3 dirilis terpisah, diuji di staging dengan test Livewire yang ada |
| Unique `active_key` atau `nim` gagal pada data ganda | Pra-pemeriksaan T1; selesaikan duplikat sebelum migrasi |
| Generated column ditolak di hosting | Cadangan: unique biasa + pemeriksaan aplikasi; uji di staging |
| Harga berubah untuk pendaftar lama | T3b: `period_programs` dikunci hanya baca saat arsip |
| Pendaftar lama "hilang" karena resolver | Pemilih periode + banner tindakan tertunda (T7) |
| Pendaftar berhenti di tengah menahan kuota | T-2: tenggat kelengkapan opsional; admin dapat menghapus pendaftaran basi |
| Admin salah menayangkan/mengarsipkan periode | Konfirmasi UI + log (siapa, kapan) + pengaman arsip + dapat dikembalikan |
| Pemblokiran NIK keliru (kasus nyata: pindah prodi) | Blokir keras hanya setelah verifikasi dokumen; pesan jelas; jalur penanganan manual admin |
| NIK orang lain dipakai untuk menghalangi atau menguji | Blokir tidak berlaku pada status awal; pemeriksaan hanya di `submit()` terautentikasi; pesan generik |
| Pergantian NIK untuk melewati K7/K8 | Pemeriksaan ulang saat NIK berubah; NIK dibekukan pada reward |
| Pendaftar mepet batas terkunci saat membayar | Masa bayar `payment_grace_days` (K2) |
| Gerbang memutus pendaftar yang dikonfirmasi staf tanpa bukti | Gerbang berbasis status (K3), test khusus di T4 |
| Pembaca periode lama memilih periode salah setelah periode kedua tayang | `displayPeriod()` di T1, test dua periode `published` |
| Alumni ber-NIM terblokir saat lanjut jenjang | K7(a) hanya untuk prodi sama; prodi berbeda boleh dan bertanda |
| Penulis lama `is_active` menghasilkan periode `draft` tanpa sengaja | Hook berbasis atribut yang diubah + test T1 |
| Nomor pendaftaran acak 4 digit per tahun (kapasitas 9.000, loop cek unik) | Cukup untuk volume sekarang; ditinjau bila satu `year` dipakai banyak periode |
| Mahasiswa tidak bisa mengunduh e-KTM/SKL setelah periode diarsipkan | Arsip hanya menolak aksi yang mengubah data; unduhan tidak digerbangi + test T4 |
| Blokir K7 menolak pendaftar sah di jalur konversi utama | Saklar `PMB_ENFORCE_NIK_BLOCK` default mati; tanda "NIK ganda" ditinjau dulu; penanda `reapply_allowed` untuk kasus ber-NIM |
| Pendaftar `diterima` 2026 terblokir selamanya karena tenggat kosong | K7(b) tidak berlaku bila tenggat kosong; tenggat terisi sebagai prasyarat rilis 3 |

## 10. Di luar cakupan

- Gelombang di dalam satu periode dengan biaya berbeda (selain biaya per periode).
- Tombol "buka kunci per pendaftar" dan tenggat kelengkapan (T-2).
- Notifikasi otomatis (email/WA), jadwal tes/interview, kartu peserta.
- Pra-isi formulir dari pendaftaran sebelumnya.
- Verifikasi NIK ke pihak ketiga (menjadi alasan K7 dibuat longgar pada status awal).

## 11. Definisi selesai

- Admin membuat, menyalin, menayangkan, dan mengarsipkan periode lewat UI; biaya, kuota, dan masa bayar
  diatur per periode.
- Akun yang mendaftar tahun lalu dapat mendaftar lagi sesuai K7 dan melihat kedua pendaftarannya.
- Setelah `close_date`, pendaftaran baru ditolak; bukti bayar pertama diterima sampai `close_date +
  payment_grace_days`; pendaftar yang sudah melewati tahap bayar (termasuk yang dikonfirmasi staf dengan
  catatan) tetap bisa lanjut sampai tenggat daftar ulang; aksi staf tetap jalan.
- Mengganti tahun ajaran tidak mengubah SKL, harga, NIM, kuota, atau rekening periode sebelumnya.
- Mahasiswa tetap dapat mengunduh e-KTM, SKL, dan formulir dari periode yang sudah diarsipkan.
- Admin dapat membuka blokir ber-NIM prodi sama lewat `reapply_allowed`, dan blokir K7 hanya aktif setelah
  saklar `PMB_ENFORCE_NIK_BLOCK` dinyalakan.
- Satu orang tidak mendapat NIM kedua pada prodi yang sama dan tidak menghasilkan komisi ganda, termasuk
  setelah mengganti NIK; yang sudah ber-NIM boleh mendaftar ke prodi lain.
- Tamu tidak dapat mengetahui status NIK seseorang lewat formulir pendaftaran.
- Dua periode `published` bersamaan tidak membuat landing atau formulir memilih periode yang salah.
- Seluruh suite hijau, ditambah test untuk setiap aturan pada bagian 5.
