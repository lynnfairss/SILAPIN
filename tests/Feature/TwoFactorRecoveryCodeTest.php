<?php

use App\Models\User;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
});

/**
 * Akun dengan 2FA aktif beserta daftar kode pemulihan polosnya.
 *
 * @return array{0: User, 1: string, 2: array<int, string>}
 */
function akunDengan2FA(): array
{
    $user = User::factory()->create();
    $secret = Google2FA::generateSecretKey();

    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
    ])->save();

    $kode = $user->generateTwoFactorRecoveryCodes();
    $user->save();

    return [$user, $secret, $kode];
}

function keluarDariSesi(): void
{
    test()->post(route('logout'));
}

it('menghasilkan kode pemulihan saat konfirmasi 2FA dan memakainya sebagai pengganti OTP', function () {
    $user = User::factory()->create();
    $secret = Google2FA::generateSecretKey();
    $user->forceFill(['two_factor_secret' => $secret])->save();

    $this->actingAs($user)
        ->post(route('security.2fa.confirm'), ['code' => Google2FA::getCurrentOtp($secret)])
        ->assertSessionHas('recovery_codes');

    $kode = session('recovery_codes');
    $user->refresh();

    expect($kode)->toHaveCount(User::RECOVERY_CODE_COUNT)
        ->and($user->recoveryCodeCount())->toBe(User::RECOVERY_CODE_COUNT);

    foreach ($kode as $satu) {
        expect($satu)->toMatch('/^SLPN-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
    }

    // Kode polos tidak pernah ikut tersimpan di database.
    expect($user->getRawOriginal('two_factor_recovery_codes'))->not->toContain($kode[0]);

    keluarDariSesi();
    $this->session(['2fa_user_id' => $user->id]);

    $this->post(route('login.2fa.post'), ['code' => $kode[0]])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('menolak kode pemulihan yang sudah dipakai', function () {
    [$user, $secret, $kode] = akunDengan2FA();

    $this->session(['2fa_user_id' => $user->id]);
    $this->post(route('login.2fa.post'), ['code' => $kode[0]]);
    $this->assertAuthenticated();

    keluarDariSesi();

    $this->session(['2fa_user_id' => $user->id]);
    $this->post(route('login.2fa.post'), ['code' => $kode[0]])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
    expect($user->refresh()->recoveryCodeCount())->toBe(User::RECOVERY_CODE_COUNT - 1);
});

it('menerima kode pemulihan yang diketik tanpa format baku', function () {
    [$user, $secret, $kode] = akunDengan2FA();

    $this->session(['2fa_user_id' => $user->id]);

    $this->post(route('login.2fa.post'), [
        'code' => strtolower(str_replace('-', '', $kode[2])),
    ]);

    $this->assertAuthenticated();
});

it('memberlakukan daftar kode baru dan meninggalkan kode lama', function () {
    [$user, $secret, $lama] = akunDengan2FA();

    $this->actingAs($user)
        ->post(route('security.2fa.recovery'))
        ->assertSessionHas('recovery_codes');

    $baru = session('recovery_codes');
    expect($baru)->not->toBe($lama);

    keluarDariSesi();

    $this->session(['2fa_user_id' => $user->id]);
    $this->post(route('login.2fa.post'), ['code' => $lama[0]])
        ->assertSessionHasErrors('code');
    $this->assertGuest();

    $this->session(['2fa_user_id' => $user->id]);
    $this->post(route('login.2fa.post'), ['code' => $baru[0]]);
    $this->assertAuthenticated();
});

it('menghapus kode pemulihan ketika 2FA dinonaktifkan', function () {
    [$user, $secret, $kode] = akunDengan2FA();

    $this->actingAs($user)
        ->post(route('security.2fa.disable'), [
            'code' => Google2FA::getCurrentOtp($secret),
        ])
        ->assertSessionHas('success');

    $user->refresh();
    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->recoveryCodeCount())->toBe(0);

    keluarDariSesi();

    $this->session(['2fa_user_id' => $user->id]);
    $this->post(route('login.2fa.post'), ['code' => $kode[0]])
        ->assertRedirect(route('login.admin'));

    $this->assertGuest();
});

it('menampilkan halaman keamanan pada tiap kondisi 2FA', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('security'))
        ->assertOk()
        ->assertSee('Mulai Aktivasi 2FA', false);

    $secret = Google2FA::generateSecretKey();
    $user->forceFill(['two_factor_secret' => $secret])->save();

    $this->actingAs($user)
        ->get(route('security'))
        ->assertSee('Kode Verifikasi OTP', false)
        ->assertSee('data:image/svg+xml;base64,', false);

    $user->forceFill(['two_factor_confirmed_at' => now()])->save();
    $kode = $user->generateTwoFactorRecoveryCodes();
    $user->save();

    $this->actingAs($user)
        ->withSession(['recovery_codes' => $kode])
        ->get(route('security'))
        ->assertSee('Autentikasi Dua Faktor Telah Aktif', false)
        ->assertSee('Simpan sekarang', false)
        ->assertSee($kode[0], false);
});
