<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Template DOCX surat untuk satu tahun tertentu.
 *
 * Satu tahun boleh punya banyak versi. Hanya satu versi yang aktif, tetapi
 * versi lama tetap tersimpan (tidak dihapus) supaya surat lama masih bisa
 * memakai template yang dulu dipakai saat surat itu dibuat.
 */
class SuratTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'tahun',
        'versi',
        'is_active',
        'nama_file',
        'path',
        'hash',
        'ukuran',
        'catatan',
        'uploaded_by',
    ];

    protected $casts = [
        'tahun'     => 'integer',
        'versi'     => 'integer',
        'is_active' => 'boolean',
        'ukuran'    => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template): void {
            if (blank($template->uuid)) {
                $template->uuid = (string) Str::uuid();
            }
        });
    }

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

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUntukTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Template aktif untuk tahun tertentu (versi terbaru bila ada beberapa).
     */
    public static function untukTahun(int $tahun): ?self
    {
        return static::aktif()
            ->where('tahun', $tahun)
            ->orderByDesc('versi')
            ->first();
    }

    /**
     * Nomor versi berikutnya untuk satu tahun, termasuk versi yang diarsipkan.
     */
    public static function nextVersi(int $tahun): int
    {
        $tertinggi = static::withTrashed()
            ->where('tahun', $tahun)
            ->max('versi');

        return ((int) $tertinggi) + 1;
    }

    public function isArchived(): bool
    {
        return $this->trashed();
    }
}
