<?php

use App\Models\Inventaris;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Pengembalian kini mencatat kondisi fisik barang dan menyimpan foto bukti
 * serah terima. Tes ini memastikan kontrak form baru benar-benar tersimpan
 * dan file tidak tertinggal saat transisi status ditolak.
 */
function petugasKondisi(): User
{
    return User::factory()->create(['role' => 'admin', 'status' => 'approved']);
}

function permohonanKondisi(string $status = 'Disetujui', int $stokAwal = 5, int $jumlah = 2): array
{
    $barang = Inventaris::create([
        'kode_barang' => 'KOND-'.strtoupper(uniqid()),
        'nama_barang' => 'Kamera Kondisi Uji',
        'stok' => $stokAwal,
        'kondisi' => 'Baik',
    ]);

    $permohonan = Permohonan::create([
        'nomor_permohonan' => 'SP-KOND-'.strtoupper(uniqid()),
        'nama_peminjam' => 'Peminjam Kondisi',
        'nik' => '3500000000000088',
        'telepon' => '08123456780',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDays(3)->toDateString(),
        'keperluan' => 'Uji kondisi pengembalian',
        'status' => $status,
    ]);

    $permohonan->detailPermohonan()->create([
        'inventaris_id' => $barang->id,
        'jumlah' => $jumlah,
    ]);

    // Stok sudah berkurang karena barang sedang dipinjam.
    $barang->update(['stok' => $stokAwal - $jumlah]);

    return [$permohonan, $barang];
}

it('stores the returned condition and the uploaded proof photo', function () {
    Storage::fake('public');

    [$permohonan, $barang] = permohonanKondisi();
    $admin = petugasKondisi();

    $this->actingAs($admin)
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'kondisi_barang' => 'Rusak Ringan',
            'catatan' => 'Adaptor baret, barang tetap berfungsi.',
            'bukti_pengembalian' => UploadedFile::fake()->image('bukti.jpg', 640, 480),
        ])
        ->assertRedirect(route('pengembalian.index'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    $segar = $permohonan->fresh();

    expect($segar->status)->toBe('Dikembalikan')
        ->and($segar->kondisi_barang)->toBe('Rusak Ringan')
        ->and($segar->catatan_admin)->toBe('Adaptor baret, barang tetap berfungsi.')
        ->and($segar->bukti_pengembalian)->not->toBeNull()
        ->and($segar->tanggal_pengembalian)->not->toBeNull();

    Storage::disk('public')->assertExists($segar->bukti_pengembalian);
    expect($segar->bukti_pengembalian)->toStartWith('bukti-pengembalian/');

    // Stok kembali seperti semula.
    expect($barang->fresh()->stok)->toBe(5);
});

it('allows a return without a proof photo', function () {
    Storage::fake('public');

    [$permohonan, $barang] = permohonanKondisi();
    $admin = petugasKondisi();

    $this->actingAs($admin)
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'kondisi_barang' => 'Baik',
            'catatan' => 'Barang diterima lengkap.',
        ])
        ->assertRedirect(route('pengembalian.index'))
        ->assertSessionHasNoErrors();

    expect($permohonan->fresh()->kondisi_barang)->toBe('Baik')
        ->and($permohonan->fresh()->bukti_pengembalian)->toBeNull()
        ->and($barang->fresh()->stok)->toBe(5);
});

it('requires a valid condition', function () {
    [$permohonan, $barang] = permohonanKondisi();
    $admin = petugasKondisi();

    $this->actingAs($admin)
        ->from(route('pengembalian.index'))
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'catatan' => 'Tanpa kondisi.',
        ])
        ->assertSessionHasErrors('kondisi_barang');

    $this->actingAs($admin)
        ->from(route('pengembalian.index'))
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'kondisi_barang' => 'Hancur Total',
            'catatan' => 'Kondisi tidak dikenal.',
        ])
        ->assertSessionHasErrors('kondisi_barang');

    expect($permohonan->fresh()->status)->toBe('Disetujui')
        ->and($barang->fresh()->stok)->toBe(3);
});

it('rejects a proof file that is not an image', function () {
    Storage::fake('public');

    [$permohonan] = permohonanKondisi();
    $admin = petugasKondisi();

    $this->actingAs($admin)
        ->from(route('pengembalian.index'))
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'kondisi_barang' => 'Baik',
            'catatan' => 'Bukti salah format.',
            'bukti_pengembalian' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('bukti_pengembalian');

    expect($permohonan->fresh()->status)->toBe('Disetujui');
    expect(Storage::disk('public')->allFiles())->toBe([]);
});

it('deletes the uploaded proof when the status transition is rejected', function () {
    Storage::fake('public');

    // Status Menunggu tidak boleh langsung menuju Dikembalikan.
    [$permohonan] = permohonanKondisi('Menunggu');
    $admin = petugasKondisi();

    $this->actingAs($admin)
        ->from(route('pengembalian.index'))
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'kondisi_barang' => 'Baik',
            'catatan' => 'Transisi tidak sah.',
            'bukti_pengembalian' => UploadedFile::fake()->image('bukti.jpg'),
        ])
        ->assertSessionHas('error');

    expect($permohonan->fresh()->status)->toBe('Menunggu')
        ->and($permohonan->fresh()->bukti_pengembalian)->toBeNull();

    // File yang sudah terlanjur diunggah tidak boleh tertinggal.
    expect(Storage::disk('public')->allFiles())->toBe([]);
});
