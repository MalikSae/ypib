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
        'registration_fee',
        'referral_reward_amount',
        'is_active',
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

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'period_id');
    }
}
