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

    /**
     * Kelas badge status: pill pastel dengan border tipis, gaya sama dengan <x-badge-status>.
     * Satu sumber untuk semua tampilan (daftar, kartu mobile, detail).
     */
    public function statusBadgeClass(): string
    {
        return [
            'info'    => 'bg-info-50 text-info-700 border-info-200',
            'warning' => 'bg-warning-50 text-warning-700 border-warning-200',
            'primary' => 'bg-primary-50 text-primary-700 border-primary-200',
            'success' => 'bg-success-50 text-success-700 border-success-200',
            'neutral' => 'bg-neutral-100 text-neutral-600 border-neutral-200',
        ][$this->statusColor()] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200';
    }

    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_SUDAH_DAFTAR;
    }

    /** Catat kontak: waktu kontak diperbarui, status "baru" naik jadi "sudah dihubungi". */
    public function markContacted(): void
    {
        $this->forceFill([
            'last_contacted_at' => now(),
            'status'            => $this->status === self::STATUS_BARU ? self::STATUS_DIHUBUNGI : $this->status,
        ])->save();
    }

    /** Nomor WA dalam format internasional (62…) untuk tautan wa.me. */
    public function whatsappDigits(): string
    {
        return self::toWhatsappDigits($this->phone_normalized);
    }

    public static function toWhatsappDigits(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return str_starts_with($digits, '0') ? '62' . substr($digits, 1) : $digits;
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
