<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'slug',
        'faculty_id',
        'accreditation',
        'quota',
        'registration_fee',
        're_registration_fee',
        're_registration_fee_details',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        're_registration_fee_details' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function galleries()
    {
        return $this->hasMany(ProgramGallery::class);
    }
}
