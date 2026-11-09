@extends('layouts.admin-master')

@section('title', 'Dashboard')
@section('page_key', 'dashboard')

@php
    // Class Tailwind harus literal agar ikut ter-compile oleh JIT.
    $statusPill = [
        'Menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Dipinjam' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Dikembalikan' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $statusFill = [
        'Menunggu' => 'bg-amber-500',
        'Disetujui' => 'bg-emerald-500',
        'Ditolak' => 'bg-rose-500',
        'Dipinjam' => 'bg-blue-500',
        'Dikembalikan' => 'bg-slate-400',
    ];
    // Dipakai Chart.js — canvas tidak bisa memakai class Tailwind.
    $statusHex = [
        'Menunggu' => '#f59e0b',
        'Disetujui' => '#10b981',
        'Ditolak' => '#f43f5e',
        'Dipinjam' => '#3b82f6',
        'Dikembalikan' => '#94a3b8',
    ];

    // Master Data hanya untuk Super Admin; Admin tetap melihat angkanya,
    // tetapi kartu tidak dijadikan tautan agar tidak berujung 403.
    $canManageMasterData = (bool) auth()->user()?->isSuperAdmin();

    $statCards = [
        ['icon' => 'fa-box', 'box' => 'border-blue-100 bg-blue-50 text-blue-600', 'label' => 'Total Inventaris', 'value' => $totalInventaris, 'sub' => 'Aset terdaftar', 'route' => $canManageMasterData ? 'inventaris.index' : null],
        ['icon' => 'fa-file-alt', 'box' => 'border-rose-100 bg-rose-50 text-rose-600', 'label' => 'Total Permohonan', 'value' => $totalPermohonan, 'sub' => 'Item dipinjam: ' . number_format($totalItemDipinjam, 0, ',', '.'), 'route' => 'permohonan.index'],
        ['icon' => 'fa-building', 'box' => 'border-violet-100 bg-violet-50 text-violet-600', 'label' => 'Instansi Aktif', 'value' => $totalInstansi, 'sub' => 'Unit peminjam', 'route' => $canManageMasterData ? 'instansi.index' : null],
        ['icon' => 'fa-tags', 'box' => 'border-emerald-100 bg-emerald-50 text-emerald-600', 'label' => 'Kategori Barang', 'value' => $totalKategori, 'sub' => 'Pengelompokan', 'route' => $canManageMasterData ? 'kategori.index' : null],
        ['icon' => 'fa-layer-group', 'box' => 'border-indigo-100 bg-indigo-50 text-indigo-600', 'label' => 'Permohonan Bulan Ini', 'value' => $permohonanBulanIni, 'sub' => now()->translatedFormat('F Y'), 'route' => 'permohonan.index'],
    ];

    $rangeDesc = 'Semua data';
    if (!empty($filters['dari']) && !empty($filters['sampai']) && $filters['dari'] !== 'null' && $filters['sampai'] !== 'null') {
        $rangeDesc = \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y')
            . ' – ' . \Carbon\Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
    } elseif (!empty($filters['dari']) && $filters['dari'] !== 'null') {
        $rangeDesc = 'Dari ' . \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y');
    } elseif (!empty($filters['bulan']) || !empty($filters['tahun'])) {
        $bulanNama = !empty($filters['bulan'])
            ? \Carbon\Carbon::create()->month((int) $filters['bulan'])->translatedFormat('F')
            : null;
        if ($bulanNama && !empty($filters['tahun'])) {
            $rangeDesc = $bulanNama . ' ' . $filters['tahun'];
        } elseif ($bulanNama) {
            $rangeDesc = $bulanNama;
        } else {
            $rangeDesc = 'Tahun ' . $filters['tahun'];
        }
    }
    if (!empty($filters['status'])) { $rangeDesc .= ' · ' . $filters['status']; }
    if (!empty($filters['instansi_id'])) { $rangeDesc .= ' · Instansi terpilih'; }

    $filterAktif = collect($filters)->filter(fn ($v) => filled($v) && $v !== 'null')->isNotEmpty();

    $inputClass = 'h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20';
    $cardClass = 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm';
@endphp

@push('scripts')
    <script src="{{ asset('vendor/chartjs/Chart.bundle.min.js') }}?v={{ @filemtime(public_path('vendor/chartjs/Chart.bundle.min.js')) ?: 1 }}"></script>
    <script>
        (function () {
            const statusHex = @json($statusHex);

            // Panel filter bisa dilipat agar dashboard tetap ringkas.
            const filterToggle = document.getElementById('filterToggle');
            const filterPanel = document.getElementById('filterPanel');
            const filterChevron = document.getElementById('filterChevron');
            if (filterToggle && filterPanel && filterChevron) {
                filterToggle.addEventListener('click', function () {
                    const terbuka = !filterPanel.classList.toggle('hidden');
                    filterChevron.classList.toggle('rotate-180', terbuka);
                    filterToggle.setAttribute('aria-expanded', String(terbuka));
                });
            }

            Chart.defaults.global.defaultFontFamily = 'Inter, sans-serif';
            Chart.defaults.global.defaultFontColor = '#64748b';

            const tooltipStyle = {
                backgroundColor: '#0F172A',
                titleColor: '#ffffff',
                bodyColor: '#cbd5e1',
                padding: 10,
                cornerRadius: 8,
                displayColors: false
            };

            // Chart area: permohonan vs total item dipinjam.
            new Chart(document.getElementById('chartTren'), {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Permohonan',
                        data: @json($values),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,.14)',
                        fill: true,
                        tension: .35,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }, {
                        label: 'Item Dipinjam',
                        data: @json($trenItemValues),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,.12)',
                        fill: true,
                        tension: .35,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, boxWidth: 8, fontSize: 11, padding: 16 }
                    },
                    tooltips: Object.assign({}, tooltipStyle, { displayColors: true }),
                    scales: {
                        yAxes: [{
                            ticks: { beginAtZero: true, callback: function (v) { return Number(v).toFixed(0); } },
                            gridLines: { color: 'rgba(15,23,42,.06)' }
                        }],
                        xAxes: [{
                            gridLines: { display: false },
                            ticks: { autoSkip: true, maxTicksLimit: 12, maxRotation: 0 }
                        }]
                    }
                }
            });

            // Donut distribusi status (legenda digambar manual di HTML).
            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: @json($statusLabels),
                    datasets: [{
                        data: @json($statusValues),
                        backgroundColor: @json($statusLabels).map(function (s) { return statusHex[s] || '#94a3b8'; }),
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 68,
                    legend: { display: false },
                    tooltips: Object.assign({}, tooltipStyle, { displayColors: true })
                }
            });

            // horizontalBar adalah bentuk horizontal yang benar pada Chart.js v2.
            new Chart(document.getElementById('chartTopItem'), {
                type: 'horizontalBar',
                data: {
                    labels: @json($topLabels),
                    datasets: [{
                        label: 'Jumlah Dipinjam',
                        data: @json($topValues),
                        backgroundColor: 'rgba(245,158,11,.8)',
                        hoverBackgroundColor: 'rgba(234,88,12,.9)',
                        maxBarThickness: 18
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: tooltipStyle,
                    scales: {
                        xAxes: [{
                            ticks: { beginAtZero: true, callback: function (v) { return Number(v).toFixed(0); } },
                            gridLines: { color: 'rgba(15,23,42,.06)' }
                        }],
                        yAxes: [{
                            gridLines: { display: false },
                            ticks: {
                                fontSize: 10,
                                callback: function (value) {
                                    var label = String(value);
                                    return label.length > 20 ? label.slice(0, 19) + '…' : label;
                                }
                            }
                        }]
                    }
                }
            });
        })();
    </script>
@endpush

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Dashboard SILAPIN</h1>
            <p class="mt-1 text-xs font-medium text-slate-500">
                Selamat datang kembali,
                <strong class="font-bold text-blue-600">{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                — ringkasan data inventaris Anda hari ini.
            </p>
        </div>

        <span class="inline-flex items-center gap-2 self-start rounded-2xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 shadow-sm sm:self-auto">
            <i class="fas fa-calendar-alt text-[12px] text-blue-600"></i>
            {{ $rangeDesc }}
        </span>
    </div>

    {{-- ==================== KARTU STATISTIK ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach($statCards as $card)
            @php
                $cardRoute = $card['route'] ?? null;
                $cardTag = $cardRoute ? 'a' : 'div';
            @endphp
            <{{ $cardTag }} @if($cardRoute) href="{{ route($cardRoute) }}" @endif
               class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all {{ $cardRoute ? 'hover:-translate-y-1 hover:shadow-md' : '' }}">
                <div class="mb-3 flex items-center justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border {{ $card['box'] }}">
                        <i class="fas {{ $card['icon'] }} text-base"></i>
                    </span>
                    @if($cardRoute)
                        <i class="fas fa-external-link-alt text-[10px] text-slate-300 opacity-0 transition group-hover:opacity-100"></i>
                    @endif
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                <h3 class="mt-0.5 text-2xl font-extrabold leading-none text-slate-900">
                    {{ number_format($card['value'], 0, ',', '.') }}
                </h3>
                <p class="mt-1.5 text-[10px] font-medium text-slate-400">{{ $card['sub'] }}</p>
            </{{ $cardTag }}>
        @endforeach
    </div>

    {{-- ==================== FILTER ACCORDION ==================== --}}
    <section class="{{ $cardClass }}">
        <button type="button" id="filterToggle" aria-expanded="{{ $filterAktif ? 'true' : 'false' }}"
                class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition-colors hover:bg-slate-50/70">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/20">
                    <i class="fas fa-filter text-sm"></i>
                </span>
                <div>
                    <h2 class="flex flex-wrap items-center gap-2 text-sm font-bold text-slate-900">
                        Filter &amp; Rekap Peminjaman per Instansi
                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">
                            {{ $rangeDesc }}
                        </span>
                    </h2>
                    <p class="mt-0.5 text-[11px] text-slate-400">
                        Klik untuk menampilkan atau menyembunyikan opsi filter tanggal &amp; instansi.
                    </p>
                </div>
            </div>

            <span id="filterChevron"
                  class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition-transform {{ $filterAktif ? 'rotate-180' : '' }}">
                <i class="fas fa-chevron-down text-xs"></i>
            </span>
        </button>

        <div id="filterPanel" class="{{ $filterAktif ? '' : 'hidden' }} border-t border-slate-100 p-5">
            <form method="GET" action="{{ route('dashboard') }}" class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="mr-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Rentang cepat:</span>
                    @foreach($presets as $key => [$name])
                    <button type="submit" name="preset" value="{{ $key }}"
                            class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all {{ $preset === $key ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $name }}
                    </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-calendar text-[10px] text-slate-400"></i> Dari Tanggal
                        </label>
                        <input type="date" name="dari" value="{{ old('dari', $filters['dari'] ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-calendar-check text-[10px] text-slate-400"></i> Sampai Tanggal
                        </label>
                        <input type="date" name="sampai" value="{{ old('sampai', $filters['sampai'] ?? '') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-calendar-day text-[10px] text-slate-400"></i> Bulan
                        </label>
                        <select name="bulan" class="{{ $inputClass }}">
                            <option value="">Semua Bulan</option>
                            @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $filters['bulan'] == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-calendar-alt text-[10px] text-slate-400"></i> Tahun
                        </label>
                        <select name="tahun" class="{{ $inputClass }}">
                            <option value="">Semua Tahun</option>
                            @foreach($tahunList as $t)
                            <option value="{{ $t }}" {{ $filters['tahun'] == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-info-circle text-[10px] text-slate-400"></i> Status
                        </label>
                        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            @foreach($statusList as $st)
                            <option value="{{ $st }}" {{ $filters['status'] === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-building text-[10px] text-slate-400"></i> Instansi
                        </label>
                        <select name="instansi_id" class="{{ $inputClass }}" onchange="this.form.submit()">
                            <option value="">Semua Instansi</option>
                            @foreach($instansiList as $ins)
                            <option value="{{ $ins->id }}" {{ $filters['instansi_id'] == $ins->id ? 'selected' : '' }}>
                                {{ $ins->nama_instansi }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2">
                        <a href="{{ route('dashboard') }}"
                           class="flex h-9 shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 transition-all hover:bg-slate-50">
                            <i class="fas fa-undo text-[11px]"></i>
                            <span>Reset</span>
                        </a>
                        <button type="submit"
                                class="flex h-9 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition-all hover:bg-blue-700">
                            <i class="fas fa-search text-[11px]"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    {{-- ==================== CHART: AREA + TOP BARANG ==================== --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <section class="{{ $cardClass }} lg:col-span-8">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                        <i class="fas fa-chart-area text-blue-600"></i>
                        Pergerakan Permohonan &amp; Item Dipinjam
                    </h2>
                    <p class="mt-0.5 text-[11px] text-slate-400">Perbandingan jumlah permohonan dengan total item yang dipinjam.</p>
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                    @foreach(['dari', 'sampai', 'bulan', 'tahun', 'status', 'instansi_id'] as $filterKey)
                        @if(filled($filters[$filterKey] ?? null) && $filters[$filterKey] !== 'null')
                            <input type="hidden" name="{{ $filterKey }}" value="{{ $filters[$filterKey] }}">
                        @endif
                    @endforeach
                    @if(filled($preset))
                        <input type="hidden" name="preset" value="{{ $preset }}">
                    @endif

                    <label for="perSelect" class="text-[11px] font-medium text-slate-400">Granularitas</label>
                    <select id="perSelect" name="per" onchange="this.form.submit()"
                            class="rounded-lg border border-slate-200 bg-white py-1.5 pl-2.5 pr-7 text-[11px] font-medium leading-tight text-slate-600 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="auto" {{ $per === 'auto' ? 'selected' : '' }}>Otomatis</option>
                        <option value="hari" {{ $per === 'hari' ? 'selected' : '' }}>Per Hari</option>
                        <option value="bulan" {{ $per === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                    </select>
                </form>
            </div>
            <div class="p-5">
                <div class="relative h-72">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>
        </section>

        <section class="{{ $cardClass }} lg:col-span-4">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <i class="fas fa-trophy text-amber-500"></i>
                    Top Barang Paling Sering Dipinjam
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-400">Peringkat aset paling populer.</p>
            </div>
            <div class="p-5">
                <div class="relative h-72">
                    <canvas id="chartTopItem"></canvas>
                </div>
            </div>
        </section>
    </div>

    {{-- ==================== BARIS BAWAH ==================== --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Rekap per instansi --}}
        <section class="{{ $cardClass }} lg:col-span-4">
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-building text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Rekap per Instansi</h2>
                </div>
                @if($canManageMasterData)
                    <a href="{{ route('instansi.index') }}" class="flex items-center gap-1 text-[11px] font-semibold text-blue-600 transition hover:text-blue-700">
                        Lihat <i class="fas fa-chevron-right text-[9px]"></i>
                    </a>
                @endif
            </div>

            <div class="admin-table-wrap">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#0F172A] text-[10px] font-semibold uppercase tracking-wider text-slate-200">
                        <tr>
                            <th class="w-10 px-3 py-3 text-center">No</th>
                            <th class="px-3 py-3">Nama Instansi</th>
                            <th class="w-16 px-3 py-3 text-center">Barang</th>
                            <th class="px-3 py-3">Paling Sering</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recapInstansi as $i => $r)
                            @php $bisaDiklik = !empty($r['instansi_id']); @endphp
                            <tr @if($bisaDiklik) onclick="window.location='{{ route('dashboard.instansi.detail', $r['instansi_id']) }}'" @endif
                                class="{{ $bisaDiklik ? 'cursor-pointer ' : '' }}group transition-colors hover:bg-blue-50/40">
                                <td class="px-3 py-3 text-center font-medium text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-3 py-3">
                                    <p class="truncate text-xs font-bold text-slate-900">{{ $r['nama_instansi'] }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-400">{{ $r['total_permohonan'] }} permohonan</p>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700 ring-1 ring-blue-200">
                                        {{ $r['total_barang'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    @if($r['top_barang'] !== '-')
                                    <span class="inline-flex max-w-[150px] items-center gap-1 truncate rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-200" title="{{ $r['top_barang'] }}">
                                        <i class="fas fa-fire shrink-0 text-[9px]"></i>
                                        <span class="truncate">{{ $r['top_barang'] }}</span>
                                    </span>
                                    @else
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center">
                                    <i class="fas fa-inbox mb-2 block text-xl text-slate-300"></i>
                                    <span class="text-xs text-slate-400">Belum ada data peminjaman.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Permohonan terbaru --}}
        <section class="{{ $cardClass }} lg:col-span-5">
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-list text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Permohonan Terbaru</h2>
                </div>
                <a href="{{ route('permohonan.index') }}" class="flex items-center gap-1 text-[11px] font-semibold text-blue-600 transition hover:text-blue-700">
                    Lihat Semua <i class="fas fa-chevron-right text-[9px]"></i>
                </a>
            </div>

            <div class="admin-table-wrap">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#0F172A] text-[10px] font-semibold uppercase tracking-wider text-slate-200">
                        <tr>
                            <th class="whitespace-nowrap px-3 py-3">No. Permohonan</th>
                            <th class="px-3 py-3">Peminjam</th>
                            <th class="whitespace-nowrap px-3 py-3">Tgl Pinjam</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="w-12 px-3 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($permohonan->take(5) as $p)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="whitespace-nowrap px-3 py-3">
                                    <span class="rounded bg-blue-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-blue-700">
                                        {{ $p->nomor_permohonan }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-semibold leading-tight text-slate-800">{{ $p->nama_peminjam }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-400">{{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '—' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-medium text-slate-500">
                                    {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $statusPill[$p->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusFill[$p->status] ?? 'bg-slate-400' }}"></span>
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('permohonan.show', $p) }}"
                                       class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600" title="Lihat detail">
                                        <i class="fas fa-eye text-[11px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <i class="fas fa-inbox mb-2 block text-xl text-slate-300"></i>
                                    <span class="text-xs text-slate-400">Tidak ada permohonan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Distribusi status --}}
        <section class="{{ $cardClass }} lg:col-span-3">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <i class="fas fa-chart-pie text-blue-600"></i>
                    Distribusi Status
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-400">Persentase permohonan aktif.</p>
            </div>

            <div class="p-5">
                <div class="relative h-48">
                    <canvas id="chartStatus"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-extrabold leading-none text-slate-900">{{ number_format($recapGrandTotal, 0, ',', '.') }}</span>
                        <span class="mt-1 text-[9px] font-semibold uppercase tracking-wider text-slate-400">Total</span>
                    </div>
                    @if(count($statusLabels) === 0)
                    <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-400">Belum ada data</div>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 gap-1.5 border-t border-slate-100 pt-3">
                    @foreach($statusList as $st)
                    <div class="flex items-center gap-1.5 text-[11px]">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $statusFill[$st] }}"></span>
                        <span class="truncate font-medium text-slate-500">{{ $st }}</span>
                        <span class="ml-auto font-bold text-slate-700">{{ $statusCounts[$st] ?? 0 }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
