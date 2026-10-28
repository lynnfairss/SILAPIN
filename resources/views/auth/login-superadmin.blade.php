<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Super Admin - SILAPIN</title>
    @vite('resources/js/passkeys.js')
    <script src="{{ asset('js/security.js') }}?v={{ time() }}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background:
                radial-gradient(700px 340px at 82% 0%, rgba(59,130,246,.22), transparent 60%),
                radial-gradient(600px 320px at 8% 100%, rgba(37,99,235,.18), transparent 62%),
                linear-gradient(160deg, #0B2E6E 0%, #08224F 55%, #061A3F 100%);
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        body::after {
            content: '';
            position: fixed;
            right: 6%;
            top: 12%;
            width: 200px;
            height: 240px;
            background-image: radial-gradient(rgba(147,197,253,.5) 1.5px, transparent 1.6px);
            background-size: 14px 14px;
            opacity: .4;
            pointer-events: none;
            -webkit-mask-image: radial-gradient(closest-side, #000 55%, transparent 100%);
            mask-image: radial-gradient(closest-side, #000 55%, transparent 100%);
        }
        @media (max-width: 767.98px) {
            body::after { display: none; }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-brand {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
        }
        .login-brand .brand-logo {
            display: block;
            width: 96px;
            height: 96px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: #fff;
            padding: 8px;
            box-shadow: 0 18px 44px rgba(3, 15, 40, .4);
        }
        .login-brand h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 4px; }
        .login-brand p { opacity: .5; font-size: .85rem; }

        .login-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
        }

        .card-top {
            height: 5px;
            background: linear-gradient(90deg, #3B82F6, #2563EB);
        }

        .card-body-custom {
            padding: 32px;
        }

        .card-body-custom h4 {
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .card-body-custom .subtitle {
            color: #888;
            font-size: .85rem;
            margin-bottom: 24px;
        }

        .form-control {
            border-radius: 10px;
            padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            font-size: .9rem;
            transition: border .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 .2rem rgba(13,110,253,.12);
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1.5px solid #e0e0e0;
            border-right: 0;
            background: #f8f9fa;
            color: #888;
            font-size: .85rem;
        }
        .input-group .form-control {
            border-left: 0;
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #2563EB;
        }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            font-size: .85rem;
        }

        .btn-login {
            border-radius: 10px;
            padding: 11px;
            font-weight: 600;
            font-size: .95rem;
            background: linear-gradient(135deg, #3B82F6, #2563EB);
            border: none;
            color: #fff;
            transition: all .2s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(37,99,235,.35);
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
        }

        .form-links {
            font-size: .85rem;
        }
        .form-links a {
            color: #2563EB;
            text-decoration: none;
            transition: color .2s;
        }
        .form-links a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 24px;
        }
        .back-home a {
            color: rgba(255,255,255,.4);
            text-decoration: none;
            font-size: .85rem;
            transition: color .2s;
        }
        .back-home a:hover {
            color: #fff;
        }

        .alert-custom {
            border-radius: 10px;
            font-size: .85rem;
        }
    </style>
</head>
<body>

<div class="login-container">

    <div class="login-brand">
        <img class="brand-logo" src="{{ asset('images/logosilapin.png') }}" alt="Logo SILAPIN">
        <h1>SILAPIN</h1>
        <p>Login Super Admin</p>
    </div>

    <div class="login-card">
        <div class="card-top"></div>
        <div class="card-body-custom">

            <h4>Selamat Datang</h4>
            <p class="subtitle">Masuk ke panel super admin SILAPIN</p>

            @if(session('status'))
            <div class="alert alert-info alert-custom d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>{{ session('status') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-custom d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login.superadmin.post') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-medium text-secondary" style="font-size:.85rem;">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                               placeholder="Masukkan email" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium text-secondary" style="font-size:.85rem;">Password</label>
                    <div class="input-group position-relative">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="inputPassword" class="form-control"
                               placeholder="Masukkan password" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 form-links">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label text-secondary" for="remember" style="font-size:.85rem;">
                            Ingat saya
                        </label>
                    </div>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>

                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1">
                    <span class="px-3 text-muted" style="font-size:.75rem;">atau</span>
                    <hr class="flex-grow-1">
                </div>

                <div id="passkeyAlert" class="alert alert-danger d-none alert-custom mb-3"></div>

                <button type="button" id="btnPasskey" data-role="super_admin" class="btn w-100"
                        style="border-radius:10px; padding:11px; font-weight:600; font-size:.95rem;
                               border:1.5px solid #2563EB; color:#2563EB; background:#fff; transition:all .2s;">
                    <i class="fas fa-fingerprint me-2"></i>Masuk dengan Passkey
                </button>
            </form>

        </div>
    </div>

    <div class="back-home">
        <a href="{{ route('website') }}"><i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda</a>
    </div>

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('inputPassword');
        const icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btnPasskey');
        const alertBox = document.getElementById('passkeyAlert');
        const role = btn.dataset.role || 'admin';
        const passkeyRoutes = {
            options: '{{ route('passkey.login-options', absolute: false) }}',
            submit: '{{ route('passkey.login', absolute: false) }}',
        };

        function showPasskeyError(message) {
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        }

        function clearPasskeyError() {
            alertBox.textContent = '';
            alertBox.classList.add('d-none');
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
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

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
