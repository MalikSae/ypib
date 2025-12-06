<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::create('jurusans', function (Blueprint $table) {
        $table->id();
        $table->string('kode_jurusan')->unique();
        $table->string('nama_jurusan');
        $table->text('deskripsi')->nullable();
        $table->integer('kuota');
        $table->timestamps();
    });
}
};
