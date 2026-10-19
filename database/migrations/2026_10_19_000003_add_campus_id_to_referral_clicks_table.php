<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Klik dari tautan afiliasi per kampus (/ref/KODE?kampus=cirebon) mencatat kampusnya.
 * Klik lama dan klik dari tautan umum tetap NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('referral_clicks', 'campus_id')) {
            Schema::table('referral_clicks', function (Blueprint $table) {
                $table->unsignedBigInteger('campus_id')->nullable()->after('referrer_id');
                $table->foreign('campus_id')->references('id')->on('campuses')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('referral_clicks', 'campus_id')) {
            Schema::table('referral_clicks', function (Blueprint $table) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            });
        }
    }
};
