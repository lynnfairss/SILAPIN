<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>

<div class="row g-0" style="min-height:100vh;">

    {{-- LEFT PANEL --}}
    <div class="col-lg-5 login-left text-white">
        <div class="left-content">
            <div class="brand-icon">
                <i class="fas fa-boxes-stacked fa-3x"></i>
            </div>
            <h2 class="fw-bold mb-2">SILAPIN</h2>
            <p class="opacity-75 mb-0">Sistem Informasi Peminjaman Inventaris</p>

            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Kelola data inventaris</li>
                <li><i class="fas fa-check-circle"></i> Proses peminjaman cepat</li>
                <li><i class="fas fa-check-circle"></i> Pantau status实时</li>
                <li><i class="fas fa-check-circle"></i> Laporan transparan</li>
            </ul>

            <div class="mt-5 pt-3 border-top border-white border-opacity-25">
                <small class="opacity-50">&copy; 2026 SILAPIN</small>
            </div>
        </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="col-lg-7 login-right">
        <div class="login-card">
            <div class="card">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4 d-lg-none">
                        <div class="brand-icon mx-auto mb-3" style="width:70px;height:70px;background:linear-gradient(135deg,#0d6efd,#084298);border-radius:18px;">
                            <i class="fas fa-boxes-stacked fa-2x text-white"></i>
                        </div>
                    </div>

                    <h4 class="fw-bold mb-1">Selamat Datang</h4>
                    <p class="text-muted mb-4">Masuk ke panel admin SILAPIN</p>

                    <x-auth-session-status class="mb-3" :status="session('status')" />

                    @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary" style="font-size:.875rem;">Email</label>
                            <div class="input-group position-relative">
                                <i class="fas fa-envelope input-icon"></i>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                       class="form-control" placeholder="Masukkan email Anda">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-secondary" style="font-size:.875rem;">Password</label>
                            <div class="input-group position-relative">
                                <i class="fas fa-lock input-icon"></i>
                                <input id="password" type="password" name="password" required
                                       class="form-control pe-5" placeholder="Masukkan password Anda">
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" for="remember" style="font-size:.875rem;">
                                    Ingat saya
                                </label>
                            </div>
                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size:.875rem;color:#0d6efd;">
                                Lupa password?
                            </a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('website') }}" class="back-link">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke beranda
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
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
