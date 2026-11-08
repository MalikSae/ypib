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
