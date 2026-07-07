<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE rewards MODIFY COLUMN status ENUM('pending', 'approved', 'disbursed', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE rewards MODIFY COLUMN status ENUM('pending', 'approved', 'disbursed') DEFAULT 'pending'");

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
