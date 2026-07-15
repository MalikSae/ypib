<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique(['kode_prodi']);
            $table->unique(['kode_prodi', 'registration_track'], 'programs_kode_prodi_track_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropUnique('programs_kode_prodi_track_unique');
            $table->unique(['kode_prodi']);
        });
    }
};
