@extends('adminlte::page')

@section('title', 'Detail Permohonan')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h1>Detail Permohonan</h1>
        <a href="{{ route('permohonan.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
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
@endphp

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

{{-- Header status --}}
<div class="card card-flat mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-primary">{{ $permohonan->nomor_permohonan }}</h4>
            <span class="text-muted small">Diajukan {{ $permohonan->created_at->translatedFormat('d M Y H:i') }}</span>
        </div>
        <span class="badge badge-soft-{{ $statusColor[$permohonan->status] ?? 'secondary' }}">
            <i class="fas {{ $permohonan->status === 'Menunggu' ? 'fa-clock' : ($permohonan->status === 'Disetujui' ? 'fa-check-circle' : ($permohonan->status === 'Ditolak' ? 'fa-times-circle' : ($permohonan->status === 'Dipinjam' ? 'fa-box' : 'fa-undo'))) }} me-1"></i>
            {{ $permohonan->status }}
        </span>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Data pemohon --}}
    <div class="col-lg-7">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user me-2 text-primary"></i>Data Pemohon</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="detail-label">Nama Peminjam</div>
                        <div class="detail-value">{{ $permohonan->nama_peminjam }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Instansi</div>
                        <div class="detail-value">{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">{{ $permohonan->instansi?->effective_tipe_identitas ?? 'NIK' }}</div>
                        <div class="detail-value">{{ $permohonan->nik }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Jabatan</div>
                        <div class="detail-value">{{ $permohonan->jabatan ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Telepon</div>
                        <div class="detail-value">{{ $permohonan->telepon }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Tempat / Tgl Lahir</div>
                        <div class="detail-value">{{ $permohonan->tempat_tanggal_lahir ?? '-' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="detail-label">Alamat</div>
                        <div class="detail-value">{{ $permohonan->alamat ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Tanggal Pinjam</div>
                        <div class="detail-value">{{ \Carbon\Carbon::parse($permohonan->tanggal_pinjam)->translatedFormat('d M Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Tanggal Kembali</div>
                        <div class="detail-value">{{ \Carbon\Carbon::parse($permohonan->tanggal_kembali)->translatedFormat('d M Y') }}</div>
                    </div>
                    <div class="col-12">
                        <div class="detail-label">Keperluan</div>
                        <div class="detail-value">{{ $permohonan->keperluan }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lampiran --}}
    <div class="col-lg-5">
        <div class="card card-flat mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paperclip me-2 text-primary"></i>Lampiran</h3>
            </div>
            <div class="card-body">
                @php
                    $ktpExt = $permohonan->foto_ktp ? strtolower(pathinfo($permohonan->foto_ktp, PATHINFO_EXTENSION)) : '';
                    $suratExt = $permohonan->surat_tugas ? strtolower(pathinfo($permohonan->surat_tugas, PATHINFO_EXTENSION)) : '';
                    $suratGambar = in_array($suratExt, ['jpg', 'jpeg', 'png', 'webp']);
                @endphp

                <div class="lampiran-flex">
                    <div>
                        <div class="detail-label mb-2">Foto KTP</div>
                        @if($permohonan->foto_ktp && in_array($ktpExt, ['jpg', 'jpeg', 'png', 'webp']))
                        <img src="{{ asset('storage/'.$permohonan->foto_ktp) }}"
                            class="ktp-thumb mb-3" data-full="{{ asset('storage/'.$permohonan->foto_ktp) }}"
                            alt="Foto KTP" title="Klik untuk memperbesar">
                        @elseif($permohonan->foto_ktp)
                        <a href="{{ asset('storage/'.$permohonan->foto_ktp) }}" target="_blank" class="lampiran-box mb-3">
                            <i class="fas fa-file mb-1"></i>
                            <div class="fw-semibold">File KTP</div>
                        </a>
                        @else
                        <div class="lampiran-box mb-3 opacity-50">
                            <i class="fas fa-id-card mb-1"></i>
                            <div class="fw-semibold">Foto KTP <small>(tidak ada)</small></div>
                        </div>
                        @endif
                    </div>

                    <div>
                        <div class="detail-label mb-2">Surat Tugas</div>
                        @if($permohonan->surat_tugas && $suratGambar)
                        <img src="{{ asset('storage/'.$permohonan->surat_tugas) }}"
                            class="ktp-thumb mb-2" data-full="{{ asset('storage/'.$permohonan->surat_tugas) }}"
                            alt="Surat Tugas" title="Klik untuk memperbesar">
                        @elseif($permohonan->surat_tugas)
                        <a href="{{ asset('storage/'.$permohonan->surat_tugas) }}" target="_blank" class="lampiran-box mb-2">
                            <i class="fas fa-file-alt mb-1"></i>
                            <div class="fw-semibold">Surat Tugas</div>
                        </a>
                        @else
                        <div class="lampiran-box mb-2 opacity-50">
                            <i class="fas fa-file-alt mb-1"></i>
                            <div class="fw-semibold">Surat Tugas <small>(tidak ada)</small></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Catatan admin --}}
        @if($permohonan->catatan_admin)
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sticky-note me-2 text-primary"></i>Catatan Admin</h3>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $permohonan->catatan_admin }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Barang dipinjam --}}
    <div class="col-lg-7">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes me-2 text-primary"></i>Barang Dipinjam</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px">No</th>
                            <th>Barang</th>
                            <th class="text-center" style="width:90px">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonan->detailPermohonan as $i => $d)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $d->inventaris->nama_barang ?? 'Barang #'.$d->inventaris_id }}</div>
                                <small class="text-muted">{{ $d->inventaris->kode_barang ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary">{{ $d->jumlah }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="col-lg-5">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history me-2 text-primary"></i>Riwayat Status</h3>
            </div>
            <div class="card-body">
                @forelse($permohonan->statusLogs as $log)
                <div class="timeline">
                    <div class="tl-item {{ $log->status_baru === 'Ditolak' ? 'rejected' : 'active' }}">
                        <div class="tl-dot"></div>
                        <div class="tl-title">
                            @if($log->status_baru === 'Menunggu')
                                Permohonan Diajukan
                            @else
                                {{ $log->status_lama }} <i class="fas fa-arrow-right mx-1 text-muted small"></i> {{ $log->status_baru }}
                            @endif
                        </div>
                        <div class="tl-desc">
                            {{ $log->catatan ?? 'Status diperbarui.' }}
                        </div>
                        <div class="tl-who">
                            <i class="fas fa-clock me-1"></i>{{ $log->created_at->translatedFormat('d M Y H:i') }}
                            @if($log->user)
                            · <i class="fas fa-user-shield me-1"></i>{{ $log->user->name }}
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center mb-0">Belum ada riwayat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Aksi --}}
@if($permohonan->status == 'Menunggu')
<div class="card card-flat">
    <div class="card-body d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-success"
            data-toggle="modal" data-target="#modalACC"
            data-id="{{ $permohonan->id }}"
            data-nama="{{ $permohonan->nama_peminjam }}"
            data-barang="{{ $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ') }}">
            <i class="fas fa-check"></i> ACC / Setujui
        </button>

        <button type="button" class="btn btn-danger"
            data-toggle="modal" data-target="#modalTolak"
            data-id="{{ $permohonan->id }}"
            data-nama="{{ $permohonan->nama_peminjam }}"
            data-barang="{{ $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ') }}">
            <i class="fas fa-times"></i> Tolak
        </button>
    </div>
</div>
@endif

{{-- MODAL ACC --}}
<div class="modal fade" id="modalACC" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="" id="formACC">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Disetujui">
                <div class="modal-header modal-header-grad-success">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Setujui Permohonan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Anda yakin ingin menyetujui permohonan ini?</p>
                    <table class="table table-detail mb-3">
                        <tr><th>Nama</th><td id="accNama"></td></tr>
                        <tr><th>Barang</th><td id="accBarang"></td></tr>
                    </table>
                    <div class="form-group">
                        <label>Catatan <small class="text-muted">(opsional)</small></label>
                        <textarea name="catatan_admin" class="form-control" rows="2"
                            placeholder="Tambahkan catatan jika perlu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i>Ya, Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TOLAK --}}
<div class="modal fade" id="modalTolak" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="" id="formTolak">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Ditolak">
                <div class="modal-header modal-header-grad-danger">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Tolak Permohonan</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Anda yakin ingin menolak permohonan ini?</p>
                    <table class="table table-detail mb-3">
                        <tr><th>Nama</th><td id="tolakNama"></td></tr>
                        <tr><th>Barang</th><td id="tolakBarang"></td></tr>
                    </table>
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan_admin" class="form-control" rows="3" required
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Ya, Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- LIGHTBOX --}}
<div class="lightbox-overlay" id="lightbox">
    <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Tutup">
        <i class="fas fa-times"></i>
    </button>
    <div class="lightbox-content">
        <img src="" alt="Pratinjau" id="lightboxImg">
    </div>
</div>

@stop

@section('js')
<script>
    // Lightbox
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightboxImg');

    document.querySelectorAll('.ktp-thumb').forEach(thumb => {
        thumb.addEventListener('click', function () {
            lightboxImg.src = this.dataset.full;
            lightbox.classList.add('active');
        });
    });

    function closeLightbox() {
        lightbox.classList.remove('active');
        lightboxImg.src = '';
    }

    document.getElementById('lightboxClose').addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox();
    });

    $('#modalACC').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var id = btn.data('id');
        var nama = btn.data('nama');
        var barang = btn.data('barang');
        $(this).find('#accNama').text(nama);
        $(this).find('#accBarang').text(barang || '-');
        $(this).find('form').attr('action', '/permohonan/' + id + '/status');
    });

    $('#modalTolak').on('show.bs.modal', function (e) {
        var btn = $(e.relatedTarget);
        var id = btn.data('id');
        var nama = btn.data('nama');
        var barang = btn.data('barang');
        $(this).find('#tolakNama').text(nama);
        $(this).find('#tolakBarang').text(barang || '-');
        $(this).find('form').attr('action', '/permohonan/' + id + '/status');
    });
</script>
@stop
