<?php

$appUrl = rtrim((string) config('app.url'), '/');
$appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';

$configuredOrigins = env('PASSKEYS_ALLOWED_ORIGINS') ?: $appUrl;

$allowedOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => rtrim(trim($origin), '/'),
    explode(',', (string) $configuredOrigins)
)));

if ($allowedOrigins === []) {
    $allowedOrigins = [$appUrl];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Relying Party ID
    |--------------------------------------------------------------------------
    |
    | Domain yang menjadi identitas aplikasi pada WebAuthn. Passkey terikat
    | pada nilai ini, jadi harus sama dengan domain yang dibuka pengguna.
    | Untuk development gunakan "localhost" (bukan alamat IP).
    |
    */

    'relying_party_id' => env('PASSKEYS_RELYING_PARTY_ID') ?: $appHost,

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Origin yang diizinkan menyelesaikan proses WebAuthn. Bisa diisi lebih
    | dari satu, dipisahkan koma, melalui PASSKEYS_ALLOWED_ORIGINS.
    |
    */

    'allowed_origins' => $allowedOrigins,

    /*
    |--------------------------------------------------------------------------
    | User Handle Secret
    |--------------------------------------------------------------------------
    |
    | Kunci untuk menurunkan user handle WebAuthn. Jika kosong, memakai
    | APP_KEY. Set eksplisit di produksi agar passkey tidak rusak saat
    | APP_KEY diganti.
    |
    */

    'user_handle_secret' => env('PASSKEYS_USER_HANDLE_SECRET') ?: config('app.key'),

    /*
    |--------------------------------------------------------------------------
    | WebAuthn Timeout (milidetik)
    |--------------------------------------------------------------------------
    */

    'timeout' => 60000,

    /*
    |--------------------------------------------------------------------------
    | Guard Autentikasi
    |--------------------------------------------------------------------------
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Middleware Route Passkeys
    |--------------------------------------------------------------------------
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Middleware Pengelolaan Passkey
    |--------------------------------------------------------------------------
    |
    | Dikosongkan agar perilakunya sama dengan implementasi sebelumnya
    | (tidak meminta konfirmasi kata sandi ulang).
    |
    */

    'management_middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Throttling
    |--------------------------------------------------------------------------
    */

    'throttle' => 'throttle:6,1',

    /*
    |--------------------------------------------------------------------------
    | Redirect Setelah Login
    |--------------------------------------------------------------------------
    */

    'redirect' => '/dashboard',

];
