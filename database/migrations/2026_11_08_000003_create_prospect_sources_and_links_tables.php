<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat event tempat prospek muncul (satu nomor WA bisa ikut banyak event).
        Schema::create('prospect_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->foreignId('prospect_event_id')->constrained('prospect_events')->cascadeOnDelete();
            $table->foreignId('referrer_id')->nullable()->constrained('referrers')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['prospect_id', 'prospect_event_id']);
        });

        // Link daftar terkunci per prospek. Sekali pakai: used_at terisi saat registrasi dibuat.
        Schema::create('prospect_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            // Event yang potongannya dipakai saat registrasi lewat link ini.
            $table->foreignId('prospect_event_id')->nullable()->constrained('prospect_events')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_links');
        Schema::dropIfExists('prospect_sources');
    }
};
