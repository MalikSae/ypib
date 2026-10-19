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

    /** Cache nilai periode per instance: ["period_id|campus_id" => PeriodProgram]. */
    protected array $termsCache = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Prodi yang ditawarkan pada periode di satu kampus (null = kampus bawaan).
     * Kampus bawaan: aktif dan tidak ditandai `is_offered = false`. Kampus lain: harus punya
     * baris `period_programs` dengan `is_offered = true` pada periode itu.
     */
    public function scopeOfferedIn($query, $period, $campus = null)
    {
        $periodId = static::periodId($period);
        $campusId = Campus::idOf($campus);
        $query->where('programs.is_active', true);

        if (!$periodId) {
            return $query;
        }

        if ($campusId !== Campus::defaultId()) {
            return $query->whereExists(function ($sub) use ($periodId, $campusId) {
                $sub->selectRaw('1')
                    ->from('period_programs')
                    ->whereColumn('period_programs.program_id', 'programs.id')
                    ->where('period_programs.period_id', $periodId)
                    ->where('period_programs.campus_id', $campusId)
                    ->where('period_programs.is_offered', true);
            });
        }

        return $query->whereNotExists(function ($sub) use ($periodId, $campusId) {
            $sub->selectRaw('1')
                ->from('period_programs')
                ->whereColumn('period_programs.program_id', 'programs.id')
                ->where('period_programs.period_id', $periodId)
                ->where('period_programs.campus_id', $campusId)
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
     * Biaya, kuota, dan komisi prodi ini pada sebuah periode di satu kampus (null = bawaan).
     * Bila baris belum ada (atau periode tidak diketahui), dipakai nilai `programs` sebagai cadangan.
     */
    public function termsFor($period = null, $campus = null): PeriodProgram
    {
        $periodId = static::periodId($period);
        $campusId = Campus::idOf($campus);
        $key = ($periodId ?? 0) . '|' . $campusId;

        if (!array_key_exists($key, $this->termsCache)) {
            $row = $periodId
                ? PeriodProgram::where('period_id', $periodId)
                    ->where('program_id', $this->id)
                    ->where('campus_id', $campusId)
                    ->first()
                : null;

            $this->termsCache[$key] = $row ?? $this->fallbackTerms($periodId, $campusId);
        }

        return $this->termsCache[$key];
    }

    /** Muat nilai satu periode + kampus untuk banyak prodi sekaligus (satu query, tanpa N+1). */
    public static function preloadTerms($programs, $period, $campus = null): void
    {
        $periodId = static::periodId($period);
        $campusId = Campus::idOf($campus);
        $programs = collect($programs)->filter()->unique('id')->values();

        if (!$periodId || $programs->isEmpty()) {
            return;
        }

        $rows = PeriodProgram::where('period_id', $periodId)
            ->where('campus_id', $campusId)
            ->whereIn('program_id', $programs->pluck('id'))
            ->get()
            ->keyBy('program_id');

        foreach ($programs as $program) {
            $program->termsCache[$periodId . '|' . $campusId] =
                $rows->get($program->id) ?? $program->fallbackTerms($periodId, $campusId);
        }
    }

    protected function fallbackTerms(?int $periodId, ?int $campusId = null): PeriodProgram
    {
        $terms = new PeriodProgram();
        $terms->forceFill([
            'period_id'  => $periodId,
            'program_id' => $this->id,
            'campus_id'  => $campusId ?? Campus::defaultId(),
            'is_offered' => true,
        ]);

        foreach (PeriodProgram::TERM_COLUMNS as $column) {
            $terms->{$column} = $this->{$column};
        }

        return $terms;
    }

    /**
     * Jumlah kursi terpakai: pendaftar aktif (tidak ditolak, tidak dihapus) yang memilih
     * prodi ini, opsional dibatasi satu periode dan satu kampus. Tanpa $campus, semua kampus
     * dihitung. Pendaftaran tanpa kampus dihitung milik kampus bawaan.
     */
    public function occupiedSeats(?int $periodId = null, $campus = null): int
    {
        $campusId = $campus === null ? null : Campus::idOf($campus);

        return Registration::withoutGlobalScope(\App\Models\Scopes\CampusScope::class)->where('first_choice_program_id', $this->id)
            ->when($periodId, fn ($q) => $q->where('period_id', $periodId))
            ->when($campusId, function ($q) use ($campusId) {
                $q->where(function ($w) use ($campusId) {
                    $w->where('campus_id', $campusId);

                    if ($campusId === Campus::defaultId()) {
                        $w->orWhereNull('campus_id');
                    }
                });
            })
            ->where('status', '!=', 'ditolak')
            ->count();
    }

    /** quota 0/kosong berarti tanpa batas. Kuota dan kursi per kampus (null = kampus bawaan). */
    public function isFull(?int $periodId = null, $campus = null): bool
    {
        $campusId = Campus::idOf($campus);
        $quota = (int) $this->termsFor($periodId, $campusId)->quota;

        return $quota > 0 && $this->occupiedSeats($periodId, $campusId) >= $quota;
    }

    /**
     * Prodi yang ditawarkan di setidaknya satu kampus aktif pada periode (untuk landing publik).
     * Kampus bawaan: tidak ditandai `is_offered = false`. Kampus lain: baris `is_offered = true`.
     */
    public function scopeOfferedInAny($query, $period)
    {
        $periodId = static::periodId($period);
        $defaultId = Campus::defaultId();
        $query->where('programs.is_active', true);

        if (!$periodId) {
            return $query;
        }

        return $query->where(function ($any) use ($periodId, $defaultId) {
            $any->whereNotExists(function ($sub) use ($periodId, $defaultId) {
                $sub->selectRaw('1')->from('period_programs')
                    ->whereColumn('period_programs.program_id', 'programs.id')
                    ->where('period_programs.period_id', $periodId)
                    ->where('period_programs.campus_id', $defaultId)
                    ->where('period_programs.is_offered', false);
            })->orWhereExists(function ($sub) use ($periodId, $defaultId) {
                $sub->selectRaw('1')->from('period_programs')
                    ->join('campuses', 'campuses.id', '=', 'period_programs.campus_id')
                    ->whereColumn('period_programs.program_id', 'programs.id')
                    ->where('period_programs.period_id', $periodId)
                    ->where('period_programs.campus_id', '!=', $defaultId)
                    ->where('period_programs.is_offered', true)
                    ->where('campuses.is_active', true);
            });
        });
    }

    /**
     * Isi relasi `offeringCampuses` (kampus bawaan lebih dulu) untuk banyak prodi dengan
     * satu query, lalu muat biaya/kuota kampus pertama tiap prodi untuk `landingTerms()`.
     */
    public static function attachOfferingCampuses($programs, $period): void
    {
        // Semua instance (satu prodi bisa muncul dari beberapa relasi) ikut diisi.
        $programs = collect($programs)->filter()->values();
        $periodId = static::periodId($period);

        if ($programs->isEmpty()) {
            return;
        }

        $campuses = Campus::active()->orderByDesc('is_default')->orderBy('name')->get();
        $defaultId = Campus::defaultId();

        $rows = $periodId
            ? PeriodProgram::where('period_id', $periodId)
                ->whereIn('program_id', $programs->pluck('id')->unique())
                ->get(['program_id', 'campus_id', 'is_offered'])
                ->groupBy('program_id')
            : collect();

        $byFirstCampus = [];

        foreach ($programs as $program) {
            $programRows = $rows->get($program->id, collect());

            $offering = $campuses->filter(function (Campus $campus) use ($programRows, $defaultId, $periodId) {
                $row = $programRows->firstWhere('campus_id', $campus->id);

                if ($campus->id === $defaultId) {
                    return !$periodId || !$row || $row->is_offered;
                }

                return $row && $row->is_offered;
            })->values();

            $program->setRelation('offeringCampuses', $offering);
            $first = $offering->first()?->id ?? $defaultId;
            $byFirstCampus[$first][] = $program;
        }

        if (!$periodId) {
            return;
        }

        foreach ($byFirstCampus as $campusId => $group) {
            $terms = PeriodProgram::where('period_id', $periodId)
                ->where('campus_id', $campusId)
                ->whereIn('program_id', collect($group)->pluck('id')->unique())
                ->get()->keyBy('program_id');

            foreach ($group as $program) {
                $program->termsCache[$periodId . '|' . $campusId] =
                    $terms->get($program->id) ?? $program->fallbackTerms($periodId, $campusId);
            }
        }
    }

    /**
     * Landing satu kampus: semua prodi dianggap ditawarkan di kampus itu, biaya/kuotanya dimuat
     * dengan satu query untuk semua instance. `landingTerms()` lalu membaca kampus ini.
     */
    public static function attachCampus($programs, $period, Campus $campus): void
    {
        $programs = collect($programs)->filter()->values();
        $periodId = static::periodId($period);

        if ($programs->isEmpty()) {
            return;
        }

        $terms = $periodId
            ? PeriodProgram::where('period_id', $periodId)
                ->where('campus_id', $campus->id)
                ->whereIn('program_id', $programs->pluck('id')->unique())
                ->get()->keyBy('program_id')
            : collect();

        foreach ($programs as $program) {
            $program->setRelation('offeringCampuses', collect([$campus]));

            if ($periodId) {
                $program->termsCache[$periodId . '|' . $campus->id] =
                    $terms->get($program->id) ?? $program->fallbackTerms($periodId, $campus->id);
            }
        }
    }

    /** Biaya dan kuota di kampus pertama yang menawarkan prodi ini (landing publik). */
    public function landingTerms($period = null): PeriodProgram
    {
        $campus = $this->relationLoaded('offeringCampuses') ? $this->offeringCampuses->first() : null;

        return $this->termsFor($period, $campus);
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
