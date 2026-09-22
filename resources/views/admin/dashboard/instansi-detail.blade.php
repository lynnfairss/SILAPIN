@extends('adminlte::page')

@section('title', 'Detail Peminjaman - ' . $instansi->nama_instansi)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-building me-2 text-primary"></i>{{ $instansi->nama_instansi }}</h1>
            <p class="text-muted mb-0 small">Detail barang yang pernah dipinjam oleh instansi ini</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
    </div>
@endsection

@section('css')
<style>
    .card-flat { border-radius: 14px; border: 1px solid #eef0f4; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
    .card-flat .card-header { background: transparent; border-bottom: 1px solid #eef0f4; border-radius: 14px 14px 0 0; }
    .card-flat .card-title { font-weight: 700; color: #1a1a2e; font-size: .95rem; }
    .table-modern thead th { background: #1a1a2e; color: #fff; font-weight: 600; font-size: .78rem; text-transform: uppercase; letter-spacing: .4px; border: none; padding: .7rem .9rem; white-space: nowrap; }
    .table-modern tbody td { padding: .65rem .9rem; font-size: .88rem; vertical-align: middle; }
    .table-modern tbody tr { border-bottom: 1px solid #f1f3f7; }
    .table-modern tbody tr:hover { background: rgba(13,110,253,.04); }
    .badge-soft { font-weight: 600; border-radius: 20px; padding: .3em .75em; font-size: .75rem; }
    .summary-card { background: #fff; border-radius: 14px; border: 1px solid #eef0f4; box-shadow: 0 2px 12px rgba(0,0,0,.05); text-align: center; padding: 20px; }
    .summary-card .summary-icon { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #fff; margin-bottom: 8px; }
    .summary-card .summary-value { font-size: 1.6rem; font-weight: 700; color: #1a1a2e; }
    .summary-card .summary-label { font-size: .75rem; color: #6c757d; text-transform: uppercase; letter-spacing: .4px; font-weight: 600; }
    .rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; font-weight: 700; font-size: .75rem; }
    .rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
    .rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; }
    .rank-3 { background: linear-gradient(135deg, #d97706, #b45309); color: #fff; }
    .rank-n { background: #e2e8f0; color: #475569; }
</style>
@endsection

@section('content')

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"><i class="fas fa-file-alt"></i></div>
            <div class="summary-value">{{ $totalPermohonan }}</div>
            <div class="summary-label">Total Permohonan</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-boxes"></i></div>
            <div class="summary-value">{{ $totalBarang }}</div>
            <div class="summary-label">Total Barang Dipinjam</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="summary-card">
            <div class="summary-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="fas fa-users"></i></div>
            <div class="summary-value">{{ $peminjamUniq }}</div>
            <div class="summary-label">Peminjam Unik</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Barang paling sering dipinjam --}}
    <div class="col-lg-5">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-fire me-2 text-warning"></i>Barang Paling Sering Dipinjam</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px">#</th>
                            <th>Barang</th>
                            <th class="text-center" style="width:80px">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangList as $i => $b)
                        <tr>
                            <td class="text-center">
                                <span class="rank-badge {{ $i < 3 ? 'rank-' . ($i + 1) : 'rank-n' }}">{{ $i + 1 }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $b['nama'] }}</div>
                                <small class="text-muted">{{ $b['kode'] }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary badge-soft text-white">{{ $b['total'] }}x</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Belum ada barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Daftar permohonan --}}
    <div class="col-lg-7">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list me-2 text-primary"></i>Daftar Permohonan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Peminjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonan as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->nomor_permohonan }}</td>
                            <td>{{ $p->nama_peminjam }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_kembali)->translatedFormat('d M Y') }}</td>
                            <td class="text-center">
                                @php
                                    $sc = ['Menunggu'=>'warning','Disetujui'=>'success','Ditolak'=>'danger','Dipinjam'=>'info','Dikembalikan'=>'secondary'];
                                @endphp
                                <span class="badge bg-{{ $sc[$p->status] ?? 'secondary' }} badge-soft text-white">{{ $p->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada permohonan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Detail barang yang dipinjam (sort paling sering di atas) --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes me-2 text-primary"></i>Detail Barang yang Dipinjam</h3>
                <div class="card-tools"><small class="text-muted">Diurutkan dari barang paling sering dipinjam</small></div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px">No</th>
                            <th>Barang</th>
                            <th>Kode</th>
                            <th class="text-center" style="width:70px">Jumlah</th>
                            <th>Peminjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailList as $i => $d)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $d['barang'] }}</td>
                            <td><small class="text-muted">{{ $d['kode'] }}</small></td>
                            <td class="text-center">
                                <span class="badge bg-primary badge-soft text-white">{{ $d['jumlah'] }}</span>
                            </td>
                            <td>{{ $d['peminjam'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($d['tanggal_pinjam'])->translatedFormat('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($d['tanggal_kembali'])->translatedFormat('d M Y') }}</td>
                            <td class="text-center">
                                @php
                                    $sc = ['Menunggu'=>'warning','Disetujui'=>'success','Ditolak'=>'danger','Dipinjam'=>'info','Dikembalikan'=>'secondary'];
                                @endphp
                                <span class="badge bg-{{ $sc[$d['status']] ?? 'secondary' }} badge-soft text-white">{{ $d['status'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
