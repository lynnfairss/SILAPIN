<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventaris;

class Kategori extends Model
{
    protected $fillable = [
        'nama_kategori',
        'keterangan',
    ];

    /**
     * Satu kategori memiliki banyak inventaris
     */
    public function inventaris()
    {
        return $this->hasMany(Inventaris::class);
    }
}