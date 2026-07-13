<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationDocument extends Model
{
    protected $fillable = [
        'registration_id',
        'document_type',
        'label',
        'file_path',
        'status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const MANDATORY_TYPES = [
        'foto',
        'ktp',
        'kk',
        'akta_lahir',
        'transkrip_nilai',
        'surat_keterangan_sehat',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
