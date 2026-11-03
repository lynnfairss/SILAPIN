<?php

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function buatPermohonanAksesSurat(string $status): Permohonan
{
    return Permohonan::create([
        'nomor_permohonan' => 'SP-TEST-AKSES-'.uniqid(),
        'nama_peminjam' => 'Rahma',
        'nik' => '3500000000000009',
        'telepon' => '081298765432',
        'tanggal_pinjam' => now()->toDateString(),
        'tanggal_kembali' => now()->addDay()->toDateString(),
        'keperluan' => 'Uji akses surat',
        'status' => $status,
    ]);
}

function adminAksesSurat(string $role = 'admin'): User
{
    return User::factory()->create([
        'role' => $role,
        'status' => 'approved',
    ]);
}

it('blocks letter access before approval for both admin roles', function (string $role) {
    $admin = adminAksesSurat($role);
    $permohonan = buatPermohonanAksesSurat('Menunggu');

    $this->actingAs($admin)->get(route('surat.preview', $permohonan))->assertForbidden();
    $this->actingAs($admin)->get(route('surat.download.docx', $permohonan))->assertForbidden();
    $this->actingAs($admin)->get(route('surat.download.pdf', $permohonan))->assertForbidden();
    $this->actingAs($admin)->get(route('surat.berkas', $permohonan))->assertForbidden();
})->with(['admin', 'super_admin']);

it('blocks letter access for a rejected request', function () {
    $admin = adminAksesSurat();
    $permohonan = buatPermohonanAksesSurat('Ditolak');

    $this->actingAs($admin)->get(route('surat.preview', $permohonan))->assertForbidden();
    $this->actingAs($admin)->get(route('surat.download.docx', $permohonan))->assertForbidden();
    $this->actingAs($admin)->get(route('surat.download.pdf', $permohonan))->assertForbidden();
    $this->actingAs($admin)->get(route('surat.berkas', $permohonan))->assertForbidden();
});

it('allows admins to preview letters after approval', function (string $status) {
    $admin = adminAksesSurat();
    $permohonan = buatPermohonanAksesSurat($status);

    $this->actingAs($admin)
        ->get(route('surat.preview', $permohonan))
        ->assertOk();
})->with(['Disetujui', 'Dipinjam', 'Dikembalikan']);

it('hides letter actions on admin pages for unapproved requests', function () {
    $admin = adminAksesSurat();
    $permohonan = buatPermohonanAksesSurat('Menunggu');

    $this->actingAs($admin)
        ->get(route('permohonan.show', $permohonan))
        ->assertOk()
        ->assertDontSee(route('surat.preview', $permohonan))
        ->assertDontSee(route('surat.download.docx', $permohonan))
        ->assertDontSee(route('surat.download.pdf', $permohonan))
        ->assertDontSee('Terkunci');

    $this->actingAs($admin)
        ->get(route('permohonan.index'))
        ->assertOk()
        ->assertDontSee(route('surat.preview', $permohonan))
        ->assertDontSee('Terkunci');

    $this->actingAs($admin)
        ->get(route('surat.index'))
        ->assertOk()
        ->assertDontSee($permohonan->nomor_permohonan)
        ->assertDontSee(route('surat.preview', $permohonan));

    $this->actingAs($admin)
        ->get(route('surat.upload', ['permohonan' => $permohonan->id]))
        ->assertOk()
        ->assertDontSee($permohonan->nomor_permohonan)
        ->assertDontSee(route('surat.preview', $permohonan))
        ->assertDontSee('Template untuk Surat Terpilih');
});

it('shows letter actions and lists after approval', function () {
    $admin = adminAksesSurat();
    $permohonan = buatPermohonanAksesSurat('Disetujui');

    $this->actingAs($admin)
        ->get(route('permohonan.show', $permohonan))
        ->assertOk()
        ->assertSee(route('surat.preview', $permohonan))
        ->assertSee('Lihat Surat');

    $this->actingAs($admin)
        ->get(route('surat.index'))
        ->assertOk()
        ->assertSee($permohonan->nomor_permohonan)
        ->assertSee(route('surat.preview', $permohonan));

    $this->actingAs($admin)
        ->get(route('surat.upload', ['permohonan' => $permohonan->id]))
        ->assertOk()
        ->assertSee($permohonan->nomor_permohonan)
        ->assertSee('Template untuk Surat Terpilih');
});

it('rejects uploading a letter for an unapproved request', function () {
    Storage::fake('local');

    $admin = adminAksesSurat();
    $permohonan = buatPermohonanAksesSurat('Menunggu');

    $berkas = UploadedFile::fake()->create(
        'surat.docx',
        10,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    );

    $this->actingAs($admin)
        ->from(route('surat.upload', ['permohonan' => $permohonan->id]))
        ->post(route('surat.upload.store'), [
            'permohonan_id' => $permohonan->id,
            'file' => $berkas,
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('permohonan_id');

    expect($permohonan->fresh()->word_path)->toBeNull();
});

it('rejects assigning a template to an unapproved request', function () {
    $admin = adminAksesSurat();
    $permohonan = buatPermohonanAksesSurat('Menunggu');

    $this->actingAs($admin)
        ->from(route('surat.upload', ['permohonan' => $permohonan->id]))
        ->post(route('surat.template.assign', $permohonan), [
            'surat_template_id' => null,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});
