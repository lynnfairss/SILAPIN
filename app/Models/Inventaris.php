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
        'jenis_id',
        'stok',
        'kondisi',
        'deskripsi',
        'foto',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function jenis()
    {
        return $this->belongsTo(Jenis::class);
    }

    public function detailPermohonan()
    {
        return $this->hasMany(DetailPermohonan::class);
    }

    public function fotos()
    {
        return $this->hasMany(InventarisFoto::class)->orderBy('urutan');
    }
}