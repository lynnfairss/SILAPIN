<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/website.css') }}" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow" id="mainNav" style="background: rgba(33,37,41,.9); backdrop-filter: blur(10px);">
    <div class="container">
        <button class="navbar-toggler border-0 me-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand fw-bold" href="#home">
            <i class="fas fa-boxes-stacked me-2"></i>SILAPIN
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#home"><i class="fas fa-home me-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#inventaris"><i class="fas fa-boxes-stacked me-1"></i>Daftar Inventaris</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#layanan"><i class="fas fa-hand-holding-heart me-1"></i>Layanan Peminjaman</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#cek-status"><i class="fas fa-search me-1"></i>Cek Status</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#kontak"><i class="fas fa-envelope me-1"></i>Kontak</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                @else
                    <a href="{{ route('login.admin') }}" class="btn btn-light btn-sm fw-semibold">
                        <i class="fas fa-sign-in-alt me-1"></i>Login Admin
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<section id="home" class="hero text-white text-center" style="margin-top: 56px;">
    <div class="container py-5 position-relative" style="z-index:1;">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3 fade-up"><i class="fas fa-boxes-stacked me-2"></i>SILAPIN</h1>
                <p class="lead mb-3 fade-up" style="transition-delay:.1s">Sistem Informasi Peminjaman Inventaris</p>
                <hr class="border-light mx-auto mb-4" style="width: 80px;">
                <p class="mb-4 fade-up" style="transition-delay:.2s">Kelola peminjaman barang inventaris dengan mudah, cepat, dan transparan.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap fade-up" style="transition-delay:.3s">
                    <a href="#inventaris" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-boxes-stacked me-2"></i>Lihat Daftar Barang
                    </a>
                    <a href="{{ route('peminjam.form') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-hand-holding-heart me-2"></i>Ajukan Peminjaman
                    </a>
                </div>
            </div>
        </div>
        <div class="row mt-5 pt-4 justify-content-center fade-up" style="transition-delay:.4s">
            <div class="col-auto">
                <div class="stat-card bg-white bg-opacity-10 text-white rounded-3 px-4 py-3 text-center" style="border-left-color:#4dabf7;">
                    <h3 class="fw-bold mb-0" id="counterBarang">0</h3>
                    <small class="opacity-75">Barang Tersedia</small>
                </div>
            </div>
            <div class="col-auto">
                <div class="stat-card bg-white bg-opacity-10 text-white rounded-3 px-4 py-3 text-center" style="border-left-color:#69db7c;">
                    <h3 class="fw-bold mb-0" id="counterKategori">0</h3>
                    <small class="opacity-75">Kategori</small>
                </div>
            </div>
            <div class="col-auto">
                <div class="stat-card bg-white bg-opacity-10 text-white rounded-3 px-4 py-3 text-center" style="border-left-color:#ffd43b;">
                    <h3 class="fw-bold mb-0" id="counterStok">0</h3>
                    <small class="opacity-75">Total Stok</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="inventaris" class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center fw-bold mb-2 section-title fade-up">Daftar Inventaris</h3>
        <p class="text-center text-muted mb-4 fade-up">Daftar barang yang tersedia untuk dipinjam</p>

        <div class="card shadow-sm fade-up">
            <div class="card-body p-0">
                <div class="table-responsive-custom">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th class="text-center">Stok</th>
                                <th>Kondisi</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventaris as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td><code>{{ $item->kode_barang }}</code></td>
                                <td class="fw-semibold">{{ $item->nama_barang }}</td>
                                <td><span class="badge bg-secondary badge-kondisi">{{ $item->kategori->nama_kategori ?? '-' }}</span></td>
                                <td class="text-center">
                                    @if($item->stok > 0)
                                        <span class="badge bg-success badge-kondisi">{{ $item->stok }}</span>
                                    @else
                                        <span class="badge bg-danger badge-kondisi">Habis</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->kondisi == 'Baik')
                                        <span class="badge bg-success badge-kondisi"><i class="fas fa-check-circle me-1"></i>{{ ucfirst($item->kondisi) }}</span>
                                    @elseif($item->kondisi == 'Rusak Ringan')
                                        <span class="badge bg-warning text-dark badge-kondisi"><i class="fas fa-exclamation-triangle me-1"></i>{{ $item->kondisi }}</span>
                                    @else
                                        <span class="badge bg-danger badge-kondisi"><i class="fas fa-times-circle me-1"></i>{{ $item->kondisi }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->foto)
                                        <img src="{{ asset('storage/'.$item->foto) }}" width="60" height="60" class="rounded object-fit-cover" alt="{{ $item->nama_barang }}">
                                    @else
                                        <span class="text-muted"><i class="fas fa-image"></i></span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Belum ada data barang.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="layanan" class="py-5">
    <div class="container">
        <h3 class="text-center fw-bold mb-2 section-title fade-up">Layanan Peminjaman</h3>
        <p class="text-center text-muted mb-4 fade-up">Langkah mudah untuk mengajukan peminjaman barang inventaris</p>

        <div class="row g-4">
            <div class="col-md-4 fade-up">
                <div class="card h-100 shadow-sm text-center step-card">
                    <div class="card-body py-4">
                        <div class="step-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:75px;height:75px;">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">1. Ajukan Permohonan</h5>
                        <p class="text-muted mb-0">Isi formulir permohonan peminjaman dengan data yang valid dan lengkap.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-up" style="transition-delay:.15s">
                <div class="card h-100 shadow-sm text-center step-card">
                    <div class="card-body py-4">
                        <div class="step-icon bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:75px;height:75px;">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">2. Tunggu Persetujuan</h5>
                        <p class="text-muted mb-0">Admin akan memproses dan menyetujui permohonan Anda.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-up" style="transition-delay:.3s">
                <div class="card h-100 shadow-sm text-center step-card">
                    <div class="card-body py-4">
                        <div class="step-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:75px;height:75px;">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <h5 class="fw-bold">3. Ambil Barang</h5>
                        <p class="text-muted mb-0">Setelah disetujui, silakan ambil barang sesuai jadwal yang ditentukan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="cek-status" class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center fw-bold mb-2 section-title fade-up">Cek Status Peminjaman</h3>
        <p class="text-center text-muted mb-4 fade-up">Masukkan nomor permohonan untuk mengecek status peminjaman Anda</p>

        <div class="row justify-content-center fade-up">
            <div class="col-md-6">
                <div class="card shadow-sm cek-status-card">
                    <div class="card-body">
                        <form id="formCekStatus">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" id="inputStatus" class="form-control border-start-0" placeholder="Masukkan Nomor Permohonan..." autocomplete="off">
                                <button class="btn btn-primary px-4 fw-semibold" type="submit" id="btnCek">
                                    <span class="btn-text">Cek</span>
                                    <span class="spinner-border spinner-border-sm search-loading ms-1" role="status"></span>
                                </button>
                            </div>
                        </form>
                        <div id="hasilStatus" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="kontak" class="py-5">
    <div class="container">
        <h3 class="text-center fw-bold mb-2 section-title fade-up">Kontak</h3>
        <p class="text-center text-muted mb-4 fade-up">Hubungi kami untuk informasi lebih lanjut</p>

        <div class="row justify-content-center fade-up">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-start mb-3 contact-item">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:48px;height:48px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Alamat</h6>
                                    <p class="text-muted mb-0">Jl. Contoh Alamat No. 123, Kota</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start mb-3 contact-item">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:48px;height:48px;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Telepon</h6>
                                    <p class="text-muted mb-0">(021) 1234-5678</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start contact-item">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:48px;height:48px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Email</h6>
                                    <p class="text-muted mb-0">silapin@example.com</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="text-white text-center py-4">
    <div class="container">
        <div class="d-flex justify-content-center mb-3">
            <a href="#" class="text-white me-3 fs-5"><i class="fab fa-facebook"></i></a>
            <a href="#" class="text-white me-3 fs-5"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-white fs-5"><i class="fab fa-whatsapp"></i></a>
        </div>
        <p class="mb-0">&copy; 2026 SILAPIN - Sistem Informasi Peminjaman Inventaris</p>
    </div>
</footer>

<button id="backToTop" class="btn btn-primary shadow">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Active nav on scroll
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    function activateNav() {
        let scrollY = window.scrollY + 100;
        sections.forEach(section => {
            const top = section.offsetTop - 80;
            const height = section.offsetHeight;
            const id = section.getAttribute('id');
            if (scrollY >= top && scrollY < top + height) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + id) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    // Navbar background on scroll
    const mainNav = document.getElementById('mainNav');
    function handleNavScroll() {
        if (window.scrollY > 50) {
            mainNav.classList.add('navbar-scrolled');
        } else {
            mainNav.classList.remove('navbar-scrolled');
        }
    }

    // Back to top
    const backToTop = document.getElementById('backToTop');
    function handleBackToTop() {
        if (window.scrollY > 400) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    }
    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Fade-up on scroll
    const fadeUps = document.querySelectorAll('.fade-up');
    function handleFadeUp() {
        fadeUps.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight - 60) {
                el.classList.add('visible');
            }
        });
    }

    // Counter animation
    let counted = false;
    function animateCounter(id, target) {
        const el = document.getElementById(id);
        let current = 0;
        const step = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current;
        }, 30);
    }

    function handleCounter() {
        if (counted) return;
        const hero = document.getElementById('home');
        if (!hero) return;
        const rect = hero.getBoundingClientRect();
        if (rect.top < window.innerHeight) {
            counted = true;
            const totalBarang = Number("{{ $inventaris->count() }}");
            const totalKategori = Number("{{ $inventaris->pluck('kategori_id')->unique()->count() }}");
            const totalStok = Number("{{ $inventaris->sum('stok') }}");
            animateCounter('counterBarang', totalBarang);
            animateCounter('counterKategori', totalKategori);
            animateCounter('counterStok', totalStok);
        }
    }

    // Scroll handler
    window.addEventListener('scroll', () => {
        activateNav();
        handleNavScroll();
        handleBackToTop();
        handleFadeUp();
        handleCounter();
    });

    // Close mobile nav on link click
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            const collapse = document.getElementById('navbarNav');
            const bsCollapse = bootstrap.Collapse.getInstance(collapse);
            if (bsCollapse) bsCollapse.hide();
        });
    });

    // Cek status form
    document.getElementById('formCekStatus').addEventListener('submit', function(e) {
        e.preventDefault();
        const val = document.getElementById('inputStatus').value.trim();
        const btn = document.getElementById('btnCek');
        const result = document.getElementById('hasilStatus');

        if (!val) {
            result.style.display = 'block';
            result.innerHTML = '<div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Masukkan nomor permohonan.</div>';
            return;
        }

        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Mencari';
        btn.querySelector('.search-loading').classList.add('active');

        setTimeout(() => {
            btn.disabled = false;
            btn.querySelector('.btn-text').textContent = 'Cek';
            btn.querySelector('.search-loading').classList.remove('active');
            result.style.display = 'block';
            result.innerHTML = '<div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>Data permohonan dengan nomor <strong>' + val + '</strong> tidak ditemukan.</div>';
        }, 1500);
    });

    // Init on load
    window.addEventListener('load', () => {
        handleFadeUp();
        handleCounter();
    });
</script>

</body>

</html>