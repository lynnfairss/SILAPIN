<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Dua Faktor - SILAPIN</title>
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

        .alert-custom {
            border-radius: 10px;
            font-size: .85rem;
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

        .otp-input {
            letter-spacing: 8px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="login-container">

    <div class="login-brand">
        <img class="brand-logo" src="{{ asset('images/logosilapin.png') }}" alt="Logo SILAPIN">
        <h1>SILAPIN</h1>
        <p>Verifikasi Dua Faktor</p>
    </div>

    <div class="login-card">
        <div class="card-top"></div>
        <div class="card-body-custom">

            <h4>Kode Verifikasi</h4>
            <p class="subtitle">Masukkan kode 6 digit dari aplikasi Google Authenticator</p>

            @if($expired)
            <div class="alert alert-warning alert-custom d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Sesi verifikasi sudah kedaluwarsa. Silakan masuk kembali.
            </div>
            <a href="{{ route('login.admin') }}" class="btn btn-login w-100">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
            </a>
            @else

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

            <form method="POST" action="{{ route('login.2fa.post') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-medium text-secondary" style="font-size:.85rem;">Kode OTP</label>
                    <input type="text" name="code" class="form-control otp-input" inputmode="numeric"
                           maxlength="6" placeholder="••••••" required autofocus autocomplete="one-time-code">
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="fas fa-check-circle me-2"></i>Verifikasi
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login.admin') }}" class="text-decoration-none" style="font-size:.8rem; color:#888;">
                    <i class="fas fa-arrow-left me-1"></i>Batalkan dan kembali
                </a>
            </div>

            @endif

        </div>
    </div>

</div>

</body>
</html>
