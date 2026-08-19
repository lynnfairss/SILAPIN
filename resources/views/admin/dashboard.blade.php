@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-end">
        <div>
            <h1>Dashboard</h1>
            <p class="text-muted mb-0 small">Rekap inventaris &amp; permohonan SILAPIN</p>
        </div>
    </div>
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

    $greeting = now()->format('H') < 12 ? 'Selamat Pagi' : (now()->format('H') < 15 ? 'Selamat Siang' : (now()->format('H') < 18 ? 'Selamat Sore' : 'Selamat Malam'));
    $today = now()->translatedFormat('l, d F Y');

    $hasDate = (!empty($filters['dari']) && $filters['dari'] !== 'null') || (!empty($filters['sampai']) && $filters['sampai'] !== 'null');
    $hasActiveFilter = $hasDate
        || !empty($filters['status'])
        || !empty($filters['instansi_id'])
        || !empty($filters['bulan'])
        || !empty($filters['tahun']);

    $rangeDesc = 'Semua data';
    if (!empty($filters['dari']) && !empty($filters['sampai']) && $filters['dari'] !== 'null' && $filters['sampai'] !== 'null') {
        $rangeDesc = \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y')
            . ' – ' . \Carbon\Carbon::parse($filters['sampai'])->translatedFormat('d M Y');
    } elseif (!empty($filters['dari']) && $filters['dari'] !== 'null') {
        $rangeDesc = 'Dari ' . \Carbon\Carbon::parse($filters['dari'])->translatedFormat('d M Y');
    }

    $filterInstansi = null;
    if (!empty($filters['instansi_id'])) {
        $filterInstansi = $instansiList->firstWhere('id', (int) $filters['instansi_id'])?->nama_instansi ?? 'Instansi';
    }
@endphp

{{-- ============ HERO ============ --}}
<div class="dash-hero mb-3">
    <div class="dash-hero-inner">
        <div>
            <span class="dash-hero-chip"><i class="fas fa-tachometer-alt"></i> SILAPIN</span>
            <h2 class="dash-hero-title">{{ $greeting }}, {{ Auth::user()->name }}</h2>
            <p class="dash-hero-sub">
                <i class="fas fa-calendar-alt me-1"></i>{{ $today }}
                <span class="mx-2" style="opacity:.5">|</span>
                {{ $rangeDesc }}
            </p>
            <div class="dash-hero-badges">
                @if($hasDate)
                    <span class="dash-hero-badge"><i class="fas fa-calendar-week"></i>Rentang: {{ $rangeDesc }}</span>
                @endif
                @if(!empty($filters['status']))
                    <span class="dash-hero-badge"><i class="fas fa-circle" style="color:{{ $statusHex[$filters['status']] ?? '#adb5bd' }}"></i>Status: {{ $filters['status'] }}</span>
                @endif
                @if($filterInstansi)
                    <span class="dash-hero-badge"><i class="fas fa-building"></i>{{ $filterInstansi }}</span>
                @endif
                @if(!empty($filters['bulan']))
                    <span class="dash-hero-badge"><i class="fas fa-calendar-alt"></i>Bulan: {{ \Carbon\Carbon::create()->month((int) $filters['bulan'])->translatedFormat('F') }}</span>
                @endif
                @if(!empty($filters['tahun']))
                    <span class="dash-hero-badge"><i class="fas fa-calendar"></i>{{ $filters['tahun'] }}</span>
                @endif
                @if(!$hasActiveFilter)
                    <span class="dash-hero-badge"><i class="fas fa-globe"></i>Semua data</span>
                @endif
            </div>
        </div>
        <div class="dash-hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-label">Total Permohonan</div>
                <div class="hero-stat-value">{{ number_format($totalPermohonan, 0, ',', '.') }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">Menunggu</div>
                <div class="hero-stat-value">{{ number_format($statusCounts['Menunggu'] ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-label">Item Dipinjam</div>
                <div class="hero-stat-value">{{ number_format($totalItemDipinjam, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ============ FILTER ============ --}}
<div class="card card-flat filter-box mb-3">
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

{{-- ============ KPI ============ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card kpi-accent-blue d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#0d6efd,#0dcaf0);">
                <i class="fas fa-box"></i>
            </div>
            <div>
                <div class="kpi-label">Total Inventaris</div>
                <div class="kpi-value">{{ number_format($totalInventaris, 0, ',', '.') }}</div>
                <div class="kpi-sub text-muted small mt-1">Aset terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card kpi-accent-purple d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#0d6efd,#6610f2);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="kpi-label">Permohonan (Filter)</div>
                <div class="kpi-value">{{ number_format($totalPermohonan, 0, ',', '.') }}</div>
                <div class="kpi-sub text-muted small mt-1">Item Dipinjam: {{ number_format($totalItemDipinjam, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card kpi-accent-pink d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#6610f2,#d63384);">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <div class="kpi-label">Instansi</div>
                <div class="kpi-value">{{ number_format($totalInstansi, 0, ',', '.') }}</div>
                <div class="kpi-sub text-muted small mt-1">Terkait</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="kpi-card kpi-accent-teal d-flex align-items-center gap-3">
            <div class="kpi-icon" style="background: linear-gradient(135deg,#20c997,#0dcaf0);">
                <i class="fas fa-tags"></i>
            </div>
            <div>
                <div class="kpi-label">Kategori</div>
                <div class="kpi-value">{{ number_format($totalKategori, 0, ',', '.') }}</div>
                <div class="kpi-sub text-muted small mt-1">Barang</div>
            </div>
        </div>
    </div>
</div>

{{-- ============ STATUS: DOUGHNUT + BARS ============ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card card-flat h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Distribusi Status</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap doughnut-wrap">
                    <canvas id="chartStatus"></canvas>
                    <div class="doughnut-center">
                        <div class="dc-value">{{ number_format($totalPermohonan, 0, ',', '.') }}</div>
                        <div class="dc-label">Total</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-flat h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Status Permohonan</h3>
            </div>
            <div class="card-body">
                @foreach($statusList as $st)
                <div class="status-bar-row">
                    <span class="status-bar-label">
                        <span class="badge badge-soft-{{ $statusColor[$st] }}">{{ $st }}</span>
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
                    <span class="fw-bold">{{ number_format($totalPermohonan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ TREN + TOP ITEM ============ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
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
    <div class="col-lg-4">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy me-2 text-primary"></i>Item Paling Dipinjam</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap-sm">
                    <canvas id="chartTopItem"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ KATEGORI + PERMOHONAN TERBARU ============ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="card card-flat h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Inventaris per Kategori</h3>
            </div>
            <div class="card-body">
                <div class="chart-wrap-sm">
                    <canvas id="chartKategori"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card card-flat h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list me-2 text-primary"></i>Permohonan Terbaru</h3>
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
                            <th>Tanggal</th>
                            <th class="text-center">Item</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonan as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->nomor_permohonan }}</td>
                            <td>{{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary">{{ $p->detailPermohonan->sum('jumlah') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $statusColor[$p->status] ?? 'secondary' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Tidak ada permohonan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============ RECAP TABEL ============ --}}
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
                                <span class="badge badge-soft-{{ $statusColor[$st] }}">
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
                            <td colspan="{{ count($statusList) + 2 }}" class="text-center text-muted py-3">Tidak ada data untuk rentang ini.</td>
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
                backgroundColor: 'rgba(102,16,242,.8)',
                hoverBackgroundColor: 'rgba(13,110,253,.9)',
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
