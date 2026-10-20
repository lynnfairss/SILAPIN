<?php

use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Endpoint yang dipanggil halaman Pengembalian Barang setelah QR discan.
 * Admin memakainya untuk mencocokkan identitas peminjam, barang yang
 * dipinjam, dan lampiran (foto KTP / surat tugas) sebelum menerima barang.
 */
function petugasPengembalian(): User
{
    return User::factory()->create(['role' => 'admin', 'status' => 'approved']);
}

function permohonanScan(array $atribut = []): Permohonan
{
    $instansi = Instansi::create(['nama_instansi' => 'Dinas Uji '.uniqid()]);

    $barang = Inventaris::create([
        'kode_barang' => 'SCAN-'.uniqid(),
        'nama_barang' => 'Proyektor Uji',
        'stok' => 5,
        'kondisi' => 'Baik',
    ]);

    $permohonan = Permohonan::create(array_merge([
        'nomor_permohonan' => 'SP-SCAN-'.strtoupper(uniqid()),
        'instansi_id' => $instansi->id,
        'nama_peminjam' => 'Rahma Wulandari',
        'nik' => '3500000000000009',
        'telepon' => '081298765432',
        'alamat' => 'Jl. Diponegoro No. 7, Ponorogo',
        'tanggal_pinjam' => '2026-10-20',
        'tanggal_kembali' => '2026-10-23',
        'keperluan' => 'Rapat koordinasi',
        'status' => 'Dipinjam',
    ], $atribut));

    $permohonan->detailPermohonan()->create([
        'inventaris_id' => $barang->id,
        'jumlah' => 2,
    ]);

    return $permohonan;
}

it('returns the borrower data and borrowed items after a scan', function () {
    $permohonan = permohonanScan();

    $response = $this->actingAs(petugasPengembalian())
        ->getJson(route('permohonan.cek-nomor', [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
        ]))
        ->assertOk()
        ->assertJson([
            'exists' => true,
            'nama_peminjam' => 'Rahma Wulandari',
            'nik' => '3500000000000009',
            'telepon' => '081298765432',
            'alamat' => 'Jl. Diponegoro No. 7, Ponorogo',
            'keperluan' => 'Rapat koordinasi',
            'status' => 'Dipinjam',
            'total_jumlah' => 2,
            'foto_ktp' => null,
            'surat_tugas' => null,
        ]);

    $data = $response->json();

    expect($data['instansi'])->toBe($permohonan->instansi->nama_instansi);
    expect($data['tanggal_pinjam'])
        ->toBe(\Illuminate\Support\Carbon::parse('2026-10-20')->translatedFormat('d M Y'));
    expect($data['tanggal_kembali'])
        ->toBe(\Illuminate\Support\Carbon::parse('2026-10-23')->translatedFormat('d M Y'));

    expect($data['items'])->toHaveCount(1);
    expect($data['items'][0])->toMatchArray([
        'nama' => 'Proyektor Uji',
        'kode' => $permohonan->detailPermohonan->first()->inventaris->kode_barang,
        'jumlah' => 2,
    ]);
});

it('exposes the KTP photo and a PDF surat tugas with their type', function () {
    Storage::fake('public');

    $permohonan = permohonanScan([
        'foto_ktp' => 'foto-ktp/ktp-uji.jpg',
        'surat_tugas' => 'surat-tugas/surat-uji.pdf',
    ]);

    $data = $this->actingAs(petugasPengembalian())
        ->getJson(route('permohonan.cek-nomor', [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
        ]))
        ->assertOk()
        ->json();

    expect($data['foto_ktp'])->not->toBeNull();
    expect($data['foto_ktp']['is_image'])->toBeTrue();
    expect($data['foto_ktp']['is_pdf'])->toBeFalse();
    expect($data['foto_ktp']['url'])->toContain('storage/foto-ktp/ktp-uji.jpg');

    expect($data['surat_tugas'])->not->toBeNull();
    expect($data['surat_tugas']['is_image'])->toBeFalse();
    expect($data['surat_tugas']['is_pdf'])->toBeTrue();
    expect($data['surat_tugas']['url'])->toContain('storage/surat-tugas/surat-uji.pdf');
});

it('marks an image surat tugas as an image', function () {
    $permohonan = permohonanScan([
        'surat_tugas' => 'surat-tugas/surat-uji.png',
    ]);

    $data = $this->actingAs(petugasPengembalian())
        ->getJson(route('permohonan.cek-nomor', [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
        ]))
        ->assertOk()
        ->json();

    expect($data['surat_tugas']['is_image'])->toBeTrue();
    expect($data['surat_tugas']['is_pdf'])->toBeFalse();
});

it('reports an unknown number as not found', function () {
    $this->actingAs(petugasPengembalian())
        ->getJson(route('permohonan.cek-nomor', [
            'nomor_permohonan' => 'SP-TIDAK-ADA-9999',
        ]))
        ->assertOk()
        ->assertExactJson(['exists' => false]);
});

it('refuses guests', function () {
    $permohonan = permohonanScan();

    $this->getJson(route('permohonan.cek-nomor', [
        'nomor_permohonan' => $permohonan->nomor_permohonan,
    ]))->assertUnauthorized();
});

it('only answers to super admin and admin', function () {
    $permohonan = permohonanScan();

    $this->actingAs(User::factory()->superAdmin()->create())
        ->getJson(route('permohonan.cek-nomor', [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
        ]))
        ->assertOk()
        ->assertJson(['exists' => true]);
});
