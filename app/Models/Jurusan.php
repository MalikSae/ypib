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
        'status',
    ];

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function mahasiswaDiterima()
    {
        return $this->hasMany(Mahasiswa::class)->whereHas('pembayarans', function($query) {
            $query->where('status', 'terverifikasi');
        });
    }

    public function getSisaKuotaAttribute()
    {
        // Hitung sisa kuota: Total Kuota - Jumlah Mahasiswa Diterima
        $terisi = $this->mahasiswaDiterima()->count();
        return max(0, $this->kuota - $terisi);
    }
}