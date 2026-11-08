<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat follow-up: kontak, catatan, dan perubahan status.
        Schema::create('prospect_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['prospect_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_activities');
    }
};
