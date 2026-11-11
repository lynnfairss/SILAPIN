@php($kominfoBg = file_exists(public_path('images/kominfo.jpg')) ? asset('images/kominfo.jpg') : null)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#153b79">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login Super Admin - SILAPIN</title>

    @vite('resources/js/passkeys.js')
    <script src="{{ asset('js/security.js') }}?v={{ filemtime(public_path('js/security.js')) ?: 1 }}" defer></script>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =====================================
           RESET & GLOBAL
        ===================================== */

        :root {
            --primary: #2055b2;
            --primary-dark: #153b79;
            --primary-light: #eaf2ff;
            --text: #243650;
            --muted: #8190a5;
            --border: #dfe6ef;
            --white: #ffffff;
            --kominfo-blue: #00377f;
            --kominfo-blue-mid: #0b5cb8;
            --kominfo-cyan: #009df7;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 20px;
            position: relative;
            overflow-x: hidden;
            background: var(--kominfo-blue);
            color: var(--text);
        }

        button, input {
            font: inherit;
        }

        button, a {
            -webkit-tap-highlight-color: transparent;
        }

        /* =====================================
           BACKGROUND KOMINFO
        ===================================== */

        .background-image {
            position: fixed;
            inset: -8px;
            z-index: -3;
            background-color: var(--kominfo-blue);
            background-image: linear-gradient(160deg, var(--kominfo-blue) 0%, var(--kominfo-blue-mid) 55%, var(--kominfo-cyan) 100%);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            filter: blur(2px);
            transform: scale(1.025);
        }

        @if ($kominfoBg)
        .background-image {
            background-image:
                linear-gradient(160deg, rgba(0, 55, 127, .55) 0%, rgba(11, 92, 184, .38) 55%, rgba(0, 157, 247, .25) 100%),
                url('{{ $kominfoBg }}');
        }
        @endif

        .background-overlay {
            position: fixed;
            inset: 0;
            z-index: -2;
            background: rgba(0, 55, 127, 0.30);
        }

        /* Dekorasi sudut sederhana */

        .decoration {
            position: fixed;
            z-index: -1;
            width: 240px;
            height: 240px;
            pointer-events: none;
            opacity: .55;
        }

        .decoration-top {
            top: -150px;
            left: -100px;
            border-radius: 50%;
            border: 35px solid rgba(255, 255, 255, .10);
        }

        .decoration-bottom {
            right: -130px;
            bottom: -160px;
            border-radius: 50%;
            border: 35px solid rgba(255, 255, 255, .08);
        }

        /* =====================================
           MAIN CONTAINER
        ===================================== */

        .login-wrapper {
            width: 100%;
            max-width: 860px;
            margin: auto;
            animation: fadeUp .7s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =====================================
           BRAND HEADER
        ===================================== */

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-logo {
            width: 116px;
            height: 116px;
            object-fit: contain;
            padding: 8px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 5px 20px rgba(26, 60, 116, .13);
            margin-bottom: 12px;
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, .82);
            font-size: 14px;
            font-weight: 500;
            margin-top: 5px;
        }

        /* =====================================
           LOGIN CARD
        ===================================== */

        .login-card {
            display: grid;
            grid-template-columns: .95fr 1.15fr;
            background: rgba(255, 255, 255, .97);
            border: 1px solid rgba(255, 255, 255, .9);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(0, 25, 60, .28);
        }

        /* =====================================
           LEFT WELCOME PANEL
        ===================================== */

        .welcome-panel {
            position: relative;
            overflow: hidden;
            background: linear-gradient(
                145deg,
                #edf3ff 0%,
                #dce9ff 100%
            );
            padding: 48px 38px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 440px;
        }

        .welcome-panel::before {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            background: rgba(77, 132, 218, .08);
            border-radius: 50%;
            bottom: -145px;
            left: -90px;
        }

        .welcome-panel::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            border: 1px solid rgba(32, 85, 178, .10);
            border-radius: 50%;
            right: -85px;
            top: -75px;
        }

        /* Samar-samar logo, meniru logo di kepala halaman. */
        .welcome-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            height: 220px;
            background: url("{{ asset('images/logo-silapin.png') }}") no-repeat center / contain;
            opacity: .07;
            pointer-events: none;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-panel h2 {
            color: #173f7e;
            font-size: 29px;
            line-height: 1.3;
            font-weight: 750;
            margin-bottom: 12px;
        }

        .welcome-panel p {
            color: #536783;
            font-size: 14px;
            line-height: 1.8;
            max-width: 290px;
        }

        .welcome-divider {
            height: 3px;
            width: 105px;
            border-radius: 10px;
            background: var(--primary);
            margin: 27px 0 30px;
        }

        /* Ilustrasi */

        .security-illustration {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 145px;
            color: #3974cb;
        }

        .security-illustration svg {
            width: 145px;
            height: 145px;
        }

        .welcome-caption {
            font-size: 11px;
            color: #8496b0;
            text-align: center;
            margin-top: 12px;
        }

        /* =====================================
           RIGHT FORM PANEL
        ===================================== */

        .form-panel {
            background: rgba(255, 255, 255, .98);
            padding: 42px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-heading {
            margin-bottom: 28px;
        }

        .form-heading h2 {
            color: #203653;
            font-size: 23px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-heading p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        /* =====================================
           INPUT
        ===================================== */

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #344965;
            margin-bottom: 9px;
        }

        .input-wrapper {
            display: flex;
            align-items: center;
            height: 52px;
            padding: 0 15px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .input-wrapper:focus-within {
            border-color: #3979dc;
            box-shadow: 0 0 0 3px rgba(57, 121, 220, .10);
        }

        .input-icon {
            font-size: 17px;
            color: #71839c;
            margin-right: 13px;
            flex-shrink: 0;
        }

        .input-wrapper input {
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            background: transparent;
            color: #263b59;
            font-size: 14px;
        }

        .input-wrapper input::placeholder {
            color: #a1adbd;
        }

        .password-toggle {
            border: none;
            background: transparent;
            color: #8190a5;
            cursor: pointer;
            font-size: 17px;
            padding: 5px;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        /* =====================================
           OPTIONS
        ===================================== */

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin: 3px 0 25px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            cursor: pointer;
        }

        .remember-label input {
            width: 15px;
            height: 15px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 13px;
            font-weight: 600;
            color: #2865cf;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: #153b79;
            text-decoration: underline;
        }

        /* =====================================
           BUTTON
        ===================================== */

        .btn-login {
            width: 100%;
            height: 51px;
            border: none;
            border-radius: 10px;
            background: #2055b2;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all .2s ease;
        }

        .btn-login:hover {
            background: #17458f;
            box-shadow: 0 6px 15px rgba(32, 85, 178, .17);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: scale(.99);
        }

        /* =====================================
           DIVIDER
        ===================================== */

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #94a0b1;
            font-size: 12px;
            margin: 22px 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #e4e9f0;
        }

        /* =====================================
           PASSKEY
        ===================================== */

        .btn-passkey {
            width: 100%;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background: white;
            color: #2055b2;
            border: 1px solid #3979e8;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .btn-passkey:hover {
            background: #f3f7ff;
        }

        .btn-passkey:disabled {
            opacity: .7;
            cursor: wait;
        }

        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* =====================================
           VALIDATION
        ===================================== */

        .alert-error,
        .alert-status {
            padding: 12px 14px;
            border-radius: 9px;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .alert-error {
            border: 1px solid #f4caca;
            background: #fff3f3;
            color: #b42318;
        }

        .alert-status {
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .alert-error ul {
            padding-left: 18px;
        }

        .is-hidden {
            display: none !important;
        }

        /* =====================================
           FOOTER
        ===================================== */

        .footer {
            text-align: center;
            margin-top: 27px;
            color: rgba(255, 255, 255, .78);
            font-size: 12px;
            font-weight: 500;
        }

        .footer-link {
            margin-top: 7px;
        }

        .footer-link a {
            color: rgba(255, 255, 255, .85);
            text-decoration: none;
        }

        .footer-link a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* =====================================
           RESPONSIVE TABLET
        ===================================== */

        @media (max-width: 768px) {
            .login-wrapper {
                max-width: 550px;
            }

            .login-card {
                grid-template-columns: 1fr;
            }

            .welcome-panel {
                min-height: auto;
                padding: 27px 30px;
                text-align: center;
            }

            .welcome-panel h2 {
                font-size: 24px;
            }

            .welcome-panel p {
                margin: auto;
                max-width: 380px;
            }

            .welcome-divider,
            .welcome-watermark,
            .security-illustration,
            .welcome-caption {
                display: none;
            }

            .form-panel {
                padding: 32px;
            }
        }

        /* =====================================
           RESPONSIVE MOBILE
        ===================================== */

        @media (max-width: 480px) {
            body {
                padding: 22px 14px;
            }

            .brand-header {
                margin-bottom: 20px;
            }

            .brand-logo {
                width: 92px;
                height: 92px;
            }

            .brand-subtitle {
                font-size: 13px;
            }

            .login-card {
                border-radius: 15px;
            }

            .welcome-panel {
                padding: 23px 20px;
            }

            .welcome-panel h2 {
                font-size: 22px;
                margin-bottom: 7px;
            }

            .welcome-panel p {
                font-size: 12px;
            }

            .form-panel {
                padding: 27px 21px;
            }

            .form-heading h2 {
                font-size: 21px;
            }

            .form-heading {
                margin-bottom: 24px;
            }

            .form-options {
                align-items: center;
            }

            .remember-label,
            .forgot-link {
                font-size: 12px;
            }

            .footer {
                font-size: 11px;
                line-height: 1.6;
                margin-top: 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

    <!-- BACKGROUND -->
    <div class="background-image"></div>
    <div class="background-overlay"></div>
    <div class="decoration decoration-top"></div>
    <div class="decoration decoration-bottom"></div>

    <main class="login-wrapper">

        <!-- BRAND -->
        <header class="brand-header">

            <img
                src="{{ asset('images/logo-silapin.png') }}"
                alt="Logo SILAPIN"
                class="brand-logo"
            >

            <p class="brand-subtitle">Login Super Admin</p>

        </header>

        <!-- LOGIN CARD -->
        <section class="login-card">

            <!-- LEFT PANEL -->
            <div class="welcome-panel">

                <div class="welcome-watermark"></div>

                <div class="welcome-content">

                    <h2>Selamat Datang</h2>

                    <p>
                        Masuk ke panel super admin SILAPIN
                        untuk melanjutkan aktivitas Anda.
                    </p>

                    <div class="welcome-divider"></div>

                    <!-- SECURITY ILLUSTRATION -->
                    <div class="security-illustration">

                        <svg viewBox="0 0 150 150"
                             fill="none"
                             xmlns="http://www.w3.org/2000/svg">

                            <rect x="15" y="15" width="120" height="92"
                                  rx="8" stroke="#3974cb"
                                  stroke-width="5"/>

                            <path d="M52 128H98"
                                  stroke="#3974cb"
                                  stroke-width="5"
                                  stroke-linecap="round"/>

                            <path d="M75 107V128"
                                  stroke="#3974cb"
                                  stroke-width="5"
                                  stroke-linecap="round"/>

                            <path d="M75 30L105 42V62C105 82 91 94 75 101C59 94 45 82 45 62V42L75 30Z"
                                  fill="#3479d7"/>

                            <path d="M61 64L71 74L91 53"
                                  stroke="white"
                                  stroke-width="7"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>

                            <circle cx="75" cy="118" r="3"
                                    fill="#3974cb"/>

                        </svg>

                    </div>

                    <div class="welcome-caption">
                        Sistem Informasi Layanan Pemerintahan
                    </div>

                </div>

            </div>

            <!-- RIGHT FORM -->
            <div class="form-panel">

                <div class="form-heading">
                    <h2>Masuk ke Akun</h2>
                    <p>
                        Silakan masukkan email dan password
                        untuk mengakses panel super admin.
                    </p>
                </div>

                @if (session('status'))
                    <div class="alert-status">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- ERROR VALIDATION -->
                @if ($errors->any())
                    <div class="alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- LOGIN FORM -->
                <form action="{{ route('login.superadmin.post') }}"
                      method="POST">

                    @csrf

                    <!-- EMAIL -->
                    <div class="form-group">

                        <label class="form-label" for="email">
                            Email
                        </label>

                        <div class="input-wrapper">

                            <i class="bi bi-envelope input-icon"></i>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Masukkan email"
                                value="{{ old('email') }}"
                                autocomplete="username"
                                required
                                autofocus
                            >

                        </div>

                    </div>

                    <!-- PASSWORD -->
                    <div class="form-group">

                        <label class="form-label" for="password">
                            Password
                        </label>

                        <div class="input-wrapper">

                            <i class="bi bi-lock input-icon"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                                data-sec-allow-type
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>

                        </div>

                    </div>

                    <!-- REMEMBER & FORGOT -->
                    <div class="form-options">

                        <label class="remember-label">

                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >

                            <span>Ingat saya</span>

                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="forgot-link">
                                Lupa password?
                            </a>
                        @endif

                    </div>

                    <!-- LOGIN BUTTON -->
                    <button type="submit" class="btn-login">

                        <i class="bi bi-box-arrow-in-right"></i>

                        <span>Masuk</span>

                    </button>

                    <!-- DIVIDER -->
                    <div class="divider">
                        <span>atau</span>
                    </div>

                    <!-- PASSKEY ALERT -->
                    <div id="passkeyAlert" class="alert-error is-hidden"></div>

                    <!-- PASSKEY BUTTON -->
                    <button
                        type="button"
                        class="btn-passkey"
                        id="btnPasskey"
                        data-role="super_admin"
                    >

                        <i class="bi bi-fingerprint"></i>

                        <span>Masuk dengan Passkey</span>

                    </button>

                </form>

            </div>

        </section>

        <!-- FOOTER -->
        <footer class="footer">
            &copy; {{ date('Y') }} SILAPIN.
            Semua hak dilindungi.
            <div class="footer-link">
                <a href="{{ route('website') }}">Kembali ke Beranda</a>
            </div>
        </footer>

    </main>

    <!-- JAVASCRIPT -->
    <script>

        // Show / Hide Password
        const togglePassword =
            document.getElementById('togglePassword');

        const passwordInput =
            document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {

                const isPassword =
                    passwordInput.type === 'password';

                passwordInput.type =
                    isPassword ? 'text' : 'password';

                this.innerHTML = isPassword
                    ? '<i class="bi bi-eye"></i>'
                    : '<i class="bi bi-eye-slash"></i>';

                this.setAttribute(
                    'aria-pressed',
                    isPassword ? 'true' : 'false'
                );

                this.setAttribute(
                    'aria-label',
                    isPassword
                        ? 'Sembunyikan password'
                        : 'Tampilkan password'
                );

            });
        }

        // Fokus otomatis ke email jika kosong
        window.addEventListener('DOMContentLoaded', function () {

            const emailInput =
                document.getElementById('email');

            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }

        });

        // Login dengan Passkey
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('btnPasskey');
            const alertBox = document.getElementById('passkeyAlert');

            if (!btn || !alertBox) return;

            const role = btn.dataset.role || 'super_admin';
            const passkeyRoutes = {
                options: '{{ route('passkey.login-options', absolute: false) }}',
                submit: '{{ route('passkey.login', absolute: false) }}',
            };

            function showPasskeyError(message) {
                alertBox.textContent = message;
                alertBox.classList.remove('is-hidden');
            }

            function clearPasskeyError() {
                alertBox.textContent = '';
                alertBox.classList.add('is-hidden');
            }

            function ipHostHint() {
                const host = location.hostname;
                if (!/^\d{1,3}(\.\d{1,3}){3}$/.test(host)) return null;
                if (/^127\./.test(host)) {
                    return 'Passkey tidak mendukung alamat IP. Buka halaman ini lewat http://localhost' +
                        (location.port ? ':' + location.port : '') + ' (bukan ' + host + ').';
                }
                const dashed = host.split('.').join('-');
                return 'Passkey tidak mendukung alamat IP. Buka halaman ini lewat https://' + dashed +
                    '.nip.io:8443 (domain pengganti untuk IP ' + host + ').';
            }

            function passkeyErrorMessage(error) {
                if (typeof window.passkeyErrorMessage === 'function') {
                    return window.passkeyErrorMessage(error);
                }
                return error && error.message ? error.message : 'Terjadi kesalahan. Silakan coba lagi.';
            }

            btn.addEventListener('click', async function () {
                clearPasskeyError();

                const hint = ipHostHint();
                if (hint) {
                    showPasskeyError(hint);
                    return;
                }

                if (!window.Passkeys) {
                    showPasskeyError('Fitur passkey belum siap. Muat ulang halaman lalu coba lagi.');
                    return;
                }

                if (!window.Passkeys.isSupported()) {
                    showPasskeyError('Browser atau perangkat ini tidak mendukung passkey.');
                    return;
                }

                window.Passkeys.configure({
                    fetch: { headers: { 'X-Passkey-Role': role } },
                });

                const remember = document.getElementById('remember') ? document.getElementById('remember').checked : false;
                const original = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat spin" style="margin-right:8px;"></i>Memproses...';

                try {
                    const result = await window.Passkeys.verify({ remember, routes: passkeyRoutes });

                    if (result && result.redirect) {
                        window.location.href = result.redirect;
                        return;
                    }

                    showPasskeyError('Login passkey berhasil, tetapi halaman tujuan tidak ditemukan.');
                } catch (error) {
                    showPasskeyError(passkeyErrorMessage(error));
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            });
        });

    </script>

</body>
</html>
