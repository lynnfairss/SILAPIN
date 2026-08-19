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
        padding: 18px 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        transition: transform .25s ease, box-shadow .25s ease;
        border: 1px solid #eef0f4;
        height: 100%;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(13,110,253,.12);
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0d6efd, #6610f2);
    }
    .kpi-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
        color: #fff;
        box-shadow: 0 6px 14px rgba(102,16,242,.25);
    }
    .kpi-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6c757d;
        font-weight: 600;
    }
    .kpi-value {
        font-size: 1.7rem;
        font-weight: 700;
        color: #1a1a2e;
        line-height: 1.1;
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
        border-radius: 20px;
        font-size: .8rem;
        padding: .3rem .85rem;
    }
    .preset-btn.active {
        background: linear-gradient(90deg, #0d6efd, #6610f2);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 4px 12px rgba(102,16,242,.3);
    }
    .preset-btn:not(.active) {
        color: #495057;
        border-color: #dee2e6;
        background: #fff;
    }
    .preset-btn:not(.active):hover {
        background: #f0f0f5;
    }

    .btn-gradient {
        background: linear-gradient(90deg, #0d6efd, #6610f2);
        border: none;
        color: #fff;
        box-shadow: 0 4px 12px rgba(102,16,242,.28);
        border-radius: 8px;
    }
    .btn-gradient:hover { color: #fff; filter: brightness(1.05); }

    .filter-box .form-control-sm, .filter-box .form-select-sm {
        border-radius: 8px;
        border-color: #dee2e6;
    }
    .filter-box .form-control-sm:focus, .filter-box .form-select-sm:focus {
        border-color: #6610f2;
        box-shadow: 0 0 0 3px rgba(102,16,242,.1);
    }

    .status-bar-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }
    .status-bar-label {
        width: 120px;
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

    .chart-wrap { position: relative; height: 300px; }
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

    .total-row td { font-weight: 700; background: #f7f8fc !important; }

    @media (max-width: 576px) {
        .status-bar-label { width: 100px; }
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
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-flat filter-box">
            <div class="card-header d-flex align-items-center justify-content-between py-3">
                <h3 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Recap</h3>
                <button class="btn btn-tool" type="button" data-toggle="collapse" data-target="#filterBody" aria-expanded="true" aria-controls="filterBody">
                    <i class="fas fa-minus" id="filterToggleIcon"></i>
                </button>
            </div>
            <div id="filterBody" class="collapse show">
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted mb-2">Preset Cepat</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($presets as $key => [$name])
                                <button type="submit" name="preset" value="{{ $key }}"
                                    class="btn preset-btn {{ $preset === $key ? 'active' : '' }}">
                                    {{ $name }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Dari Tanggal</label>
                                <input type="date" name="dari" value="{{ old('dari', $filters['dari'] ?? '') }}"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Sampai Tanggal</label>
                                <input type="date" name="sampai" value="{{ old('sampai', $filters['sampai'] ?? '') }}"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Bulan</label>
                                <select name="bulan" class="form-select form-select-sm">
                                    <option value="">Semua Bulan</option>
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $filters['bulan'] == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Tahun</label>
                                <select name="tahun" class="form-select form-select-sm">
                                    <option value="">Semua Tahun</option>
                                    @foreach($tahunList as $t)
                                    <option value="{{ $t }}" {{ $filters['tahun'] == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-sm btn-gradient w-100"><i class="fas fa-search me-1"></i>Terapkan</button>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Status</option>
                                    @foreach($statusList as $st)
                                    <option value="{{ $st }}" {{ $filters['status'] === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Instansi</label>
                                <select name="instansi_id" class="form-select form-select-sm"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Semua Instansi</option>
                                    @foreach($instansiList as $ins)
                                    <option value="{{ $ins->id }}" {{ $filters['instansi_id'] == $ins->id ? 'selected' : '' }}>
                                        {{ $ins->nama_instansi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Granularitas Recap</label>
                                <select name="per" class="form-select form-select-sm"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="auto" {{ $per === 'auto' ? 'selected' : '' }}>Otomatis</option>
                                    <option value="hari" {{ $per === 'hari' ? 'selected' : '' }}>Per Hari</option>
                                    <option value="bulan" {{ $per === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#0d6efd,#0dcaf0);">
                <i class="fas fa-box"></i>
            </div>
            <div>
                <div class="kpi-label">Total Inventaris</div>
                <div class="kpi-value">{{ number_format($totalInventaris, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#0d6efd,#6610f2);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="kpi-label">Permohonan (Filter)</div>
                <div class="kpi-value">{{ number_format($totalPermohonan, 0, ',', '.') }}</div>
                <div class="kpi-sub text-muted small">Item Dipinjam: {{ number_format($totalItemDipinjam, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#6610f2,#d63384);">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <div class="kpi-label">Instansi</div>
                <div class="kpi-value">{{ number_format($totalInstansi, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#20c997,#0dcaf0);">
                <i class="fas fa-tags"></i>
            </div>
            <div>
                <div class="kpi-label">Kategori</div>
                <div class="kpi-value">{{ number_format($totalKategori, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Status breakdown (progress bars) --}}
<div class="row mb-3">
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
<div class="row g-3 mb-3">
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
<div class="row g-3 mb-3">
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
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table me-2 text-primary"></i>Recap Permohonan per {{ ucfirst($per) }}</h3>
            </div>
            <div class="card-body table-responsive p-0">
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
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list me-2 text-primary"></i>Permohonan Terbaru (Maks. 20)</h3>
                <div class="card-tools">
                    <a href="{{ route('permohonan.index') }}" class="btn btn-tool btn-sm">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
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
