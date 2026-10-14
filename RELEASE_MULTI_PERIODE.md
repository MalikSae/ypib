# Rilis Produksi: Multi-Periode PMB

Panduan satu kali untuk merilis fitur multi-periode (T1–T8) beserta audit performa. Pelengkap `DEV_REPORT.md` bagian 10.G dan `PLAN_MULTI_PERIODE.md`. Langkah bertanda **[DATA]** mengubah data produksi.

## 1. Ringkasan

| Hal | Isi |
|---|---|
| Migrasi baru (5) | `add_multi_period_columns`, `create_period_programs_table`, `add_reapply_columns_to_registrations`, `add_nik_to_rewards_table`, `add_performance_indexes` |
| Sifat migrasi | Hanya penambahan (kolom, tabel, indeks) dan pengisian awal. Semuanya reversibel. Tidak ada kolom lama yang dihapus |
| Saklar baru | `PMB_ENFORCE_WINDOW`, `PMB_ENFORCE_NIK_BLOCK`. Keduanya mati bawaan |
| Command baru | `periods:precheck`, `periods:readiness`, `periods:assign` |
| Aset frontend | `public/build` harus ikut di-commit (CSS baru: kelas responsif halaman periode) |
| Waktu migrasi | ±3 detik pada data uji (15 pendaftar). Pembuatan indeks pada tabel besar lebih lama; pada tabel puluhan ribu baris saya perkirakan beberapa detik, tetapi belum diukur di server produksi. Gunakan `php artisan down` selama migrasi |

## 2. Yang sudah diverifikasi sebelum rilis

Dijalankan terhadap salinan data dev yang diambil **sebelum** T1 (15 pendaftar, 8 komisi, 9 prodi), jalur peningkatan yang sama dengan produksi:

- `periods:precheck` bersih; lima migrasi berjalan tanpa galat.
- Jumlah baris tidak berubah (15 pendaftar, 8 komisi). `period_programs` terisi 9 baris (1 periode x 9 prodi). Semua komisi mendapat `nik`. `active_key` terisi untuk 15 pendaftar (2 yang soft-delete sudah ikut dihitung).
- Periode lama otomatis `published`, `nim_sequence_start = 400`, `payment_grace_days = 3`, `re_registration_deadline` kosong.
- `migrate:rollback --step=5` lalu `migrate` ulang bersih.
- `config:cache`, `route:cache`, `view:cache` berhasil.
- Suite otomatis: **261 test lulus**.
- Halaman baru diperiksa di browser (admin dan pendaftar, layar sempit); satu cacat editor rincian biaya sudah diperbaiki.
- Performa pada 30.000 pendaftar: daftar/dashboard 370 ms menjadi 3 ms; rekap 92 query menjadi 3.

**Belum terverifikasi:** perilaku di hosting produksi (MySQL/MariaDB versi Hostinger), perilaku blokir NIK pada data pendaftar nyata, dan tampilan pemilih periode di layar desktop lebar.

**Ketahanan migrasi pertama:** langkah-langkahnya idempoten (menjalankan ulang pada skema yang sudah sebagian/seluruhnya jadi tidak gagal; teruji). Bila hosting menolak kolom turunan `registrations.active_key`, migrasi **tidak gagal**: kolom itu dilewati dengan baris `PERINGATAN` di keluaran dan di `storage/logs`. Aturan satu pendaftaran per akun per periode tetap ditegakkan aplikasi; hanya lapisan pengaman unique database yang hilang. Jalur penolakan ini diuji lewat pembacaan kode, bukan injeksi kegagalan.

## 3. Sebelum rilis (H-1)

1. **Commit aset build.** Bundel CSS yang ada di repo belum memuat kelas baru (mis. `lg:max-w-sm`, `lg:justify-end`). Hasil `npm run build` sudah ada di working tree: `public/build/manifest.json` berubah, `app-D43BrlW8.css` diganti `app-BQmpow4e.css`. Tanpa ini kartu periode di produksi tampil tanpa tata letak responsif.
2. **Siapkan nilai data** (diperlukan di langkah 5.4 dan 5.5). Keputusan yang hanya bisa Anda ambil:
   - tanggal buka, tutup, dan **tenggat daftar ulang** periode berjalan (data dev: periode berakhir 2026-09-30, sudah lewat);
   - nomor rekening, atas nama, dan WhatsApp admin periode;
   - `kode_prodi` untuk prodi aktif yang masih kosong (data dev: 5 dari 9). Tanpa kode, konfirmasi daftar ulang ditolak.
3. **Cadangkan** database dan folder berkas privat (`storage/app/private`). Simpan salinan di luar server.
4. Pastikan `composer.lock` sama dengan yang diuji (`composer install --no-dev --optimize-autoloader` tidak menambah paket baru pada rilis ini).
5. Beri tahu staf: halaman admin kini memilih periode di header (bawaan periode berjalan), dan biaya/kuota pindah ke `Periode PMB > Biaya & Kuota` (lihat bagian 8).

## 4. Langkah rilis (Hostinger, memakai skrip deploy yang sudah ada)

Server, folder, dan skrip mengikuti `DEPLOY_YPIB.md` (di luar repo; berisi alamat SSH dan folder). Ringkas: staging di `~/domains/univypib.ac.id/public_html/dev` (skrip `~/deploy.sh`, DB `u538956564_pmbaff`), produksi di `~/domains/univypib.ac.id/public_html/daftar` (skrip `~/deploy-production.sh`, DB `u538956564_daftaraff`). Kode datang dari `git pull origin master`; PHP diatur `export PATH=/opt/alt/php83/usr/bin:$PATH`.

### 4.1 Gladi di staging dengan salinan data produksi (wajib)

Menguji migrasi (termasuk kolom turunan `active_key`) pada versi MySQL/MariaDB Hostinger dan pada data nyata, tanpa risiko.

1. Cadangkan DB produksi: `mysqldump -u u538956564_daftaraff -p u538956564_daftaraff > ~/backup_daftaraff_<tanggal>.sql`.
2. **Timpa DB staging** dengan salinan itu (menghapus data staging): `mysql -u u538956564_pmbaff -p u538956564_pmbaff < ~/backup_daftaraff_<tanggal>.sql`. Arah ini hanya produksi ke staging, tidak pernah sebaliknya.
3. Jalankan `bash ~/deploy.sh`. Perhatikan keluaran `migrate`: lima baris DONE dan **tidak ada baris `PERINGATAN`**. Jika ada `PERINGATAN`, kolom turunan ditolak hosting; rilis tetap aman (lihat bagian 2).
4. Di folder staging: `php artisan periods:readiness`, lalu jalankan smoke test (bagian 6) di `dev.univypib.ac.id`.

### 4.2 Produksi

1. **Cadangan segar** (DB dan berkas): `mysqldump` DB produksi ke `~/backup_daftaraff_<tanggal-jam>.sql`; `tar -czf ~/backup_files_<tanggal-jam>.tar.gz storage/app public/storage` dari folder produksi.
2. **Pra-periksa**, di folder produksi: `git status --short` (harus bersih; berkas `public/build` yang pernah di-`scp` dan berbeda dari commit akan menggagalkan `git pull`), `git fetch origin`, lalu `git log --oneline HEAD..origin/master | wc -l` (jumlah commit yang akan masuk; rilis ini membawa seluruh perubahan sejak deploy terakhir, bukan hanya multi-periode).
3. **Cek apakah produksi sudah memiliki rilis keamanan berkas privat** (`1be5a09`): `git merge-base --is-ancestor 1be5a09 HEAD && echo sudah || echo belum`. Jika `belum`, setelah deploy wajib `php artisan files:privatize --dry-run` lalu `php artisan files:privatize` (README).
4. Tambahkan ke `.env` produksi bila belum ada: `SESSION_SECURE_COOKIE=true`, `PMB_ENFORCE_WINDOW=false`, `PMB_ENFORCE_NIK_BLOCK=false`.
5. `php artisan down --retry=60`, lalu `bash ~/deploy-production.sh` (git pull, composer install, migrate, config/route cache), lalu `php artisan up`.
6. `php artisan periods:readiness` dan smoke test (bagian 6) di `daftar.univypib.ac.id`.

Catatan skrip: `deploy-production.sh` tidak memakai maintenance mode dan tidak menjalankan `periods:precheck`. Migrasi pertama sudah memeriksa duplikat sendiri sebelum mengubah skema dan berhenti dengan pesan jelas, jadi kekurangan itu tidak berbahaya, hanya kurang rapi.

Jika `periods:precheck`/migrasi melaporkan pendaftaran ganda per akun+periode atau NIM ganda, **hentikan**, selesaikan duplikatnya secara manual, lalu ulangi.

## 5. Setelah migrasi (saklar masih mati)

1. **Smoke test** (bagian 6).
2. Buka `/admin/periode` dan pastikan periode lama muncul dengan status Berjalan/tayang.
3. **[DATA]** Pada periode berjalan klik **Edit**: isi tenggat daftar ulang, rekening, WhatsApp; tinjau masa bayar (bawaan 3 hari) dan nomor urut NIM pertama.
4. **[DATA]** Buka **Biaya & Kuota**: tinjau tiap prodi (ditawarkan, kuota, biaya, komisi, rincian daftar ulang). Nilainya berasal dari master prodi.
5. **[DATA]** Isi `kode_prodi` prodi yang kosong di `Kelola Program Studi`.
6. Jalankan:

```bash
php artisan periods:readiness
```

   Harus **tanpa temuan GAGAL**. Tinjau semua PERIKSA.
7. Jika perlu memecah pendaftar lama per tahun ajaran (jika tidak, lewati): `php artisan periods:assign --to=<ID> --from=<ID> [--created-until=YYYY-MM-DD]` untuk simulasi, lalu `--apply --acted-by=<ID admin>`.
8. Untuk membuka pendaftaran tahun ajaran berikutnya: **Salin** periode, sesuaikan tanggal dan biaya, lalu **Tayangkan**. Penayangan menolak bila tenggat, rekening, WhatsApp, atau jendela tanggal bermasalah.

## 6. Smoke test setelah rilis

Dengan saklar mati, semua alur lama harus berjalan seperti sebelumnya.

| Cek | Hasil yang diharapkan |
|---|---|
| `/` landing | banner periode benar, "Biaya Pendaftaran / Mulai Rp ..." sesuai data, kartu prodi menampilkan biaya |
| `/prodi/{slug}` | tombol daftar aktif hanya saat periode terbuka |
| Daftar akun lalu wizard `/daftar?prodi=` | pendaftaran terkirim, status `menunggu_pembayaran` |
| `/pendaftaran/status` | nama periode benar, nominal pembayaran dari periode |
| Unggah bukti, konfirmasi admin, dokumen, tes, interview, daftar ulang | alur utuh, NIM terbit di akhir |
| Admin `/admin/dashboard`, `/admin/pendaftar`, `/admin/pendaftar/rekap` | angka mengikuti periode di header; "Semua periode" tersedia |
| Ekspor pendaftar (XLSX dan CSV) | hanya periode terpilih |
| `/admin/periode`, `/admin/periode/{id}/biaya` | tampil; simpan biaya berhasil |
| Unduh formulir, SKL, e-KTM | berfungsi |

## 7. Menyalakan saklar (bertahap)

Ubah `.env`, lalu `php artisan config:cache`. Nyalakan **terpisah** dan amati beberapa hari.

| Saklar | Syarat | Dampak jika dinyalakan | Cara mematikan |
|---|---|---|---|
| `PMB_ENFORCE_WINDOW=true` | `periods:readiness` tanpa GAGAL; tenggat daftar ulang dan masa bayar terisi | Unggah bukti bayar pertama ditolak di luar masa bayar; bukti daftar ulang ditolak setelah tenggat; ubah data/dokumen/tes ditolak di periode arsip | set `false` + `config:cache` |
| `PMB_ENFORCE_NIK_BLOCK=true` | satu siklus pendaftaran berjalan; tanda **NIK ganda** di `/admin/pendaftar` sudah ditinjau | Pendaftaran baru dengan NIK yang sudah terverifikasi/ber-NIM ditolak dengan pesan generik | set `false` + `config:cache` |

Indikator yang diamati setelah menyalakan: keluhan pendaftar tidak bisa unggah bukti; jumlah pesan penolakan generik; admin memakai tombol **Izinkan Daftar Lagi** pada kasus ber-NIM yang sah.

## 8. Perubahan yang terlihat staf dan pendaftar

- **Admin:** header memiliki pemilih periode (bawaan periode berjalan). Dashboard, daftar, rekap, dan ekspor mengikuti pilihan itu. Menu "Pengaturan PMB" menjadi **Periode PMB**.
- **Biaya, kuota, komisi** tidak lagi diedit di form prodi; pindah ke `Biaya & Kuota` per periode. Prodi baru tidak ditawarkan sampai biayanya diatur di sana.
- **Komisi:** satu komisi pendaftaran dan satu komisi daftar ulang **per orang (NIK)** seumur hidup, termasuk jika mendaftar lagi di periode atau prodi lain.
- **NIM:** urutan dihitung per kombinasi tahun + kode prodi + jalur, bukan satu urutan global per tahun. NIM lama tidak berubah.
- **Pendaftar:** dapat mendaftar lagi di periode berikutnya; halaman status menampilkan riwayat dan tombol "Daftar di Periode Ini".

## 9. Rollback

| Situasi | Tindakan |
|---|---|
| Gerbang atau blokir NIK menolak pendaftar sah | Matikan saklar terkait di `.env` dan `config:cache`. Tidak perlu rilis ulang |
| Halaman baru bermasalah, data aman | Kembalikan commit sebelumnya (`git checkout <commit>`), `composer install`, `php artisan optimize:clear`. Kolom baru tetap ada dan tidak dipakai kode lama |
| Migrasi bermasalah | `php artisan migrate:rollback --step=5 --force`. Reversibel; teruji pada salinan data |
| Data rusak | Pulihkan dari cadangan database. Perubahan command tercatat di `payment_logs` (`period_reassigned`, `reapply_*`) |

Catatan rollback kode: kode lama membaca `programs.quota/registration_fee/...` dan `pmb_periods.is_active`. Keduanya sengaja tidak dihapus dan tetap sinkron selama pengelolaan periode memakai UI baru. Setelah rollback kode, perubahan biaya yang dibuat di `Biaya & Kuota` **tidak** muncul di kode lama; sesuaikan master prodi bila perlu.

## 10. Risiko yang diketahui

1. **Blokir NIK belum diuji pada data nyata.** Itu alasan saklarnya mati dan disarankan menyala setelah satu siklus tinjauan.
2. **Mengubah daftar prodi aktif sesudah rilis:** prodi baru dibuat tidak ditawarkan; bila admin lupa mengatur biayanya, prodi tidak muncul di form.
3. **Ekspor CSV besar** memakan ±0,5 ms per baris (30.000 baris ±15 detik); pastikan `max_execution_time` hosting memadai.
4. **Pencarian pendaftar** dengan `LIKE '%...%'` tetap memindai tabel (±250 ms di 30.000 baris).
5. **Tenggat kosong:** periode lama tanpa tenggat daftar ulang tidak membatasi daftar ulang dan tidak memblokir K7(b). Isi sebelum menyalakan saklar.
6. **T9 (pembersihan)** sengaja belum dikerjakan; kolom cadangan masih ada.

## 11. Keputusan terbuka (default sudah dipakai)

- T-1: kalender reguler dan non-reguler (default: satu kalender bersama).
- T-2: tenggat kelengkapan bagi pendaftar yang berhenti di tengah (default: tidak ada).
- T-3: lantai urutan NIM 400 global atau per prodi (default: per awalan, dari `nim_sequence_start` periode).
