<?php

use App\Models\Permohonan;
use App\Models\User;
use App\Services\SuratKopExtractor;
use App\Services\SuratTemplateResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function kopAdmin(): User
{
    return User::factory()->create([
        'name' => 'Admin Kop',
        'role' => 'admin',
        'status' => 'approved',
    ]);
}

function kopSurat(): Permohonan
{
    return Permohonan::create([
        'nomor_permohonan' => 'SP-KOP-' . uniqid(),
        'nama_peminjam'    => 'Rahma',
        'jabatan'          => 'Staf Bagian Umum',
        'nik'              => '3500000000000009',
        'telepon'          => '081298765432',
        'tanggal_pinjam'   => now()->toDateString(),
        'tanggal_kembali'  => now()->addDay()->toDateString(),
        'keperluan'        => 'Uji kop surat',
        'status'           => 'Disetujui',
    ]);
}

/**
 * Salin template bawaan lalu ganti salah satu teks kopnya. Placeholder dan
 * gaya setiap baris tetap utuh, jadi hasil ini persis seperti template yang
 * diedit admin di Word.
 */
function docxKopBaru(string $cari, string $ganti): string
{
    $path = tempnam(sys_get_temp_dir(), 'silapin-kop-') . '.docx';
    copy(public_path('templates/template_peminjaman.docx'), $path);

    $zip = new ZipArchive();
    $zip->open($path);
    $zip->addFromString('word/document.xml', str_replace(
        $cari,
        $ganti,
        $zip->getFromName('word/document.xml')
    ));
    $zip->close();

    return $path;
}

function unggahKop($test, User $admin, string $path): void
{
    $test->actingAs($admin)
        ->post(route('surat.templates.store'), [
            'tahun'         => 2026,
            'template_file' => new UploadedFile(
                $path,
                'template_kop.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                null,
                true
            ),
        ])
        ->assertSessionHasNoErrors();
}

it('reads the letterhead lines with their styles from a template file', function () {
    $kop = SuratKopExtractor::dariFile(public_path('templates/template_peminjaman.docx'));

    expect($kop)->toBeArray()->and(count($kop))->toBe(5);

    expect($kop[0]['teks'])->toBe('PEMERINTAH KABUPATEN PONOROGO')
        ->and($kop[0]['tebal'])->toBeTrue()
        ->and($kop[0]['ukuran'])->toBe(12.0)
        ->and($kop[3]['miring'])->toBeTrue()
        ->and($kop[3]['ukuran'])->toBe(10.0)
        ->and($kop[4]['teks'])->toBe('P O N O R O G O')
        ->and($kop[4]['tebal'])->toBeTrue()
        ->and($kop[4]['ukuran'])->toBe(14.0);
});

it('stops reading the letterhead before the letter body', function () {
    $kop = SuratKopExtractor::dariFile(public_path('templates/template_peminjaman.docx'));

    expect(collect($kop)->every(fn ($baris) => !str_contains($baris['teks'], '${')))
        ->toBeTrue()
        ->and($kop[count($kop) - 1]['teks'])->toBe('P O N O R O G O');
});

it('uses the active template letterhead in the letter preview', function () {
    $admin = kopAdmin();

    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'KABUPATEN CONTOH'));

    $permohonan = kopSurat();

    $this->actingAs($admin)
        ->get(route('surat.preview', $permohonan))
        ->assertOk()
        ->assertSee('KABUPATEN CONTOH')
        ->assertSee('PEMERINTAH KABUPATEN PONOROGO')
        ->assertDontSee('P O N O R O G O');

    // Gaya baris kota (14 pt tebal) tetap dipakai untuk teks dari template.
    $this->actingAs($admin)
        ->get(route('surat.preview', $permohonan))
        ->assertSee('font-size:14pt;', false)
        ->assertSee('font-weight:bold;', false);
});

it('follows the newest template version in the preview', function () {
    $admin = kopAdmin();

    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'KOTA LAMA'));
    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'KOTA BARU'));

    $permohonan = kopSurat();

    expect(SuratTemplateResolver::forPermohonan($permohonan)->versi)->toBe(2)
        ->and(SuratKopExtractor::untuk($permohonan)[4]['teks'])->toBe('KOTA BARU');

    $this->actingAs($admin)
        ->get(route('surat.preview', $permohonan))
        ->assertSee('KOTA BARU')
        ->assertDontSee('KOTA LAMA');
});

it('keeps the built-in letterhead for a template without one', function () {
    $phpWord = new PhpOffice\PhpWord\PhpWord();
    $phpWord->addSection()->addText('Dokumen tanpa kop surat.');

    $path = tempnam(sys_get_temp_dir(), 'silapin-tanpa-kop-') . '.docx';
    PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($path);

    expect(SuratKopExtractor::dariFile($path))->toBeNull()
        ->and(SuratKopExtractor::untuk(kopSurat()))->toBe(SuratKopExtractor::BAWAAN);
});

it('takes the city of the date line from the template letterhead', function () {
    $admin = kopAdmin();

    expect(SuratKopExtractor::kota(kopSurat()))->toBe('Ponorogo');

    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'M A D I U N'));

    $permohonan = kopSurat();
    $permohonan->forceFill([
        'surat_template_id' => SuratTemplateResolver::activeForYear(2026)?->id,
    ])->saveQuietly();

    expect(SuratKopExtractor::kota($permohonan))->toBe('Madiun');

    $this->actingAs($admin)
        ->get(route('surat.preview', $permohonan))
        ->assertOk()
        ->assertSee('Madiun, ');

    $response = $this->actingAs($admin)->get(route('surat.download.docx', $permohonan));
    $response->assertOk();

    $docx = tempnam(sys_get_temp_dir(), 'silapin-kop-hasil-') . '.docx';
    file_put_contents($docx, $response->streamedContent());

    $zip = new ZipArchive();
    $zip->open($docx);
    $xml = (string) $zip->getFromName('word/document.xml');
    $zip->close();

    expect($xml)->toContain('Madiun, ')->not->toContain('Ponorogo, ');
});

it('keeps the default city when the last letterhead line is not a city', function () {
    $admin = kopAdmin();

    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'Jl. Contoh Nomor 12'));

    expect(SuratKopExtractor::kota(kopSurat()))->toBe('Ponorogo');
});

it('writes only the city name on the date line', function () {
    $admin = kopAdmin();

    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'KABUPATEN PONOROGO'));

    expect(SuratKopExtractor::kota(kopSurat()))->toBe('Ponorogo');

    // Kota dua kata tetap terbaca utuh.
    unggahKop($this, $admin, docxKopBaru('P O N O R O G O', 'KABUPATEN TANJUNG PINANG'));

    expect(SuratKopExtractor::kota(kopSurat()))->toBe('Tanjung Pinang');
});
