<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Calon mahasiswa yang menyatakan minat (form minat), belum tentu mendaftar. Terpisah dari
 * registrations: prospek tidak punya akun, NIK, atau alur pembayaran.
 */
class Prospect extends Model
{
    use HasFactory;

    public const STATUS_BARU = 'baru';
    public const STATUS_DIHUBUNGI = 'dihubungi';
    public const STATUS_TERTARIK = 'tertarik';
    public const STATUS_TIDAK_TERTARIK = 'tidak_tertarik';
    public const STATUS_SUDAH_DAFTAR = 'sudah_daftar';

    /** Label dan warna status prospek. Tambah status baru hanya di sini. */
    public const STATUS_LABELS = [
        self::STATUS_BARU             => ['label' => 'Baru', 'color' => 'info'],
        self::STATUS_DIHUBUNGI        => ['label' => 'Sudah Dihubungi', 'color' => 'warning'],
        self::STATUS_TERTARIK         => ['label' => 'Tertarik', 'color' => 'primary'],
        self::STATUS_TIDAK_TERTARIK   => ['label' => 'Tidak Tertarik', 'color' => 'neutral'],
        self::STATUS_SUDAH_DAFTAR     => ['label' => 'Sudah Daftar', 'color' => 'success'],
    ];

    protected $fillable = [
        'full_name',
        'phone',
        'phone_normalized',
        'school_name',
        'education_level',
        'campus_id',
        'program_id',
        'referrer_id',
        'status',
        'internal_notes',
        'referrer_notes',
        'last_contacted_at',
        'wa_clicked_at',
        'fraud_flag',
        'registration_id',
    ];

    protected static function booted(): void
    {
        // Staf kampus tunggal hanya melihat prospek kampusnya (sama seperti pendaftar).
        static::addGlobalScope(new \App\Models\Scopes\CampusScope);
    }

    protected $casts = [
        'last_contacted_at' => 'datetime',
        'wa_clicked_at'     => 'datetime',
        'fraud_flag'        => 'boolean',
    ];

    /**
     * Ubah nomor WA ke bentuk baku 08xxxxxxxxxx. Null bila bukan nomor Indonesia yang masuk akal.
     */
    public static function normalizePhone(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if (str_starts_with($digits, '0062')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }
        if (!str_starts_with($digits, '0')) {
            $digits = '0' . $digits;
        }

        return preg_match('/^08\d{8,13}$/', $digits) ? $digits : null;
    }

    /** Nomor untuk tampilan affiliator: disamarkan sampai prospek mendaftar. */
    public function maskedPhone(): string
    {
        $p = (string) $this->phone_normalized;

        return substr($p, 0, 4) . str_repeat('x', max(strlen($p) - 6, 0)) . substr($p, -2);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status]['label'] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function statusColor(): string
    {
        return self::STATUS_LABELS[$this->status]['color'] ?? 'neutral';
    }

    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_SUDAH_DAFTAR;
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ProspectSource::class)->latest('created_at');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProspectActivity::class)->latest('id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProspectLink::class)->latest('id');
    }
}
