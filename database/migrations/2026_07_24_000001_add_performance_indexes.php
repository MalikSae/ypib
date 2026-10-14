<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks untuk daftar, dashboard, rekap, dan hitungan per periode (audit performa).
 * Diukur pada 30.000 pendaftar: daftar/dashboard 370 ms -> 3 ms, hitungan per periode
 * 590 ms -> 70 ms, rekap per prodi 80 ms -> 12 ms. Semuanya indeks tambahan (reversibel).
 *
 * Idempoten: tiap indeks diperiksa lebih dulu, sehingga menjalankan ulang setelah kegagalan
 * sebagian (DDL MySQL tidak transaksional) tidak gagal karena indeks yang sudah ada.
 */
return new class extends Migration
{
    /** @return array<string, array<string, string|array<int, string>>> tabel => [nama indeks => kolom] */
    private function indexes(): array
    {
        return [
            'registrations' => [
                // Daftar terbaru (semua periode) dan urutan created_at.
                'registrations_created_at_index'                    => 'created_at',
                // Daftar/terbaru per periode tanpa filesort.
                'registrations_period_deleted_created_index'        => ['period_id', 'deleted_at', 'created_at'],
                // Hitungan per status: per periode, dan semua periode (kolom ditutup indeks).
                'registrations_period_deleted_status_index'         => ['period_id', 'deleted_at', 'status'],
                'registrations_deleted_period_status_index'         => ['deleted_at', 'period_id', 'status'],
                // Rekap per prodi dan hitungan kursi per prodi per periode.
                'registrations_period_deleted_program_status_index' => ['period_id', 'deleted_at', 'first_choice_program_id', 'status'],
            ],
            'payment_logs' => [
                'payment_logs_created_at_index' => 'created_at',
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->indexes() as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                    $blueprint->index($columns, $name);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes(), true) as $table => $indexes) {
            foreach (array_reverse($indexes, true) as $name => $columns) {
                if (!Schema::hasIndex($table, $name)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($name) {
                    $blueprint->dropIndex($name);
                });
            }
        }
    }
};
