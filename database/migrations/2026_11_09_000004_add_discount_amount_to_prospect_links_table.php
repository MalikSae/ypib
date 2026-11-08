<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospect_links', function (Blueprint $table) {
            // Snapshot potongan saat link dibuat. Mengubah potongan event tidak memengaruhi link lama.
            $table->unsignedInteger('discount_amount')->default(0)->after('prospect_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('prospect_links', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};
