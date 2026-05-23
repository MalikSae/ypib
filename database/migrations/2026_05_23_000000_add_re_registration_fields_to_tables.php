<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to pmb_periods
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->bigInteger('re_registration_fee')->default(0)->after('registration_fee');
            $table->bigInteger('re_registration_reward_amount')->default(0)->after('referral_reward_amount');
        });

        // 2. Modify registrations table
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('re_registration_payment_proof')->nullable()->after('payment_proof');
            // Change status column from enum to string to avoid complex ALTER ENUM
            $table->string('status')->default('draft')->change();
        });

        // 3. Modify rewards table
        Schema::table('rewards', function (Blueprint $table) {
            $table->enum('reward_type', ['registration', 're_registration'])->default('registration')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->dropColumn(['re_registration_fee', 're_registration_reward_amount']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['re_registration_payment_proof']);
            // Note: Cannot easily revert string to enum without raw sql and data loss risks
        });

        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn(['reward_type']);
        });
    }
};
