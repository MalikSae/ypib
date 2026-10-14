<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-kampus, tahap D: admin, operator, dan panitia dapat dibatasi ke satu kampus.
 * NULL = akses semua kampus (perilaku lama).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'campus_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('campus_id')->nullable()->after('role');
                $table->foreign('campus_id')->references('id')->on('campuses')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'campus_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            });
        }
    }
};
