<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blokir satu proses aktif per NIK (PLAN_MULTI_PERIODE.md, K7)
    |--------------------------------------------------------------------------
    | Selama false, pendaftaran dengan NIK yang sama tidak ditolak; admin hanya
    | melihat tanda "NIK ganda". Nyalakan setelah tanda tersebut ditinjau satu
    | siklus pendaftaran.
    */
    'enforce_nik_block' => (bool) env('PMB_ENFORCE_NIK_BLOCK', false),

    /*
    |--------------------------------------------------------------------------
    | Gerbang jendela pendaftaran (PLAN_MULTI_PERIODE.md T4, K2/K4)
    |--------------------------------------------------------------------------
    | Selama false, tidak ada penolakan berbasis tanggal atau arsip pada aksi
    | pendaftar (hanya pemberitahuan di halaman status). Nyalakan setelah
    | tenggat daftar ulang dan masa bayar periode berjalan terisi (T8).
    */
    'enforce_window' => (bool) env('PMB_ENFORCE_WINDOW', false),

];
