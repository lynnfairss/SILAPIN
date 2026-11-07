<?php

use App\Models\Inventaris;
use App\Models\Permohonan;
use App\Models\User;

function exportAdmin(): User
{
    return User::factory()->superAdmin()->create();
}

function exportBarang(string $kode, string $kondisi = 'Baik'): Inventaris
{
    return Inventaris::create([
        'kode_barang' => $kode,
        'nama_barang' => 'Barang ' . $kode,
        'stok'        => 2,
        'kondisi'     => $kondisi,
    ]);
}

/**
 * Baris CSV tanpa header dan BOM, supaya assertion tidak perlu tahu
 * detail encoding.
 */
function exportRows(string $csv): array
{
    $lines = preg_split('/\r\n|\r|\n/', preg_replace('/^\xEF\xBB\xBF/', '', $csv));

    return array_slice(array_filter($lines, fn ($l) => $l !== ''), 1);
}

it('exports every inventory row instead of only the active page', function () {
    // 9 barang > PER_PAGE, jadi ekspor harus lebih banyak dari yang tampil.
    foreach (range(1, 9) as $i) {
        exportBarang('KOD' . $i);
    }

    $response = $this->actingAs(exportAdmin())->get('/inventaris/export');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    expect(str_starts_with($response->getContent(), "\xEF\xBB\xBF"))->toBeTrue();
    expect(exportRows($response->getContent()))->toHaveCount(9);
});

it('keeps the export aligned with the filters shown on screen', function () {
    exportBarang('BAI1');
    exportBarang('BAI2');
    exportBarang('RSK1', 'Rusak Berat');

    $rows = exportRows(
        $this->actingAs(exportAdmin())->get('/inventaris/export?kondisi=Baik')->getContent()
    );

    expect($rows)->toHaveCount(2);
    expect(implode("\n", $rows))->not->toContain('Rusak Berat');
});

it('neutralises spreadsheet formulas inside exported text', function () {
    Inventaris::create([
        'kode_barang' => 'JEBUG',
        'nama_barang' => 'Barang uji',
        'stok'        => 1,
        'kondisi'     => 'Baik',
        'deskripsi'   => '=1+1',
    ]);

    $rows = exportRows(
        $this->actingAs(exportAdmin())->get('/inventaris/export')->getContent()
    );

    expect(implode("\n", $rows))->toContain("'=1+1");
});

it('shows eight inventory rows per page', function () {
    foreach (range(1, 9) as $i) {
        exportBarang('PAGE' . $i);
    }

    $response = $this->actingAs(exportAdmin())->get('/inventaris');

    $response->assertOk();

    expect($response->viewData('inventaris'))->toHaveCount(8);
});

it('exports only the active status tab of the requests list', function () {
    foreach (['Satu', 'Dua', 'Tiga'] as $nama) {
        Permohonan::create([
            'nomor_permohonan' => 'SP-EXPORT-' . $nama,
            'nama_peminjam'    => $nama,
            'nik'              => '3500000000000001',
            'telepon'          => '08123456789',
            'tanggal_pinjam'   => now()->toDateString(),
            'tanggal_kembali'  => now()->addDay()->toDateString(),
            'keperluan'        => 'Uji ekspor',
            'status'           => 'Menunggu',
        ]);
    }

    Permohonan::create([
        'nomor_permohonan' => 'SP-EXPORT-Setuju',
        'nama_peminjam'    => 'Setuju',
        'nik'              => '3500000000000002',
        'telepon'          => '08123456780',
        'tanggal_pinjam'   => now()->toDateString(),
        'tanggal_kembali'  => now()->addDay()->toDateString(),
        'keperluan'        => 'Uji ekspor',
        'status'           => 'Disetujui',
    ]);

    $response = $this->actingAs(exportAdmin())->get('/permohonan/export?status=Menunggu');

    $response->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="data-permohonan-menunggu.csv"');

    expect(exportRows($response->getContent()))->toHaveCount(3);
});

it('refuses to export an unknown status tab', function () {
    $this->actingAs(exportAdmin())
        ->get('/permohonan/export?status=BukanStatus')
        ->assertNotFound();
});
