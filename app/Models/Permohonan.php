<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    protected $fillable = [
        'nomor_permohonan',
        'instansi_id',
        'nama_instansi_lain',
        'nama_peminjam',
        'nik',
        'jabatan',
        'telepon',
        'alamat',
        'tempat_tanggal_lahir',
        'tanggal_pinjam',
        'tanggal_kembali',
        'keperluan',
        'status',
        'catatan_admin',
        'foto_ktp',
        'surat_tugas',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    public function detailPermohonan()
    {
        return $this->hasMany(DetailPermohonan::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(PermohonanStatusLog::class)->orderBy('created_at');
    }
}