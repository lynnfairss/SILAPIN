<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    protected $table = 'jensis';

    protected $fillable = [
        'nama_jenis',
        'keterangan',
    ];

    public function inventaris()
    {
        return $this->hasMany(Inventaris::class);
    }
}
