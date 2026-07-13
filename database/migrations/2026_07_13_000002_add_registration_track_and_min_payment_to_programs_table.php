<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->enum('registration_track', ['reguler', 'non_reguler'])
                  ->default('reguler')
                  ->after('kode_prodi');

            $table->integer('re_registration_minimum_payment')
                  ->default(0)
                  ->after('re_registration_fee_details');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['registration_track', 're_registration_minimum_payment']);
        });
    }
};
