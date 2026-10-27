{{--
    Navbar publik SILAPIN.
    Pemakaian:
      @include('partials.public-navbar', ['active' => 'home', 'transparent' => true])
    Variabel:
      $active      : home | inventaris | layanan | status | kontak | form (string, opsional)
      $transparent : true untuk landing (transparan di atas hero)
--}}
@php
    $spActive = $active ?? '';
    $spTransparent = !empty($transparent);
@endphp

<nav class="navbar navbar-expand-lg sp-navbar {{ $spTransparent ? 'sp-navbar--transparent' : 'sp-navbar--solid' }}" id="spNavbar">
    <div class="container">
        <a class="navbar-brand sp-brand" href="{{ route('website') }}">
            <span class="sp-brand__mark"><i class="fas fa-cubes"></i></span>
            <span class="sp-brand__text">SILAPIN</span>
        </a>

        <button class="navbar-toggler sp-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#spNav" aria-controls="spNav" aria-expanded="false" aria-label="Buka menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="spNav">
            <ul class="navbar-nav sp-nav mx-lg-auto">
                <li class="nav-item">
                    <a class="nav-link sp-nav-link {{ $spActive === 'home' ? 'active' : '' }}"
                       href="{{ route('website') }}#home">
                        <i class="fas fa-house"></i><span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sp-nav-link {{ $spActive === 'inventaris' ? 'active' : '' }}"
                       href="{{ route('website') }}#inventaris">
                        <i class="fas fa-boxes-stacked"></i><span>Daftar Inventaris</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sp-nav-link {{ in_array($spActive, ['layanan', 'form']) ? 'active' : '' }}"
                       href="{{ route('website') }}#layanan">
                        <i class="fas fa-hand-holding-heart"></i><span>Layanan Peminjaman</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sp-nav-link {{ $spActive === 'status' ? 'active' : '' }}"
                       href="{{ route('peminjam.cek-status') }}">
                        <i class="fas fa-magnifying-glass"></i><span>Cek Status</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link sp-nav-link {{ $spActive === 'kontak' ? 'active' : '' }}"
                       href="{{ route('website') }}#kontak">
                        <i class="fas fa-envelope"></i><span>Kontak</span>
                    </a>
                </li>
            </ul>

            <div class="sp-nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="sp-btn-login">
                        <i class="fas fa-gauge-high"></i>Dashboard
                    </a>
                @else
                    <a href="{{ route('login.admin') }}" class="sp-btn-login">
                        <i class="fas fa-user"></i>Login Admin
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    // Tutup menu mobile setelah salah satu link diklik.
    document.addEventListener('DOMContentLoaded', function () {
        var nav = document.getElementById('spNav');
        if (!nav) return;
        nav.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.bootstrap && window.bootstrap.Collapse) {
                    var instance = window.bootstrap.Collapse.getInstance(nav);
                    if (instance) instance.hide();
                }
            });
        });
    });
</script>
