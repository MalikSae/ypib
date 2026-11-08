<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            // Sama dengan registrations.education_level: sma_smk_ma, d3, s1, lainnya.
            $table->string('education_level', 20)->nullable()->after('school_name');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};
