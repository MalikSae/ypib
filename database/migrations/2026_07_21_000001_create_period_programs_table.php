<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-periode, tahap T3b (PLAN_MULTI_PERIODE.md 5.1): biaya, kuota, dan komisi per periode.
 * Kolom serupa di `programs` dipertahankan sebagai cadangan baca sampai T9.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('pmb_periods')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->boolean('is_offered')->default(true);
            $table->unsignedInteger('quota')->default(0);
            $table->unsignedBigInteger('registration_fee')->default(0);
            $table->unsignedBigInteger('re_registration_fee')->default(0);
            $table->json('re_registration_fee_details')->nullable();
            $table->unsignedBigInteger('re_registration_minimum_payment')->default(0);
            $table->unsignedBigInteger('referral_reward_amount')->default(0);
            $table->unsignedBigInteger('re_registration_reward_amount')->default(0);
            $table->timestamps();

            $table->unique(['period_id', 'program_id']);
        });

        // Isi awal: setiap periode yang ada mewarisi nilai `programs` saat ini, sehingga
        // perilaku pendaftar tidak berubah. Prodi nonaktif ditandai tidak ditawarkan.
        $now = now();
        $periods = DB::table('pmb_periods')->pluck('id');
        $programs = DB::table('programs')->get();

        foreach ($periods as $periodId) {
            $rows = [];
            foreach ($programs as $program) {
                $rows[] = [
                    'period_id'                       => $periodId,
                    'program_id'                      => $program->id,
                    'is_offered'                      => (bool) $program->is_active,
                    'quota'                           => max(0, (int) $program->quota),
                    'registration_fee'                => max(0, (int) $program->registration_fee),
                    're_registration_fee'             => max(0, (int) $program->re_registration_fee),
                    're_registration_fee_details'     => $program->re_registration_fee_details,
                    're_registration_minimum_payment' => max(0, (int) $program->re_registration_minimum_payment),
                    'referral_reward_amount'          => max(0, (int) $program->referral_reward_amount),
                    're_registration_reward_amount'   => max(0, (int) $program->re_registration_reward_amount),
                    'created_at'                      => $now,
                    'updated_at'                      => $now,
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('period_programs')->insert($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('period_programs');
    }
};
