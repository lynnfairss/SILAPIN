<?php

use App\Models\Inventaris;
use App\Models\Permohonan;
use App\Models\PermohonanStatusLog;
use App\Services\StatusPermohonan;
use App\Services\StokTidakCukup;
use App\Services\TransisiStatus;
use App\Services\TransisiStatusTidakValid;

function barang(string $kode, int $stok): Inventaris
{
    return Inventaris::create([
        'kode_barang' => $kode,
        'nama_barang' => 'Barang '.$kode,
        'stok' => $stok,
        'kondisi' => 'Baik',
    ]);
}

function permohonanDengan(Inventaris $barang, int $jumlah, string $status = TransisiStatus::MENUNGGU): Permohonan
{
    $permohonan = Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-'.$barang->id.'-'.$jumlah,
        'nama_peminjam' => 'Budi',
        'nik' => '3500000000000001',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'status' => $status,
    ]);

    $permohonan->detailPermohonan()->create([
        'inventaris_id' => $barang->id,
        'jumlah' => $jumlah,
    ]);

    return $permohonan->fresh();
}

it('reduces stock when a request is approved', function () {
    $barang = barang('ELK-T1', 5);
    $permohonan = permohonanDengan($barang, 2);

    app(StatusPermohonan::class)->ubah($permohonan, TransisiStatus::DISETUJUI);

    expect($barang->fresh()->stok)->toBe(3)
        ->and($permohonan->fresh()->status)->toBe(TransisiStatus::DISETUJUI);
});

it('restores stock when the goods come back', function () {
    $barang = barang('ELK-T2', 5);
    $permohonan = permohonanDengan($barang, 2);

    $layanan = app(StatusPermohonan::class);
    $layanan->ubah($permohonan, TransisiStatus::DISETUJUI);
    $layanan->ubah($permohonan->fresh(), TransisiStatus::DIKEMBALIKAN);

    expect($barang->fresh()->stok)->toBe(5)
        ->and($permohonan->fresh()->status)->toBe(TransisiStatus::DIKEMBALIKAN);
});

it('cannot approve a request when the stock is no longer sufficient', function () {
    $barang = barang('ELK-T3', 1);

    // Dua permohonan dibuat saat stok masih 1; yang kedua hanya akan ditolak
    // saat disetujui, bukan saat pengajuan.
    $pertama = permohonanDengan($barang, 1);
    $kedua = Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-KEDUA',
        'nama_peminjam' => 'Siti',
        'nik' => '3500000000000002',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'status' => TransisiStatus::MENUNGGU,
    ]);
    $kedua->detailPermohonan()->create(['inventaris_id' => $barang->id, 'jumlah' => 1]);

    app(StatusPermohonan::class)->ubah($pertama, TransisiStatus::DISETUJUI);

    expect($barang->fresh()->stok)->toBe(0);

    app(StatusPermohonan::class)->ubah($kedua->fresh(), TransisiStatus::DISETUJUI);
})->throws(StokTidakCukup::class);

it('leaves stock untouched when the request is rejected', function () {
    $barang = barang('ELK-T4', 7);
    $permohonan = permohonanDengan($barang, 3);

    app(StatusPermohonan::class)->ubah($permohonan, TransisiStatus::DITOLAK);

    expect($barang->fresh()->stok)->toBe(7);
});

it('refuses to return goods twice', function () {
    $barang = barang('ELK-T5', 4);
    $permohonan = permohonanDengan($barang, 2);

    $layanan = app(StatusPermohonan::class);
    $layanan->ubah($permohonan, TransisiStatus::DISETUJUI);
    $layanan->ubah($permohonan->fresh(), TransisiStatus::DIKEMBALIKAN);

    expect($barang->fresh()->stok)->toBe(4);

    // Pengembalian kedua harus ditolak, bukan menambah stok lagi.
    $layanan->ubah($permohonan->fresh(), TransisiStatus::DIKEMBALIKAN);
})->throws(TransisiStatusTidakValid::class);

it('refuses to reopen a request that was already rejected', function () {
    $barang = barang('ELK-T6', 2);
    $permohonan = permohonanDengan($barang, 1, TransisiStatus::DITOLAK);

    app(StatusPermohonan::class)->ubah($permohonan, TransisiStatus::DISETUJUI);
})->throws(TransisiStatusTidakValid::class);

it('refuses to return goods that were never taken out', function () {
    $barang = barang('ELK-T7', 9);
    $permohonan = permohonanDengan($barang, 1, TransisiStatus::MENUNGGU);

    app(StatusPermohonan::class)->ubah($permohonan, TransisiStatus::DIKEMBALIKAN);
})->throws(TransisiStatusTidakValid::class);

it('writes one status log per transition', function () {
    $barang = barang('ELK-T8', 3);
    $permohonan = permohonanDengan($barang, 1);

    $layanan = app(StatusPermohonan::class);
    $layanan->ubah($permohonan, TransisiStatus::DISETUJUI);
    $layanan->ubah($permohonan->fresh(), TransisiStatus::DIKEMBALIKAN);

    $log = PermohonanStatusLog::where('permohonan_id', $permohonan->id)
        ->orderBy('id')
        ->get(['status_lama', 'status_baru']);

    expect($log)->toHaveCount(2)
        ->and($log[0]->status_baru)->toBe(TransisiStatus::DISETUJUI)
        ->and($log[1]->status_lama)->toBe(TransisiStatus::DISETUJUI)
        ->and($log[1]->status_baru)->toBe(TransisiStatus::DIKEMBALIKAN);
});

it('rolls back every stock change when one item is short', function () {
    // Barang pertama sempat dikurangi sebelum barang kedua gagal, jadi
    // rollback-lah yang mencegah stok berkurang separuh jalan.
    $pertama = barang('ELK-T10', 5);
    $kedua = barang('ELK-T11', 0);

    $permohonan = Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-ROLLBACK',
        'nama_peminjam' => 'Dewi',
        'nik' => '3500000000000003',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'status' => TransisiStatus::MENUNGGU,
    ]);

    $permohonan->detailPermohonan()->create(['inventaris_id' => $pertama->id, 'jumlah' => 2]);
    $permohonan->detailPermohonan()->create(['inventaris_id' => $kedua->id, 'jumlah' => 1]);

    try {
        app(StatusPermohonan::class)->ubah($permohonan->fresh(), TransisiStatus::DISETUJUI);
    } catch (StokTidakCukup) {
        // Diharapkan.
    }

    expect($pertama->fresh()->stok)->toBe(5)
        ->and($kedua->fresh()->stok)->toBe(0)
        ->and($permohonan->fresh()->status)->toBe(TransisiStatus::MENUNGGU)
        ->and(PermohonanStatusLog::where('permohonan_id', $permohonan->id)->count())->toBe(0);
});
