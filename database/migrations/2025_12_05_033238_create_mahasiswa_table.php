<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
{
    Schema::create('mahasiswas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('jurusan_id')->constrained()->onDelete('cascade');
        $table->string('no_pendaftaran')->unique();
        $table->string('nama_lengkap');
        $table->enum('jenis_kelamin', ['L', 'P']);
        $table->string('tempat_lahir');
        $table->date('tanggal_lahir');
        $table->text('alamat');
        $table->string('no_hp');
        $table->string('asal_sekolah');
        $table->string('foto')->nullable();
        $table->enum('status_pendaftaran', ['pending', 'diverifikasi', 'ditolak', 'diterima'])->default('pending');
        $table->text('catatan_admin')->nullable();
        $table->timestamps();
    });
}
};
