<?php

namespace App\Services;

use App\Models\SuratTemplate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Memindai berkas template DOCX yang sudah ada di folder template, lalu
 * mendaftarkannya ke database.
 *
 * Berkas asli tidak pernah dihapus. Berkas dari folder public disalin ke
 * storage private dengan nama acak, sedangkan berkas yang sudah berada di
 * storage private didaftarkan apa adanya.
 */
class SuratTemplateScanner
{
    public const STORAGE_DIR = 'surat-templates';

    /**
     * @return array{baru:int, dilewati:int, berkas:int}
     */
    public function scan(?int $userId = null): array
    {
        $berkas = $this->daftarBerkas();

        $baru = 0;
        $dilewati = 0;

        foreach ($berkas as $sumber) {
            $hash = @hash_file('sha256', $sumber['absolute']);

            if (! $hash) {
                $dilewati++;
                continue;
            }

            $sudahAda = SuratTemplate::withTrashed()
                ->where('hash', $hash)
                ->exists();

            if ($sudahAda) {
                $dilewati++;
                continue;
            }

            $tahun = $this->tahunDariNama($sumber['nama'])
                ?? (int) date('Y', (int) @filemtime($sumber['absolute']));

            $versi = SuratTemplate::nextVersi($tahun);
            $punyaAktif = SuratTemplate::aktif()->where('tahun', $tahun)->exists();

            $relative = $sumber['relative'];

            if ($relative === null) {
                // Berkas dari public: salin ke storage private dengan nama acak.
                $relative = self::STORAGE_DIR . '/' . Str::uuid() . '.docx';
                Storage::disk('local')->put($relative, File::get($sumber['absolute']));
            }

            SuratTemplate::create([
                'tahun'       => $tahun,
                'versi'       => $versi,
                'is_active'   => ! $punyaAktif,
                'nama_file'   => $sumber['nama'],
                'path'        => $relative,
                'hash'        => $hash,
                'ukuran'      => (int) @filesize($sumber['absolute']),
                'catatan'     => 'Hasil pemindaian folder template.',
                'uploaded_by' => $userId,
            ]);

            $baru++;
        }

        return [
            'baru'     => $baru,
            'dilewati' => $dilewati,
            'berkas'   => count($berkas),
        ];
    }

    /**
     * @return array<int, array{absolute:string, nama:string, relative:?string}>
     */
    private function daftarBerkas(): array
    {
        $hasil = [];

        // 1. Berkas yang sudah ada di storage private.
        $storageDir = Storage::disk('local')->path(self::STORAGE_DIR);
        if (File::isDirectory($storageDir)) {
            foreach (File::files($storageDir) as $file) {
                if (strtolower($file->getExtension()) !== 'docx') {
                    continue;
                }

                $nama = $file->getFilename();
                $relative = self::STORAGE_DIR . '/' . $nama;

                // Berkas dengan nama non-acak disalin ke nama acak agar tidak
                // bertabrakan dengan unggahan baru.
                if (! $this->namaAcak($nama)) {
                    $relative = null;
                }

                $hasil[] = [
                    'absolute' => $file->getPathname(),
                    'nama'     => $nama,
                    'relative' => $relative,
                ];
            }
        }

        // 2. Template default / lama di folder public.
        $publicDir = public_path('templates');
        if (File::isDirectory($publicDir)) {
            foreach (File::files($publicDir) as $file) {
                if (strtolower($file->getExtension()) !== 'docx') {
                    continue;
                }

                $hasil[] = [
                    'absolute' => $file->getPathname(),
                    'nama'     => $file->getFilename(),
                    'relative' => null,
                ];
            }
        }

        return $hasil;
    }

    private function tahunDariNama(string $nama): ?int
    {
        if (preg_match('/(20\d{2})/', $nama, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function namaAcak(string $nama): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.docx$/i',
            $nama
        );
    }
}
