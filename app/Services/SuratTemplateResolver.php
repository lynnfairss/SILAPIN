<?php

namespace App\Services;

use App\Models\Permohonan;
use App\Models\SuratTemplate;
use Illuminate\Support\Facades\File;

/**
 * Menentukan file template DOCX yang dipakai untuk sebuah surat.
 *
 * Aturannya: pakai template dari tahun surat dibuat. Kalau tahun itu belum
 * punya template (atau filenya hilang), pakai template default supaya surat
 * tetap bisa dicetak.
 */
class SuratTemplateResolver
{
    public const DEFAULT_RELATIVE = 'templates/template_peminjaman.docx';

    public static function defaultPath(): string
    {
        return public_path(self::DEFAULT_RELATIVE);
    }

    /**
     * Tahun surat: tanggal yang tercetak di surat berasal dari created_at.
     */
    public static function tahun(Permohonan $permohonan): int
    {
        return (int) ($permohonan->created_at?->year ?? now()->year);
    }

    public static function pathFor(int $tahun): string
    {
        $template = SuratTemplate::untukTahun($tahun);
        $path = $template?->absolutePath();

        if ($path && File::isFile($path)) {
            return $path;
        }

        return self::defaultPath();
    }
}
