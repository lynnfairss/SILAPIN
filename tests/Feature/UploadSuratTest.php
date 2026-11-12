<?php

use App\Models\Permohonan;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Services\SuratTemplateResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

beforeEach(function () {
    Storage::fake('local');
});

function adminUpload(): User
{
    return User::factory()->create([
        'name' => 'Admin Upload',
        'role' => 'admin',
        'status' => 'approved',
    ]);
}

function permohonanUpload(?string $nomor = null): Permohonan
{
    return Permohonan::create([
        'nomor_permohonan' => $nomor ?? 'SP-TEST-UP-'.uniqid(),
        'nama_peminjam'    => 'Rahma',
        'jabatan'          => 'Staf Bagian Umum',
        'nik'              => '3500000000000009',
        'telepon'          => '081298765432',
        'tanggal_pinjam'   => now()->toDateString(),
        'tanggal_kembali'  => now()->addDay()->toDateString(),
        'keperluan'        => 'Uji upload surat',
        'status'           => 'Disetujui',
    ]);
}

/**
 * Ambil DOCX yang dihasilkan aplikasi (sudah terisi semua placeholder),
 * lalu jadikan file unggahan seperti yang dipilih admin.
 */
function docxSiapUnggah($test, User $admin, Permohonan $permohonan): UploadedFile
{
    $response = $test->actingAs($admin)->get(route('surat.download.docx', $permohonan));
    $response->assertOk();

    $sementara = tempnam(sys_get_temp_dir(), 'silapin-upload-') . '.docx';
    file_put_contents($sementara, $response->streamedContent());

    return new UploadedFile(
        $sementara,
        $permohonan->nomor_permohonan . '.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true
    );
}

function docxKosong(): string
{
    $phpWord = new PhpWord();
    $phpWord->addSection()->addText('Dokumen tanpa placeholder.');

    $path = tempnam(sys_get_temp_dir(), 'silapin-kosong-') . '.docx';
    IOFactory::createWriter($phpWord, 'Word2007')->save($path);

    return $path;
}

/**
 * Buat DOCX dari template default dengan penanda tambahan supaya setiap
 * berkas punya hash berbeda (uji versi tidak dianggap duplikat).
 */
function docxTemplate(string $penanda): string
{
    $phpWord = IOFactory::load(public_path('templates/template_peminjaman.docx'));
    $phpWord->getSections()[0]->addText($penanda);

    $path = tempnam(sys_get_temp_dir(), 'silapin-tpl-') . '.docx';
    IOFactory::createWriter($phpWord, 'Word2007')->save($path);

    return $path;
}

function unggahTemplate($test, User $admin, int $tahun, string $path, ?string $catatan = null)
{
    $berkas = new UploadedFile(
        $path,
        'template_' . $tahun . '.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true
    );

    return $test->actingAs($admin)->post(route('surat.templates.store'), [
        'tahun'         => $tahun,
        'template_file' => $berkas,
        'catatan'       => $catatan,
    ]);
}

function suratTahun(Permohonan $permohonan, string $tanggal): Permohonan
{
    $permohonan->created_at = $tanggal;
    $permohonan->save();

    return $permohonan->fresh();
}

it('redirects the old sync page to the upload page', function () {
    $this->actingAs(adminUpload())
        ->get('/surat/sync-status')
        ->assertRedirect('/surat/upload');
});

it('refuses guests on the upload page', function () {
    $this->get(route('surat.upload'))->assertRedirect(route('login'));
});

it('lets admins open the upload page', function () {
    $this->actingAs(adminUpload())
        ->get(route('surat.upload'))
        ->assertOk()
        ->assertSee('Upload Surat')
        ->assertSee('Template Surat per Tahun')
        ->assertSee('Pindai Folder Template');
});

it('shows the per-letter template picker on the upload page', function () {
    $admin = adminUpload();
    $permohonan = permohonanUpload('SP-PILIH-CARD');

    $this->actingAs($admin)
        ->get(route('surat.upload', ['permohonan' => $permohonan->id]))
        ->assertOk()
        ->assertSee('Template untuk Surat Terpilih')
        ->assertSee('SP-PILIH-CARD');
});

it('lets admins open the letter list with template info', function () {
    $admin = adminUpload();
    permohonanUpload('SP-INDEX-LIST');

    $this->actingAs($admin)
        ->get(route('surat.index'))
        ->assertOk()
        ->assertSee('SP-INDEX-LIST')
        ->assertSee('Template');
});

it('stores an uploaded docx and refreshes the letter content', function () {
    $admin = adminUpload();
    $permohonan = permohonanUpload();

    $berkas = docxSiapUnggah($this, $admin, $permohonan);

    $this->actingAs($admin)
        ->post(route('surat.upload.store'), [
            'permohonan_id' => $permohonan->id,
            'file'          => $berkas,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $permohonan->refresh();

    expect($permohonan->surat_content['nama_peminjam'])->toBe('Rahma')
        ->and($permohonan->surat_content['nik'])->toBe('3500000000000009')
        ->and($permohonan->word_path)->not->toBeNull()
        ->and($permohonan->last_sync_at)->not->toBeNull()
        ->and($permohonan->pdf_path)->not->toBeNull();

    Storage::disk('local')->assertExists('surat-docx/' . $permohonan->nomor_permohonan . '.docx');
    Storage::disk('local')->assertExists('surat-pdf/' . $permohonan->nomor_permohonan . '.pdf');

    // Berkas yang tersimpan bisa diunduh kembali oleh admin.
    $this->actingAs($admin)
        ->get(route('surat.berkas', $permohonan))
        ->assertOk();
});

it('rejects an uploaded file that is not a docx', function () {
    $admin = adminUpload();
    $permohonan = permohonanUpload();

    $berkas = UploadedFile::fake()->create('surat.pdf', 20, 'application/pdf');

    $this->actingAs($admin)
        ->post(route('surat.upload.store'), [
            'permohonan_id' => $permohonan->id,
            'file'          => $berkas,
        ])
        ->assertSessionHasErrors('file');

    expect($permohonan->fresh()->word_path)->toBeNull();
});

it('stores a yearly template and resolves it for that year', function () {
    $admin = adminUpload();

    $berkas = new UploadedFile(
        public_path('templates/template_peminjaman.docx'),
        'template_2026.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true
    );

    $this->actingAs($admin)
        ->post(route('surat.templates.store'), [
            'tahun'         => 2026,
            'template_file' => $berkas,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $template = SuratTemplate::where('tahun', 2026)->first();

    expect($template)->not->toBeNull()
        ->and($template->uploaded_by)->toBe($admin->id);

    Storage::disk('local')->assertExists($template->path);

    expect(SuratTemplateResolver::pathFor(2026))
        ->toBe(Storage::disk('local')->path($template->path));
});

it('falls back to the default template when the year has none', function () {
    $permohonan = permohonanUpload();
    $permohonan->created_at = '2026-05-01 10:00:00';
    $permohonan->save();

    expect(SuratTemplateResolver::tahun($permohonan))->toBe(2026)
        ->and(SuratTemplateResolver::pathFor(2026))
        ->toBe(public_path('templates/template_peminjaman.docx'));
});

it('uses the yearly template when generating the docx', function () {
    $admin = adminUpload();
    $permohonan = permohonanUpload();
    $permohonan->created_at = '2026-05-01 10:00:00';
    $permohonan->save();

    // Buat template 2026 dari template default, lalu beri penanda unik.
    $phpWord = IOFactory::load(public_path('templates/template_peminjaman.docx'));
    $phpWord->getSections()[0]->addText('PENANDA TEMPLATE 2026');

    $sementara = tempnam(sys_get_temp_dir(), 'tpl-') . '.docx';
    IOFactory::createWriter($phpWord, 'Word2007')->save($sementara);

    $relative = 'surat-templates/template_peminjaman_2026.docx';
    Storage::disk('local')->put($relative, file_get_contents($sementara));

    SuratTemplate::create([
        'tahun'       => 2026,
        'nama_file'   => 'template_2026.docx',
        'path'        => $relative,
        'uploaded_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('surat.download.docx', $permohonan));
    $response->assertOk();

    $hasil = tempnam(sys_get_temp_dir(), 'hasil-') . '.docx';
    file_put_contents($hasil, $response->streamedContent());

    $zip = new ZipArchive();
    $zip->open($hasil);
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    expect($xml)->toBeString()->toContain('PENANDA TEMPLATE 2026');
});

it('rejects a template without the required placeholders', function () {
    $berkas = new UploadedFile(
        docxKosong(),
        'template_asal.docx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        null,
        true
    );

    $this->actingAs(adminUpload())
        ->post(route('surat.templates.store'), [
            'tahun'         => 2027,
            'template_file' => $berkas,
        ])
        ->assertSessionHasErrors('template_file');

    expect(SuratTemplate::where('tahun', 2027)->exists())->toBeFalse();
});

it('shows template versions and archive status on the upload page', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('TAMPIL LAMA'))->assertSessionHasNoErrors();
    unggahTemplate($this, $admin, 2026, docxTemplate('TAMPIL BARU'))->assertSessionHasNoErrors();

    $lama = SuratTemplate::where('tahun', 2026)->orderBy('versi')->first();

    $this->actingAs($admin)
        ->delete(route('surat.templates.destroy', $lama))
        ->assertRedirect();

    $this->actingAs($admin)
        ->get(route('surat.upload'))
        ->assertOk()
        ->assertSee('v2')
        ->assertSee('Arsip')
        ->assertSee('Aktif');
});

it('can assign an archived template to a letter', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('ARSIP PILIH'))->assertSessionHasNoErrors();

    $template = SuratTemplate::where('tahun', 2026)->first();

    $this->actingAs($admin)
        ->delete(route('surat.templates.destroy', $template))
        ->assertRedirect();

    $permohonan = suratTahun(permohonanUpload('SP-ARSIP-PILIH'), '2026-05-01 10:00:00');

    $this->actingAs($admin)
        ->post(route('surat.template.assign', $permohonan), [
            'surat_template_id' => $template->id,
        ])
        ->assertSessionHasNoErrors();

    expect(SuratTemplateResolver::forPermohonan($permohonan->fresh())->id)->toBe($template->id);
});

it('archives a yearly template without deleting its file', function () {
    $admin = adminUpload();

    Storage::disk('local')->put(
        'surat-templates/arsip.docx',
        file_get_contents(public_path('templates/template_peminjaman.docx'))
    );

    $template = SuratTemplate::create([
        'tahun'       => 2026,
        'versi'       => 1,
        'is_active'   => true,
        'nama_file'   => 'template_2026.docx',
        'path'        => 'surat-templates/arsip.docx',
        'uploaded_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('surat.templates.destroy', $template))
        ->assertRedirect();

    // Tidak dihapus permanen: hanya diarsipkan.
    expect(SuratTemplate::where('tahun', 2026)->exists())->toBeFalse()
        ->and(SuratTemplate::withTrashed()->where('tahun', 2026)->exists())->toBeTrue();

    Storage::disk('local')->assertExists('surat-templates/arsip.docx');

    // Surat yang sudah memakai template itu tetap memakainya walau diarsipkan.
    $permohonan = suratTahun(permohonanUpload('SP-ARSIP'), '2026-05-01 10:00:00');
    $permohonan->surat_template_id = $template->id;
    $permohonan->save();

    expect(SuratTemplateResolver::forPermohonan($permohonan->fresh())->id)->toBe($template->id);

    // Tahun tanpa template aktif kembali ke template default.
    expect(SuratTemplateResolver::pathFor(2026))
        ->toBe(public_path('templates/template_peminjaman.docx'));
});

it('keeps old versions and auto-activates the newest upload', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('VERSI SATU'), 'awal')
        ->assertSessionHasNoErrors();
    unggahTemplate($this, $admin, 2026, docxTemplate('VERSI DUA'), 'revisi')
        ->assertSessionHasNoErrors();

    $semua = SuratTemplate::where('tahun', 2026)->orderBy('versi')->get();

    expect($semua)->toHaveCount(2)
        ->and($semua[0]->versi)->toBe(1)
        ->and($semua[1]->versi)->toBe(2)
        ->and($semua[0]->is_active)->toBeFalse()
        ->and($semua[1]->is_active)->toBeTrue()
        ->and($semua[1]->catatan)->toBe('revisi');

    // Kedua file tetap ada dengan nama acak yang berbeda.
    Storage::disk('local')->assertExists($semua[0]->path);
    Storage::disk('local')->assertExists($semua[1]->path);
    expect($semua[0]->path)->not->toBe($semua[1]->path);

    expect(SuratTemplateResolver::activeForYear(2026)->id)->toBe($semua[1]->id);
});

it('can reactivate an older template version', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('LAMA'))->assertSessionHasNoErrors();
    unggahTemplate($this, $admin, 2026, docxTemplate('BARU'))->assertSessionHasNoErrors();

    $lama = SuratTemplate::where('tahun', 2026)->orderBy('versi')->first();

    $this->actingAs($admin)
        ->post(route('surat.templates.activate', $lama))
        ->assertRedirect();

    expect($lama->fresh()->is_active)->toBeTrue()
        ->and(SuratTemplate::where('tahun', 2026)->where('is_active', true)->count())->toBe(1);
});

it('lets admins pick an older template for one letter only', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('LAMA'))->assertSessionHasNoErrors();
    unggahTemplate($this, $admin, 2026, docxTemplate('BARU'))->assertSessionHasNoErrors();

    $lama = SuratTemplate::where('tahun', 2026)->orderBy('versi')->first();
    $baru = SuratTemplate::where('tahun', 2026)->orderByDesc('versi')->first();

    $suratA = suratTahun(permohonanUpload('SP-PILIH-A'), '2026-05-01 10:00:00');
    $suratB = suratTahun(permohonanUpload('SP-PILIH-B'), '2026-05-01 10:00:00');

    $this->actingAs($admin)
        ->post(route('surat.template.assign', $suratA), [
            'surat_template_id' => $lama->id,
        ])
        ->assertRedirect();

    expect(SuratTemplateResolver::forPermohonan($suratA->fresh())->id)->toBe($lama->id)
        ->and(SuratTemplateResolver::forPermohonan($suratB->fresh())->id)->toBe($baru->id);
});

it('keeps each letter on the template version recorded when it was made', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('PONOROGO'), 'versi awal')->assertSessionHasNoErrors();

    $suratLama = suratTahun(permohonanUpload('SP-TETAP-LAMA'), '2026-05-01 10:00:00');

    // Surat mencatat versi yang aktif pada saat ia dibuat.
    $dicatat = SuratTemplateResolver::forPermohonan($suratLama);
    $suratLama->forceFill(['surat_template_id' => $dicatat?->id])->saveQuietly();

    unggahTemplate($this, $admin, 2026, docxTemplate('KABUPATEN PONOROGO'), 'revisi kota')->assertSessionHasNoErrors();
    $baru = SuratTemplate::where('tahun', 2026)->orderByDesc('versi')->first();

    // Surat lama tidak ikut berubah...
    expect(SuratTemplateResolver::forPermohonan($suratLama->fresh())->id)->toBe($dicatat->id)
        // ...sedangkan surat yang versinya belum tercatat memakai versi aktif terbaru.
        ->and(SuratTemplateResolver::activeForYear(2026)->id)->toBe($baru->id);
});

it('uses the latest active template for a year without its own template', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('AKTIF 2026'))->assertSessionHasNoErrors();

    $surat2027 = suratTahun(permohonanUpload('SP-2027'), '2027-01-05 09:00:00');

    expect(SuratTemplateResolver::forPermohonan($surat2027)->tahun)->toBe(2026);
});

it('restores an archived template', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('PULIH'))->assertSessionHasNoErrors();

    $template = SuratTemplate::where('tahun', 2026)->first();

    $this->actingAs($admin)
        ->delete(route('surat.templates.destroy', $template))
        ->assertRedirect();

    expect($template->fresh()->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->post(route('surat.templates.restore', $template->id))
        ->assertRedirect();

    $template->refresh();

    expect($template->trashed())->toBeFalse()
        ->and($template->is_active)->toBeTrue();
});

it('scans existing template files without deleting them', function () {
    $admin = adminUpload();

    Storage::disk('local')->put(
        'surat-templates/template_peminjaman_2025.docx',
        file_get_contents(docxTemplate('HASIL SCAN'))
    );

    $this->actingAs($admin)
        ->post(route('surat.templates.scan'))
        ->assertRedirect();

    $template = SuratTemplate::where('tahun', 2025)->first();

    expect($template)->not->toBeNull()
        ->and($template->is_active)->toBeTrue()
        // Disalin ke nama acak, file asli tetap ada.
        ->and($template->path)->toMatch('/^surat-templates\/[0-9a-f-]{36}\.docx$/');

    Storage::disk('local')->assertExists('surat-templates/template_peminjaman_2025.docx');
    Storage::disk('local')->assertExists($template->path);
});

it('fills only the placeholders that exist in the template file', function () {
    $admin = adminUpload();

    $permohonan = suratTahun(permohonanUpload('SP-MINIMAL'), '2026-05-01 10:00:00');

    // Template minimal: tanpa placeholder item, QR, maupun tanda tangan.
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();
    $section->addText('Nomor: ${nomor}');
    $section->addText('Nama: ${nama_peminjam}');

    $path = tempnam(sys_get_temp_dir(), 'silapin-min-') . '.docx';
    IOFactory::createWriter($phpWord, 'Word2007')->save($path);

    Storage::disk('local')->put('surat-templates/minimal.docx', file_get_contents($path));

    SuratTemplate::create([
        'tahun'       => 2026,
        'versi'       => 1,
        'is_active'   => true,
        'nama_file'   => 'minimal.docx',
        'path'        => 'surat-templates/minimal.docx',
        'uploaded_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('surat.download.docx', $permohonan));
    $response->assertOk();

    $hasil = tempnam(sys_get_temp_dir(), 'silapin-hasil-') . '.docx';
    file_put_contents($hasil, $response->streamedContent());

    $zip = new ZipArchive();
    $zip->open($hasil);
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    expect($xml)->toBeString()
        ->toContain('SP-MINIMAL')
        ->toContain('Rahma');

    // Template dipin ke surat setelah dipakai.
    expect($permohonan->fresh()->surat_template_id)->toBe(
        SuratTemplate::where('tahun', 2026)->first()->id
    );
});

it('deletes an archived template permanently together with its file', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('versi dibuang'), 'mau dihapus')->assertSessionHasNoErrors();
    $template = SuratTemplate::where('tahun', 2026)->first();
    $berkas = $template->path;

    expect(Storage::disk('local')->exists($berkas))->toBeTrue();

    $this->actingAs($admin)->delete(route('surat.templates.destroy', $template))->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('surat.templates.permanent', $template->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(SuratTemplate::withTrashed()->find($template->id))->toBeNull()
        ->and(Storage::disk('local')->exists($berkas))->toBeFalse();
});

it('refuses to delete permanently a template that letters still record', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('versi terpakai'), 'masih dipakai')->assertSessionHasNoErrors();
    $template = SuratTemplate::where('tahun', 2026)->first();

    $surat = permohonanUpload('SP-PAKAI-TEMPLATE');
    $surat->forceFill(['surat_template_id' => $template->id])->saveQuietly();

    $this->actingAs($admin)->delete(route('surat.templates.destroy', $template))->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('surat.templates.permanent', $template->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(SuratTemplate::withTrashed()->find($template->id))->not->toBeNull()
        ->and(Storage::disk('local')->exists($template->path))->toBeTrue()
        ->and(SuratTemplateResolver::forPermohonan($surat->fresh())->id)->toBe($template->id);
});

it('offers the permanent delete button on archived templates', function () {
    $admin = adminUpload();

    unggahTemplate($this, $admin, 2026, docxTemplate('versi arsip ui'), 'arsip ui')->assertSessionHasNoErrors();
    $template = SuratTemplate::where('tahun', 2026)->first();

    $this->actingAs($admin)->delete(route('surat.templates.destroy', $template))->assertRedirect();

    $this->actingAs($admin)
        ->get(route('surat.upload'))
        ->assertOk()
        ->assertSee('Hapus permanen (baris database + berkasnya)', false);
});
