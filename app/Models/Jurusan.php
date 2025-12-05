<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jurusan';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
        'deskripsi',
        'kuota',
        'biaya_pendaftaran',
        'is_active',
    ];

    protected $casts = [
        'kuota' => 'integer',
        'biaya_pendaftaran' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // Relationships
    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'jurusan_id');
    }

    // Accessors
    public function getBiayaPendaftaranFormattedAttribute()
    {
        return 'Rp ' . number_format($this->biaya_pendaftaran, 0, ',', '.');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithAvailableQuota($query)
    {
        return $query->withCount('mahasiswa')
            ->whereColumn('kuota', '>', 'mahasiswa_count');
    }

    // Methods
    public function getAvailableQuota()
    {
        return $this->kuota - $this->mahasiswa()->count();
    }

    public function isQuotaFull()
    {
        return $this->mahasiswa()->count() >= $this->kuota;
    }

    public function hasStudents()
    {
        return $this->mahasiswa()->exists();
    }
}