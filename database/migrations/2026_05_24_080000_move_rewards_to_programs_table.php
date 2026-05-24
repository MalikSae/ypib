<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add reward columns to programs
        Schema::table('programs', function (Blueprint $table) {
            $table->bigInteger('referral_reward_amount')->default(0)->after('registration_fee');
            $table->bigInteger('re_registration_reward_amount')->default(0)->after('referral_reward_amount');
        });

        // 2. Copy data from pmb_periods to all programs
        // Mengambil data komisi dari periode aktif (atau periode terakhir jika tidak ada yang aktif)
        $period = DB::table('pmb_periods')->where('is_active', true)->first() 
               ?? DB::table('pmb_periods')->latest('id')->first();

        if ($period) {
            $referralAmount = $period->referral_reward_amount ?? 0;
            $reRegistrationAmount = $period->re_registration_reward_amount ?? 0;

            DB::table('programs')->update([
                'referral_reward_amount' => $referralAmount,
                're_registration_reward_amount' => $reRegistrationAmount,
            ]);
        }

        // 3. Drop columns from pmb_periods
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->dropColumn(['referral_reward_amount', 're_registration_reward_amount']);
        });
    }

    public function down(): void
    {
        // 1. Add columns back to pmb_periods
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->bigInteger('referral_reward_amount')->default(0)->after('registration_fee');
            $table->bigInteger('re_registration_reward_amount')->default(0)->after('referral_reward_amount');
        });

        // 2. Try to copy back data from first program (lossy operation)
        $program = DB::table('programs')->first();
        if ($program) {
            DB::table('pmb_periods')->update([
                'referral_reward_amount' => $program->referral_reward_amount,
                're_registration_reward_amount' => $program->re_registration_reward_amount,
            ]);
        }

        // 3. Drop columns from programs
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['referral_reward_amount', 're_registration_reward_amount']);
        });
    }
};
