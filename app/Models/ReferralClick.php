<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'ip_address',
        'user_agent',
        'converted',
        'converted_at',
    ];

    protected $casts = [
        'converted'    => 'boolean',
        'converted_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(Referrer::class);
    }
}
