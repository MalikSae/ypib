<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone', 20);
            // Nomor WA dinormalisasi (62xxxx -> 08xxxx, hanya digit). Unik: satu baris per nomor.
            $table->string('phone_normalized', 20)->unique();
            $table->string('school_name')->nullable();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            // Pemilik prospek (first-touch). Tidak berpindah walau siswa klik affiliator lain.
            $table->foreignId('referrer_id')->nullable()->constrained('referrers')->nullOnDelete();
            $table->string('status', 30)->default('baru');
            $table->text('internal_notes')->nullable();
            $table->text('referrer_notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('wa_clicked_at')->nullable();
            $table->boolean('fraud_flag')->default(false);
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
            $table->index(['campus_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
