<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('acted_by')->constrained('users');
            $table->string('action');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrer_logs');
    }
};
