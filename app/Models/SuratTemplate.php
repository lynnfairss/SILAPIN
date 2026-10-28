<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Template DOCX surat untuk satu tahun tertentu.
 *
 * Berkas disimpan di disk "local" (storage/app/private/surat-templates), bukan
 * di public, karena hanya dipakai server saat membuat surat.
 */
class SuratTemplate extends Model
{
    protected $fillable = [
        'tahun',
        'nama_file',
        'path',
        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function absolutePath(): ?string
    {
        if (blank($this->path)) {
            return null;
        }

        return Storage::disk('local')->path($this->path);
    }

    /**
     * Template yang dipakai untuk tahun tertentu, kalau ada.
     */
    public static function untukTahun(int $tahun): ?self
    {
        return static::where('tahun', $tahun)->first();
    }
}
