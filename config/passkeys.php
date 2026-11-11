<?php

$appUrl = rtrim((string) config('app.url'), '/');
$appHost = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';

/*
 * Pada development, host yang sedang dibuka ikut dianggap sah. WebAuthn
 * mengikat passkey pada registrable domain halaman, jadi nilai yang sama
 * harus dipakai dua sisi: rpId untuk browser dan origin untuk verifikasi
 * server. Tanpa ini, pengujian dari HP lewat nip.io selalu ditolak karena
 * konfigurasi hanya mengenal localhost.
 */
$devMode = (string) env('APP_ENV', 'production') === 'local';

$hostHeader = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$hostLayak = $devMode
    && $hostHeader !== ''
    && preg_match('/^[a-z0-9._-]+(:\d{1,5})?$/', $hostHeader) === 1;

$skema = ((($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off')
    || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') ? 'https' : 'http';

/*
 * WebAuthn menolak alamat IP dan menuntut registrable domain, jadi
 * 192-168-1-16.nip.io dipangkas menjadi nip.io sementara localhost utuh.
 */
$domainInduk = static function (string $host): string {
    if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
        return '';
    }

    $label = explode('.', trim($host, '.'));

    return count($label) <= 2 ? $host : implode('.', array_slice($label, -2));
};

$rpIdAktif = $hostLayak ? $domainInduk(explode(':', $hostHeader)[0]) : '';

$configuredOrigins = env('PASSKEYS_ALLOWED_ORIGINS') ?: $appUrl;

$allowedOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => rtrim(trim($origin), '/'),
    explode(',', (string) $configuredOrigins)
)));

if ($hostLayak) {
    $allowedOrigins[] = $skema . '://' . $hostHeader;
}

$allowedOrigins = array_values(array_unique(array_filter($allowedOrigins)));

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
    | Pada development nilainya diturunkan dari host yang sedang diakses;
    | alamat IP tidak bisa dipakai, jadi pakai nama domain (mis. nip.io).
    |
    */

    'relying_party_id' => env('PASSKEYS_RELYING_PARTY_ID') ?: ($rpIdAktif !== '' ? $rpIdAktif : $appHost),

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Origin yang diizinkan menyelesaikan proses WebAuthn. Bisa diisi lebih
    | dari satu, dipisahkan koma, melalui PASSKEYS_ALLOWED_ORIGINS. Origin
    | host aktif ikut ditambahkan pada development.
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
