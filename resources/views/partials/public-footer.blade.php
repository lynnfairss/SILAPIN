{{--
    Footer publik SILAPIN.
    Pemakaian:
      @include('partials.public-footer')              -> footer lengkap (landing)
      @include('partials.public-footer', ['compact' => true]) -> footer ringkas (form/cek status)
--}}
@php $spFooterCompact = !empty($compact); @endphp

@if($spFooterCompact)
<footer class="sp-footer sp-footer--compact">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="sp-footer__brand mb-0">
            <span class="sp-brand__mark"><i class="fas fa-cubes"></i></span>
            <span>SILAPIN</span>
        </div>
        <p class="mb-0 small">&copy; 2026 SILAPIN — Sistem Informasi Peminjaman Inventaris</p>
    </div>
</footer>
@else
<footer class="sp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="sp-footer__brand">
                    <span class="sp-brand__mark"><i class="fas fa-cubes"></i></span>
                    <span>SILAPIN</span>
                </div>
                <p class="mb-0 small" style="max-width: 380px;">
                    Sistem Informasi Peminjaman Inventaris — Dinas Komunikasi, Informatika dan Statistik
                    Kabupaten Ponorogo.
                </p>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Menu</h6>
                <ul class="small">
                    <li><a href="{{ route('website') }}#inventaris">Daftar Inventaris</a></li>
                    <li><a href="{{ route('website') }}#layanan">Layanan Peminjaman</a></li>
                    <li><a href="{{ route('peminjam.cek-status') }}">Cek Status</a></li>
                    <li><a href="{{ route('website') }}#kontak">Kontak</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-4">
                <h6>Kontak</h6>
                <ul class="small">
                    <li><i class="fas fa-map-marker-alt me-2"></i>Jl. Contoh Alamat No. 123, Kota</li>
                    <li><i class="fas fa-phone me-2"></i>(021) 1234-5678</li>
                    <li><i class="fas fa-envelope me-2"></i>silapin@example.com</li>
                </ul>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small">
            <span>&copy; 2026 SILAPIN — Sistem Informasi Peminjaman Inventaris</span>
            <div class="d-flex gap-2">
                <a href="#" class="sp-footer__social" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="sp-footer__social" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" class="sp-footer__social" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>
</footer>
@endif
