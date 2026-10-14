<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-kampus, tahap B: biaya/kuota/komisi per periode × prodi × kampus, dan kampus pada
 * pendaftaran. Semua data lama dibackfill ke kampus bawaan (Majalengka).
 *
 * Tiap langkah idempoten (DDL MySQL tidak transaksional).
 */
return new class extends Migration
{
    public function up(): void
    {
        $defaultId = DB::table('campuses')->where('is_default', true)->value('id');

        if (!$defaultId) {
            throw new RuntimeException('Kampus bawaan belum ada. Jalankan migrasi create_campuses_table dulu.');
        }

        // period_programs.campus_id
        if (!Schema::hasColumn('period_programs', 'campus_id')) {
            Schema::table('period_programs', function (Blueprint $table) {
                $table->unsignedBigInteger('campus_id')->nullable()->after('program_id');
            });
        }

        DB::table('period_programs')->whereNull('campus_id')->update(['campus_id' => $defaultId]);

        if (!Schema::hasIndex('period_programs', 'period_programs_period_program_campus_unique')) {
            Schema::table('period_programs', function (Blueprint $table) {
                $table->unique(['period_id', 'program_id', 'campus_id'], 'period_programs_period_program_campus_unique');
            });
        }

        // Unique lama dilepas setelah yang baru ada, supaya FK period_id tetap punya index.
        if (Schema::hasIndex('period_programs', 'period_programs_period_id_program_id_unique')) {
            Schema::table('period_programs', function (Blueprint $table) {
                $table->dropUnique('period_programs_period_id_program_id_unique');
            });
        }

        Schema::table('period_programs', function (Blueprint $table) {
            $table->unsignedBigInteger('campus_id')->nullable(false)->change();
        });

        if (!$this->hasForeignKey('period_programs', 'period_programs_campus_id_foreign')) {
            Schema::table('period_programs', function (Blueprint $table) {
                $table->foreign('campus_id')->references('id')->on('campuses');
            });
        }

        // registrations.campus_id
        if (!Schema::hasColumn('registrations', 'campus_id')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->unsignedBigInteger('campus_id')->nullable()->after('period_id');
            });
        }

        DB::table('registrations')->whereNull('campus_id')->update(['campus_id' => $defaultId]);

        if (!$this->hasForeignKey('registrations', 'registrations_campus_id_foreign')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->foreign('campus_id')->references('id')->on('campuses');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasForeignKey('registrations', 'registrations_campus_id_foreign')) {
            Schema::table('registrations', fn (Blueprint $t) => $t->dropForeign('registrations_campus_id_foreign'));
        }
        if (Schema::hasColumn('registrations', 'campus_id')) {
            Schema::table('registrations', fn (Blueprint $t) => $t->dropColumn('campus_id'));
        }

        // PERINGATAN: baris biaya/kuota kampus non-default DIHAPUS di sini (data tidak bisa
        // dipulihkan). Unique (period_id, program_id) hanya bisa kembali tanpa baris ganda.
        $defaultId = DB::table('campuses')->where('is_default', true)->value('id');
        if ($defaultId && Schema::hasColumn('period_programs', 'campus_id')) {
            DB::table('period_programs')->where('campus_id', '!=', $defaultId)->delete();
        }

        if ($this->hasForeignKey('period_programs', 'period_programs_campus_id_foreign')) {
            Schema::table('period_programs', fn (Blueprint $t) => $t->dropForeign('period_programs_campus_id_foreign'));
        }

        if (!Schema::hasIndex('period_programs', 'period_programs_period_id_program_id_unique')) {
            Schema::table('period_programs', fn (Blueprint $t) => $t->unique(['period_id', 'program_id']));
        }
        if (Schema::hasIndex('period_programs', 'period_programs_period_program_campus_unique')) {
            Schema::table('period_programs', fn (Blueprint $t) => $t->dropUnique('period_programs_period_program_campus_unique'));
        }
        if (Schema::hasColumn('period_programs', 'campus_id')) {
            Schema::table('period_programs', fn (Blueprint $t) => $t->dropColumn('campus_id'));
        }
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        foreach (Schema::getForeignKeys($table) as $fk) {
            if (($fk['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
};
