<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferrerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'acted_by',
        'action',
        'note',
    ];

    public function referrer()
    {
        return $this->belongsTo(Referrer::class);
    }

    public function actedBy()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
