<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Biaya, kuota, dan komisi sebuah prodi pada satu periode (PLAN_MULTI_PERIODE.md 5.1, K6).
 * Pendaftaran selalu membaca baris milik periode pendaftarannya, sehingga mengubah harga
 * tahun berikutnya tidak mengubah SKL atau halaman bayar pendaftar lama.
 */
class PeriodProgram extends Model
{
    protected $fillable = [
        'period_id',
        'program_id',
        'is_offered',
        'quota',
        'registration_fee',
        're_registration_fee',
        're_registration_fee_details',
        're_registration_minimum_payment',
        'referral_reward_amount',
        're_registration_reward_amount',
    ];

    protected $casts = [
        'is_offered'                  => 'boolean',
        're_registration_fee_details' => 'array',
    ];

    /** Kolom nilai (selain kunci) yang disalin antar sumber. */
    public const TERM_COLUMNS = [
        'quota',
        'registration_fee',
        're_registration_fee',
        're_registration_fee_details',
        're_registration_minimum_payment',
        'referral_reward_amount',
        're_registration_reward_amount',
    ];

    public function period()
    {
        return $this->belongsTo(PmbPeriod::class, 'period_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Pastikan setiap prodi punya baris pada periode ini. Nilai awal diambil dari `programs`
     * (cadangan). Prodi yang ditambah belakangan (`offered = false`) tidak ditawarkan
     * sampai admin mengaturnya.
     */
    public static function seedFromPrograms(PmbPeriod $period, ?bool $offered = null, ?int $onlyProgramId = null): void
    {
        $existing = static::where('period_id', $period->id)->pluck('program_id')->all();

        Program::query()
            ->whereNotIn('id', $existing)
            ->when($onlyProgramId, fn ($q) => $q->whereKey($onlyProgramId))
            ->get()->each(function (Program $program) use ($period, $offered) {
            $row = new static(['period_id' => $period->id, 'program_id' => $program->id]);
            $row->is_offered = $offered ?? (bool) $program->is_active;

            foreach (static::TERM_COLUMNS as $column) {
                $row->{$column} = $program->{$column};
            }

            $row->save();
        });
    }

    /**
     * Salin matriks dari satu periode ke periode lain. Baris yang sudah ada di tujuan ditimpa
     * hanya bila $overwrite; prodi yang belum punya baris di sumber diisi dari `programs`.
     */
    public static function copyBetween(PmbPeriod $from, PmbPeriod $to, bool $overwrite = false): int
    {
        $copied = 0;

        foreach (static::where('period_id', $from->id)->get() as $source) {
            $target = static::where('period_id', $to->id)->where('program_id', $source->program_id)->first();

            if ($target && !$overwrite) {
                continue;
            }

            $target ??= new static(['period_id' => $to->id, 'program_id' => $source->program_id]);
            $target->is_offered = $source->is_offered;
            foreach (static::TERM_COLUMNS as $column) {
                $target->{$column} = $source->{$column};
            }
            $target->save();
            $copied++;
        }

        static::seedFromPrograms($to);

        return $copied;
    }
}
