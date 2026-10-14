<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-kampus, tahap A: master data kampus. Majalengka menjadi kampus bawaan (is_default),
 * semua data lama dianggap milik kampus ini oleh migrasi tahap B.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campuses')) {
            Schema::create('campuses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 20)->unique();
                $table->string('slug')->unique();
                $table->string('city')->nullable();
                $table->text('address')->nullable();
                $table->string('admin_whatsapp', 20)->nullable();
                $table->string('university_bank_name')->nullable();
                $table->string('university_bank_account')->nullable();
                $table->string('university_bank_account_name')->nullable();
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $now = now();

        if (!DB::table('campuses')->where('code', 'MJL')->exists()) {
            DB::table('campuses')->insert([
                'name'       => 'Kampus Majalengka',
                'code'       => 'MJL',
                'slug'       => 'majalengka',
                'city'       => 'Majalengka',
                'is_default' => true,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!DB::table('campuses')->where('code', 'CRB')->exists()) {
            DB::table('campuses')->insert([
                'name'       => 'Kampus Cirebon',
                'code'       => 'CRB',
                'slug'       => 'cirebon',
                'city'       => 'Cirebon',
                'address'    => 'Jl. Perjuangan No. 10a, Kesambi, Kota Cirebon, Jawa Barat',
                'is_default' => false,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
