<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status - SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/peminjam.css') }}" rel="stylesheet">
    <style>
        .badge-status { font-size: .85rem; padding: .4em .8em; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('website') }}">
            <i class="fas fa-boxes-stacked me-2"></i>SILAPIN
        </a>
        <div>
            <a href="{{ route('peminjam.form') }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-plus me-1"></i>Ajukan Peminjaman
            </a>
        </div>
    </div>
</nav>

<div class="container" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
            @endif

            <div class="text-center mb-4">
                <h3 class="fw-bold"><i class="fas fa-search text-primary me-2"></i>Cek Status Permohonan</h3>
                <p class="text-muted">Masukkan nomor permohonan untuk melihat status terkini</p>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="{{ route('peminjam.cek-status') }}" id="formCek">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white"><i class="fas fa-file-invoice text-muted"></i></span>
                            <input type="text" name="nomor" class="form-control" placeholder="Masukkan nomor permohonan..." value="{{ request('nomor') }}" autocomplete="off" required>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="fas fa-search me-1"></i>Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($permohonan))
            <div class="card shadow-sm" style="animation: fadeStep .4s ease;">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-file-invoice text-primary me-2"></i>
                            {{ $permohonan->nomor_permohonan }}
                        </h5>
                        @php
                            $statusMap = [
                                'Menunggu' => ['badge' => 'warning', 'icon' => 'fa-clock'],
                                'Disetujui' => ['badge' => 'success', 'icon' => 'fa-check-circle'],
                                'Ditolak' => ['badge' => 'danger', 'icon' => 'fa-times-circle'],
                                'Dipinjam' => ['badge' => 'primary', 'icon' => 'fa-box'],
                                'Dikembalikan' => ['badge' => 'secondary', 'icon' => 'fa-undo'],
                            ];
                            $status = $statusMap[$permohonan->status] ?? ['badge' => 'secondary', 'icon' => 'fa-question'];
                        @endphp
                        <span class="badge bg-{{ $status['badge'] }} badge-status">
                            <i class="fas {{ $status['icon'] }} me-1"></i>{{ $permohonan->status }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Pemohon</small>
                            <strong>{{ $permohonan->nama_peminjam }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Instansi</small>
                            <strong>{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Tanggal Pinjam</small>
                            <strong>{{ $permohonan->tanggal_pinjam }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Tanggal Kembali</small>
                            <strong>{{ $permohonan->tanggal_kembali }}</strong>
                        </div>
                    </div>

                    @if($permohonan->detailPermohonan->count())
                    <h6 class="fw-bold mb-2"><i class="fas fa-boxes me-2 text-primary"></i>Barang Dipinjam</h6>
                    <table class="table table-bordered table-sm mb-4">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permohonan->detailPermohonan as $i => $detail)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $detail->inventaris->nama_barang ?? '-' }}</td>
                                <td>{{ $detail->jumlah }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    <div class="mt-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-history me-2 text-primary"></i>Riwayat Status</h6>
                        <div class="status-timeline">
                            <div class="status-item {{ in_array($permohonan->status, ['Menunggu','Disetujui','Ditolak','Dipinjam','Dikembalikan']) ? 'completed' : '' }}">
                                <div class="status-dot"></div>
                                <strong>Permohonan Diajukan</strong>
                                <div class="text-muted small">{{ $permohonan->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="status-item {{ in_array($permohonan->status, ['Menunggu','Disetujui','Ditolak','Dipinjam','Dikembalikan']) ? 'active' : '' }} {{ $permohonan->status == 'Menunggu' ? 'active' : '' }}">
                                <div class="status-dot"></div>
                                <strong>Menunggu Persetujuan</strong>
                                <div class="text-muted small">Permohonan sedang diproses admin</div>
                            </div>
                            @if($permohonan->status == 'Disetujui' || $permohonan->status == 'Dipinjam' || $permohonan->status == 'Dikembalikan')
                            <div class="status-item completed {{ $permohonan->status == 'Disetujui' ? 'active' : '' }}">
                                <div class="status-dot"></div>
                                <strong>Disetujui</strong>
                                <div class="text-muted small">Silakan ambil barang sesuai jadwal</div>
                            </div>
                            @endif
                            @if($permohonan->status == 'Ditolak')
                            <div class="status-item rejected active">
                                <div class="status-dot"></div>
                                <strong>Ditolak</strong>
                                <div class="text-muted small">{{ $permohonan->catatan_admin ?? 'Permohonan ditolak oleh admin.' }}</div>
                            </div>
                            @endif
                            @if($permohonan->status == 'Dipinjam')
                            <div class="status-item completed active">
                                <div class="status-dot"></div>
                                <strong>Dipinjam</strong>
                                <div class="text-muted small">Barang sedang dipinjam</div>
                            </div>
                            @endif
                            @if($permohonan->status == 'Dikembalikan')
                            <div class="status-item completed active">
                                <div class="status-dot"></div>
                                <strong>Dikembalikan</strong>
                                <div class="text-muted small">Barang telah dikembalikan</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white text-end">
                    <a href="{{ route('peminjam.download-surat', $permohonan->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-download me-1"></i>Download Surat
                    </a>
                </div>
            </div>
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('website') }}" class="text-decoration-none text-muted">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0 small">&copy; 2026 SILAPIN - Sistem Informasi Peminjaman Inventaris</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
