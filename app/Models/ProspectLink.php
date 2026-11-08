<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Link daftar terkunci untuk satu prospek. Sekali pakai, kedaluwarsa mengikuti periode. */
class ProspectLink extends Model
{
    use HasFactory;

    protected $fillable = ['prospect_id', 'prospect_event_id', 'discount_amount', 'token', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at'      => 'datetime',
        'used_at'         => 'datetime',
        'discount_amount' => 'integer',
    ];

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public static function newToken(): string
    {
        do {
            $token = Str::lower(Str::random(40));
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(ProspectEvent::class, 'prospect_event_id');
    }
}
