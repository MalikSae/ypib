<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-periode T3 (PLAN_MULTI_PERIODE.md 5.1, 5.5): penanda admin yang mengizinkan NIK pada
 * pendaftaran ber-NIM ini mendaftar lagi pada prodi yang sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->boolean('reapply_allowed')->default(false);
            $table->text('reapply_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['reapply_allowed', 'reapply_note']);
        });
    }
};
