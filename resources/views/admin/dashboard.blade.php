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
        padding: 22px 26px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        transition: transform .2s ease, box-shadow .2s ease;
        border: 1px solid #eef0f4;
        height: 100%;
        min-height: 110px;
        gap: 28px;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,.08);
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--kpi-color, #0d6efd);
    }
    .kpi-icon {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,.1);
    }
    .kpi-body {
        flex: 1;
        min-width: 0;
    }
    .kpi-label {
        font-size: .73rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .kpi-value {
        font-size: 1.65rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }
    .kpi-sub {
        font-size: .75rem;
        color: #6c757d;
        margin-top: 4px;
        padding-top: 4px;
        border-top: 1px solid #eef0f4;
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
    }
    .card-flat .card-title {
        font-weight: 700;
        color: #1a1a2e;
        font-size: .95rem;
    }

    .preset-btn {
        border-radius: 8px;
        font-size: .82rem;
        padding: .4rem 1rem;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #495057;
        transition: all .15s ease;
    }
    .preset-btn:hover {
        background: #f0f0f5;
        border-color: #adb5bd;
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

    .filter-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }
    .filter-table th {
        background: #f1f3f5;
        font-size: .82rem;
        font-weight: 600;
        color: #212529;
        padding: 10px 14px;
        white-space: nowrap;
        width: 180px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    .filter-table td {
        padding: 8px 14px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        color: #212529;
    }
    .filter-table tr:last-child th,
    .filter-table tr:last-child td {
        border-bottom: none;
    }
    .filter-table .filter-preset td {
        background: #f1f3f5;
    }
    .filter-table tr:nth-child(even) td {
        background: #f8f9fa;
    }
    .filter-table .filter-actions td {
        background: #fff;
        border-bottom: none;
    }
    .filter-box .form-control,
    .filter-box .form-select {
        border-radius: 8px;
        border-color: #dee2e6;
        height: 38px;
        font-size: .85rem;
    }
    .filter-box .form-control:focus,
    .filter-box .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12);
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
        background: #1a1a2e;
        color: #fff;
        font-weight: 600;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        border: none;
        padding: .7rem .9rem;
        white-space: nowrap;
    }
    .table-modern tbody td {
        padding: .65rem .9rem;
        font-size: .88rem;
        vertical-align: middle;
    }
    .table-modern tbody tr { border-bottom: 1px solid #f1f3f7; }
    .table-modern tbody tr:hover { background: rgba(13,110,253,.04); }
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

{{-- Filter Panel --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-flat filter-box">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <h3 class="card-title mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filter Recap</h3>
                <button class="btn btn-tool" type="button" data-toggle="collapse" data-target="#filterBody" aria-expanded="true" aria-controls="filterBody">
                    <i class="fas fa-minus" id="filterToggleIcon"></i>
                </button>
            </div>
            <div id="filterBody" class="collapse show">
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                        <table class="filter-table">
                            <tr class="filter-preset">
                                <th><i class="fas fa-clock me-1 text-muted"></i> Pilih Rentang Waktu</th>
                                <td colspan="3">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($presets as $key => [$name])
                                        <button type="submit" name="preset" value="{{ $key }}"
                                            class="btn preset-btn {{ $preset === $key ? 'active' : '' }}">
                                            {{ $name }}
                                        </button>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar me-1 text-muted"></i> Dari Tanggal</th>
                                <td>
                                    <input type="date" name="dari" value="{{ old('dari', $filters['dari'] ?? '') }}" class="form-control">
                                </td>
                                <th><i class="fas fa-calendar me-1 text-muted"></i> Sampai Tanggal</th>
                                <td>
                                    <input type="date" name="sampai" value="{{ old('sampai', $filters['sampai'] ?? '') }}" class="form-control">
                                </td>
                            </tr>
                            <tr>
                                <th>Bulan</th>
                                <td>
                                    <select name="bulan" class="form-select">
                                        <option value="">Semua Bulan</option>
                                        @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $filters['bulan'] == $m ? 'selected' : '' }}>
                                            {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                        @endfor
                                    </select>
                                </td>
                                <th>Tahun</th>
                                <td>
                                    <select name="tahun" class="form-select">
                                        <option value="">Semua Tahun</option>
                                        @foreach($tahunList as $t)
                                        <option value="{{ $t }}" {{ $filters['tahun'] == $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Status</option>
                                        @foreach($statusList as $st)
                                        <option value="{{ $st }}" {{ $filters['status'] === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <th>Instansi</th>
                                <td>
                                    <select name="instansi_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Instansi</option>
                                        @foreach($instansiList as $ins)
                                        <option value="{{ $ins->id }}" {{ $filters['instansi_id'] == $ins->id ? 'selected' : '' }}>
                                            {{ $ins->nama_instansi }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Granularitas Recap</th>
                                <td colspan="3">
                                    <select name="per" class="form-select" style="max-width: 250px;" onchange="this.form.submit()">
                                        <option value="auto" {{ $per === 'auto' ? 'selected' : '' }}>Otomatis</option>
                                        <option value="hari" {{ $per === 'hari' ? 'selected' : '' }}>Per Hari</option>
                                        <option value="bulan" {{ $per === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="filter-actions">
                                <th></th>
                                <td colspan="3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Terapkan Filter
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
@php
$kpiCards = [
    ['icon' => 'fa-box', 'label' => 'Total Inventaris', 'value' => $totalInventaris, 'color' => '#0d6efd'],
    ['icon' => 'fa-file-alt', 'label' => 'Permohonan', 'value' => $totalPermohonan, 'color' => '#6610f2', 'sub' => 'Item Dipinjam: ' . number_format($totalItemDipinjam, 0, ',', '.')],
    ['icon' => 'fa-building', 'label' => 'Instansi', 'value' => $totalInstansi, 'color' => '#d63384'],
    ['icon' => 'fa-tags', 'label' => 'Kategori', 'value' => $totalKategori, 'color' => '#20c997'],
];
@endphp
<div class="row g-3 mb-4">
    @foreach($kpiCards as $kpi)
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card d-flex align-items-center gap-4" style="--kpi-color: {{ $kpi['color'] }}">
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
        </div>
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

{{-- Recap tabel --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table me-2 text-primary"></i>Recap Permohonan per {{ ucfirst($per) }}</h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>{{ $recapColumns[0] }}</th>
                            @foreach($statusList as $st)
                            <th class="text-center">{{ $st }}</th>
                            @endforeach
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recapRows as $row)
                        <tr>
                            <td class="fw-bold">{{ $row['periode'] }}</td>
                            @foreach($statusList as $st)
                            <td class="text-center">
                                @if($row['status'][$st] > 0)
                                <span class="badge bg-{{ $statusColor[$st] }} badge-soft text-white">
                                    {{ $row['status'][$st] }}
                                </span>
                                @else
                                <span class="text-muted">0</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center fw-bold">{{ array_sum($row['status']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Tidak ada data untuk rentang ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($recapRows))
                    <tfoot>
                        <tr class="total-row">
                            <td>Total</td>
                            @foreach($statusList as $st)
                            <td class="text-center">{{ $recapTotals[$st] }}</td>
                            @endforeach
                            <td class="text-center">{{ $recapGrandTotal }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
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
