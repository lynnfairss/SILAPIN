<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'surat_content',
        'bukti_pengembalian',
        'tanggal_pengembalian',
        'last_sync_at',
        'word_path',
        'pdf_path',
    ];

    protected $casts = [
        'surat_content' => 'array',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Sengaja tidak ada di $fillable: token adalah rahasia yang harus dibuat
     * server, bukan pernah diterima dari input pengguna.
     */
    protected static function booted(): void
    {
        static::creating(function (self $permohonan): void {
            if (blank($permohonan->token)) {
                $permohonan->token = Str::random(64);
            }
        });
    }

    /**
     * Bandingkan token dengan constant-time agar tidak bisa ditebak lewat
     * perbedaan waktu respons.
     */
    public function tokenMatches(?string $token): bool
    {
        return filled($token)
            && filled($this->token)
            && hash_equals($this->token, $token);
    }

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