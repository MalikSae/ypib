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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
        return (int) $this->quota > 0 && $this->occupiedSeats($periodId) >= (int) $this->quota;
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
