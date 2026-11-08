<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('prospect_id')->nullable()->after('referrer_id')
                ->constrained('prospects')->nullOnDelete();
            // Snapshot potongan daftar ulang saat registrasi dibuat (0 = tanpa link diskon).
            $table->unsignedInteger('event_discount_amount')->default(0)->after('prospect_id');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['prospect_id']);
            $table->dropColumn(['prospect_id', 'event_discount_amount']);
        });
    }
};
