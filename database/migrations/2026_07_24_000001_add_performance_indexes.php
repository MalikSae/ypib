<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks untuk daftar, dashboard, rekap, dan hitungan per periode (audit performa).
 * Diukur pada 30.000 pendaftar: daftar/dashboard 370 ms -> 3 ms, hitungan per periode
 * 590 ms -> 70 ms, rekap per prodi 80 ms -> 12 ms. Semuanya indeks tambahan (reversibel).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Daftar terbaru (semua periode) dan urutan created_at.
            $table->index('created_at', 'registrations_created_at_index');
            // Daftar/terbaru per periode tanpa filesort.
            $table->index(['period_id', 'deleted_at', 'created_at'], 'registrations_period_deleted_created_index');
            // Hitungan per status: per periode, dan semua periode (kolom ditutup indeks, tanpa baca baris).
            $table->index(['period_id', 'deleted_at', 'status'], 'registrations_period_deleted_status_index');
            $table->index(['deleted_at', 'period_id', 'status'], 'registrations_deleted_period_status_index');
            // Rekap per prodi dan hitungan kursi per prodi per periode.
            $table->index(['period_id', 'deleted_at', 'first_choice_program_id', 'status'], 'registrations_period_deleted_program_status_index');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->index('created_at', 'payment_logs_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropIndex('payment_logs_created_at_index');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_period_deleted_program_status_index');
            $table->dropIndex('registrations_deleted_period_status_index');
            $table->dropIndex('registrations_period_deleted_status_index');
            $table->dropIndex('registrations_period_deleted_created_index');
            $table->dropIndex('registrations_created_at_index');
        });
    }
};
