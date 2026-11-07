<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Response;

class CsvExport
{
    /** Karakter yang bisa membuka formula di Excel/LibreOffice. */
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Bangkitkan satu berkas CSV dari seluruh baris hasil query.
     *
     * @param  iterable<int, mixed>  $rows
     * @param  array<int, string>  $headers
     */
    public static function stream(iterable $rows, array $headers, Closure $mapRow, string $filename): Response
    {
        $handle = fopen('php://temp', 'r+');

        // BOM supaya Excel membaca UTF-8 (nama instansi ber-aksen).
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, self::bersihkan($headers));

        foreach ($rows as $row) {
            fputcsv($handle, self::bersihkan($mapRow($row)));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . self::namaBerkas($filename) . '"',
        ]);
    }

    /**
     * Sel yang diawali karakter formula diberi apostrof agar tidak ikut
     * dieksekusi saat CSV dibuka di spreadsheet.
     */
    private static function bersihkan(array $row): array
    {
        return array_map(function ($nilai) {
            $nilai = $nilai === null ? '' : (string) $nilai;

            if ($nilai !== '' && in_array($nilai[0], self::FORMULA_PREFIXES, true)) {
                $nilai = "'" . $nilai;
            }

            return $nilai;
        }, $row);
    }

    private static function namaBerkas(string $filename): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

        return str_ends_with(strtolower($filename), '.csv') ? $filename : $filename . '.csv';
    }
}
