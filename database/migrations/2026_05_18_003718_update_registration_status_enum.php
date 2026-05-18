<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Ubah kolom ke string dulu agar bisa diisi nilai baru
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('status')->default('menunggu_pembayaran')->change();
            $table->string('registration_number')->nullable()->change();
        });

        // Step 2: Reset semua data lama ke nilai default baru
        DB::statement("UPDATE registrations SET status = 'menunggu_pembayaran', registration_number = NULL");

        // Step 3: Ubah ke enum dengan nilai yang benar
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM(
            'menunggu_pembayaran',
            'menunggu_konfirmasi',
            'terdaftar',
            'diterima',
            'ditolak',
            'perlu_revisi'
        ) NOT NULL DEFAULT 'menunggu_pembayaran'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'submitted'");
    }
};
