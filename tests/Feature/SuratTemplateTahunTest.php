<?php

use App\Models\Permohonan;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Services\SuratTemplateResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function reprintAdmin(): User
{
    return User::factory()->create([
        'name' => 'Admin Reprint',
        'role' => 'admin',
        'status' => 'approved',
    ]);
}

function reprintSurat(string $tanggal): Permohonan
{
    $permohonan = Permohonan::create([
        'nomor_permohonan' => 'SP-REPRINT-' . uniqid(),
        'nama_peminjam'    => 'Rahma',
        'jabatan'          => 'Staf Bagian Umum',
        'nik'              => '3500000000000009',
        'telepon'          => '081298765432',
        'tanggal_pinjam'   => now()->toDateString(),
        'tanggal_kembali'  => now()->addDay()->toDateString(),
        'keperluan'        => 'Uji cetak ulang antar tahun',
        'status'           => 'Disetujui',
    ]);

    $permohonan->created_at = $tanggal;
    $permohonan->saveQuietly();

    return $permohonan->fresh();
}

/**
 * Meniru cara surat baru mencatat template saat dibuat: versi yang sedang
 * aktif pada tahun itu. Setelah tercatat, surat tidak ikut berubah walau
 * admin mengunggah atau mengaktifkan versi baru.
 */
function catatVersiAktif(Permohonan $permohonan, int $tahun): Permohonan
{
    $permohonan->forceFill([
        'surat_template_id' => SuratTemplateResolver::activeForYear($tahun)?->id,
    ])->saveQuietly();

    return $permohonan->fresh();
}

/**
 * Template DOCX dengan baris kop khusus per tahun, supaya bisa dibuktikan
 * surat tahun berapa pun memakai kop tahun itu saat dicetak ulang.
 */
function docxKopTahun(string $penanda): string
{
    $path = tempnam(sys_get_temp_dir(), 'silapin-reprint-') . '.docx';
    copy(public_path('templates/template_peminjaman.docx'), $path);

    $zip = new ZipArchive();
    $zip->open($path);
    $zip->addFromString('word/document.xml', str_replace(
        'P O N O R O G O',
        $penanda,
        $zip->getFromName('word/document.xml')
    ));
    $zip->close();

    return $path;
}

function unggahReprint($test, User $admin, int $tahun, string $penanda): void
{
    $test->actingAs($admin)
        ->post(route('surat.templates.store'), [
            'tahun'         => $tahun,
            'template_file' => new UploadedFile(
                docxKopTahun($penanda),
                'template_' . $tahun . '.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                null,
                true
            ),
        ])
        ->assertSessionHasNoErrors();
}

/** Isi word/document.xml dari DOCX yang dihasilkan aplikasi. */
function xmlSuratHasil($test, User $admin, Permohonan $permohonan): string
{
    $response = $test->actingAs($admin)->get(route('surat.download.docx', $permohonan));
    $response->assertOk();

    $path = tempnam(sys_get_temp_dir(), 'silapin-hasil-') . '.docx';
    file_put_contents($path, $response->streamedContent());

    $zip = new ZipArchive();
    $zip->open($path);
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    return (string) $xml;
}

it('uses each year template for letters from that year', function () {
    $admin = reprintAdmin();

    unggahReprint($this, $admin, 2025, 'KOP TAHUN 2025');
    $surat2025 = reprintSurat('2025-06-01 09:00:00');
    catatVersiAktif($surat2025, 2025);

    unggahReprint($this, $admin, 2027, 'KOP TAHUN 2027');
    $surat2027 = reprintSurat('2027-02-01 09:00:00');
    catatVersiAktif($surat2027, 2027);

    expect(SuratTemplateResolver::forPermohonan($surat2025)->tahun)->toBe(2025)
        ->and(SuratTemplateResolver::forPermohonan($surat2027)->tahun)->toBe(2027)
        // Keduanya boleh aktif bersamaan: satu versi aktif per tahun.
        ->and(SuratTemplate::aktif()->pluck('tahun')->sort()->values()->all())->toBe([2025, 2027]);

    $xml2025 = xmlSuratHasil($this, $admin, $surat2025);
    $xml2027 = xmlSuratHasil($this, $admin, $surat2027);

    expect($xml2025)->toContain('KOP TAHUN 2025')->not->toContain('KOP TAHUN 2027');
    expect($xml2027)->toContain('KOP TAHUN 2027')->not->toContain('KOP TAHUN 2025');

    // Surat 2025 tetap 2025 walaupun template 2027 baru saja diaktifkan.
    expect(SuratTemplateResolver::forPermohonan($surat2025->fresh())->tahun)->toBe(2025);
});

it('keeps using the archived year template when reprinting an old letter', function () {
    $admin = reprintAdmin();

    unggahReprint($this, $admin, 2025, 'KOP LAMA 2025');
    $lama = SuratTemplate::where('tahun', 2025)->first();

    $this->actingAs($admin)
        ->delete(route('surat.templates.destroy', $lama))
        ->assertRedirect();

    unggahReprint($this, $admin, 2027, 'KOP BARU 2027');

    $surat2025 = reprintSurat('2025-06-01 09:00:00');

    // Surat 2025 tetap memakai template 2025 walau sudah diarsipkan.
    expect(SuratTemplateResolver::forPermohonan($surat2025)->id)->toBe($lama->id);
});

it('can deactivate a template without archiving it and keeps old letters on it', function () {
    $admin = reprintAdmin();

    unggahReprint($this, $admin, 2026, 'KOP MATI 2026');
    $lama = SuratTemplate::where('tahun', 2026)->first();

    $surat = catatVersiAktif(reprintSurat('2026-06-01 09:00:00'), 2026);

    $this->actingAs($admin)
        ->post(route('surat.templates.deactivate', $lama))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($lama->fresh()->is_active)->toBeFalse()
        ->and($lama->fresh()->trashed())->toBeFalse()
        // Surat lama tetap pada versinya walau template itu sudah dimatikan.
        ->and(SuratTemplateResolver::forPermohonan($surat->fresh())->id)->toBe($lama->id);

    // Versi baru tahun yang sama hanya dipakai surat yang dibuat setelahnya.
    unggahReprint($this, $admin, 2026, 'KOP HIDUP 2026');
    $baru = SuratTemplate::where('tahun', 2026)->orderByDesc('versi')->first();

    $suratBaru = catatVersiAktif(reprintSurat('2026-07-01 09:00:00'), 2026);

    expect(SuratTemplateResolver::forPermohonan($suratBaru)->id)->toBe($baru->id)
        ->and(SuratTemplateResolver::forPermohonan($surat->fresh())->id)->toBe($lama->id)
        // Surat tahun itu yang versinya belum tercatat tetap terlayani.
        ->and(SuratTemplateResolver::forPermohonan(reprintSurat('2026-08-01 09:00:00'))->id)->toBe($baru->id);

    $this->actingAs($admin)
        ->post(route('surat.templates.deactivate', $baru))
        ->assertRedirect()
        ->assertSessionHas('success');

    // Semua versi tahun 2026 mati: surat lama tak terpengaruh, surat tanpa catatan
    // tetap memakai versi terbaru tahun itu.
    expect(SuratTemplateResolver::forPermohonan($surat->fresh())->id)->toBe($lama->id)
        ->and(SuratTemplateResolver::activeForYear(2026))->toBeNull();
});

it('refuses to deactivate a template that is already inactive', function () {
    $admin = reprintAdmin();

    unggahReprint($this, $admin, 2026, 'KOP SATU');
    unggahReprint($this, $admin, 2026, 'KOP DUA');
    $lama = SuratTemplate::where('tahun', 2026)->orderBy('versi')->first();

    $this->actingAs($admin)
        ->post(route('surat.templates.deactivate', $lama))
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('offers activate and deactivate controls on the template list', function () {
    $admin = reprintAdmin();

    unggahReprint($this, $admin, 2026, 'KOP LAMA AKTIF');
    unggahReprint($this, $admin, 2026, 'KOP BARU AKTIF');

    $this->actingAs($admin)
        ->get(route('surat.upload'))
        ->assertOk()
        ->assertSee('Nonaktifkan, tapi tetap tersimpan')
        ->assertSee('Aktifkan versi ini');
});
