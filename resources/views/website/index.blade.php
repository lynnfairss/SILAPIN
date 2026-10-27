<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SILAPIN — Sistem Informasi Peminjaman Inventaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/silapin-theme.css') }}?v={{ filemtime(public_path('css/silapin-theme.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/website.css') }}?v={{ filemtime(public_path('css/website.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/splash.css') }}?v={{ time() }}" rel="stylesheet">
    <script src="{{ asset('js/security.js') }}?v={{ time() }}"></script>
    <script>
        (function () {
            try {
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    document.documentElement.classList.add('silapin-splash-skip');
                    return;
                }
                document.documentElement.classList.add('silapin-splash-lock');
            } catch (e) {}
        })();
    </script>
    <noscript>
        <style>
            #silapinSplash { display: none !important; }
            html.silapin-splash-lock { overflow: auto !important; }
        </style>
    </noscript>
</head>

<body>
@include('partials.splash')

@include('partials.public-navbar', ['active' => 'home', 'transparent' => true])

<section id="home" class="sp-hero">
    <span class="sp-hero__dots sp-hero__dots--left" aria-hidden="true"></span>
    <span class="sp-hero__dots sp-hero__dots--right" aria-hidden="true"></span>

    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9 col-xl-8">
                <img class="sp-hero__logo fade-up"
                     src="{{ asset('images/logosilapin.png') }}?v={{ filemtime(public_path('images/logosilapin.png')) }}"
                     alt="Logo SILAPIN">
                <h1 class="sp-hero__title fade-up" style="transition-delay:.1s">
                    Peminjaman Inventaris Lebih Mudah dan Transparan
                </h1>
                <p class="sp-hero__sub fade-up" style="transition-delay:.18s">
                    Temukan barang, ajukan peminjaman, dan pantau status permohonan dalam satu tempat.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap fade-up" style="transition-delay:.26s">
                    <a href="#inventaris" class="sp-btn sp-btn--light">
                        <i class="fas fa-boxes-stacked"></i>Lihat Daftar Barang
                    </a>
                    <a href="{{ route('peminjam.form') }}" class="sp-btn sp-btn--primary">
                        <i class="fas fa-paper-plane"></i>Ajukan Peminjaman<i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="sp-stats fade-up" style="transition-delay:.34s">
            <div class="sp-stat">
                <div class="sp-stat__icon sp-stat__icon--blue"><i class="fas fa-cube"></i></div>
                <div class="text-start">
                    <div class="sp-stat__value" id="counterBarang">0</div>
                    <div class="sp-stat__label">Barang Tersedia</div>
                </div>
            </div>
            <div class="sp-stat">
                <div class="sp-stat__icon sp-stat__icon--purple"><i class="fas fa-table-cells-large"></i></div>
                <div class="text-start">
                    <div class="sp-stat__value" id="counterKategori">0</div>
                    <div class="sp-stat__label">Kategori</div>
                </div>
            </div>
            <div class="sp-stat">
                <div class="sp-stat__icon sp-stat__icon--teal"><i class="fas fa-layer-group"></i></div>
                <div class="text-start">
                    <div class="sp-stat__value" id="counterStok">0</div>
                    <div class="sp-stat__label">Total Stok</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="inventaris" class="sp-section sp-section--light">
    <div class="container">
        <div class="sp-section-head fade-up">
            <div>
                <h2 class="sp-section-head__title">Inventaris Pilihan</h2>
                <p class="sp-section-head__sub">Berbagai peralatan yang paling sering dipinjam oleh instansi dan masyarakat.</p>
            </div>
            <a href="#inventoryGrid" class="sp-link-more">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="inv-search fade-up">
            <i class="fas fa-search inv-search-icon"></i>
            <input type="text" id="filterInventaris" class="form-control" placeholder="Cari barang, kode, atau kategori..." oninput="filterInventaris(this.value)">
        </div>

        <div class="inv-chips fade-up" id="invFilterChips">
            <span class="inv-chip active" data-kategori="" onclick="filterInvByKategori(this, '')">Semua</span>
            @foreach($kategori as $kat)
            <span class="inv-chip" data-kategori="{{ $kat->nama_kategori }}" onclick="filterInvByKategori(this, '{{ $kat->nama_kategori }}')">{{ $kat->nama_kategori }}</span>
            @endforeach
        </div>

        <div class="inventory-grid fade-up" id="inventoryGrid">
            @forelse($inventaris as $item)
            @php
                $katName = strtolower($item->kategori->nama_kategori ?? '');
                $catIcon = 'fa-tag';
                foreach ([
                    'kamera' => 'fa-camera', 'camera' => 'fa-camera',
                    'laptop' => 'fa-laptop', 'komputer' => 'fa-laptop', 'notebook' => 'fa-laptop',
                    'proyektor' => 'fa-video', 'projector' => 'fa-video',
                    'mikrofon' => 'fa-microphone', 'mic' => 'fa-microphone', 'audio' => 'fa-microphone', 'sound' => 'fa-microphone',
                    'printer' => 'fa-print', 'cetak' => 'fa-print',
                    'jaringan' => 'fa-network-wired', 'router' => 'fa-wifi',
                    'meja' => 'fa-chair', 'kursi' => 'fa-chair',
                    'kendaraan' => 'fa-car', 'mobil' => 'fa-car', 'motor' => 'fa-motorcycle',
                    'tenda' => 'fa-campground',
                ] as $needle => $icon) {
                    if ($katName !== '' && str_contains($katName, $needle)) { $catIcon = $icon; break; }
                }
            @endphp
            <div class="product-card" data-nama="{{ strtolower($item->nama_barang) }}" data-kode="{{ strtolower($item->kode_barang) }}" data-kategori="{{ strtolower($item->kategori->nama_kategori ?? '') }}">
                <div class="img-slider" id="slider_inv_{{ $item->id }}">
                    @php $fotos = $item->fotos; @endphp
                    @if($fotos->count() > 0)
                        @foreach($fotos as $idx => $f)
                        <div class="slider-img {{ $idx === 0 ? 'active' : '' }}" data-img="{{ asset('storage/'.$f->foto) }}">
                            <img src="{{ asset('storage/'.$f->foto) }}" alt="{{ $item->nama_barang }}" onclick="openLightboxInv({{ $item->id }})">
                        </div>
                        @endforeach
                        @if($fotos->count() > 1)
                        <button type="button" class="slider-btn slider-prev" onclick="event.stopPropagation(); slideImgInv({{ $item->id }}, -1)"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="slider-btn slider-next" onclick="event.stopPropagation(); slideImgInv({{ $item->id }}, 1)"><i class="fas fa-chevron-right"></i></button>
                        <div class="slider-dots">
                            @foreach($fotos as $idx => $f)
                            <span class="dot {{ $idx === 0 ? 'active' : '' }}" onclick="event.stopPropagation(); goToSlideInv({{ $item->id }}, {{ $idx }})"></span>
                            @endforeach
                        </div>
                        @endif
                    @elseif($item->foto)
                        <div class="slider-img active" data-img="{{ asset('storage/'.$item->foto) }}">
                            <img src="{{ asset('storage/'.$item->foto) }}" alt="{{ $item->nama_barang }}" onclick="openLightboxInv({{ $item->id }})">
                        </div>
                    @else
                        <div class="slider-img active no-foto">
                            <i class="fas fa-box"></i>
                        </div>
                    @endif

                    <span class="stock-badge {{ $item->stok > 0 ? 'stock-badge--available' : 'stock-badge--empty' }}">
                        {{ $item->stok > 0 ? 'Tersedia' : 'Habis' }}
                    </span>
                </div>

                <div class="product-info">
                    <div class="product-name">{{ $item->nama_barang }}</div>
                    <div class="product-cat">
                        <i class="fas {{ $catIcon }}"></i>
                        <span>{{ $item->kategori->nama_kategori ?? 'Lainnya' }}</span>
                    </div>
                    <div class="product-stok-kondisi">
                        <span><i class="fas fa-boxes-stacked me-1"></i>Stok: {{ $item->stok }}</span>
                        <span class="meta-dot">•</span>
                        <span>
                            @if($item->kondisi == 'Baik')
                                <i class="fas fa-circle-check me-1 text-success"></i>{{ $item->kondisi }}
                            @elseif($item->kondisi == 'Rusak Ringan')
                                <i class="fas fa-triangle-exclamation me-1 text-warning"></i>{{ $item->kondisi }}
                            @else
                                <i class="fas fa-circle-xmark me-1 text-danger"></i>{{ $item->kondisi }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x d-block mb-2"></i>
                Belum ada data barang.
            </div>
            @endforelse
        </div>
    </div>
</section>

<div class="inv-modal-overlay" id="invImageModal" onclick="closeLightboxInv(event)">
    <div class="inv-modal-content">
        <button type="button" class="inv-modal-close" onclick="closeLightboxInv()">&times;</button>
        <button type="button" class="inv-modal-nav inv-modal-prev" onclick="navigateLightboxInv(-1)"><i class="fas fa-chevron-left"></i></button>
        <img id="invModalImg" src="" alt="Foto Barang">
        <button type="button" class="inv-modal-nav inv-modal-next" onclick="navigateLightboxInv(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="inv-modal-caption" id="invModalCaption"></div>
        <div class="inv-modal-dots" id="invModalDots"></div>
    </div>
</div>

<section id="layanan" class="sp-section sp-section--muted">
    <div class="container">
        <div class="sp-section-head fade-up">
            <div>
                <h2 class="sp-section-head__title">Layanan Peminjaman</h2>
                <p class="sp-section-head__sub">Langkah mudah untuk mengajukan peminjaman barang inventaris.</p>
            </div>
            <a href="{{ route('peminjam.form') }}" class="sp-link-more">Mulai Ajukan <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <div class="col-md-4 fade-up">
                <div class="card h-100 sp-step-card">
                    <div class="card-body">
                        <div class="sp-step-icon sp-step-icon--blue"><i class="fas fa-file-alt"></i></div>
                        <h5>1. Ajukan Permohonan</h5>
                        <p class="mb-0">Isi formulir permohonan peminjaman dengan data yang valid dan lengkap.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-up" style="transition-delay:.15s">
                <div class="card h-100 sp-step-card">
                    <div class="card-body">
                        <div class="sp-step-icon sp-step-icon--amber"><i class="fas fa-clock"></i></div>
                        <h5>2. Tunggu Persetujuan</h5>
                        <p class="mb-0">Admin akan memproses dan menyetujui permohonan Anda.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-up" style="transition-delay:.3s">
                <div class="card h-100 sp-step-card">
                    <div class="card-body">
                        <div class="sp-step-icon sp-step-icon--teal"><i class="fas fa-check-circle"></i></div>
                        <h5>3. Ambil Barang</h5>
                        <p class="mb-0">Setelah disetujui, silakan ambil barang sesuai jadwal yang ditentukan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="cek-status" class="sp-section sp-section--light">
    <div class="container">
        <div class="sp-section-head fade-up">
            <div>
                <h2 class="sp-section-head__title">Cek Status Peminjaman</h2>
                <p class="sp-section-head__sub">Masukkan nomor permohonan untuk mengecek status peminjaman Anda.</p>
            </div>
            <a href="{{ route('peminjam.cek-status') }}" class="sp-link-more">Buka Halaman Status <i class="fas fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row justify-content-center fade-up">
            <div class="col-lg-7">
                <div class="card cek-status-card">
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

<section id="kontak" class="sp-section sp-section--muted">
    <div class="container">
        <div class="sp-section-head fade-up">
            <div>
                <h2 class="sp-section-head__title">Kontak</h2>
                <p class="sp-section-head__sub">Hubungi kami untuk informasi lebih lanjut.</p>
            </div>
        </div>

        <div class="row justify-content-center fade-up">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex align-items-start mb-3 contact-item">
                                <div class="sp-contact-icon sp-contact-icon--blue">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Alamat</h6>
                                    <p class="text-muted mb-0">Jl. Contoh Alamat No. 123, Kota</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start mb-3 contact-item">
                                <div class="sp-contact-icon sp-contact-icon--teal">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Telepon</h6>
                                    <p class="text-muted mb-0">(021) 1234-5678</p>
                                </div>
                            </li>
                            <li class="d-flex align-items-start contact-item">
                                <div class="sp-contact-icon sp-contact-icon--amber">
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

@include('partials.public-footer')

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
    const mainNav = document.getElementById('spNavbar');
    function handleNavScroll() {
        if (!mainNav) return;
        mainNav.classList.toggle('sp-navbar--scrolled', window.scrollY > 40);
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
            const collapse = document.getElementById('spNav');
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
        result.style.display = 'none';

        const statusMap = {
            'Menunggu':     { badge: 'warning',  icon: 'fa-clock' },
            'Disetujui':    { badge: 'success',  icon: 'fa-check-circle' },
            'Ditolak':      { badge: 'danger',   icon: 'fa-times-circle' },
            'Dipinjam':     { badge: 'info',     icon: 'fa-box' },
            'Dikembalikan': { badge: 'secondary', icon: 'fa-undo' },
        };

        fetch("{{ route('peminjam.cek-status') }}?nomor=" + encodeURIComponent(val), {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            result.style.display = 'block';

            if (!ok) {
                result.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle me-2"></i>' +
                    (data.error || 'Data permohonan tidak ditemukan.') + '</div>';
                return;
            }

            const st = statusMap[data.status] || { badge: 'secondary', icon: 'fa-question' };

            let barangHtml = '<div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">' +
                '<thead class="table-light"><tr><th style="width:40px">No</th><th>Barang</th><th class="text-center" style="width:70px">Jumlah</th></tr></thead><tbody>';

            if (data.barang && data.barang.length) {
                data.barang.forEach((b, i) => {
                    barangHtml += '<tr><td class="text-center">' + (i + 1) + '</td><td>' +
                        '<div class="fw-semibold">' + b.nama + '</div>' +
                        (b.kode ? '<small class="text-muted">' + b.kode + '</small>' : '') +
                        '</td><td class="text-center fw-semibold">' + b.jumlah + '</td></tr>';
                });
            } else {
                barangHtml += '<tr><td colspan="3" class="text-center text-muted">Tidak ada barang.</td></tr>';
            }
            barangHtml += '</tbody></table></div>';

            let catatanHtml = '';
            if (data.catatan) {
                catatanHtml = '<div class="mb-3">' +
                    '<small class="text-muted d-block mb-1"><i class="fas fa-sticky-note me-1"></i>Catatan Admin</small>' +
                    '<div class="p-2 bg-light rounded small">' + data.catatan + '</div></div>';
            }

            result.innerHTML =
                '<div class="card border-0 shadow-sm status-result">' +
                    '<div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">' +
                        '<div>' +
                            '<h6 class="fw-bold mb-0"><i class="fas fa-file-invoice text-primary me-2"></i>' + data.nomor + '</h6>' +
                            '<small class="text-muted">Diajukan ' + (data.created_at || '-') + '</small>' +
                        '</div>' +
                        '<span class="badge bg-' + st.badge + ' fs-6"><i class="fas ' + st.icon + ' me-1"></i>' + data.status + '</span>' +
                    '</div>' +
                    '<div class="card-body">' +
                        '<div class="row g-3 mb-3">' +
                            '<div class="col-sm-6"><small class="text-muted d-block">Pemohon</small><strong>' + data.nama + '</strong></div>' +
                            '<div class="col-sm-6"><small class="text-muted d-block">Instansi</small><strong>' + data.instansi + '</strong></div>' +
                            '<div class="col-sm-6"><small class="text-muted d-block">Tanggal Pinjam</small><strong>' + data.tanggal_pinjam + '</strong></div>' +
                            '<div class="col-sm-6"><small class="text-muted d-block">Tanggal Kembali</small><strong>' + data.tanggal_kembali + '</strong></div>' +
                        '</div>' +
                        catatanHtml +
                        '<h6 class="fw-bold mb-2"><i class="fas fa-boxes me-2 text-primary"></i>Barang Dipinjam</h6>' +
                        barangHtml +
                    '</div>' +
                    '<div class="card-footer bg-white text-end">' +
                        '<a href="{{ route('peminjam.cek-status') }}?nomor=' + encodeURIComponent(val) + '" class="btn btn-primary btn-sm">' +
                            '<i class="fas fa-history me-1"></i>Lihat Riwayat Lengkap</a>' +
                    '</div>' +
                '</div>';
        })
        .catch(() => {
            result.style.display = 'block';
            result.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-2"></i>Terjadi kesalahan saat memeriksa status. Silakan coba lagi.</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.querySelector('.btn-text').textContent = 'Cek';
            btn.querySelector('.search-loading').classList.remove('active');
        });
    });

    // Init on load
    window.addEventListener('load', () => {
        handleFadeUp();
        handleCounter();
    });

    // ========== INVENTORY IMAGE SLIDER ==========
    function slideImgInv(itemId, dir) {
        const slider = document.getElementById('slider_inv_' + itemId);
        if (!slider) return;
        const imgs = slider.querySelectorAll('.slider-img');
        const dots = slider.querySelectorAll('.dot');
        let active = 0;
        imgs.forEach((img, i) => { if (img.classList.contains('active')) active = i; });
        imgs[active].classList.remove('active');
        if (dots.length) dots[active].classList.remove('active');
        active = (active + dir + imgs.length) % imgs.length;
        imgs[active].classList.add('active');
        if (dots.length) dots[active].classList.add('active');
    }

    function goToSlideInv(itemId, idx) {
        const slider = document.getElementById('slider_inv_' + itemId);
        if (!slider) return;
        const imgs = slider.querySelectorAll('.slider-img');
        const dots = slider.querySelectorAll('.dot');
        imgs.forEach(img => img.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        imgs[idx].classList.add('active');
        dots[idx].classList.add('active');
    }

    // ========== INVENTORY FILTER ==========
    function filterInventaris(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('#inventoryGrid .product-card').forEach(el => {
            const nama = el.dataset.nama || '';
            const kode = el.dataset.kode || '';
            const kategori = el.dataset.kategori || '';
            el.style.display = (nama.includes(q) || kode.includes(q) || kategori.includes(q)) ? '' : 'none';
        });
        if (q) {
            document.querySelectorAll('.inv-chip').forEach(c => c.classList.remove('active'));
        }
    }

    function filterInvByKategori(chip, kategori) {
        document.querySelectorAll('.inv-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        document.getElementById('filterInventaris').value = '';
        document.querySelectorAll('#inventoryGrid .product-card').forEach(el => {
            if (!kategori) { el.style.display = ''; return; }
            el.style.display = el.dataset.kategori === kategori.toLowerCase() ? '' : 'none';
        });
    }

    // ========== INVENTORY LIGHTBOX ==========
    let invLightboxData = [];
    let invLightboxIndex = 0;

    function openLightboxInv(itemId) {
        const slider = document.getElementById('slider_inv_' + itemId);
        if (!slider) return;
        const allImgs = slider.querySelectorAll('.slider-img');
        invLightboxData = [];
        let startIdx = 0;
        allImgs.forEach((img, i) => {
            const src = img.dataset.img;
            if (src) invLightboxData.push(src);
            if (img.classList.contains('active') && src) startIdx = i;
        });
        if (!invLightboxData.length) return;
        invLightboxIndex = startIdx;
        document.getElementById('invImageModal').classList.add('active');
        updateInvLightbox();
    }

    function navigateLightboxInv(dir) {
        if (!invLightboxData.length) return;
        invLightboxIndex = (invLightboxIndex + dir + invLightboxData.length) % invLightboxData.length;
        updateInvLightbox();
    }

    function updateInvLightbox() {
        document.getElementById('invModalImg').src = invLightboxData[invLightboxIndex];
        document.getElementById('invModalCaption').textContent = (invLightboxIndex + 1) + ' / ' + invLightboxData.length;
        document.getElementById('invModalDots').innerHTML = invLightboxData.map((_, i) =>
            '<span class="dot ' + (i === invLightboxIndex ? 'active' : '') + '" onclick="invLightboxIndex=' + i + ';updateInvLightbox()"></span>'
        ).join('');
    }

    function closeLightboxInv(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('invImageModal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (document.getElementById('invImageModal').classList.contains('active')) {
            if (e.key === 'Escape') closeLightboxInv();
            if (e.key === 'ArrowLeft') navigateLightboxInv(-1);
            if (e.key === 'ArrowRight') navigateLightboxInv(1);
        }
    });
</script>

</body>

</html>