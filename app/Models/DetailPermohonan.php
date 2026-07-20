<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPermohonan extends Model
{
    protected $fillable = [
        'permohonan_id',
        'inventaris_id',
        'jumlah',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}