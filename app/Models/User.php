<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'created_by');
    }

    public function verifiedPembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'verified_by');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCalonMahasiswa()
    {
        return $this->role === 'calon_mahasiswa';
    }
}