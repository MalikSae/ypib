<?php

use App\Support\PeriodPrecheck;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-periode, tahap T1 (PLAN_MULTI_PERIODE.md bagian 5.1).
 * Bersifat penambahan: kolom lama (`is_active`) dipertahankan sampai T9.
 *
 * Tiap langkah idempoten: DDL MySQL tidak transaksional, jadi bila migrasi berhenti di tengah
 * (mis. hosting menolak kolom turunan) menjalankannya lagi tidak gagal karena "duplicate column".
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

        $freshColumns = !Schema::hasColumn('pmb_periods', 'status');

        Schema::table('pmb_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('pmb_periods', 'status')) {
                $table->string('status', 20)->default('draft')->after('is_active');
            }
            if (!Schema::hasColumn('pmb_periods', 're_registration_deadline')) {
                $table->date('re_registration_deadline')->nullable()->after('close_date');
            }
            if (!Schema::hasColumn('pmb_periods', 'payment_grace_days')) {
                $table->unsignedTinyInteger('payment_grace_days')->default(3)->after('re_registration_deadline');
            }
            if (!Schema::hasColumn('pmb_periods', 'nim_sequence_start')) {
                $table->unsignedSmallInteger('nim_sequence_start')->default(1)->after('payment_grace_days');
            }
            if (!Schema::hasColumn('pmb_periods', 'published_at')) {
                $table->timestamp('published_at')->nullable();
            }
            if (!Schema::hasColumn('pmb_periods', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
        });

        // Backfill hanya pada eksekusi pertama: periode aktif tetap tayang, selebihnya diarsipkan.
        if ($freshColumns) {
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
        }

        // `period_id` sudah ber-index lewat foreign key; hanya `nik` yang perlu ditambah.
        if (!Schema::hasIndex('registrations', 'registrations_nik_index')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->index('nik', 'registrations_nik_index');
            });
        }

        $this->addActiveKey();

        if (!Schema::hasIndex('registrations', 'registrations_nim_unique')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->unique('nim', 'registrations_nim_unique');
            });
        }
    }

    /**
     * Satu pendaftaran aktif per akun per periode, ditegakkan database (kolom turunan + unique).
     * NULL (soft-delete) tidak bentrok. Bila hosting menolak kolom turunan, langkah ini dilewati
     * dengan peringatan: aturan yang sama tetap ditegakkan aplikasi (RegistrationForm::submit,
     * restore) dan hanya lapisan pengaman database yang hilang.
     */
    private function addActiveKey(): void
    {
        try {
            if (!Schema::hasColumn('registrations', 'active_key')) {
                DB::statement(
                    "ALTER TABLE registrations ADD COLUMN active_key VARCHAR(40) "
                    . "GENERATED ALWAYS AS (IF(deleted_at IS NULL, CONCAT(user_id, '-', period_id), NULL)) STORED"
                );
            }

            if (!Schema::hasIndex('registrations', 'registrations_active_key_unique')) {
                DB::statement('ALTER TABLE registrations ADD UNIQUE INDEX registrations_active_key_unique (active_key)');
            }
        } catch (\Throwable $e) {
            // Kembalikan ke keadaan bersih supaya tidak ada kolom setengah jadi.
            try {
                if (Schema::hasColumn('registrations', 'active_key')) {
                    DB::statement('ALTER TABLE registrations DROP COLUMN active_key');
                }
            } catch (\Throwable $ignored) {
                // abaikan: kolom tidak ada atau tidak bisa dihapus
            }

            $message = 'Kolom turunan registrations.active_key dilewati: ' . $e->getMessage();
            Log::warning($message);
            fwrite(STDERR, "PERINGATAN: {$message}\n");
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('registrations', 'registrations_nim_unique')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropUnique('registrations_nim_unique');
            });
        }

        if (Schema::hasIndex('registrations', 'registrations_active_key_unique')) {
            DB::statement('ALTER TABLE registrations DROP INDEX registrations_active_key_unique');
        }
        if (Schema::hasColumn('registrations', 'active_key')) {
            DB::statement('ALTER TABLE registrations DROP COLUMN active_key');
        }

        if (Schema::hasIndex('registrations', 'registrations_nik_index')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropIndex('registrations_nik_index');
            });
        }

        $columns = array_values(array_filter([
            'status', 're_registration_deadline', 'payment_grace_days',
            'nim_sequence_start', 'published_at', 'archived_at',
        ], fn ($column) => Schema::hasColumn('pmb_periods', $column)));

        if ($columns) {
            Schema::table('pmb_periods', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
