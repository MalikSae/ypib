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
        'hero_headline',
        'hero_highlight',
        'hero_subtitle',
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

    public const SLUGS_CACHE_KEY = 'campus_slugs';

    /**
     * Semua slug kampus (cache 1 menit, dibuang saat kampus berubah). Dipakai rute /{slug} supaya
     * path asing dari bot tidak menjalankan query ke database.
     */
    public static function knownSlugs(): array
    {
        return \Illuminate\Support\Facades\Cache::remember(static::SLUGS_CACHE_KEY, 60, fn () => static::pluck('slug')->all());
    }

    /** Cache id kampus bawaan dibuang saat kampus berubah (proses panjang, test). */
    protected static function booted(): void
    {
        $flush = function () {
            static::$defaultId = null;
            \Illuminate\Support\Facades\Cache::forget(static::SLUGS_CACHE_KEY);
        };

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

    /**
     * Judul hero landing kampus sebagai HTML aman: teks di-escape, lalu kata sorotan
     * (hero_highlight) yang ada di judul dibungkus warna aksen. Kosong: judul netral.
     */
    public function heroHeadlineHtml(): \Illuminate\Support\HtmlString
    {
        $headline = $this->hero_headline ?: 'Wujudkan Karirmu di ' . $this->name . '.';
        $highlight = $this->hero_headline ? (string) $this->hero_highlight : 'Karirmu';

        $position = $highlight !== '' ? strpos($headline, $highlight) : false;

        if ($position === false) {
            return new \Illuminate\Support\HtmlString(e($headline));
        }

        // Pecah teks mentah lebih dulu, baru escape tiap potongan: mencari di teks yang sudah
        // di-escape bisa mengenai bagian dalam entitas (mis. "amp" di "&amp;").
        return new \Illuminate\Support\HtmlString(
            e(substr($headline, 0, $position))
            . '<span class="text-[#F1B10E]">' . e($highlight) . '</span>'
            . e(substr($headline, $position + strlen($highlight)))
        );
    }

    public function heroSubtitle(): string
    {
        return $this->hero_subtitle
            ?: 'Universitas YPIB Majalengka — ' . $this->name . '. Pilih program studi yang dibuka di kampus ini.';
    }

    /** Rekening kampus; kosong berarti pakai rekening periode. */
    public function hasBankDetails(): bool
    {
        return filled($this->university_bank_name) && filled($this->university_bank_account);
    }
}
