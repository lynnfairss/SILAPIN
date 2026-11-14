<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Master Data') &middot; {{ config('app.name', 'SILAPIN') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @vite(['resources/css/app.css'])

    {{-- Lightbox / komponen lama yang masih dipakai halaman inventaris --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ @filemtime(public_path('css/custom.css')) ?: 1 }}">
    <link rel="stylesheet" href="{{ asset('css/admin-master.css') }}?v={{ @filemtime(public_path('css/admin-master.css')) ?: 1 }}">

    @stack('css')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased" data-page="@yield('page_key')">

    @php
        $user = auth()->user();
        $roleLabel = $user?->isSuperAdmin()
            ? 'Super Admin'
            : ($user?->isAdmin() ? 'Admin' : 'Staff');
        $userInitial = strtoupper(substr($user?->name ?? 'S', 0, 1));

        $menungguCount = \App\Models\Permohonan::countMenunggu();

        // Grup menu dibatasi per role. Backend tetap dijaga oleh middleware
        // role:super_admin; ini hanya mencegah menu berujung 403.
        $navGroups = [
            [
                'title' => 'Main Menu',
                'roles' => ['super_admin', 'admin', 'staff'],
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'route' => 'dashboard', 'active' => 'dashboard*'],
                ],
            ],
            [
                'title' => 'Master Data',
                'roles' => ['super_admin'],
                'items' => [
                    ['label' => 'Instansi', 'icon' => 'fa-building', 'route' => 'instansi.index', 'active' => 'instansi.*'],
                    ['label' => 'Kategori', 'icon' => 'fa-tags', 'route' => 'kategori.index', 'active' => 'kategori.*'],
                    ['label' => 'Jenis', 'icon' => 'fa-boxes', 'route' => 'jenis.index', 'active' => 'jenis.*'],
                    ['label' => 'Inventaris', 'icon' => 'fa-box', 'route' => 'inventaris.index', 'active' => 'inventaris.*'],
                ],
            ],
            [
                'title' => 'Transaksi',
                'roles' => ['super_admin', 'admin', 'staff'],
                'items' => [
                    ['label' => 'Permohonan', 'icon' => 'fa-file-alt', 'route' => 'permohonan.index', 'active' => 'permohonan.*', 'badge' => $menungguCount ?: null],
                    ['label' => 'Pengembalian Barang', 'icon' => 'fa-undo', 'route' => 'pengembalian.index', 'active' => 'pengembalian.*'],
                    ['label' => 'Upload Surat', 'icon' => 'fa-file-upload', 'route' => 'surat.upload', 'active' => 'surat.*'],
                    ['label' => 'Keamanan', 'icon' => 'fa-shield-alt', 'route' => 'security', 'active' => 'security*'],
                ],
            ],
            [
                'title' => 'Pengaturan',
                'roles' => ['super_admin'],
                'items' => [
                    ['label' => 'Manajemen Admin', 'icon' => 'fa-user-shield', 'route' => 'users.index', 'active' => 'users.*'],
                ],
            ],
        ];

        $navGroups = array_values(array_filter(
            $navGroups,
            fn ($group) => $user && in_array($user->role, $group['roles'], true)
        ));
    @endphp

    {{-- ==================== TOAST ==================== --}}
    <div id="adminToast" class="pointer-events-none fixed right-4 top-4 z-[90] hidden sm:right-5 sm:top-5" role="status" aria-live="polite">
        <div class="pointer-events-auto flex max-w-sm items-start gap-3 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-xs font-medium text-white shadow-2xl">
            <i id="adminToastIcon" class="fas fa-check-circle mt-0.5 text-base text-emerald-400"></i>
            <span id="adminToastText" class="flex-1 leading-relaxed"></span>
            <button type="button" id="adminToastClose" class="text-slate-400 transition hover:text-white" aria-label="Tutup notifikasi">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    {{-- ==================== OVERLAY MOBILE ==================== --}}
    <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    {{-- ==================== SIDEBAR ==================== --}}
    <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-40 flex flex-col bg-[#0F172A] text-slate-300 shadow-xl">
        <div class="sidebar-brand flex h-16 shrink-0 items-center gap-3 border-b border-slate-800 px-5">
            <img src="{{ asset('images/logo-silapin.png') }}?v={{ @filemtime(public_path('images/logo-silapin.png')) ?: 1 }}"
                 alt="Logo SILAPIN"
                 class="h-10 w-10 shrink-0 rounded-xl bg-white object-contain p-1 shadow-md shadow-blue-500/20">
            <span class="sidebar-brand-text text-lg font-bold tracking-wide text-white">SILAPIN</span>
        </div>

        <div class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            @foreach($navGroups as $group)
                <div>
                    <div class="sidebar-group-title mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ $group['title'] }}
                    </div>
                    <nav class="space-y-1">
                        @foreach($group['items'] as $item)
                            @php $isActive = request()->routeIs($item['active']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="sidebar-link flex items-center gap-3 rounded-lg px-3 py-2 transition-all {{ $isActive ? 'bg-blue-600 font-medium text-white shadow-md shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }} {{ empty($item['badge']) ? '' : 'has-badge' }}"
                               title="{{ $item['label'] }}">
                                <i class="fas {{ $item['icon'] }} w-5 shrink-0 text-center text-[15px]"></i>
                                <span class="sidebar-label flex-1 truncate text-sm font-medium">{{ $item['label'] }}</span>
                                @if(!empty($item['badge']))
                                    <span class="sidebar-badge flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white shadow-sm shadow-rose-500/40"
                                          title="{{ $item['badge'] }} permohonan menunggu">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        <div class="sidebar-user m-2 flex items-center gap-3 rounded-xl border-t border-slate-800 bg-slate-900/50 p-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                {{ $userInitial }}
            </div>
            <div class="sidebar-user-meta min-w-0 flex-1">
                <p class="truncate text-xs font-semibold text-white">{{ $user?->name ?? 'Pengguna' }}</p>
                <p class="truncate text-[10px] text-slate-400">{{ $roleLabel }}</p>
            </div>
        </div>
    </aside>

    {{-- ==================== MAIN ==================== --}}
    <div id="adminMain" class="flex min-h-screen flex-col transition-all duration-300">
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
            <div class="flex items-center gap-4">
                <button type="button" id="sidebarToggle" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100" aria-label="Buka atau tutup menu">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <form method="GET" action="@yield('topbar_search_action', url()->current())" class="hidden w-64 items-center gap-2 rounded-lg border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs text-slate-500 transition focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-500/20 sm:flex">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="@yield('topbar_search_placeholder', 'Cari kata kunci cepat...')"
                           class="w-full border-0 bg-transparent p-0 text-xs text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-0">
                    @foreach(request()->except(['q', 'page', 'scan']) as $key => $value)
                        @if(is_scalar($value) && filled($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                </form>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <button type="button" id="fullscreenToggle" class="hidden rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 sm:block" aria-label="Layar penuh">
                    <i class="fas fa-expand text-base"></i>
                </button>

                <div class="h-6 w-px bg-slate-200"></div>

                <div class="relative">
                    <button type="button" id="userMenuToggle" class="flex items-center gap-3 rounded-lg p-1 transition hover:bg-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-blue-500 bg-blue-100 text-sm font-bold text-blue-700">
                            {{ $userInitial }}
                        </span>
                        <span class="hidden text-left md:block">
                            <span class="block text-sm font-semibold leading-tight text-slate-800">{{ $user?->name ?? 'Pengguna' }}</span>
                            <span class="block text-[11px] font-medium text-slate-400">{{ $roleLabel }}</span>
                        </span>
                        <i class="fas fa-chevron-down hidden text-[10px] text-slate-400 md:block"></i>
                    </button>

                    <div id="userMenu" class="absolute right-0 mt-2 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                            <i class="fas fa-user w-4 text-center text-slate-400"></i> Profil Saya
                        </a>
                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-50">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 space-y-6 bg-slate-50/50 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white px-6 py-3 text-center text-[11px] text-slate-400">
            &copy; {{ date('Y') }} SILAPIN &mdash; Sistem Informasi Layanan Peminjaman Barang
        </footer>
    </div>

    {{-- ==================== MODAL HAPUS (SHARED) ==================== --}}
    <div id="deleteModal" class="admin-modal hidden fixed inset-0 z-[80] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-sm space-y-4 rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div id="deleteIcon" class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div>
                <h3 id="deleteTitle" class="text-base font-bold text-slate-900">Hapus Data Ini?</h3>
                <p id="deleteMessage" class="mt-1 text-xs leading-relaxed text-slate-500">
                    Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" data-modal-close class="w-full rounded-xl border border-slate-200 py-2.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="w-full">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE" id="deleteMethod">
                    <button type="submit" id="deleteConfirm"
                            class="w-full rounded-xl bg-rose-600 py-2.5 text-xs font-medium text-white shadow-md shadow-rose-600/20 transition hover:bg-rose-700">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.__flash = {
            success: @json(session('success')),
            error: @json(session('error')),
            errors: @json($errors->any() ? $errors->all() : []),
        };
    </script>
    <script src="{{ asset('js/security.js') }}?v={{ @filemtime(public_path('js/security.js')) ?: 1 }}"></script>
    <script src="{{ asset('js/admin-master.js') }}?v={{ @filemtime(public_path('js/admin-master.js')) ?: 1 }}"></script>
    @stack('scripts')
</body>
</html>
