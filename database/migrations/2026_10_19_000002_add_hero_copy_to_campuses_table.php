<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Teks hero landing per kampus (/cirebon). Landing utama tidak memakainya.
 * Kampus non-bawaan wajib mengisinya lewat form Kampus; Cirebon diisi awal di sini
 * karena kampus ini hanya membuka Farmasi dan Profesi Apoteker.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            if (!Schema::hasColumn('campuses', 'hero_headline')) {
                $table->string('hero_headline', 120)->nullable()->after('address');
            }
            if (!Schema::hasColumn('campuses', 'hero_highlight')) {
                $table->string('hero_highlight', 60)->nullable()->after('hero_headline');
            }
            if (!Schema::hasColumn('campuses', 'hero_subtitle')) {
                $table->string('hero_subtitle', 255)->nullable()->after('hero_highlight');
            }
        });

        DB::table('campuses')->where('code', 'CRB')->whereNull('hero_headline')->update([
            'hero_headline' => 'Wujudkan Karirmu di Bidang Farmasi.',
            'hero_highlight' => 'Karirmu',
            'hero_subtitle' => 'Universitas YPIB Majalengka — Kampus Cirebon. Kuliah S1 Farmasi dan Profesi Apoteker di Kota Cirebon.',
        ]);
    }

    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            foreach (['hero_headline', 'hero_highlight', 'hero_subtitle'] as $column) {
                if (Schema::hasColumn('campuses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
