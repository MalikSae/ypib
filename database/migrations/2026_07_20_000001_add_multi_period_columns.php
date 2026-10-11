<?php

use App\Support\PeriodPrecheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-periode, tahap T1 (PLAN_MULTI_PERIODE.md bagian 5.1).
 * Bersifat penambahan: kolom lama (`is_active`) dipertahankan sampai T9.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pelanggar unique harus diselesaikan manusia; jangan tebak-tebak di migrasi.
        $duplicateActive = PeriodPrecheck::duplicateActiveRegistrations();
        $duplicateNims = PeriodPrecheck::duplicateNims();

        if ($duplicateActive || $duplicateNims) {
            throw new RuntimeException(
                'Migrasi multi-periode dibatalkan: ' . count($duplicateActive) . ' pasangan akun+periode ganda dan '
                . count($duplicateNims) . ' NIM ganda. Jalankan "php artisan periods:precheck" lalu selesaikan duplikatnya.'
            );
        }

        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('is_active');
            $table->date('re_registration_deadline')->nullable()->after('close_date');
            $table->unsignedTinyInteger('payment_grace_days')->default(3)->after('re_registration_deadline');
            $table->unsignedSmallInteger('nim_sequence_start')->default(1)->after('payment_grace_days');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
        });

        // Backfill: periode aktif tetap tayang, selebihnya diarsipkan (hanya baca).
        DB::table('pmb_periods')->where('is_active', true)->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);
        DB::table('pmb_periods')->where('is_active', false)->update([
            'status'      => 'archived',
            'archived_at' => now(),
        ]);

        // Lantai urutan NIM tahun 2026 (sebelumnya konstanta Registration::YEAR_SEQUENCE_START).
        DB::table('pmb_periods')->where('year', 2026)->update(['nim_sequence_start' => 400]);

        Schema::table('registrations', function (Blueprint $table) {
            // `period_id` sudah punya index dari foreign key; hanya `nik` yang perlu ditambah.
            $table->index('nik', 'registrations_nik_index');
        });

        // Satu pendaftaran aktif per akun per periode. NULL (soft-delete) tidak bentrok.
        DB::statement(
            "ALTER TABLE registrations ADD COLUMN active_key VARCHAR(40) "
            . "GENERATED ALWAYS AS (IF(deleted_at IS NULL, CONCAT(user_id, '-', period_id), NULL)) STORED"
        );
        DB::statement('ALTER TABLE registrations ADD UNIQUE INDEX registrations_active_key_unique (active_key)');

        Schema::table('registrations', function (Blueprint $table) {
            $table->unique('nim', 'registrations_nim_unique');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique('registrations_nim_unique');
        });

        DB::statement('ALTER TABLE registrations DROP INDEX registrations_active_key_unique');
        DB::statement('ALTER TABLE registrations DROP COLUMN active_key');

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_nik_index');
        });

        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->dropColumn([
                'status', 're_registration_deadline', 'payment_grace_days',
                'nim_sequence_start', 'published_at', 'archived_at',
            ]);
        });
    }
};
