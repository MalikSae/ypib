<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-periode T5 (PLAN_MULTI_PERIODE.md 5.1, 5.6): NIK pendaftar saat komisi dibuat.
 * Dasar aturan "satu komisi per orang"; sengaja disalin (bukan dibaca ulang) supaya
 * mengganti NIK pada pendaftaran tidak membuka komisi kedua.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('registration_id');
            $table->index(['nik', 'reward_type'], 'rewards_nik_reward_type_index');
        });

        // Reward lama: ambil NIK dari pendaftarannya (termasuk yang sudah di-soft-delete).
        DB::statement(
            'UPDATE rewards INNER JOIN registrations ON registrations.id = rewards.registration_id '
            . 'SET rewards.nik = registrations.nik WHERE rewards.nik IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropIndex('rewards_nik_reward_type_index');
            $table->dropColumn('nik');
        });
    }
};
