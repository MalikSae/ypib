<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('pembayarans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mahasiswa_id')->constrained()->onDelete('cascade');
        $table->string('no_pembayaran')->unique();
        $table->decimal('jumlah', 10, 2);
        $table->date('tanggal_bayar');
        $table->string('bukti_pembayaran');
        $table->enum('status', ['pending', 'terverifikasi', 'ditolak'])->default('pending');
        $table->text('catatan')->nullable();
        $table->timestamps();
    });
}
};
