<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Berhasil - SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/peminjam.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('website') }}">
            <i class="fas fa-boxes-stacked me-2"></i>SILAPIN
        </a>
        <div>
            <a href="{{ route('peminjam.cek-status') }}" class="btn btn-outline-light btn-sm me-2">
                <i class="fas fa-search me-1"></i>Cek Status
            </a>
            <a href="{{ route('peminjam.form') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i>Ajukan Lagi
            </a>
        </div>
    </div>
</nav>

<div class="container" style="margin-top: 100px;">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            @if(session('success'))
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
            @endif

            <div class="card shadow-sm text-center border-0">
                <div class="card-body py-5">
                    <div class="text-success mb-4">
                        <i class="fas fa-check-circle" style="font-size:5rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Permohonan Berhasil Dikirim!</h3>
                    <p class="text-muted mb-4">Permohonan Anda telah terdaftar dan sedang diproses.</p>

                    <div class="bg-light rounded-3 p-4 mb-4 d-inline-block">
                        <small class="text-muted d-block mb-1">Nomor Permohonan</small>
                        <h4 class="fw-bold text-primary mb-0">{{ $permohonan->nomor_permohonan }}</h4>
                    </div>

                    <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                        <a href="{{ route('peminjam.download-surat', $permohonan->id) }}" class="btn btn-primary px-4">
                            <i class="fas fa-download me-2"></i>Download Surat Permohonan
                        </a>
                        <a href="{{ route('peminjam.cek-status', ['nomor' => $permohonan->nomor_permohonan]) }}" class="btn btn-outline-primary px-4">
                            <i class="fas fa-search me-2"></i>Cek Status
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-start">
                        <h6 class="fw-bold mb-3">Ringkasan Permohonan</h6>
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th style="width:180px" class="bg-light">Pemohon</th>
                                <td>{{ $permohonan->nama_peminjam }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">NIK</th>
                                <td>{{ $permohonan->nik }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Instansi</th>
                                <td>{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tanggal Pinjam</th>
                                <td>{{ $permohonan->tanggal_pinjam }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Tanggal Kembali</th>
                                <td>{{ $permohonan->tanggal_kembali }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Status</th>
                                <td><span class="badge bg-warning">Menunggu</span></td>
                            </tr>
                        </table>

                        <h6 class="fw-bold mt-4 mb-2">Barang yang Dipinjam:</h6>
                        <ol class="mb-0">
                            @foreach($permohonan->detailPermohonan as $detail)
                            <li>{{ $detail->inventaris->nama_barang ?? '-' }} ({{ $detail->jumlah }} unit)</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

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

</body>
</html>
