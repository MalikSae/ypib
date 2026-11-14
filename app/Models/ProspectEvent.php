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

    /**
     * Event yang mendapat kredit pendaftaran seorang prospek (alias tabel prospek: p): event pada
     * link daftar yang terpakai, atau event pertama yang diikuti bila mendaftar tanpa link.
     */
    private const ATTRIBUTED_EVENT_SQL = 'coalesce('
        . '(select pl.prospect_event_id from prospect_links pl where pl.prospect_id = p.id'
        . ' and pl.used_at is not null and pl.prospect_event_id is not null order by pl.used_at desc, pl.id desc limit 1), '
        . '(select ps.prospect_event_id from prospect_sources ps where ps.prospect_id = p.id'
        . ' order by ps.created_at asc, ps.id asc limit 1))';

    /**
     * Corong per event: prospects_count (semua prospek yang ikut event), registered_count (bayar
     * pendaftaran terkonfirmasi), dan reregistered_count (daftar ulang selesai). Prospek bertanda
     * fraud tidak dihitung. Satu pendaftaran hanya dikreditkan ke satu event.
     */
    public function scopeWithFunnel($query)
    {
        $unpaid = ['menunggu_pembayaran', 'menunggu_konfirmasi'];
        $paid = array_values(array_diff(array_keys(Registration::STATUS_LABELS), $unpaid));

        $converted = fn (array $statuses) => \Illuminate\Support\Facades\DB::table('prospects as p')
            ->join('registrations as r', 'r.id', '=', 'p.registration_id')
            ->where('p.fraud_flag', false)
            ->whereNull('r.deleted_at')
            ->whereIn('r.status', $statuses)
            ->whereRaw(self::ATTRIBUTED_EVENT_SQL . ' = prospect_events.id')
            ->selectRaw('count(*)');

        if ($query->getQuery()->columns === null) {
            $query->select('prospect_events.*');
        }

        return $query
            ->selectSub(
                \Illuminate\Support\Facades\DB::table('prospect_sources as s')
                    ->join('prospects as sp', 'sp.id', '=', 's.prospect_id')
                    ->where('sp.fraud_flag', false)
                    ->whereColumn('s.prospect_event_id', 'prospect_events.id')
                    ->selectRaw('count(*)'),
                'prospects_count'
            )
            ->selectSub($converted($paid), 'registered_count')
            ->selectSub($converted(['daftar_ulang_selesai']), 'reregistered_count');
    }

    /** Persen terhadap jumlah prospek event ini; null bila belum ada prospek. */
    public function funnelPercent(int $count): ?int
    {
        return $this->prospects_count > 0 ? (int) round($count / $this->prospects_count * 100) : null;
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
