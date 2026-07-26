<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Super Admin - SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
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
        .login-brand .brand-icon {
            width: 70px; height: 70px;
            background: linear-gradient(135deg, rgba(255,255,255,.15), rgba(255,255,255,.05));
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 30px;
            color: #fff;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.1);
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
            background: linear-gradient(90deg, #0d6efd, #6610f2, #d63384);
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
            border-color: #0d6efd;
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
            border-color: #0d6efd;
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
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            color: #fff;
            transition: all .2s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(13,110,253,.35);
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
        }

        .form-links {
            font-size: .85rem;
        }
        .form-links a {
            color: #0d6efd;
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
        <div class="brand-icon">
            <i class="fas fa-user-lock"></i>
        </div>
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
</script>

</body>
</html>
