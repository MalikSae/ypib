<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('pmb_periods')->nullOnDelete();
            $table->string('name');
            $table->string('slug', 120)->unique();
            $table->string('school_name')->nullable();
            $table->date('event_date')->nullable();
            // Potongan biaya daftar ulang (Rp), dipotong otomatis saat tagihan daftar ulang.
            $table->unsignedInteger('discount_amount')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['campus_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_events');
    }
};
