<?php

use App\Models\DetailPermohonan;
use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function admin(): User
{
    return User::factory()->create(['role' => 'admin', 'status' => 'approved']);
}

function superAdmin(): User
{
    return User::factory()->superAdmin()->create();
}

function peminjamanAktif(Instansi $instansi, Inventaris $barang, string $status = 'Disetujui'): Permohonan
{
    $permohonan = Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-'.uniqid(),
        'instansi_id' => $instansi->id,
        'nama_peminjam' => 'Budi',
        'nik' => '3500000000000001',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'status' => $status,
    ]);

    $permohonan->detailPermohonan()->create(['inventaris_id' => $barang->id, 'jumlah' => 1]);

    return $permohonan;
}

it('refuses to delete an agency that still has active loans', function () {
    $instansi = Instansi::create(['nama_instansi' => 'Dinas A']);
    peminjamanAktif($instansi, Inventaris::create([
        'kode_barang' => 'X-1', 'nama_barang' => 'Barang X', 'stok' => 1, 'kondisi' => 'Baik',
    ]));

    $this->actingAs(superAdmin())
        ->delete(route('instansi.destroy', $instansi))
        ->assertRedirect(route('instansi.index'))
        ->assertSessionHas('error');

    expect(Instansi::whereKey($instansi->id)->exists())->toBeTrue();
});

it('keeps the loan history when an agency is deleted', function () {
    $instansi = Instansi::create(['nama_instansi' => 'Dinas B']);
    $permohonan = peminjamanAktif($instansi, Inventaris::create([
        'kode_barang' => 'X-2', 'nama_barang' => 'Barang Y', 'stok' => 1, 'kondisi' => 'Baik',
    ]), 'Ditolak');

    $this->actingAs(superAdmin())
        ->delete(route('instansi.destroy', $instansi))
        ->assertSessionHas('success');

    expect(Instansi::whereKey($instansi->id)->exists())->toBeFalse();

    // ON DELETE SET NULL: Berkas tetap ada, hanya label instansi yang hilang.
    $tersisa = Permohonan::find($permohonan->id);
    expect($tersisa)->not->toBeNull();
    expect($tersisa->instansi_id)->toBeNull();
    expect(DetailPermohonan::where('permohonan_id', $permohonan->id)->count())->toBe(1);
});

it('refuses to delete equipment that is still on loan', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'X-3', 'nama_barang' => 'Barang Z', 'stok' => 1, 'kondisi' => 'Baik',
    ]);
    peminjamanAktif(Instansi::create(['nama_instansi' => 'Dinas C']), $barang, 'Dipinjam');

    $this->actingAs(superAdmin())
        ->delete(route('inventaris.destroy', $barang))
        ->assertSessionHas('error');

    expect(Inventaris::whereKey($barang->id)->exists())->toBeTrue();
});

it('refuses to delete a request whose goods are out', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'X-4', 'nama_barang' => 'Barang W', 'stok' => 1, 'kondisi' => 'Baik',
    ]);
    $permohonan = peminjamanAktif(Instansi::create(['nama_instansi' => 'Dinas D']), $barang, 'Dipinjam');

    $this->actingAs(admin())
        ->delete(route('permohonan.destroy', $permohonan))
        ->assertSessionHas('error');

    expect(Permohonan::whereKey($permohonan->id)->exists())->toBeTrue();
});

it('rejects a status change that is not allowed', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'X-5', 'nama_barang' => 'Barang V', 'stok' => 1, 'kondisi' => 'Baik',
    ]);
    $permohonan = peminjamanAktif(Instansi::create(['nama_instansi' => 'Dinas E']), $barang, 'Ditolak');

    $this->actingAs(admin())
        ->patch(route('permohonan.status', $permohonan), ['status' => 'Disetujui'])
        ->assertSessionHas('error');

    expect($permohonan->fresh()->status)->toBe('Ditolak');
    expect($barang->fresh()->stok)->toBe(1);
});

it('rejects a quantity larger than the available stock from the public form', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'X-6', 'nama_barang' => 'Barang U', 'stok' => 2, 'kondisi' => 'Baik',
    ]);

    $this->post(route('peminjam.store'), [
        'nama_peminjam' => 'Rahma',
        'nik' => '3500000000000004',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'inventaris' => [$barang->id],
        'jumlah' => [$barang->id => 9999],
    ])->assertSessionHasErrors('jumlah.'.$barang->id);

    expect(Permohonan::where('nama_peminjam', 'Rahma')->exists())->toBeFalse();
});

it('rejects duplicate item ids from the public form', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'X-7', 'nama_barang' => 'Barang T', 'stok' => 5, 'kondisi' => 'Baik',
    ]);

    $this->post(route('peminjam.store'), [
        'nama_peminjam' => 'Rahma',
        'nik' => '3500000000000005',
        'telepon' => '08123456789',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'inventaris' => [$barang->id, $barang->id],
        'jumlah' => [$barang->id => 1],
    ])->assertSessionHasErrors('inventaris.0');

    expect(Permohonan::where('nama_peminjam', 'Rahma')->exists())->toBeFalse();
});

it('rejects a non existent agency id instead of crashing', function () {
    $barang = Inventaris::create([
        'kode_barang' => 'X-8', 'nama_barang' => 'Barang S', 'stok' => 5, 'kondisi' => 'Baik',
    ]);

    $this->post(route('peminjam.store'), [
        'nama_peminjam' => 'Rahma',
        'nik' => '3500000000000006',
        'telepon' => '08123456789',
        'instansi_id' => '999999',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'inventaris' => [$barang->id],
        'jumlah' => [$barang->id => 1],
    ])->assertSessionHasErrors('instansi_id');

    expect(Permohonan::where('nama_peminjam', 'Rahma')->exists())->toBeFalse();
});

it('lets a super admin create an admin account', function () {
    $this->actingAs(superAdmin())
        ->post(route('users.store'), [
            'name' => 'Petugas Baru',
            'email' => 'petugas@silapin.test',
            'role' => 'admin',
            'password' => 'RahasiaKuat123',
            'password_confirmation' => 'RahasiaKuat123',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $user = User::where('email', 'petugas@silapin.test')->firstOrFail();

    expect($user->role)->toBe('admin')
        ->and($user->status)->toBe('approved')
        ->and($user->isAdmin())->toBeTrue();
});

it('refuses a weak password when creating an account', function () {
    $this->actingAs(superAdmin())
        ->post(route('users.store'), [
            'name' => 'Petugas Lemah',
            'email' => 'lemah@silapin.test',
            'role' => 'admin',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])
        ->assertSessionHasErrors('password');

    expect(User::where('email', 'lemah@silapin.test')->exists())->toBeFalse();
});

it('refuses an unknown role when creating an account', function () {
    $this->actingAs(superAdmin())
        ->post(route('users.store'), [
            'name' => 'Penyusup',
            'email' => 'penyusup@silapin.test',
            'role' => 'super_admin_kedua',
            'password' => 'RahasiaKuat123',
            'password_confirmation' => 'RahasiaKuat123',
        ])
        ->assertSessionHasErrors('role');

    expect(User::where('email', 'penyusup@silapin.test')->exists())->toBeFalse();
});

it('keeps a normal admin out of account management', function () {
    $this->actingAs(admin())
        ->get(route('users.create'))
        ->assertForbidden();

    $this->actingAs(admin())
        ->post(route('users.store'), [
            'name' => 'Penyusup',
            'email' => 'penyusup2@silapin.test',
            'role' => 'super_admin',
            'password' => 'RahasiaKuat123',
            'password_confirmation' => 'RahasiaKuat123',
        ])
        ->assertForbidden();
});

it('stops a super admin from deleting their own account', function () {
    $ini = superAdmin();

    $this->actingAs($ini)
        ->delete(route('users.destroy', $ini))
        ->assertSessionHas('error');

    expect(User::whereKey($ini->id)->exists())->toBeTrue();
});

it('ends the sessions of a deleted account', function () {
    $korban = admin();
    $penyusup = superAdmin();

    DB::table('sessions')->insert([
        'id' => 'sesi-korban',
        'user_id' => $korban->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => 'x',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($penyusup)
        ->delete(route('users.destroy', $korban))
        ->assertSessionHas('success');

    expect(User::whereKey($korban->id)->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'sesi-korban')->exists())
        ->toBeFalse();
});
