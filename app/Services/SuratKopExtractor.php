<?php

namespace App\Services;

use App\Models\Permohonan;
use ZipArchive;

/**
 * Membaca baris kop surat dari file template DOCX yang dipakai sebuah surat.
 *
 * PDF dan pratinjau surat dirender dari tampilan HTML, bukan dari template,
 * jadi tanpa layanan ini kop di PDF tidak ikut berubah ketika admin mengunggah
 * template baru. Yang diambil adalah teks paragraf pembuka beserta gaya
 * dasarnya (tebal, miring, ukuran huruf) supaya keduanya tetap sama rata.
 */
class SuratKopExtractor
{
    /**
     * Kop bawaan aplikasi, dipakai bila template tidak punya baris kop.
     *
     * @var array<int, array{teks: string, tebal: bool, miring: bool, ukuran: float}>
     */
    public const BAWAAN = [
        ['teks' => 'PEMERINTAH KABUPATEN PONOROGO', 'tebal' => true, 'miring' => false, 'ukuran' => 12.0],
        ['teks' => 'DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK', 'tebal' => true, 'miring' => false, 'ukuran' => 12.0],
        ['teks' => 'Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418', 'tebal' => false, 'miring' => false, 'ukuran' => 10.0],
        ['teks' => 'Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id', 'tebal' => false, 'miring' => true, 'ukuran' => 10.0],
        ['teks' => 'P O N O R O G O', 'tebal' => true, 'miring' => false, 'ukuran' => 14.0],
    ];

    /** Kop paling pendek: dua baris (instansi + dinas/kota). */
    private const BARIS_MINIMUM = 2;

    /** @var array<string, array<int, array{teks: string, tebal: bool, miring: bool, ukuran: float}>|null> */
    private static array $cache = [];

    /**
     * Baris kop untuk satu surat, mengikuti template yang resolver pilih.
     *
     * @return array<int, array{teks: string, tebal: bool, miring: bool, ukuran: float}>
     */
    public static function untuk(Permohonan $permohonan): array
    {
        $path = SuratTemplateResolver::forPermohonan($permohonan)?->absolutePath();

        return ($path && is_file($path) ? self::dariFile($path) : null) ?? self::BAWAAN;
    }

    /**
     * Kota pada baris tanggal surat, dibaca dari baris kop terakhir template.
     * "M A D I U N" maupun "KABUPATEN PONOROGO" cukup ditulis sebagai kota
     * saja ("Madiun", "Ponorogo") karena begitu surat resmi menuliskan tanggal.
     * Surat lama ikut kota versinya sendiri karena kop diambil dari template
     * yang tercatat padanya.
     */
    public static function kota(Permohonan $permohonan, string $bawaan = 'Ponorogo'): string
    {
        $baris = self::untuk($permohonan);
        $teks = trim(end($baris)['teks'] ?? '');

        // Baris kota hanya berisi huruf dan spasi, tanpa angka atau tautan.
        if (!preg_match('/^[\p{L} ]{3,40}$/u', $teks)) {
            return $bawaan;
        }

        // Huruf yang diregang ("P O N O R O G O") dihitung satu kata.
        $kota = preg_match('/^(\p{L} )+\p{L}$/u', $teks)
            ? preg_replace('/\s+/u', '', $teks)
            : preg_replace('/\s+/u', ' ', $teks);

        $kota = preg_replace('/^(kabupaten|kota)\s+/iu', '', $kota);

        return $kota === '' ? $bawaan : mb_convert_case($kota, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return array<int, array{teks: string, tebal: bool, miring: bool, ukuran: float}>|null
     */
    public static function dariFile(string $path): ?array
    {
        $kunci = $path . '@' . (filemtime($path) ?: 0);

        if (array_key_exists($kunci, self::$cache)) {
            return self::$cache[$kunci];
        }

        return self::$cache[$kunci] = self::ambil($path);
    }

    /**
     * @return array<int, array{teks: string, tebal: bool, miring: bool, ukuran: float}>|null
     */
    private static function ambil(string $path): ?array
    {
        $zip = new ZipArchive();

        if (!$zip->open($path)) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return null;
        }

        $baris = [];

        foreach (preg_split('/<\/w:p>/', $xml) as $paragraf) {
            $teks = self::teks($paragraf);

            // Paragraf kosong dan placeholder (${LOGO}) bukan teks kop. Selama
            // belum ada baris, keduanya hanya dilewati; sesudah baris pertama,
            // paragraf begitu menandai awal isi surat.
            if ($teks === '' || str_contains($teks, '${')) {
                if ($baris !== []) {
                    break;
                }

                continue;
            }

            $gaya = self::gayaRun($paragraf);

            $baris[] = [
                'teks'   => $teks,
                'tebal'  => self::nyala($gaya, 'w:b'),
                'miring' => self::nyala($gaya, 'w:i'),
                'ukuran' => self::ukuran($gaya),
            ];

            if (count($baris) >= 12) {
                break;
            }
        }

        return count($baris) >= self::BARIS_MINIMUM ? $baris : null;
    }

    private static function teks(string $paragraf): string
    {
        $paragraf = str_replace(['<w:tab/>', '<w:br/>', '<w:br />'], ' ', $paragraf);

        $teks = html_entity_decode(strip_tags($paragraf), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $teks));
    }

    /**
     * Properti run pertama yang berisi teks: itu gaya yang terlihat di kertas.
     */
    private static function gayaRun(string $paragraf): string
    {
        foreach (array_slice(preg_split('/<w:r\b/', $paragraf), 1) as $run) {
            if (!preg_match('/<w:t[^>]*>(.*?)<\/w:t>/s', $run, $isi)) {
                continue;
            }

            if (trim(html_entity_decode(strip_tags($isi[1]), ENT_QUOTES | ENT_XML1, 'UTF-8')) === '') {
                continue;
            }

            preg_match('/<w:rPr>.*?<\/w:rPr>/s', $run, $gaya);

            return $gaya[0] ?? '';
        }

        return '';
    }

    private static function nyala(string $gaya, string $tag): bool
    {
        if (!preg_match('/<' . $tag . '\b(\s[^>]*)?\/?>/', $gaya, $match)) {
            return false;
        }

        return !preg_match('/w:val="(none|off|false|0)"/', $match[0]);
    }

    /**
     * Ukuran huruf dalam point. DOCX menyimpannya dalam half-point; baris tanpa
     * ukuran eksplisit mengikuti baris alamat pada kop bawaan (10 pt).
     */
    private static function ukuran(string $gaya): float
    {
        if (preg_match('/<w:sz\s+w:val="(\d+)"/', $gaya, $match)) {
            return ((int) $match[1]) / 2;
        }

        return 10.0;
    }
}
