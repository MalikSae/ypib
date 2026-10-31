<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('re_registration');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('claimed_amount')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('status', 20)->default('menunggu_verifikasi');
            $table->text('note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['registration_id', 'type', 'status']);
        });

        // Tarif pendaftaran yang berlaku saat pembayaran dikonfirmasi; diisi dari tarif prodi
        // saat ini untuk pendaftar lama yang sudah terkonfirmasi.
        Schema::table('registrations', function (Blueprint $table) {
            $table->unsignedBigInteger('registration_fee_paid')->nullable()->after('payment_proof');
        });

        DB::statement("
            UPDATE registrations r
            JOIN period_programs pp
              ON pp.period_id = r.period_id
             AND pp.program_id = r.first_choice_program_id
             AND (pp.campus_id <=> r.campus_id)
            SET r.registration_fee_paid = pp.registration_fee
            WHERE r.status NOT IN ('menunggu_pembayaran', 'menunggu_konfirmasi')
        ");
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('registration_fee_paid');
        });

        Schema::dropIfExists('registration_payments');
    }
};
