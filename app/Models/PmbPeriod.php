<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmbPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'open_date',
        'close_date',
        'is_active',
        'university_bank_name',
        'university_bank_account',
        'university_bank_account_name',
        'admin_whatsapp',
    ];

    protected $casts = [
        'open_date'  => 'date',
        'close_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Periode dianggap terbuka bila aktif dan hari ini berada di antara
     * tanggal buka dan tanggal tutup (inklusif).
     */
    public function isOpen(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->startOfDay();

        return $today->gte($this->open_date->copy()->startOfDay())
            && $today->lte($this->close_date->copy()->endOfDay());
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'period_id');
    }
}
