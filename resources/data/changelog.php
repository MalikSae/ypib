<?php

/*
|--------------------------------------------------------------------------
| Changelog per deploy
|--------------------------------------------------------------------------
|
| Ditampilkan di /admin/changelog. Tambahkan entri baru PALING ATAS setiap kali deploy
| produksi. Tulis dari sudut pandang pengguna (admin, affiliator, pendaftar), bukan teknis.
|
*/

return [
    [
        'date'    => '2026-11-18',
        'title'   => 'Hasil event, filter langsung, dan tampilan admin yang lebih seragam',
        'commit'  => '5dd920f',
        'sections' => [
            'Event dan Kampanye' => [
                'Setiap event menampilkan jumlah prospek, pendaftar yang sudah bayar, dan yang sudah daftar ulang.',
                'Satu pendaftar dihitung untuk satu event saja: event dari link daftar yang dipakai, atau event pertama yang dihadiri bila daftar tanpa link.',
                'Prospek yang ditandai curang tidak ikut dihitung.',
                'Filter jenis (event kampus atau kampanye online) dan periode, serta pilihan urutan.',
            ],
            'Kelola Prospek' => [
                'Tab status lengkap dengan jumlah, untuk tindak lanjut harian.',
                'Prospek yang paling lama menunggu dihubungi tampil paling atas.',
                'Di Data Afiliasi, kolom Prospek membuka daftar prospek milik affiliator tersebut.',
            ],
            'Filter langsung tanpa tombol Terapkan' => [
                'Daftar langsung tersaring saat mengetik atau memilih filter, tanpa memuat ulang halaman.',
                'Berlaku di Data Pendaftar, Arsip Pendaftar, Kelola Prospek, Event dan Kampanye, Data Afiliasi, Komisi, Program Studi, Bank Soal, Akun Staf, dan riwayat interview Panitia.',
                'Filter tetap tersimpan di alamat halaman, jadi link yang dibagikan menampilkan hasil yang sama.',
                'Filter yang sedang dipakai diberi warna biru muda, dan tombol "Hapus filter" mengosongkan semuanya.',
                'Data Pendaftar: status dipilih dari satu dropdown berisi jumlah per status.',
                'Ekspor Excel tetap mengikuti filter yang sedang aktif.',
            ],
            'Tampilan admin' => [
                'Dropdown baru yang bisa dipakai dengan keyboard dan tidak terpotong di dalam card atau jendela pop-up.',
                'Tabel maksimal dua baris per sel; teks panjang dipotong, teks lengkap muncul saat kursor diarahkan.',
                'Tombol, kotak isian, card, kotak centang, dan pilihan file diseragamkan di semua halaman admin.',
                'Status aktif/tampil di form kampus, soal, fasilitas, partner, prodi, event, dan harga kini berupa saklar on/off.',
                'Tab di halaman Komisi bisa dipindah dengan tombol panah keyboard.',
                'Pesan saat daftar kosong dibuat seragam dan menjelaskan langkah berikutnya.',
            ],
            'Perbaikan' => [
                'Nama affiliator dari kampus lain tidak lagi terlihat di filter Kelola Prospek.',
                'Pencarian Data Afiliasi tidak lagi mengabaikan filter lain, dan ekspornya ikut memakai kata kunci pencarian.',
                'Jumlah di tab Detail Transaksi Komisi kini menampilkan total, bukan jumlah baris di halaman itu saja.',
                'Data Pendaftar tidak lagi bergeser ke samping di layar laptop 1366px.',
            ],
        ],
    ],
    [
        'date'    => '2026-11-08',
        'title'   => 'Halaman changelog',
        'commit'  => null,
        'sections' => [
            'Fitur baru' => [
                'Menu Changelog di dashboard admin (bagian Pengaturan) berisi catatan update dan perbaikan di setiap deploy.',
            ],
        ],
    ],
    [
        'date'    => '2026-11-08',
        'title'   => 'Form minat, kelola prospek, dan penyeragaman tampilan admin',
        'commit'  => '121e1be',
        'sections' => [
            'Fitur baru: Form Minat & Kelola Prospek' => [
                'Form minat publik untuk calon yang belum siap daftar: nama, pendidikan terakhir, sekolah/kampus, WhatsApp, dan prodi (boleh "Belum menentukan").',
                'Setelah kirim, siswa langsung terhubung ke WhatsApp admin kampus dengan pesan berisi kode minat dan link daftar.',
                'Link daftar pribadi: nama dan WhatsApp terkunci, sekali pakai, berlaku sampai periode PMB ditutup.',
                'Potongan daftar ulang per event/kampanye, dipotong otomatis dari tagihan daftar ulang setelah siswa diterima.',
                'Dua jenis form: Event kampus (sosialisasi, tanpa affiliator) dan Kampanye online (dibagikan affiliator).',
                'Menu Kelola Prospek: filter, detail prospek, catatan, riwayat kontak, Chat WA, dan kirim ulang link daftar.',
                'Menu Event dan Kampanye: buat dan edit, salin link, unduh QR.',
            ],
            'Affiliator' => [
                'Prospek dari link kampanye otomatis milik affiliator tersebut dan tidak berpindah ke affiliator lain.',
                'Komisi mengikuti aturan yang sudah ada: komisi pendaftaran saat bayar dikonfirmasi, komisi daftar ulang saat daftar ulang dikonfirmasi.',
                'Dashboard: semua link (Link Umum, per kampus, kampanye) digabung dalam satu accordion; Link Umum terbuka otomatis.',
                'Daftar prospek hanya bisa dilihat, WhatsApp disamarkan sampai prospek mendaftar.',
            ],
            'Pendaftaran' => [
                'Buat akun lewat link form minat: nama dan WhatsApp mengikuti data form minat, dengan opsi "Bukan kamu? Daftar tanpa data ini".',
                'Validasi tahun lulus: 4 digit angka, antara 1950 sampai tahun depan.',
            ],
            'Tampilan admin' => [
                'Tombol dan input diseragamkan (sudut, fokus keyboard, teks 16px di HP).',
                'Form admin di HP hanya satu lapis card; header form (tombol kembali dan judul) satu baris.',
                'Ikon diseragamkan ke Heroicons.',
                'Detail pendaftar: tombol Upload dan Ganti dokumen tidak lagi keluar layar di HP.',
            ],
            'Perbaikan' => [
                'Field nama dan WhatsApp terkunci di form daftar sempat tampil kosong.',
                'Tombol Salin tetap berfungsi walau browser tidak mendukung Clipboard API.',
            ],
        ],
    ],
];
