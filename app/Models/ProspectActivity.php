<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu entri riwayat follow-up prospek. */
class ProspectActivity extends Model
{
    use HasFactory;

    public const TYPE_KONTAK = 'kontak';
    public const TYPE_CATATAN = 'catatan';
    public const TYPE_STATUS = 'status';

    public const TYPE_LABELS = [
        self::TYPE_KONTAK  => 'Dihubungi',
        self::TYPE_CATATAN => 'Catatan',
        self::TYPE_STATUS  => 'Status',
    ];

    protected $fillable = ['prospect_id', 'user_id', 'type', 'note'];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
