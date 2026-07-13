<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registration_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->enum('document_type', ['foto', 'ktp', 'kk', 'akta_lahir', 'transkrip_nilai', 'surat_keterangan_sehat', 'sertifikat', 'lainnya']);
            $table->string('label')->nullable();
            $table->string('file_path');
            $table->enum('status', ['menunggu_review', 'disetujui', 'perlu_revisi'])->default('menunggu_review');
            $table->text('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_documents');
    }
};
