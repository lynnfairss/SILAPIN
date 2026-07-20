<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $fillable = [
        'nama_instansi',
        'alamat',
        'telepon',
    ];

    public function permohonan()
    {
        return $this->hasMany(Permohonan::class);
    }
}