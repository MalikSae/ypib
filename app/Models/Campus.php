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

    public function slugRedirects()
    {
        return $this->hasMany(CampusSlugRedirect::class);
    }

    /**
     * Kampus aktif dengan slug ini, atau slug lamanya (alias hasil penggantian slug).
     */
    public static function findActiveBySlug(string $slug): ?self
    {
        if ($slug === '') {
            return null;
        }

        $campus = static::active()->where('slug', $slug)->first();

        if ($campus) {
            return $campus;
        }

        $alias = CampusSlugRedirect::where('slug', $slug)->first();

        return $alias ? static::active()->find($alias->campus_id) : null;
    }

    /** Alamat landing publik: / untuk kampus bawaan, /slug untuk kampus lain. */
    public function landingUrl(): string
    {
        return $this->is_default ? route('landing') : route('campus.landing', $this->slug);
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
