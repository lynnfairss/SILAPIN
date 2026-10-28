<?php

use App\Exceptions\PasskeyLoginDeniedException;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys;

function passkeyUji(User $user): Passkey
{
    return $user->passkeys()->create([
        'name' => 'Passkey Uji',
        'credential_id' => Str::uuid()->toString(),
        'credential' => [],
    ]);
}

function permintaanLoginPasskey(?string $role = null): Request
{
    $request = Request::create('/passkeys/login', 'POST');

    if ($role !== null) {
        $request->headers->set('X-Passkey-Role', $role);
    }

    return $request;
}

it('menyediakan opsi login passkey untuk guest', function () {
    $this->getJson('/passkeys/login/options')
        ->assertOk()
        ->assertJsonStructure(['options' => ['challenge', 'rpId', 'timeout', 'allowCredentials', 'userVerification']])
        ->assertJsonPath('options.rpId', config('passkeys.relying_party_id'));
});

it('menolak login passkey dengan credential tidak valid', function () {
    $this->postJson('/passkeys/login', [])
        ->assertStatus(422);
});

it('mewajibkan login untuk mengelola passkey', function () {
    $this->getJson('/user/passkeys/options')->assertUnauthorized();
});

it('menyediakan opsi registrasi passkey untuk user login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson('/user/passkeys/options')
        ->assertOk()
        ->assertJsonStructure([
            'options' => [
                'challenge',
                'rp' => ['id', 'name'],
                'user' => ['id', 'name', 'displayName'],
                'pubKeyCredParams',
            ],
        ])
        ->assertJsonPath('options.rp.id', config('passkeys.relying_party_id'));
});

it('mengizinkan user menghapus passkey miliknya', function () {
    $user = User::factory()->create();
    $passkey = passkeyUji($user);

    $this->actingAs($user)
        ->deleteJson("/user/passkeys/{$passkey->id}")
        ->assertOk()
        ->assertJson(['status' => 'passkey-deleted']);

    $this->assertDatabaseMissing('passkeys', ['id' => $passkey->id]);
});

it('menolak user menghapus passkey milik user lain', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $passkey = passkeyUji($owner);

    $this->actingAs($other)
        ->deleteJson("/user/passkeys/{$passkey->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('passkeys', ['id' => $passkey->id]);
});

it('menghapus route passkey lama', function () {
    $this->postJson('/passkey/options')->assertNotFound();
    $this->postJson('/passkey/login')->assertNotFound();
});

it('mengizinkan admin login dengan passkey dari halaman admin', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $passkey = passkeyUji($admin);

    expect(Passkeys::allowsLogin(permintaanLoginPasskey('admin'), $passkey))->toBeTrue();
});

it('mengizinkan admin login dengan passkey tanpa header role', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $passkey = passkeyUji($admin);

    expect(Passkeys::allowsLogin(permintaanLoginPasskey(), $passkey))->toBeTrue();
});

it('menolak admin login lewat halaman super admin', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
    $passkey = passkeyUji($admin);

    expect(fn () => Passkeys::allowsLogin(permintaanLoginPasskey('super_admin'), $passkey))
        ->toThrow(PasskeyLoginDeniedException::class);
});

it('mengizinkan super admin login lewat halaman super admin', function () {
    $superadmin = User::factory()->superAdmin()->create();
    $passkey = passkeyUji($superadmin);

    expect(Passkeys::allowsLogin(permintaanLoginPasskey('super_admin'), $passkey))->toBeTrue();
});

it('menolak akun non admin login dengan passkey', function () {
    $nonAdmin = new User([
        'name' => 'Non Admin',
        'email' => 'nonadmin@example.test',
        'role' => 'staff',
        'status' => 'approved',
    ]);

    $passkey = new Passkey();
    $passkey->setRelation('user', $nonAdmin);

    expect(fn () => Passkeys::allowsLogin(permintaanLoginPasskey('admin'), $passkey))
        ->toThrow(PasskeyLoginDeniedException::class);
});

it('menolak akun yang belum disetujui login dengan passkey', function () {
    $pending = User::factory()->pending()->create(['role' => 'admin']);
    $passkey = passkeyUji($pending);

    expect(fn () => Passkeys::allowsLogin(permintaanLoginPasskey('admin'), $passkey))
        ->toThrow(PasskeyLoginDeniedException::class);
});

it('menolak akun yang ditolak login dengan passkey', function () {
    $rejected = User::factory()->create(['role' => 'admin', 'status' => 'rejected']);
    $passkey = passkeyUji($rejected);

    expect(fn () => Passkeys::allowsLogin(permintaanLoginPasskey('admin'), $passkey))
        ->toThrow(PasskeyLoginDeniedException::class);
});

it('memakai kontrak passkey user dan relasi passkeys', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(PasskeyUser::class);
    expect($user->passkeys())->toBeInstanceOf(HasMany::class);
});

it('menampilkan tombol passkey di halaman login admin', function () {
    $this->get('/login/admin')
        ->assertOk()
        ->assertSee('Masuk dengan Passkey')
        ->assertSee('data-role="admin"', false);
});

it('menampilkan tombol passkey di halaman login super admin', function () {
    $this->get('/login/superadmin')
        ->assertOk()
        ->assertSee('Masuk dengan Passkey')
        ->assertSee('data-role="super_admin"', false);
});

it('menampilkan halaman keamanan beserta passkey user', function () {
    $user = User::factory()->create();
    $passkey = passkeyUji($user);

    $this->actingAs($user)
        ->get('/security')
        ->assertOk()
        ->assertSee('Tambah Passkey')
        ->assertSee($passkey->name);
});
