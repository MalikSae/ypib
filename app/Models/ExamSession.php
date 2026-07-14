<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'status',
        'score',
        'result_label',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }
}
