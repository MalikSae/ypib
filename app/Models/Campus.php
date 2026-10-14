<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Kampus penyelenggara. Biaya, kuota, dan komisi prodi diatur per kampus lewat PeriodProgram;
 * pendaftaran menyimpan kampus pilihan pendaftar. Kampus bawaan (is_default) memegang semua
 * data yang dibuat sebelum fitur multi-kampus.
 */
class Campus extends Model
{
    use HasFactory;

    protected $table = 'campuses';

    protected $fillable = [
        'name',
        'code',
        'slug',
        'city',
        'address',
        'admin_whatsapp',
        'university_bank_name',
        'university_bank_account',
        'university_bank_account_name',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    private static ?int $defaultId = null;

    /** Cache id kampus bawaan dibuang saat kampus berubah (proses panjang, test). */
    protected static function booted(): void
    {
        $flush = fn () => static::$defaultId = null;

        static::saved($flush);
        static::deleted($flush);
    }

    public static function flushDefaultCache(): void
    {
        static::$defaultId = null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function defaultId(): ?int
    {
        return static::$defaultId ??= static::where('is_default', true)->value('id');
    }

    public static function default(): ?self
    {
        return static::find(static::defaultId());
    }

    /** Terima instance, id, atau null (= kampus bawaan) dan kembalikan id kampus. */
    public static function idOf($campus): ?int
    {
        if ($campus instanceof self) {
            return $campus->id;
        }

        return $campus ? (int) $campus : static::defaultId();
    }

    public function periodPrograms()
    {
        return $this->hasMany(PeriodProgram::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /** Rekening kampus; kosong berarti pakai rekening periode. */
    public function hasBankDetails(): bool
    {
        return filled($this->university_bank_name) && filled($this->university_bank_account);
    }
}
