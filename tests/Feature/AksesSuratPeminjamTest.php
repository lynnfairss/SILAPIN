<?php

use App\Models\Permohonan;
use App\Models\User;

function permohonanPII(): Permohonan
{
    return Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-PII-0001',
        'nama_peminjam' => 'Rahma',
        'nik' => '3500000000000009',
        'telepon' => '081298765432',
        'alamat' => 'Jl. Rahasia No. 9, Ponorogo',
        'tempat_tanggal_lahir' => 'Ponorogo, 01/01/1990',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji',
        'status' => 'Disetujui',
    ]);
}

it('generates a token for every new request', function () {
    $permohonan = permohonanPII();

    expect($permohonan->token)->toBeString()->toHaveLength(64);
    expect(Permohonan::pluck('token')->duplicates())->toHaveCount(0);
});

it('does not accept a token through mass assignment', function () {
    // token sengaja tidak ada di $fillable, jadi fill()/update() dengan
    // data dari request tidak akan pernah menimpanya.
    $permohonan = permohonanPII();
    $asli = $permohonan->token;

    $permohonan->fill(['token' => 'dipilih-pengguna', 'nama_peminjam' => 'Nama Baru'])->save();

    expect($permohonan->fresh()->token)->toBe($asli)
        ->and($permohonan->fresh()->nama_peminjam)->toBe('Nama Baru');

    expect((new ReflectionProperty($permohonan, 'fillable'))->getValue($permohonan))
        ->not->toContain('token');
});

it('refuses to hand the letter to a visitor without the token', function () {
    $permohonan = permohonanPII();

    $this->get(route('peminjam.download-surat', $permohonan->id))->assertForbidden();
    $this->get(route('peminjam.download-surat.docx', $permohonan->id))->assertForbidden();
    $this->get(route('peminjam.download-surat.pdf', $permohonan->id))->assertForbidden();
});

it('refuses a wrong token', function () {
    $permohonan = permohonanPII();

    $this->get(route('peminjam.download-surat', $permohonan).'?token=token-salah')
        ->assertForbidden();
});

it('lets the applicant download their own letter with the token', function () {
    $permohonan = permohonanPII();

    $this->get(route('peminjam.download-surat', $permohonan).'?token='.$permohonan->token)
        ->assertOk();

    $this->get(route('peminjam.download-surat.docx', $permohonan).'?token='.$permohonan->token)
        ->assertOk();

    $this->get(route('peminjam.download-surat.pdf', $permohonan).'?token='.$permohonan->token)
        ->assertOk();
});

it('lets admins download any letter without a token', function () {
    $permohonan = permohonanPII();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('peminjam.download-surat', $permohonan->id))
        ->assertOk();

    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('peminjam.download-surat.pdf', $permohonan->id))
        ->assertOk();
});

it('hides personal data from the status lookup when no token is given', function () {
    $permohonan = permohonanPII();

    $response = $this->getJson(route('peminjam.cek-status').'?nomor='.$permohonan->nomor_permohonan)
        ->assertOk()
        ->assertJson([
            'nomor' => $permohonan->nomor_permohonan,
            'status' => $permohonan->status,
            'nik' => null,
            'alamat' => null,
            'telepon' => null,
            'tempat_tanggal_lahir' => null,
        ]);
});

it('shows personal data to the applicant holding the token', function () {
    $permohonan = permohonanPII();

    $this->getJson(route('peminjam.cek-status').'?nomor='.$permohonan->nomor_permohonan.'&token='.$permohonan->token)
        ->assertOk()
        ->assertJson([
            'nik' => '3500000000000009',
            'alamat' => 'Jl. Rahasia No. 9, Ponorogo',
            'telepon' => '081298765432',
        ]);
});

it('does not leak the letter of another request on the status page', function () {
    $permohonan = permohonanPII();

    $response = $this->get(route('peminjam.cek-status').'?nomor='.$permohonan->nomor_permohonan)
        ->assertOk();

    // Status tetap terlihat, tetapi tombol unduh dan data pribadi tidak.
    $response->assertDontSee($permohonan->nik);
    $response->assertDontSee($permohonan->telepon);
    $response->assertDontSee(route('peminjam.download-surat', $permohonan->id));
});
