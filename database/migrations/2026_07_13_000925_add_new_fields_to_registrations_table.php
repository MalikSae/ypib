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
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('nisn', 10)->nullable();
            $table->string('religion')->nullable();
            $table->string('school_major')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'nisn',
                'religion',
                'school_major',
                'certificate_number',
                'father_name',
                'mother_name',
                'father_occupation',
                'mother_occupation',
            ]);
        });
    }
};
