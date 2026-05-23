<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->string('university_bank_name')->nullable()->after('re_registration_reward_amount');
            $table->string('university_bank_account')->nullable()->after('university_bank_name');
            $table->string('university_bank_account_name')->nullable()->after('university_bank_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pmb_periods', function (Blueprint $table) {
            $table->dropColumn([
                'university_bank_name',
                'university_bank_account',
                'university_bank_account_name'
            ]);
        });
    }
};
