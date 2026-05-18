<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pmb_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->year('year');
            $table->date('open_date');
            $table->date('close_date');
            $table->bigInteger('registration_fee');
            $table->bigInteger('referral_reward_amount')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pmb_periods');
    }
};
