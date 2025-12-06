<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('pengumuman', function (Blueprint $table) {
        $table->id();
        $table->string('judul');
        $table->text('isi');
        $table->enum('kategori', ['umum', 'penerimaan', 'pembayaran']);
        $table->date('tanggal_publish');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
};