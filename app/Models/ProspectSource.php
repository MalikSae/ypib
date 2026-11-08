<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat event tempat sebuah prospek muncul. */
class ProspectSource extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['prospect_id', 'prospect_event_id', 'referrer_id', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(ProspectEvent::class, 'prospect_event_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }
}
