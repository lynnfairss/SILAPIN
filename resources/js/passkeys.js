import {
    InvalidDomainError,
    NotSupportedError,
    PasskeyError,
    PasskeyExistsError,
    Passkeys,
    UserCancelledError,
} from '@laravel/passkeys';

window.Passkeys = Passkeys;

window.PasskeyErrors = {
    InvalidDomainError,
    NotSupportedError,
    PasskeyError,
    PasskeyExistsError,
    UserCancelledError,
};

window.passkeyErrorMessage = function (error) {
    if (!error) {
        return 'Terjadi kesalahan. Silakan coba lagi.';
    }

    if (error.name === 'UserCancelledError') {
        return 'Operasi passkey dibatalkan.';
    }

    if (error.name === 'NotSupportedError') {
        return 'Browser atau perangkat ini tidak mendukung passkey.';
    }

    if (error.name === 'PasskeyExistsError') {
        return 'Perangkat ini sudah terdaftar sebagai passkey.';
    }

    if (error.name === 'InvalidDomainError') {
        return 'Passkey hanya bisa dipakai lewat localhost atau HTTPS.';
    }

    const message = String(error.message || '');

    if (/not recognized|unable to sign in|invalid/i.test(message)) {
        return 'Passkey tidak dikenali untuk akun ini.';
    }

    return message || 'Terjadi kesalahan. Silakan coba lagi.';
};
