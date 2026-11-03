<?php

namespace App\Services;

use App\Models\Permohonan;
use App\Models\SuratTemplate;
use Illuminate\Support\Facades\File;

/**
 * Menentukan file template DOCX yang dipakai untuk sebuah surat.
 *
 * Urutan pencarian:
 *   1. template yang dipilih khusus untuk surat itu (termasuk versi lama);
 *   2. template aktif untuk tahun surat dibuat;
 *   3. versi terbaru tahun itu, termasuk yang sudah diarsipkan
 *      (menjaga surat lama yang belum sempat dipin);
 *   4. template aktif terbaru dari tahun mana pun;
 *   5. template default di public/templates.
 *
 * Tidak ada template yang dihapus dari proses ini. File lama tetap dipakai
 * bila memang itu yang tercatat pada surat.
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

    /**
     * Template yang dipakai oleh satu surat.
     */
    public static function forPermohonan(Permohonan $permohonan): ?SuratTemplate
    {
        // 1. Pilihan manual admin/super admin untuk surat ini.
        if ($permohonan->surat_template_id) {
            $dipilih = self::usable(
                SuratTemplate::withTrashed()->find($permohonan->surat_template_id)
            );

            if ($dipilih) {
                return $dipilih;
            }
        }

        $tahun = self::tahun($permohonan);

        // 2. Template aktif tahun surat.
        $aktif = self::activeForYear($tahun);
        if ($aktif) {
            return $aktif;
        }

        // 3. Surat lama yang belum dipin: pakai versi tahun itu, termasuk arsip.
        $versiTahunItu = self::latestForYear($tahun, true);
        if ($versiTahunItu) {
            return $versiTahunItu;
        }

        // 4. Template aktif terbaru (mis. surat 2027 saat template 2027 belum ada).
        return self::latestActive();
    }

    /**
     * Template aktif untuk satu tahun.
     */
    public static function activeForYear(int $tahun): ?SuratTemplate
    {
        return self::usable(
            SuratTemplate::aktif()
                ->where('tahun', $tahun)
                ->orderByDesc('versi')
                ->first()
        );
    }

    /**
     * Versi terbaru untuk satu tahun. Bisa menyertakan versi yang diarsipkan.
     */
    public static function latestForYear(int $tahun, bool $withTrashed = false): ?SuratTemplate
    {
        $query = $withTrashed ? SuratTemplate::withTrashed() : SuratTemplate::query();

        return self::usable(
            $query->where('tahun', $tahun)->orderByDesc('versi')->first()
        );
    }

    /**
     * Template aktif dengan tahun terbaru (fallback lintas tahun).
     */
    public static function latestActive(): ?SuratTemplate
    {
        return self::usable(
            SuratTemplate::aktif()
                ->orderByDesc('tahun')
                ->orderByDesc('versi')
                ->first()
        );
    }

    /**
     * Path file template untuk sebuah surat atau tahun.
     */
    public static function pathFor(Permohonan|int $subject): string
    {
        $template = $subject instanceof Permohonan
            ? self::forPermohonan($subject)
            : (self::activeForYear($subject) ?? self::latestActive());

        return $template?->absolutePath() ?? self::defaultPath();
    }

    private static function usable(?SuratTemplate $template): ?SuratTemplate
    {
        if (! $template) {
            return null;
        }

        $path = $template->absolutePath();

        return $path && File::isFile($path) ? $template : null;
    }
}
