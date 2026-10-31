<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('education_level', 20)->nullable()->after('nisn');
        });

        // Semua pendaftar sebelum kolom ini berasal dari SMA/SMK/MA.
        DB::table('registrations')->update(['education_level' => 'sma_smk_ma']);
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
