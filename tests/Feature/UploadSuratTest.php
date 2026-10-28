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
        ->assertSee('Template Surat per Tahun');
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

it('deletes a yearly template and returns to the default', function () {
    $admin = adminUpload();

    Storage::disk('local')->put(
        'surat-templates/template_peminjaman_2026.docx',
        file_get_contents(public_path('templates/template_peminjaman.docx'))
    );

    $template = SuratTemplate::create([
        'tahun'       => 2026,
        'nama_file'   => 'template_2026.docx',
        'path'        => 'surat-templates/template_peminjaman_2026.docx',
        'uploaded_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('surat.templates.destroy', $template))
        ->assertRedirect();

    expect(SuratTemplate::where('tahun', 2026)->exists())->toBeFalse();

    Storage::disk('local')->assertMissing('surat-templates/template_peminjaman_2026.docx');

    expect(SuratTemplateResolver::pathFor(2026))
        ->toBe(public_path('templates/template_peminjaman.docx'));
});
