<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kode_prodi',
        'registration_track',
        'icon',
        'slug',
        'faculty_id',
        'accreditation',
        'quota',
        'registration_fee',
        're_registration_fee',
        're_registration_fee_details',
        're_registration_minimum_payment',
        'referral_reward_amount',
        're_registration_reward_amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        're_registration_fee_details' => 'array',
    ];

    /** Cache nilai periode per instance: [period_id|0 => PeriodProgram]. */
    protected array $termsCache = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Prodi yang ditawarkan pada periode: aktif dan tidak ditandai `is_offered = false` di periode itu. */
    public function scopeOfferedIn($query, $period)
    {
        $periodId = static::periodId($period);
        $query->where('programs.is_active', true);

        if (!$periodId) {
            return $query;
        }

        return $query->whereNotExists(function ($sub) use ($periodId) {
            $sub->selectRaw('1')
                ->from('period_programs')
                ->whereColumn('period_programs.program_id', 'programs.id')
                ->where('period_programs.period_id', $periodId)
                ->where('period_programs.is_offered', false);
        });
    }

    public function periodPrograms()
    {
        return $this->hasMany(PeriodProgram::class);
    }

    private static function periodId($period): ?int
    {
        if ($period instanceof PmbPeriod) {
            return $period->id;
        }

        return $period ? (int) $period : null;
    }

    /**
     * Biaya, kuota, dan komisi prodi ini pada sebuah periode. Bila periode belum punya baris
     * (atau periode tidak diketahui), dipakai nilai `programs` sebagai cadangan transisi.
     */
    public function termsFor($period = null): PeriodProgram
    {
        $periodId = static::periodId($period);
        $key = $periodId ?? 0;

        if (!array_key_exists($key, $this->termsCache)) {
            $row = $periodId
                ? PeriodProgram::where('period_id', $periodId)->where('program_id', $this->id)->first()
                : null;

            $this->termsCache[$key] = $row ?? $this->fallbackTerms($periodId);
        }

        return $this->termsCache[$key];
    }

    /** Muat nilai satu periode untuk banyak prodi sekaligus (satu query, tanpa N+1). */
    public static function preloadTerms($programs, $period): void
    {
        $periodId = static::periodId($period);
        $programs = collect($programs)->filter()->unique('id')->values();

        if (!$periodId || $programs->isEmpty()) {
            return;
        }

        $rows = PeriodProgram::where('period_id', $periodId)
            ->whereIn('program_id', $programs->pluck('id'))
            ->get()
            ->keyBy('program_id');

        foreach ($programs as $program) {
            $program->termsCache[$periodId] = $rows->get($program->id) ?? $program->fallbackTerms($periodId);
        }
    }

    protected function fallbackTerms(?int $periodId): PeriodProgram
    {
        $terms = new PeriodProgram();
        $terms->forceFill([
            'period_id'  => $periodId,
            'program_id' => $this->id,
            'is_offered' => true,
        ]);

        foreach (PeriodProgram::TERM_COLUMNS as $column) {
            $terms->{$column} = $this->{$column};
        }

        return $terms;
    }

    /**
     * Jumlah kursi terpakai: pendaftar aktif (tidak ditolak, tidak dihapus)
     * yang memilih prodi ini, opsional dibatasi satu periode.
     */
    public function occupiedSeats(?int $periodId = null): int
    {
        return Registration::where('first_choice_program_id', $this->id)
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->where('status', '!=', 'ditolak')
            ->count();
    }

    /** quota 0/kosong berarti tanpa batas. */
    public function isFull(?int $periodId = null): bool
    {
        $quota = (int) $this->termsFor($periodId)->quota;

        return $quota > 0 && $this->occupiedSeats($periodId) >= $quota;
    }

    /** Slug unik: tambahkan sufiks angka bila nama sama sudah dipakai. */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'prodi';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function galleries()
    {
        return $this->hasMany(ProgramGallery::class);
    }
}
