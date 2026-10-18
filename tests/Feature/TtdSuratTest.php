<?php

use App\Http\Controllers\Admin\SuratController;
use App\Models\Permohonan;
use App\Services\DocxParserService;
use App\Models\User;

function suratPermohonan(string $status = 'Menunggu'): Permohonan
{
    return Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-TTD-'.uniqid(),
        'nama_peminjam'   => 'Rahma',
        'jabatan'         => 'Staf Bagian Umum',
        'nik'             => '3500000000000009',
        'telepon'         => '081298765432',
        'tanggal_pinjam'  => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan'       => 'Uji',
        'status'          => $status,
    ]);
}

function disetujuiOleh(User $admin, Permohonan $permohonan): void
{
    $permohonan->statusLogs()->create([
        'status_lama' => 'Menunggu',
        'status_baru' => 'Disetujui',
        'catatan'     => 'Disetujui oleh admin.',
        'user_id'     => $admin->id,
    ]);

    $permohonan->update(['status' => 'Disetujui']);
}

it('puts the approving admin on the handing over side of the letter', function () {
    $admin = User::factory()->create(['name' => 'Siti Aminah', 'role' => 'admin', 'status' => 'approved']);
    $permohonan = suratPermohonan();
    disetujuiOleh($admin, $permohonan);

    $ttd = SuratController::resolveTtd($permohonan->fresh(), SuratController::getContent($permohonan));

    expect($ttd['kiri_nama'])->toBe('Siti Aminah');
    // Admin tanpa NIK tetap dibiarkan kosong, jabatan juga tidak ada di tabel users.
    expect($ttd['kiri_nrp'])->toBe('')
        ->and($ttd['kiri_jabatan'])->toBe('');

    // Sisi "Yang menerima" tetap peminjam.
    expect($ttd['kanan_nama'])->toBe('Rahma')
        ->and($ttd['kanan_nrp'])->toBe('3500000000000009')
        ->and($ttd['kanan_jabatan'])->toBe('Staf Bagian Umum');
});

it('prints the approver NIK on the handing over side of the letter', function () {
    $admin = User::factory()->create([
        'name' => 'Siti Aminah',
        'nik' => '3500000000000010',
        'role' => 'admin',
        'status' => 'approved',
    ]);
    $permohonan = suratPermohonan();
    disetujuiOleh($admin, $permohonan);

    $ttd = SuratController::resolveTtd($permohonan->fresh(), SuratController::getContent($permohonan));

    expect($ttd['kiri_nama'])->toBe('Siti Aminah')
        ->and($ttd['kiri_nrp'])->toBe('3500000000000010')
        ->and($ttd['kiri_jabatan'])->toBe('');

    // Sisi peminjam tidak ikut berubah.
    expect($ttd['kanan_nrp'])->toBe('3500000000000009');

    $html = view('peminjam.surat', ['permohonan' => $permohonan->fresh(), 'forPdf' => true])->render();

    expect($html)->toContain('NIK. 3500000000000010');
});

it('keeps the NIK stored in the letter content ahead of the account NIK', function () {
    $admin = User::factory()->create([
        'name' => 'Siti Aminah',
        'nik' => '3500000000000010',
        'role' => 'admin',
        'status' => 'approved',
    ]);
    $permohonan = suratPermohonan();
    disetujuiOleh($admin, $permohonan);

    $permohonan->update([
        'surat_content' => ['ttd_kiri_nrp' => 'NIK. Lama'],
    ]);

    $ttd = SuratController::resolveTtd($permohonan->fresh(), SuratController::getContent($permohonan));

    expect($ttd['kiri_nrp'])->toBe('NIK. Lama');
});

it('uses a super admin as the approver just as well', function () {
    $superAdmin = User::factory()->superAdmin()->create(['name' => 'Budi Santoso']);
    $permohonan = suratPermohonan();
    disetujuiOleh($superAdmin, $permohonan);

    expect($permohonan->fresh()->penyetuju()?->name)->toBe('Budi Santoso');

    $ttd = SuratController::resolveTtd($permohonan->fresh(), SuratController::getContent($permohonan));

    expect($ttd['kiri_nama'])->toBe('Budi Santoso')
        ->and($ttd['kanan_nama'])->toBe('Rahma');
});

it('falls back to the applicant while the request is not approved yet', function () {
    $permohonan = suratPermohonan();

    expect($permohonan->penyetuju())->toBeNull();

    $ttd = SuratController::resolveTtd($permohonan, SuratController::getContent($permohonan));

    expect($ttd['kiri_nama'])->toBe('Rahma')
        ->and($ttd['kiri_nrp'])->toBe('3500000000000009')
        ->and($ttd['kiri_jabatan'])->toBe('Staf Bagian Umum');
});

it('ignores logs that did not approve the request', function () {
    $admin = User::factory()->create(['name' => 'Admin Salah', 'role' => 'admin', 'status' => 'approved']);
    $permohonan = suratPermohonan();

    $permohonan->statusLogs()->create([
        'status_lama' => null,
        'status_baru' => 'Menunggu',
        'catatan'     => 'Permohonan diajukan.',
        'user_id'     => null,
    ]);
    $permohonan->statusLogs()->create([
        'status_lama' => 'Menunggu',
        'status_baru' => 'Ditolak',
        'catatan'     => 'Ditolak.',
        'user_id'     => $admin->id,
    ]);

    expect($permohonan->penyetuju())->toBeNull();
});

it('keeps a name that was already filled in through the Word sync', function () {
    $admin = User::factory()->create(['name' => 'Admin Otomatis', 'role' => 'admin', 'status' => 'approved']);
    $permohonan = suratPermohonan();
    disetujuiOleh($admin, $permohonan);

    // Isian manual hasil sync Word harus menang atas nama akun.
    $permohonan->update([
        'surat_content' => ['ttd_kiri_nama' => 'Kepala Bagian Sarana'],
    ]);

    $ttd = SuratController::resolveTtd($permohonan->fresh(), SuratController::getContent($permohonan));

    expect($ttd['kiri_nama'])->toBe('Kepala Bagian Sarana')
        ->and($ttd['kanan_nama'])->toBe('Rahma');
});

it('shows both signatures on the rendered letter', function () {
    $admin = User::factory()->create(['name' => 'Siti Aminah', 'role' => 'admin', 'status' => 'approved']);
    $permohonan = suratPermohonan();
    disetujuiOleh($admin, $permohonan);

    $html = view('peminjam.surat', ['permohonan' => $permohonan->fresh(), 'forPdf' => true])->render();

    expect($html)->toContain('Yang menyerahkan,')
        ->and($html)->toContain('Yang menerima,')
        ->and($html)->toContain('<strong>Siti Aminah</strong>')
        ->and($html)->toContain('<strong>Rahma</strong>');
});

it('writes the approver into the generated docx signature column', function () {
    $admin = User::factory()->create([
        'name' => 'Siti Aminah',
        'nik' => '3500000000000010',
        'role' => 'admin',
        'status' => 'approved',
    ]);
    $permohonan = suratPermohonan();
    disetujuiOleh($admin, $permohonan);

    $permohonan = $permohonan->fresh();
    $sc = SuratController::getContent($permohonan);
    $ttd = SuratController::resolveTtd($permohonan, $sc);

    $templatePath = public_path('templates/template_peminjaman.docx');
    expect(file_exists($templatePath))->toBeTrue();

    $processor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
    $processor->setValue('ttd_kiri_label', $sc['ttd_kiri_label']);
    $processor->setValue('ttd_kiri_nama', $ttd['kiri_nama']);
    $processor->setValue('ttd_kiri_nrp', $ttd['kiri_nrp']);
    $processor->setValue('ttd_kiri_jabatan', $ttd['kiri_jabatan']);
    $processor->setValue('ttd_kanan_label', $sc['ttd_kanan_label']);
    $processor->setValue('ttd_kanan_nama', $ttd['kanan_nama']);
    $processor->setValue('ttd_kanan_nrp', $ttd['kanan_nrp']);
    $processor->setValue('ttd_kanan_jabatan', $ttd['kanan_jabatan']);

    $sementara = tempnam(sys_get_temp_dir(), 'ttd').'.docx';
    $processor->saveAs($sementara);

    // Baca balik file yang sudah terisi, persis seperti saat sync dari Word.
    $terbaca = app(DocxParserService::class)->parse($sementara);
    @unlink($sementara);

    expect($terbaca['ttd_kiri_label'])->toContain('Yang menyerahkan')
        ->and($terbaca['ttd_kanan_label'])->toContain('Yang menerima')
        ->and($terbaca['ttd_kiri_nama'])->toBe('Siti Aminah')
        // Prefix "NIK." harus dilepas parser, jadi yang tersimpan NIK bersih.
        ->and($terbaca['ttd_kiri_nrp'])->toBe('3500000000000010')
        ->and($terbaca['ttd_kanan_nama'])->toBe('Rahma')
        ->and($terbaca['ttd_kanan_nrp'])->toBe('3500000000000009');
});

it('still reads a letter that was saved back with the old NRP label', function () {
    // Regex parser sengaja menerima "NRP." supaya file Word lama yang sudah
    // di-sync sebelum label diganti ke NIK tetap terbaca.
    expect(preg_replace('/^(?:NRP|NIK)\.\s*/', '', 'NRP. 3500000000000009'))
        ->toBe('3500000000000009')
        ->and(preg_replace('/^(?:NRP|NIK)\.\s*/', '', 'NIK. 3500000000000009'))
        ->toBe('3500000000000009');
});

it('keeps the applicant on both sides while the request is not approved yet', function () {
    $permohonan = suratPermohonan();

    $html = $this->actingAs(User::factory()->create(['role' => 'admin', 'status' => 'approved']))
        ->get(route('surat.preview', $permohonan))
        ->assertOk()
        ->getContent();

    // Belum ada yang menyetujui, jadi nama peminjam mengisi kedua kolom.
    expect(substr_count($html, '<strong>Rahma</strong>'))->toBe(2);
});