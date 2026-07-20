<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventaris extends Model
{
    protected $table = 'inventaris';

    protected $fillable = [
        'kategori_id',
        'kode_barang',
        'nama_barang',
        'stok',
        'kondisi',
        'deskripsi',
        'foto',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
    public function detailPermohonan()
    {
    return $this->hasMany(DetailPermohonan::class);
    }
}