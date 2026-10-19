<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slug lama sebuah kampus. Mengganti slug (/cirebon menjadi /cirebon-kota) tidak boleh memutus
 * tautan yang sudah dibagikan: slug lama dialihkan 301 ke slug baru, dan tautan afiliasi
 * ?kampus=slug-lama tetap dikenali.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campus_slug_redirects')) {
            Schema::create('campus_slug_redirects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_slug_redirects');
    }
};
