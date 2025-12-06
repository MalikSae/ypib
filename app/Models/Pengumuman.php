<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'tanggal_publish',
        'is_active',
    ];

    protected $casts = [
        'tanggal_publish' => 'date',
        'is_active' => 'boolean',
    ];
}