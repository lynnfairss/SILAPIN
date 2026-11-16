<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status - SILAPIN</title>
    <script src="{{ asset('js/security.js') }}?v={{ filemtime(public_path('js/security.js')) ?: 1 }}" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/silapin-theme.css') }}?v={{ filemtime(public_path('css/silapin-theme.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/peminjam.css') }}?v={{ filemtime(public_path('css/peminjam.css')) }}" rel="stylesheet">
    <style>
        .badge-status { font-size: .85rem; padding: .35em .75em; border-radius: 6px; }

        /* Card styling */
        .cs-card, .cs-result-card {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .cs-nomor {
            font-size: 1.05rem;
            color: #0f172a;
            font-family: Consolas, Monaco, monospace;
            letter-spacing: -.3px;
        }

        .cs-info-box {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 10px;
            padding: 9px 12px;
            height: 100%;
        }

        .cs-info-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
            color: #64748b !important;
            margin-bottom: 2px;
        }

        .cs-info-value {
            font-size: .9rem;
            color: #0f172a;
            font-weight: 700;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
            line-height: 1.25;
        }

        .cs-section-title {
            font-size: .92rem;
            color: #1e293b;
        }

        .cs-table th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #475569;
            background: #f1f5f9 !important;
            padding: 7px 10px;
        }

        .cs-table td {
            font-size: .84rem;
            padding: 7px 10px;
            vertical-align: middle;
        }

        .cs-timeline {
            padding-left: 20px;
        }

        .cs-timeline .status-item {
            padding-bottom: 16px;
            padding-left: 16px;
        }

        .cs-tl-title {
            font-size: .86rem;
            color: #0f172a;
        }

        .cs-tl-desc {
            font-size: .76rem;
        }

        .cs-qr-box #cekStatusBarcode {
            width: 140px;
            height: 140px;
            display: block;
            margin: 0 auto .4rem;
        }

        .cs-qr-box #cekStatusBarcode canvas,
        .cs-qr-box #cekStatusBarcode img {
            max-width: 100%;
            height: auto !important;
            margin: 0 auto;
        }

        .cs-qr-label {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            font-weight: 700;
            color: #64748b;
        }

        .cs-qr-number {
            font-family: Consolas, Monaco, monospace;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .cs-qr-help {
            font-size: .72rem;
        }

        /* ===== MOBILE TWEAKS (<= 576px) ===== */
        @media (max-width: 576px) {
            .sp-pagehead {
                padding: 74px 0 20px !important;
            }
            .sp-pagehead h1 {
                font-size: 1.25rem !important;
                margin-bottom: 2px !important;
            }
            .sp-pagehead p {
                font-size: .78rem !important;
                line-height: 1.3 !important;
            }
            .sp-pagehead__crumb {
                font-size: .7rem !important;
                margin-bottom: 4px !important;
            }
            .sp-page-body {
                padding: 12px 0 28px !important;
            }

            /* Search Box */
            .cs-search-body {
                padding: .75rem !important;
            }
            .cs-input-group {
                border-radius: 9px;
            }
            .cs-input-icon {
                padding: .4rem .6rem !important;
                font-size: .85rem !important;
            }
            .cs-input-text {
                font-size: .82rem !important;
                padding: .4rem .6rem !important;
            }
            .cs-btn-cari {
                padding: .4rem .8rem !important;
                font-size: .82rem !important;
            }

            /* Result Card */
            .cs-card-header {
                padding: .7rem .85rem !important;
            }
            .cs-header-icon {
                font-size: .88rem !important;
            }
            .cs-nomor {
                font-size: .88rem !important;
            }
            .badge-status {
                font-size: .7rem !important;
                padding: .25em .55em !important;
            }
            .cs-card-body {
                padding: .85rem !important;
            }

            /* Info Box: 2 kolom padat dan rapi */
            .cs-info-box {
                padding: 6px 8px !important;
                border-radius: 8px !important;
            }
            .cs-info-label {
                font-size: .64rem !important;
            }
            .cs-info-value {
                font-size: .8rem !important;
            }

            /* Table */
            .cs-section-title {
                font-size: .84rem !important;
                margin-bottom: .35rem !important;
            }
            .cs-table th {
                font-size: .7rem !important;
                padding: 5px 6px !important;
            }
            .cs-table td {
                font-size: .76rem !important;
                padding: 5px 6px !important;
            }

            /* Timeline */
            .cs-timeline {
                padding-left: 14px !important;
            }
            .cs-timeline .status-item {
                padding-bottom: 12px !important;
                padding-left: 12px !important;
            }
            .cs-timeline .status-dot {
                width: 12px !important;
                height: 12px !important;
                left: -7px !important;
                top: 2px !important;
            }
            .cs-tl-title {
                font-size: .78rem !important;
            }
            .cs-tl-desc {
                font-size: .7rem !important;
            }

            /* QR */
            .cs-qr-box {
                padding-top: .7rem !important;
                margin-top: .7rem !important;
            }
            .cs-qr-box #cekStatusBarcode {
                width: 120px !important;
                height: 120px !important;
            }
            .cs-qr-number {
                font-size: .88rem !important;
            }

            /* Back link */
            .cs-back-link {
                font-size: .8rem !important;
            }

            .sp-footer--compact {
                padding: 16px 0 !important;
            }
            .sp-footer--compact .sp-footer__brand {
                font-size: .9rem !important;
            }
            .sp-footer--compact p {
                font-size: .7rem !important;
            }
        }
    </style>
</head>
<body>

@include('partials.public-navbar', ['active' => 'status'])

<div class="sp-pagehead">
    <div class="container position-relative" style="z-index:2;">
        <div class="sp-pagehead__crumb">
            <a href="{{ route('website') }}">Home</a>
            <i class="fas fa-chevron-right mx-1" style="font-size:.6rem;"></i>
            <span>Cek Status</span>
        </div>
        <h1>Cek Status Permohonan</h1>
        <p>Masukkan nomor permohonan untuk melihat status terkini.</p>
    </div>
</div>

<div class="container sp-page-body">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
            @endif

            <div class="card shadow-sm mb-3 mb-sm-4 cs-card">
                <div class="card-body p-3 p-sm-4 cs-search-body">
                    <form method="GET" action="{{ route('peminjam.cek-status') }}" id="formCek">
                        @if(request('token'))
                            <input type="hidden" name="token" value="{{ request('token') }}">
                        @endif
                        <div class="input-group input-group-lg cs-input-group">
                            <span class="input-group-text bg-white cs-input-icon"><i class="fas fa-file-invoice text-muted"></i></span>
                            <input type="text" name="nomor" class="form-control cs-input-text font-monospace" placeholder="Masukkan nomor permohonan..." value="{{ request('nomor') }}" autocomplete="off" required>
                            <button type="submit" class="btn btn-primary px-3 px-sm-4 fw-semibold cs-btn-cari">
                                <i class="fas fa-search me-1"></i>Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($permohonan))
            <div class="card shadow-sm cs-result-card" style="animation: fadeStep .4s ease;">
                <div class="card-header bg-white py-2 py-sm-3 cs-card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center text-truncate">
                            <i class="fas fa-file-invoice text-primary me-2 cs-header-icon"></i>
                            <span class="cs-nomor fw-bold">{{ $permohonan->nomor_permohonan }}</span>
                        </div>
                        @php
                            $statusMap = [
                                'Menunggu' => ['badge' => 'warning text-dark', 'icon' => 'fa-clock'],
                                'Disetujui' => ['badge' => 'success', 'icon' => 'fa-check-circle'],
                                'Ditolak' => ['badge' => 'danger', 'icon' => 'fa-times-circle'],
                                'Dipinjam' => ['badge' => 'primary', 'icon' => 'fa-box'],
                                'Dikembalikan' => ['badge' => 'secondary', 'icon' => 'fa-undo'],
                            ];
                            $status = $statusMap[$permohonan->status] ?? ['badge' => 'secondary', 'icon' => 'fa-question'];
                            $showBarcode = in_array($permohonan->status, ['Disetujui', 'Dipinjam', 'Dikembalikan'], true);
                        @endphp
                        <span class="badge bg-{{ $status['badge'] }} badge-status">
                            <i class="fas {{ $status['icon'] }} me-1"></i>{{ $permohonan->status }}
                        </span>
                    </div>

                    {{-- Barcode hanya jika Disetujui / Dipinjam / Dikembalikan --}}
                    @if($showBarcode)
                    <div class="text-center mt-2 pt-2 mt-sm-3 pt-sm-3 border-top cs-qr-box">
                        <div id="cekStatusBarcode" class="cs-qr-code"></div>
                        <div class="cs-qr-label">Nomor Permohonan</div>
                        <div class="cs-qr-number">{{ $permohonan->nomor_permohonan }}</div>
                        <div class="text-muted cs-qr-help">Scan QR ini di Pengembalian (Admin / Super Admin).</div>
                    </div>
                    @endif
                </div>
                <div class="card-body cs-card-body p-3 p-sm-4">
                    <div class="row g-2 g-sm-3 mb-3 mb-sm-4 cs-info-grid">
                        <div class="col-6">
                            <div class="cs-info-box">
                                <small class="text-muted d-block cs-info-label">Pemohon</small>
                                <strong class="cs-info-value">{{ $permohonan->nama_peminjam }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="cs-info-box">
                                <small class="text-muted d-block cs-info-label">Instansi</small>
                                <strong class="cs-info-value">{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="cs-info-box">
                                <small class="text-muted d-block cs-info-label">Tgl Pinjam</small>
                                <strong class="cs-info-value">{{ $permohonan->tanggal_pinjam }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="cs-info-box">
                                <small class="text-muted d-block cs-info-label">Tgl Kembali</small>
                                <strong class="cs-info-value">{{ $permohonan->tanggal_kembali }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($permohonan->detailPermohonan->count())
                    <div class="cs-section mb-3 mb-sm-4">
                        <h6 class="fw-bold mb-2 cs-section-title"><i class="fas fa-boxes me-2 text-primary"></i>Barang Dipinjam</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 cs-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px;" class="text-center">No</th>
                                        <th>Barang</th>
                                        <th style="width:70px;" class="text-center">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permohonan->detailPermohonan as $i => $detail)
                                    <tr>
                                        <td class="text-center font-monospace">{{ $i + 1 }}</td>
                                        <td>{{ $detail->inventaris->nama_barang ?? '-' }}</td>
                                        <td class="text-center font-monospace">{{ $detail->jumlah }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <div class="cs-section mt-3">
                        <h6 class="fw-bold mb-2 cs-section-title"><i class="fas fa-history me-2 text-primary"></i>Riwayat Status</h6>
                        <div class="status-timeline cs-timeline">
                            <div class="status-item {{ in_array($permohonan->status, ['Menunggu','Disetujui','Ditolak','Dipinjam','Dikembalikan']) ? 'completed' : '' }}">
                                <div class="status-dot"></div>
                                <strong class="cs-tl-title">Permohonan Diajukan</strong>
                                <div class="text-muted small cs-tl-desc">{{ $permohonan->created_at->format('d M Y H:i') }}</div>
                            </div>
                            {{-- Langkah ini pasti sudah dilalui begitu permohonan dikirim,
                                 jadi titiknya hijau sama seperti "Permohonan Diajukan".
                                 Jangan pakai kelas active: tidak ada aturan CSS untuknya. --}}
                            <div class="status-item completed">
                                <div class="status-dot"></div>
                                <strong class="cs-tl-title">Menunggu Persetujuan</strong>
                                <div class="text-muted small cs-tl-desc">Permohonan sedang diproses admin</div>
                            </div>
                            @if($permohonan->status == 'Disetujui' || $permohonan->status == 'Dipinjam' || $permohonan->status == 'Dikembalikan')
                            <div class="status-item completed {{ $permohonan->status == 'Disetujui' ? 'active' : '' }}">
                                <div class="status-dot"></div>
                                <strong class="cs-tl-title">Disetujui</strong>
                                <div class="text-muted small cs-tl-desc">Silakan ambil barang sesuai jadwal</div>
                            </div>
                            @endif
                            @if($permohonan->status == 'Ditolak')
                            <div class="status-item rejected active">
                                <div class="status-dot"></div>
                                <strong class="cs-tl-title">Ditolak</strong>
                                <div class="text-muted small cs-tl-desc">{{ $permohonan->catatan_admin ?? 'Permohonan ditolak oleh admin.' }}</div>
                            </div>
                            @endif
                            @if($permohonan->status == 'Dipinjam')
                            <div class="status-item completed active">
                                <div class="status-dot"></div>
                                <strong class="cs-tl-title">Dipinjam</strong>
                                <div class="text-muted small cs-tl-desc">Barang sedang dipinjam</div>
                            </div>
                            @endif
                            @if($permohonan->status == 'Dikembalikan')
                            <div class="status-item completed active">
                                <div class="status-dot"></div>
                                <strong class="cs-tl-title">Dikembalikan</strong>
                                <div class="text-muted small cs-tl-desc">Barang telah dikembalikan</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="text-center mt-3 mt-sm-4">
                <a href="{{ route('website') }}" class="text-decoration-none text-muted cs-back-link">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</div>

@include('partials.public-footer', ['compact' => true])

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@isset($permohonan)
@if(in_array($permohonan->status, ['Disetujui', 'Dipinjam', 'Dikembalikan'], true))
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}"></script>
<script>
    (function () {
        var el = document.getElementById('cekStatusBarcode');
        if (!el || typeof QRCode === 'undefined') return;
        try {
            el.innerHTML = '';
            new QRCode(el, {
                text: @json($permohonan->nomor_permohonan),
                width: 150,
                height: 150,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            el.querySelectorAll('img').forEach(function (img) {
                img.style.display = 'none';
            });
        } catch (e) {
            console.warn('Gagal render QR', e);
        }
    })();
</script>
@endif
@endisset

</body>
</html>
