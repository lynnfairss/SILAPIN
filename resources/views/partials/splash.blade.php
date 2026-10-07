{{-- Splash screen awal masuk web (hanya halaman landing "/"). Lihat css/splash.css --}}
@php($splashLogo = file_exists(public_path('images/logo-silapin.png')))

<div id="silapinSplash" class="silapin-splash{{ $splashLogo ? '' : ' silapin-splash--no-logo' }}"
     role="status" aria-label="SILAPIN">
    <div class="silapin-splash__box">
        @if($splashLogo)
            <img class="silapin-splash__logo"
                 src="{{ asset('images/logo-silapin.png') }}?v={{ filemtime(public_path('images/logo-silapin.png')) }}"
                 alt="Logo SILAPIN"
                 onerror="var b=this.closest('.silapin-splash'); if(b){b.classList.add('silapin-splash--no-logo');}">
        @endif
        <div class="silapin-splash__fallback" aria-hidden="true">SILAPIN</div>
    </div>
</div>

<script>
    (function () {
        var el = document.getElementById('silapinSplash');
        var root = document.documentElement;

        if (!el) {
            // Elemen hilang/rusak: jangan biarkan scroll tetap terkunci
            root.classList.remove('silapin-splash-lock');
            if (document.body) document.body.classList.remove('silapin-splash-lock');
            return;
        }

        var MIN = 1800;   // durasi minimal splash (ms)
        var MAX = 4000;   // pembatas bila halaman lambat dimuat (ms)

        function unlock() {
            root.classList.remove('silapin-splash-lock');
            if (document.body) document.body.classList.remove('silapin-splash-lock');
        }

        function drop() {
            if (el.parentNode) el.parentNode.removeChild(el);
            unlock();
        }

        // Sudah tampil di tab ini / reduced-motion → hilangkan seketika
        if (root.classList.contains('silapin-splash-skip')) {
            drop();
            return;
        }

        var start = Date.now();
        var hidden = false;

        function hide() {
            if (hidden) return;
            hidden = true;
            el.classList.add('is-hidden');
            unlock();
            setTimeout(drop, 500);
        }

        function plan() {
            setTimeout(hide, Math.max(MIN - (Date.now() - start), 0));
        }

        if (document.readyState === 'complete') {
            plan();
        } else {
            window.addEventListener('load', plan);
        }

        setTimeout(hide, MAX);
    })();
</script>
