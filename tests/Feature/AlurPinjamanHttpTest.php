<?php

use App\Models\Inventaris;
use App\Models\Jenis;
use App\Models\Permohonan;
use App\Models\User;

/**
 * Alur persetujuan dan pengembalian lewat endpoint HTTP sungguhan, bukan
 * lewat service secara langsung, supaya routing, middleware, validasi, dan
 * efek stok ikut teruji sebagai satu rangkaian.
 */
function adminL(): User
{
    return User::factory()->create(['role' => 'admin', 'status' => 'approved']);
}

function buatPermohonan(Inventaris $barang, int $jumlah, string $status = 'Menunggu'): Permohonan
{
    $permohonan = Permohonan::create([
        'nomor_permohonan' => 'SP-E2E-'.uniqid(),
        'nama_peminjam' => 'Peminjam Uji',
        'nik' => '3500000000000099',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDays(3)->toDateString(),
        'keperluan' => 'Uji end-to-end',
        'status' => $status,
    ]);

    $permohonan->detailPermohonan()->create([
        'inventaris_id' => $barang->id,
        'jumlah' => $jumlah,
    ]);

    return $permohonan;
}

it('runs the full approve then return cycle through the HTTP endpoints', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'E2E-1', 'nama_barang' => 'Kamera Uji', 'stok' => 6, 'kondisi' => 'Baik',
    ]);
    $permohonan = buatPermohonan($barang, 2);
    $admin = adminL();

    $this->actingAs($admin)
        ->patch(route('permohonan.status', $permohonan), ['status' => 'Disetujui'])
        ->assertRedirect(route('permohonan.index'))
        ->assertSessionHas('success');

    expect($barang->fresh()->stok)->toBe(4)
        ->and($permohonan->fresh()->status)->toBe('Disetujui');

    $this->actingAs($admin)
        ->post(route('pengembalian.proses'), [
            'nomor_permohonan' => $permohonan->nomor_permohonan,
            'catatan' => 'Barang utuh dikembalikan.',
        ])
        ->assertRedirect(route('pengembalian.index'))
        ->assertSessionHas('success');

    expect($barang->fresh()->stok)->toBe(6)
        ->and($permohonan->fresh()->status)->toBe('Dikembalikan');
});

it('blocks a second return of the same request through the endpoint', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'E2E-2', 'nama_barang' => 'Tripod Uji', 'stok' => 3, 'kondisi' => 'Baik',
    ]);
    $permohonan = buatPermohonan($barang, 1, 'Disetujui');
    $barang->update(['stok' => 2]);
    $admin = adminL();

    $this->actingAs($admin)->post(route('pengembalian.proses'), [
        'nomor_permohonan' => $permohonan->nomor_permohonan,
        'catatan' => 'Dikembalikan.',
    ])->assertSessionHas('success');

    expect($barang->fresh()->stok)->toBe(3);

    // Pengembalian kedua harus ditolak dan stok tetap 3.
    $this->actingAs($admin)->post(route('pengembalian.proses'), [
        'nomor_permohonan' => $permohonan->nomor_permohonan,
        'catatan' => 'Dikembalikan lagi.',
    ])->assertSessionHas('error');

    expect($barang->fresh()->stok)->toBe(3)
        ->and($permohonan->fresh()->status)->toBe('Dikembalikan');
});

it('refuses to approve when the stock has already been taken', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'E2E-3', 'nama_barang' => 'Mic Uji', 'stok' => 1, 'kondisi' => 'Baik',
    ]);
    $pertama = buatPermohonan($barang, 1);
    $kedua = buatPermohonan($barang, 1);
    $admin = adminL();

    $this->actingAs($admin)
        ->patch(route('permohonan.status', $pertama), ['status' => 'Disetujui'])
        ->assertSessionHas('success');

    expect($barang->fresh()->stok)->toBe(0);

    $this->actingAs($admin)
        ->patch(route('permohonan.status', $kedua), ['status' => 'Disetujui'])
        ->assertSessionHas('error');

    expect($barang->fresh()->stok)->toBe(0)
        ->and($kedua->fresh()->status)->toBe('Menunggu');
});

it('keeps an unknown inventory condition from becoming a 500', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->post(route('inventaris.store'), [
            'kode_barang' => 'E2E-4',
            'nama_barang' => 'Barang Uji',
            'jenis_id' => Jenis::firstOrCreate(['nama_jenis' => 'Uji'])->id,
            'stok' => 1,
            'kondisi' => 'Hancur Total',
        ])
        ->assertSessionHasErrors('kondisi');

    expect(Inventaris::where('kode_barang', 'E2E-4')->exists())->toBeFalse();
});

it('allows stock to be set to zero', function () {
    $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Uji Nol']);
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->post(route('inventaris.store'), [
            'kode_barang' => 'E2E-5',
            'nama_barang' => 'Barang Kosong',
            'jenis_id' => $jenis->id,
            'stok' => 0,
            'kondisi' => 'Baik',
        ])
        ->assertSessionHasNoErrors();

    expect((int) Inventaris::where('kode_barang', 'E2E-5')->value('stok'))->toBe(0);
});

it('rejects a repeated public submission larger than the stock', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'E2E-6', 'nama_barang' => 'Adaptor Uji', 'stok' => 2, 'kondisi' => 'Baik',
    ]);

    $this->from(route('peminjam.form'))->post(route('peminjam.store'), [
        'nama_peminjam' => 'Warga',
        'nik' => '3500000000000077',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'inventaris' => [$barang->id],
        'jumlah' => [$barang->id => 3],
    ])->assertSessionHasErrors('jumlah.'.$barang->id);

    expect(Permohonan::where('nik', '3500000000000077')->exists())->toBeFalse();
});

it('creates a request from the public form with a usable token', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'E2E-7', 'nama_barang' => 'Powerbank Uji', 'stok' => 5, 'kondisi' => 'Baik',
    ]);

    $response = $this->post(route('peminjam.store'), [
        'nama_peminjam' => 'Warga Uji',
        'nik' => '3500000000000066',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'inventaris' => [$barang->id],
        'jumlah' => [$barang->id => 1],
    ]);

    $permohonan = Permohonan::where('nik', '3500000000000066')->firstOrFail();
    $response->assertRedirect(route('peminjam.cek-status', [
        'nomor' => $permohonan->nomor_permohonan,
        'token' => $permohonan->token,
    ]));

    // Pengajuan tidak boleh menyentuh stok; stok hanya berubah saat disetujui.
    expect($barang->fresh()->stok)->toBe(5);

    $this->get(route('peminjam.download-surat', $permohonan).'?token='.$permohonan->token)
        ->assertOk();
});
