<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'deskripsi',
        'kuota',
    ];

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }
}