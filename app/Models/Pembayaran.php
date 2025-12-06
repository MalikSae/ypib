<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'no_pembayaran',
        'jumlah',
        'tanggal_bayar',
        'bukti_pembayaran',
        'status',
        'catatan',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}