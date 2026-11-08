<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Event sosialisasi/seminar tempat form minat dibagikan, beserta potongan daftar ulang-nya. */
class ProspectEvent extends Model
{
    use HasFactory;

    public const KIND_EVENT = 'event';
    public const KIND_ONLINE = 'online';

    public const KIND_LABELS = [
        self::KIND_EVENT  => 'Event kampus',
        self::KIND_ONLINE => 'Kampanye online',
    ];

    protected $fillable = [
        'campus_id',
        'kind',
        'period_id',
        'name',
        'slug',
        'school_name',
        'event_date',
        'discount_amount',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\CampusScope);
    }

    protected $casts = [
        'event_date'      => 'date',
        'discount_amount' => 'integer',
        'is_active'       => 'boolean',
    ];

    public static function makeSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';

        do {
            $slug = $base . '-' . Str::lower(Str::random(4));
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    public function isOnline(): bool
    {
        return $this->kind === self::KIND_ONLINE;
    }

    public function scopeOnline($query)
    {
        return $query->where('kind', self::KIND_ONLINE);
    }

    /** Form minat masih menerima pengisian: event aktif dan periode (bila ada) belum tutup. */
    public function acceptsInterest(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $period = $this->period;

        return $period === null || $period->close_date === null || $period->close_date->endOfDay()->isFuture();
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PmbPeriod::class, 'period_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ProspectSource::class, 'prospect_event_id');
    }
}
