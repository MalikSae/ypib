<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referrer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'total_clicks',
        'total_conversions',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clicks()
    {
        return $this->hasMany(ReferralClick::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}
