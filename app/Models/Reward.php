<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'registration_id',
        'amount',
        'reward_type',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(Referrer::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'Pending',
            'approved'  => 'Siap Cair',
            'disbursed' => 'Sudah Cair',
            'cancelled' => 'Dibatalkan',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'gray',
            'approved'  => 'blue',
            'disbursed' => 'green',
            'cancelled' => 'red',
            default     => 'gray',
        };
    }
}
