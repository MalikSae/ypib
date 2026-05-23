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
            $table->string('admin_whatsapp')->nullable()->after('university_bank_account_name');
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
            $table->dropColumn('admin_whatsapp');
        });
    }
};
