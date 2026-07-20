<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    protected $fillable = [
        'instansi_id',
        'nama_peminjam',
        'nik',
        'jabatan',
        'telepon',
        'tanggal_pinjam',
        'tanggal_kembali',
        'keperluan',
        'status',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    public function detailPermohonan()
    {
        return $this->hasMany(DetailPermohonan::class);
    }
}