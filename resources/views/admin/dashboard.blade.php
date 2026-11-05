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
    $kpiStyle = [
        ['box' => 'border-blue-100 bg-blue-50', 'icon' => 'bg-blue-600 shadow-blue-600/20'],
        ['box' => 'border-violet-100 bg-violet-50', 'icon' => 'bg-violet-600 shadow-violet-600/20'],
        ['box' => 'border-pink-100 bg-pink-50', 'icon' => 'bg-pink-600 shadow-pink-600/20'],
        ['box' => 'border-teal-100 bg-teal-50', 'icon' => 'bg-teal-600 shadow-teal-600/20'],
    ];
    $kpiCards = [
        ['icon' => 'fa-box', 'label' => 'Total Inventaris', 'value' => $totalInventaris, 'route' => 'inventaris.index'],
        ['icon' => 'fa-file-alt', 'label' => 'Permohonan', 'value' => $totalPermohonan, 'sub' => 'Item Dipinjam: ' . number_format($totalItemDipinjam, 0, ',', '.'), 'route' => 'permohonan.index'],
        ['icon' => 'fa-building', 'label' => 'Instansi', 'value' => $totalInstansi, 'route' => 'instansi.index'],
        ['icon' => 'fa-tags', 'label' => 'Kategori', 'value' => $totalKategori, 'route' => 'kategori.index'],
    ];

    $rangeDesc = 'Semua data';
    if (!empty($filters['dari']) && !empty($filters['sampai']) && $filters['dari'] !== 'null' && $filters['sampai'] !== 'null') {
        $rangeDesc = \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y')
            . ' – ' . \Carbon\Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
    } elseif (!empty($filters['dari']) && $filters['dari'] !== 'null') {
        $rangeDesc = 'Dari ' . \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y');
    }
    if (!empty($filters['status'])) { $rangeDesc .= ' · ' . $filters['status']; }
    if (!empty($filters['instansi_id'])) { $rangeDesc .= ' · Instansi terpilih'; }

    $inputClass = 'h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20';
    $cardClass = 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm';
@endphp

@push('scripts')
    <script src="{{ asset('vendor/chartjs/Chart.bundle.min.js') }}?v={{ @filemtime(public_path('vendor/chartjs/Chart.bundle.min.js')) ?: 1 }}"></script>
    <script>
        (function () {
            const statusHex = @json($statusHex);

            // Lebar bar dimulai 0 lalu dianimasikan ke nilai hasil filter.
            document.querySelectorAll('[data-bar-width]').forEach(function (el) {
                setTimeout(function () { el.style.width = el.dataset.barWidth; }, 150);
            });

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

            new Chart(document.getElementById('chartTren'), {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Jumlah Permohonan',
                        data: @json($values),
                        borderColor: '#6d28d9',
                        backgroundColor: 'rgba(109,40,217,.12)',
                        fill: true,
                        tension: .35,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: tooltipStyle,
                    scales: {
                        yAxes: [{
                            ticks: { beginAtZero: true, callback: function (v) { return Number(v).toFixed(0); } },
                            gridLines: { color: 'rgba(15,23,42,.06)' }
                        }],
                        xAxes: [{ gridLines: { display: false } }]
                    }
                }
            });

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
                    cutoutPercentage: 62,
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 14, fontSize: 11 }
                    },
                    tooltips: Object.assign({}, tooltipStyle, { displayColors: true })
                }
            });

            new Chart(document.getElementById('chartKategori'), {
                type: 'bar',
                data: {
                    labels: @json($kategoriLabels),
                    datasets: [{
                        label: 'Jumlah Barang',
                        data: @json($kategoriValues),
                        backgroundColor: 'rgba(37,99,235,.75)',
                        hoverBackgroundColor: 'rgba(109,40,217,.85)',
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: tooltipStyle,
                    scales: {
                        yAxes: [{
                            ticks: { beginAtZero: true, callback: function (v) { return Number(v).toFixed(0); } },
                            gridLines: { color: 'rgba(15,23,42,.06)' }
                        }],
                        xAxes: [{ gridLines: { display: false } }]
                    }
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
                        maxBarThickness: 26
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
                        yAxes: [{ gridLines: { display: false } }]
                    }
                }
            });
        })();
    </script>
@endpush

@section('content')

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- ==================== KOLOM KIRI ==================== --}}
    <div class="space-y-6 lg:col-span-2">

        {{-- Ikhtisar Inventaris --}}
        <section class="{{ $cardClass }}">
            <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                <i class="fas fa-th-large text-sm text-blue-600"></i>
                <h2 class="text-sm font-bold text-slate-900">Ikhtisar Inventaris</h2>
            </div>
            <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                @foreach($kpiCards as $i => $kpi)
                <a href="{{ route($kpi['route']) }}"
                   class="flex items-center gap-4 rounded-2xl border p-4 transition-all hover:-translate-y-0.5 hover:shadow-md {{ $kpiStyle[$i]['box'] }}">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-md {{ $kpiStyle[$i]['icon'] }}">
                        <i class="fas {{ $kpi['icon'] }} text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $kpi['label'] }}</p>
                        <h3 class="mt-0.5 text-2xl font-bold leading-tight text-slate-900">{{ number_format($kpi['value'], 0, ',', '.') }}</h3>
                        @if(isset($kpi['sub']))
                        <p class="mt-0.5 text-[11px] text-slate-500">{{ $kpi['sub'] }}</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </section>

        {{-- Laporan Rekap Peminjaman per Instansi --}}
        <section class="{{ $cardClass }}">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-sliders-h text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Laporan Rekap Peminjaman per Instansi</h2>
                </div>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600">
                    {{ $rangeDesc }}
                </span>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" id="filterForm" class="space-y-4 p-5">
                <div class="flex flex-wrap gap-2">
                    @foreach($presets as $key => [$name])
                    <button type="submit" name="preset" value="{{ $key }}"
                            class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all {{ $preset === $key ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $name }}
                    </button>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
                            @foreach(($statusListFilter ?? $statusList) as $st)
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
                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <i class="fas fa-chart-line text-[10px] text-slate-400"></i> Granularitas Grafik
                        </label>
                        <select name="per" class="{{ $inputClass }}" onchange="this.form.submit()">
                            <option value="auto" {{ $per === 'auto' ? 'selected' : '' }}>Otomatis</option>
                            <option value="hari" {{ $per === 'hari' ? 'selected' : '' }}>Per Hari</option>
                            <option value="bulan" {{ $per === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="flex h-9 w-full items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 text-xs font-semibold text-blue-700 ring-1 ring-blue-200 transition-all hover:bg-blue-100">
                            <i class="fas fa-search text-[11px]"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="border-t border-slate-100 px-5 pb-5 pt-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <i class="fas fa-building text-[11px] text-blue-600"></i> Rekap Peminjaman per Instansi
                    </h3>
                    <span class="flex items-center gap-1.5 text-[11px] text-slate-400">
                        <i class="fas fa-hand-pointer text-[10px]"></i> Klik baris untuk melihat detail barang
                    </span>
                </div>

                <div class="admin-table-wrap">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                            <tr>
                                <th class="w-12 px-4 py-3.5 text-center">No</th>
                                <th class="px-5 py-3.5">Nama Instansi</th>
                                <th class="px-5 py-3.5">Peminjam</th>
                                <th class="px-4 py-3.5 text-center">Barang Dipinjam</th>
                                <th class="px-5 py-3.5">Paling Sering Dipinjam</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recapInstansi as $i => $r)
                            {{-- Route detail hanya menerima Instansi yang benar-benar ada.
                                 Permohonan tanpa instansi_id tidak punya halaman detail,
                                 jadi barisnya tidak diklikable (route() tanpa parameter
                                 akan melempar 500). --}}
                            @php $bisaDiklik = !empty($r['instansi_id']); @endphp
                            <tr @if($bisaDiklik) onclick="window.location='{{ route('dashboard.instansi.detail', $r['instansi_id']) }}'" @endif
                                class="{{ $bisaDiklik ? 'cursor-pointer ' : '' }}group transition-colors hover:bg-slate-50/80">
                                <td class="px-4 py-4 text-center font-medium text-slate-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <span class="text-xs font-bold text-slate-900">{{ $r['nama_instansi'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if(!empty($r['nama_peminjam_list']))
                                    <span class="flex flex-wrap gap-1">
                                        @foreach($r['nama_peminjam_list'] as $np)
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-600">{{ $np }}</span>
                                        @endforeach
                                    </span>
                                    @else
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center font-bold text-slate-900">{{ $r['total_barang'] }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ $r['top_barang'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center">
                                    <i class="fas fa-inbox mb-2 block text-2xl text-slate-300"></i>
                                    <span class="text-xs text-slate-400">Tidak ada data peminjaman untuk filter ini.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-[11px] text-slate-400">
                    Menampilkan {{ $recapInstansi->isEmpty() ? 0 : 1 }} sampai {{ $recapInstansi->count() }} dari {{ $recapInstansi->count() }} data
                </p>
            </div>
        </section>

        {{-- Grafik tren permohonan --}}
        <section class="{{ $cardClass }}">
            <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                <i class="fas fa-chart-line text-sm text-blue-600"></i>
                <h2 class="text-sm font-bold text-slate-900">{{ $chartLabel }}</h2>
            </div>
            <div class="p-5">
                <div class="relative h-64">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- Inventaris per kategori --}}
            <section class="{{ $cardClass }}">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                    <i class="fas fa-chart-bar text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Inventaris per Kategori</h2>
                </div>
                <div class="p-5">
                    <div class="relative h-64">
                        <canvas id="chartKategori"></canvas>
                    </div>
                </div>
            </section>

            {{-- Item paling sering dipinjam --}}
            <section class="{{ $cardClass }}">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                    <i class="fas fa-trophy text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Item Paling Sering Dipinjam</h2>
                </div>
                <div class="p-5">
                    <div class="relative h-64">
                        <canvas id="chartTopItem"></canvas>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- ==================== KOLOM KANAN ==================== --}}
    <div class="space-y-6">

        {{-- Status Permohonan --}}
        <section class="{{ $cardClass }}">
            <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                <i class="fas fa-clipboard-list text-sm text-blue-600"></i>
                <h2 class="text-sm font-bold text-slate-900">Status Permohonan</h2>
            </div>
            <div class="space-y-3 p-5">
                @foreach($statusList as $st)
                @php $persen = $totalPermohonan > 0 ? round(($statusCounts[$st] ?? 0) / $totalPermohonan * 100) : 0; @endphp
                <div class="flex items-center gap-3">
                    <span class="w-28 shrink-0 truncate rounded-full px-2.5 py-1 text-center text-[11px] font-semibold ring-1 {{ $statusPill[$st] }}">
                        {{ $st }}
                    </span>
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-[width] duration-700 ease-out {{ $statusFill[$st] }}"
                             data-bar-width="{{ $persen }}%" style="width:0%;"></div>
                    </div>
                    <span class="w-8 shrink-0 text-right text-sm font-bold text-slate-900">{{ $statusCounts[$st] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </section>

        {{-- Permohonan Terbaru --}}
        <section class="{{ $cardClass }}">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-list text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Permohonan Terbaru</h2>
                </div>
                <a href="{{ route('permohonan.index') }}" class="flex items-center gap-1.5 text-[11px] font-semibold text-blue-600 transition hover:text-blue-700">
                    Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="admin-table-wrap">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#0F172A] text-[10px] font-semibold uppercase tracking-wider text-slate-200">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3">No. Permohonan</th>
                            <th class="px-4 py-3">Instansi</th>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="whitespace-nowrap px-4 py-3">Tgl Pinjam</th>
                            <th class="px-3 py-3 text-center">Item</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($permohonan->take(5) as $p)
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-[11px] font-semibold text-slate-900">{{ $p->nomor_permohonan }}</td>
                            <td class="px-4 py-3">{{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $p->nama_peminjam }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                            <td class="px-3 py-3 text-center font-bold text-slate-900">{{ $p->detailPermohonan->sum('jumlah') }}</td>
                            <td class="px-4 py-3">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 {{ $statusPill[$p->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-xs text-slate-400">Tidak ada permohonan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Ringkasan status --}}
        <div class="grid grid-cols-2 gap-4">
            @foreach(['Menunggu', 'Disetujui', 'Dikembalikan'] as $st)
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 text-center shadow-sm">
                <p class="text-[11px] font-semibold text-slate-500">{{ $st }}</p>
                <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $statusCounts[$st] ?? 0 }}</h3>
            </div>
            @endforeach
            <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 text-center shadow-sm">
                <p class="text-[11px] font-semibold text-slate-500">Grand Total</p>
                <h3 class="mt-1 text-xl font-bold text-blue-700">{{ $recapGrandTotal }}</h3>
            </div>
        </div>

        {{-- Distribusi status --}}
        <section class="{{ $cardClass }}">
            <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                <i class="fas fa-chart-pie text-sm text-blue-600"></i>
                <h2 class="text-sm font-bold text-slate-900">Distribusi Status Peminjaman</h2>
            </div>
            <div class="p-5">
                <div class="relative h-56">
                    <canvas id="chartStatus"></canvas>
                    @if(count($statusLabels) === 0)
                    <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-400">Belum ada data</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

@endsection
