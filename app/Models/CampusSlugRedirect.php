<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Slug lama sebuah kampus; lihat migrasi create_campus_slug_redirects_table. */
class CampusSlugRedirect extends Model
{
    protected $fillable = ['campus_id', 'slug'];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }
}
