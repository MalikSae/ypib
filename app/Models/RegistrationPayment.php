<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satu pembayaran daftar ulang (bisa dicicil). Hanya yang terverifikasi dihitung di laporan. */
class RegistrationPayment extends Model
{
    public const TYPE_RE_REGISTRATION = 're_registration';

    public const STATUS_PENDING  = 'menunggu_verifikasi';
    public const STATUS_VERIFIED = 'terverifikasi';
    public const STATUS_REJECTED = 'ditolak';

    protected $fillable = [
        'registration_id',
        'type',
        'amount',
        'claimed_amount',
        'proof_path',
        'status',
        'note',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount'         => 'integer',
        'claimed_amount' => 'integer',
        'verified_at'    => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeReRegistration($query)
    {
        return $query->where('type', self::TYPE_RE_REGISTRATION);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
