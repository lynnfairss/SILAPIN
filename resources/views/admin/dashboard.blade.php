@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-end">
        <div>
            <h1>Dashboard SILAPIN</h1>
            <p class="text-muted mb-0 small">
                @php
                    $rangeDesc = 'Semua data';
                    if (!empty($filters['dari']) && !empty($filters['sampai']) && $filters['dari'] !== 'null' && $filters['sampai'] !== 'null') {
                        $rangeDesc = \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y')
                            . ' – ' . \Carbon\Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
                    } elseif (!empty($filters['dari']) && $filters['dari'] !== 'null') {
                        $rangeDesc = 'Dari ' . \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y');
                    }
                    if (!empty($filters['status'])) { $rangeDesc .= ' · Status: ' . $filters['status']; }
                    if (!empty($filters['instansi_id'])) { $rangeDesc .= ' · Per Instansi'; }
                    $greeting = now()->format('H') < 12 ? 'Selamat Pagi' : (now()->format('H') < 15 ? 'Selamat Siang' : (now()->format('H') < 18 ? 'Selamat Sore' : 'Selamat Malam'));
                @endphp
                {{ $greeting }}, {{ Auth::user()->role == 'super_admin' ? 'Super Admin' : 'Admin' }} — <strong>{{ $rangeDesc }}</strong>
            </p>
        </div>
    </div>
@stop

@section('css')
<style>
    .kpi-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        transition: box-shadow .35s cubic-bezier(.22,1,.36,1), transform .35s cubic-bezier(.22,1,.36,1);
        border: 1px solid #eef0f4;
        height: 100%;
        min-height: 110px;
        gap: 18px;
        color: inherit;
        cursor: pointer;
        will-change: transform;
        display: flex;
        align-items: center;
    }
    .kpi-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 28px rgba(0,0,0,.12);
        color: inherit;
        text-decoration: none;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--kpi-color, #0d6efd);
    }
    .kpi-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,.12);
    }
    .kpi-body {
        flex: 1;
        min-width: 0;
    }
    .kpi-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 4px;
        line-height: 1.2;
    }
    .kpi-value {
        font-size: 1.55rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.15;
    }
    .kpi-sub {
        font-size: .72rem;
        color: #64748b;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px solid #f1f5f9;
        line-height: 1.3;
    }

    .card-flat {
        border-radius: 14px;
        border: 1px solid #eef0f4;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
    }
    .card-flat .card-header {
        background: transparent;
        border-bottom: 1px solid #eef0f4;
        border-radius: 14px 14px 0 0;
        padding: .85rem 1.15rem;
    }
    .card-flat .card-title {
        font-weight: 700;
        color: #0f172a;
        font-size: .92rem;
    }

    .preset-btn {
        border-radius: 20px;
        font-size: .8rem;
        padding: .35rem .9rem;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #495057;
        font-weight: 500;
        transition: all .15s ease;
    }
    .preset-btn:hover {
        background: #f0f4ff;
        border-color: #86b7fe;
        color: #0d6efd;
    }
    .preset-btn.active {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .btn-gradient {
        background: #0d6efd;
        border: none;
        color: #fff;
        box-shadow: 0 2px 6px rgba(13,110,253,.2);
        border-radius: 8px;
    }
    .btn-gradient:hover { color: #fff; filter: brightness(1.05); }

    .filter-section-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6c757d;
        margin-bottom: .5rem;
    }
    .filter-field-label {
        font-size: .78rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: .3rem;
        display: flex;
        align-items: center;
        gap: .35rem;
    }
    .filter-field-label i { color: #94a3b8; font-size: .72rem; }
    .filter-box .form-control,
    .filter-box .form-select {
        border-radius: 8px;
        border-color: #dee2e6;
        height: 38px;
        font-size: .85rem;
        background-color: #fff;
    }
    .filter-box .form-control:focus,
    .filter-box .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12);
    }
    .filter-actions-bar {
        border-top: 1px dashed #e2e8f0;
        margin-top: .25rem;
        padding-top: 1rem;
    }
    .rekap-section-head {
        border-top: 1px solid #eef0f4;
        margin-top: .25rem;
        padding-top: 1.25rem;
    }

    .status-bar-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }
    .status-bar-label {
        width: 130px;
        min-width: 130px;
        font-size: .8rem;
        font-weight: 600;
        color: #495057;
        white-space: nowrap;
    }
    .status-bar-track {
        flex: 1;
        height: 10px;
        background: #eef0f4;
        border-radius: 20px;
        overflow: hidden;
    }
    .status-bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width .8s ease;
    }
    .status-bar-count {
        width: 56px;
        text-align: right;
        font-weight: 700;
        color: #1a1a2e;
        font-size: .9rem;
    }

    .chart-wrap { position: relative; height: 280px; }
    .chart-wrap-sm { position: relative; height: 240px; }

    .table-modern thead th {
        background: #0f172a;
        color: #fff;
        font-weight: 600;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        border: none;
        padding: .75rem .9rem;
        white-space: nowrap;
    }
    .table-modern tbody td {
        padding: .7rem .9rem;
        font-size: .875rem;
        vertical-align: middle;
        border-color: #f1f5f9;
    }
    .table-modern tbody tr { border-bottom: 1px solid #f1f5f9; }
    .table-modern tbody tr:hover { background: rgba(59,130,246,.04); cursor: pointer; }
    .badge-soft {
        font-weight: 600;
        border-radius: 20px;
        padding: .3em .75em;
        font-size: .75rem;
    }

    .total-row td { font-weight: 700; background: #f0f2f7 !important; }

    @media (max-width: 576px) {
        .status-bar-label { width: 100px; min-width: 100px; }
        .kpi-value { font-size: 1.4rem; }
    }
</style>
@stop

@section('content')

@php
    $statusColor = [
        'Menunggu' => 'warning',
        'Disetujui' => 'success',
        'Ditolak' => 'danger',
        'Dipinjam' => 'info',
        'Dikembalikan' => 'secondary',
    ];
    $statusHex = [
        'Menunggu' => '#f39c12',
        'Disetujui' => '#28a745',
        'Ditolak' => '#dc3545',
        'Dipinjam' => '#17a2b8',
        'Dikembalikan' => '#6c757d',
    ];
    $maxStatus = max($statusCounts ?: [0]);
    $kpiTotal = $totalPermohonan > 0 ? $totalPermohonan : 1;
@endphp

{{-- Filter Recap & Rekap Peminjaman per Instansi (Gabungan) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-flat filter-box">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <h3 class="card-title mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filter Recap & Rekap Peminjaman per Instansi</h3>
                <button class="btn btn-tool" type="button" data-toggle="collapse" data-target="#filterBody" aria-expanded="true" aria-controls="filterBody">
                    <i class="fas fa-minus" id="filterToggleIcon"></i>
                </button>
            </div>
            <div id="filterBody" class="collapse show">
                <div class="card-body pb-0">
                    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                        {{-- Preset rentang waktu --}}
                        <div class="filter-section-label">
                            <i class="fas fa-bolt me-1"></i> Rentang Waktu Cepat
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($presets as $key => [$name])
                            <button type="submit" name="preset" value="{{ $key }}"
                                class="btn preset-btn {{ $preset === $key ? 'active' : '' }}">
                                {{ $name }}
                            </button>
                            @endforeach
                        </div>

                        {{-- Field filter --}}
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-calendar"></i> Dari Tanggal</label>
                                <input type="date" name="dari" value="{{ old('dari', $filters['dari'] ?? '') }}" class="form-control">
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-calendar-check"></i> Sampai Tanggal</label>
                                <input type="date" name="sampai" value="{{ old('sampai', $filters['sampai'] ?? '') }}" class="form-control">
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-calendar-day"></i> Bulan</label>
                                <select name="bulan" class="form-select">
                                    <option value="">Semua Bulan</option>
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filters['bulan'] == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-calendar-alt"></i> Tahun</label>
                                <select name="tahun" class="form-select">
                                    <option value="">Semua Tahun</option>
                                    @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ $filters['tahun'] == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-info-circle"></i> Status</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Status</option>
                                    @foreach($statusList as $st)
                                    <option value="{{ $st }}" {{ $filters['status'] === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-building"></i> Instansi</label>
                                <select name="instansi_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Instansi</option>
                                    @foreach($instansiList as $ins)
                                    <option value="{{ $ins->id }}" {{ $filters['instansi_id'] == $ins->id ? 'selected' : '' }}>
                                        {{ $ins->nama_instansi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6">
                                <label class="filter-field-label"><i class="fas fa-chart-line"></i> Granularitas Grafik</label>
                                <select name="per" class="form-select" onchange="this.form.submit()">
                                    <option value="auto" {{ $per === 'auto' ? 'selected' : '' }}>Otomatis</option>
                                    <option value="hari" {{ $per === 'hari' ? 'selected' : '' }}>Per Hari</option>
                                    <option value="bulan" {{ $per === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 col-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" style="height:38px;">
                                    <i class="fas fa-search me-1"></i> Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Rekap Peminjaman per Instansi --}}
                <div class="card-body table-responsive pb-4 rekap-section-head">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <h4 class="mb-0 fw-bold" style="font-size:.95rem;">
                            <i class="fas fa-building me-2 text-primary"></i>Rekap Peminjaman per Instansi
                        </h4>
                        <small class="text-muted"><i class="fas fa-hand-pointer me-1"></i>Klik baris untuk melihat detail barang</small>
                    </div>
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th style="min-width:40px" class="text-center">No</th>
                                <th>Nama Instansi</th>
                                <th class="text-center" style="min-width:90px">Permohonan</th>
                                <th class="text-center" style="min-width:90px">Barang Dipinjam</th>
                                <th>Paling Sering Dipinjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recapInstansi as $i => $r)
                            <tr style="cursor:pointer;" onclick="window.location='{{ route('dashboard.instansi.detail', $r['instansi_id']) }}'">
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="fw-bold text-primary">{{ $r['nama_instansi'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary badge-soft text-white">{{ $r['total_permohonan'] }}</span>
                                </td>
                                <td class="text-center fw-bold">{{ $r['total_barang'] }}</td>
                                <td><small class="text-muted">{{ $r['top_barang'] }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                    Tidak ada data peminjaman untuk filter ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
@php
$kpiCards = [
    ['icon' => 'fa-box', 'label' => 'Total Inventaris', 'value' => $totalInventaris, 'color' => '#0d6efd', 'route' => 'inventaris.index'],
    ['icon' => 'fa-file-alt', 'label' => 'Permohonan', 'value' => $totalPermohonan, 'color' => '#6610f2', 'sub' => 'Item Dipinjam: ' . number_format($totalItemDipinjam, 0, ',', '.'), 'route' => 'permohonan.index'],
    ['icon' => 'fa-building', 'label' => 'Instansi', 'value' => $totalInstansi, 'color' => '#d63384', 'route' => 'instansi.index'],
    ['icon' => 'fa-tags', 'label' => 'Kategori', 'value' => $totalKategori, 'color' => '#20c997', 'route' => 'kategori.index'],
];
@endphp
<div class="row g-3 mb-4">
    @foreach($kpiCards as $kpi)
    <div class="col-lg-3 col-md-6">
        <a href="{{ route($kpi['route']) }}" class="kpi-card d-flex align-items-center gap-4 text-decoration-none" style="--kpi-color: {{ $kpi['color'] }}">
            <div class="kpi-icon" style="background: {{ $kpi['color'] }};">
                <i class="fas {{ $kpi['icon'] }}"></i>
            </div>
            <div class="kpi-body">
                <div class="kpi-label">{{ $kpi['label'] }}</div>
                <div class="kpi-value">{{ number_format($kpi['value'], 0, ',', '.') }}</div>
                @if(isset($kpi['sub']))
                <div class="kpi-sub">{{ $kpi['sub'] }}</div>
                @endif
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- Status breakdown (progress bars) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Status Permohonan</h3>
            </div>
            <div class="card-body">
                @foreach($statusList as $st)
                <div class="status-bar-row">
                    <span class="status-bar-label">
                        <span class="badge bg-{{ $statusColor[$st] }} badge-soft text-white">{{ $st }}</span>
                    </span>
                    <div class="status-bar-track">
                        <div class="status-bar-fill" data-width="{{ $totalPermohonan > 0 ? round(($statusCounts[$st] ?? 0) / $totalPermohonan * 100) : 0 }}%"
                            style="width:0%; background: {{ $statusHex[$st] }};"></div>
                    </div>
                    <span class="status-bar-count">{{ $statusCounts[$st] ?? 0 }}</span>
                </div>
                @endforeach
                <div class="d-flex justify-content-between border-top pt-2 mt-1">
                    <span class="small text-muted fw-semibold">Total Permohonan (filter)</span>
                    <span class="fw-bold">{{ $totalPermohonan }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts row 1 --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line me-2 text-primary"></i>{{ $chartLabel }}</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Distribusi Status</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts row 2 --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Inventaris per Kategori</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="chartKategori"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy me-2 text-primary"></i>Item Paling Sering Dipinjam</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="chartTopItem"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recap summary cards --}}
<div class="row mb-3">
    @foreach($statusList as $st)
    <div class="col">
        <div class="card card-flat text-center py-2">
            <div class="card-body py-2">
                <div class="text-muted small fw-semibold mb-1">{{ $st }}</div>
                <div class="fs-4 fw-bold" style="color: {{ $statusColor[$st] ?? '#6c757d' }}">{{ $statusCounts[$st] ?? 0 }}</div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="col">
        <div class="card card-flat text-center py-2" style="border-left: 3px solid #4361ee;">
            <div class="card-body py-2">
                <div class="text-muted small fw-semibold mb-1">Grand Total</div>
                <div class="fs-4 fw-bold text-primary">{{ $recapGrandTotal }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Permohonan terbaru --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list me-2 text-primary"></i>Permohonan Terbaru (Maks. 20)</h3>
                <div class="card-tools">
                    <a href="{{ route('permohonan.index') }}" class="btn btn-tool btn-sm">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Instansi</th>
                            <th>Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th class="text-center">Item</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonan as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->nomor_permohonan }}</td>
                            <td>{{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '-' }}</td>
                            <td>{{ $p->nama_peminjam }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                            <td class="text-center">{{ $p->detailPermohonan->sum('jumlah') }}</td>
                            <td>
                                <span class="badge bg-{{ $statusColor[$p->status] ?? 'secondary' }} badge-soft text-white">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Tidak ada permohonan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@stop

@section('plugins.Chartjs', true)

@section('js')
<script>
    const statusHex = @json($statusHex);

    // Animasi progress bar status
    document.querySelectorAll('.status-bar-fill').forEach(el => {
        const w = el.dataset.width;
        setTimeout(() => { el.style.width = w; }, 150);
    });

    // Toggle icon collapse filter
    document.querySelector('[data-target="#filterBody"]').addEventListener('click', function() {
        const icon = document.getElementById('filterToggleIcon');
        const show = document.getElementById('filterBody').classList.contains('show');
        icon.className = show ? 'fas fa-minus' : 'fas fa-plus';
    });

    Chart.defaults.global.defaultFontFamily = "'Source Sans Pro', sans-serif";
    Chart.defaults.global.defaultFontColor = '#495057';

    // Tren
    new Chart(document.getElementById('chartTren'), {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Jumlah Permohonan',
                data: @json($values),
                borderColor: '#6610f2',
                backgroundColor: ctx => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    g.addColorStop(0, 'rgba(102,16,242,.25)');
                    g.addColorStop(1, 'rgba(102,16,242,0)');
                    return g;
                },
                fill: true,
                tension: .35,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1a2e',
                    titleColor: '#fff',
                    bodyColor: '#dfe3ea',
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Doughnut status
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: @json($statusLabels),
            datasets: [{
                data: @json($statusValues),
                backgroundColor: @json($statusLabels).map(s => statusHex[s] || '#6c757d'),
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 62,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 14, font: { size: 11 } }
                },
                tooltip: { backgroundColor: '#1a1a2e', cornerRadius: 8 }
            }
        }
    });

    // Bar kategori
    new Chart(document.getElementById('chartKategori'), {
        type: 'bar',
        data: {
            labels: @json($kategoriLabels),
            datasets: [{
                label: 'Jumlah Barang',
                data: @json($kategoriValues),
                backgroundColor: 'rgba(13,110,253,.75)',
                hoverBackgroundColor: 'rgba(102,16,242,.85)',
                borderRadius: 6,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Bar horizontal top item
    new Chart(document.getElementById('chartTopItem'), {
        type: 'bar',
        data: {
            labels: @json($topLabels),
            datasets: [{
                label: 'Jumlah Dipinjam',
                data: @json($topValues),
                backgroundColor: 'rgba(255,193,7,.8)',
                hoverBackgroundColor: 'rgba(253,126,20,.9)',
                borderRadius: 6,
                maxBarThickness: 26
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1a1a2e', cornerRadius: 8 }
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,.05)' } },
                y: { grid: { display: false } }
            }
        }
    });
</script>
@stop
