@extends('adminlte::page')

@section('title', $pageTitle ?? 'Data Permohonan')

@section('content_header')
    <h1>{{ $pageTitle ?? 'Data Permohonan' }}</h1>
@stop

@section('css')
<style>
    .empty-state {
        padding: 2rem;
        text-align: center;
        color: #94a3b8;
        font-size: .9rem;
    }
    .empty-state i { display: block; margin-bottom: .5rem; font-size: 2rem; opacity: .4; }
    .dropdown-menu {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .1);
        padding: .35rem;
        min-width: 10rem;
    }
    .dropdown-item {
        border-radius: 6px;
        font-size: .84rem;
        padding: .4rem .65rem;
    }
    .dropdown-item:hover {
        background: #f1f5f9;
    }
</style>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@php
    $statusMeta = [
        'Menunggu'     => ['color' => '#f39c12', 'bg' => '#fff8e8', 'icon' => 'fa-hourglass-half'],
        'Disetujui'    => ['color' => '#28a745', 'bg' => '#eefaf3', 'icon' => 'fa-check-circle'],
        'Ditolak'      => ['color' => '#dc3545', 'bg' => '#fdf0f0', 'icon' => 'fa-times-circle'],
        'Dikembalikan' => ['color' => '#6c757d', 'bg' => '#f4f5f7', 'icon' => 'fa-undo'],
    ];
    $meta = $statusMeta[$activeStatus] ?? ['color' => '#0d6efd', 'bg' => '#f0f4ff', 'icon' => 'fa-file-alt'];
@endphp

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between"
         style="background: {{ $meta['bg'] }}; border-bottom: 2px solid {{ $meta['color'] }};">
        <h3 class="card-title mb-0 fw-bold" style="color: {{ $meta['color'] }}; font-size: .95rem;">
            <i class="fas {{ $meta['icon'] }} me-2"></i>{{ $pageTitle }}
            <span class="badge text-white ms-2" style="background: {{ $meta['color'] }}; font-size: .78rem; border-radius: 20px; padding: .35em .85em;">{{ $permohonan->count() }}</span>
        </h3>
    </div>
    <div class="card-body table-responsive p-0">
        @if($permohonan->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                Belum ada data permohonan dengan status ini.
            </div>
        @else
        <table class="table table-modern mb-0">
            <thead class="text-center">
                <tr>
                    <th style="min-width:40px">No</th>
                    <th>Instansi</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th style="min-width:180px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permohonan as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->instansi?->nama_instansi ?? '-' }}</td>
                    <td>{{ $item->nama_peminjam }}</td>
                    <td>{{ $item->nik }}</td>
                    <td>{{ $item->tanggal_pinjam }}</td>
                    <td>{{ $item->tanggal_kembali }}</td>
                    <td class="text-center">
                        @if($item->status == 'Menunggu')
                            <span class="badge badge-warning">Menunggu</span>
                        @elseif($item->status == 'Disetujui')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif($item->status == 'Ditolak')
                            <span class="badge badge-danger">Ditolak</span>
                        @elseif($item->status == 'Dipinjam')
                            <span class="badge badge-primary">Dipinjam</span>
                        @else
                            <span class="badge badge-secondary">Dikembalikan</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-actions">
                            <a href="{{ route('permohonan.show', $item->id) }}" class="btn btn-outline-info" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('permohonan.edit', $item->id) }}" class="btn btn-outline-warning" title="Edit Surat">
                                <i class="fas fa-pen"></i>
                            </a>
                            <a href="{{ route('surat.preview', $item->id) }}" target="_blank" class="btn btn-outline-secondary" title="Lihat Surat">
                                <i class="fas fa-file-alt"></i>
                            </a>
                            <div class="dropdown d-inline">
                                <button class="btn btn-outline-success dropdown-toggle" data-toggle="dropdown" title="Download Surat">
                                    <i class="fas fa-download"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('peminjam.download-surat.docx', $item->id) }}" target="_blank">
                                        <i class="fas fa-file-word me-2 text-primary"></i>Download DOCX
                                    </a>
                                    <a class="dropdown-item" href="{{ route('peminjam.download-surat.pdf', $item->id) }}" target="_blank">
                                        <i class="fas fa-file-pdf me-2 text-danger"></i>Download PDF
                                    </a>
                                </div>
                            </div>
                            @if($item->status == 'Menunggu')
                                <button type="button" class="btn btn-outline-success"
                                    data-toggle="modal" data-target="#modalACC"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama_peminjam }}"
                                    data-barang="{{ $item->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ') }}"
                                    title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger"
                                    data-toggle="modal" data-target="#modalTolak"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama_peminjam }}"
                                    data-barang="{{ $item->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ') }}"
                                    title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            <form action="{{ route('permohonan.destroy', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger" title="Hapus"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- MODAL ACC --}}
<div class="modal fade" id="modalACC" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="" id="formACC">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="Disetujui">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Setujui Permohonan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Anda yakin ingin menyetujui permohonan ini?</p>
                    <table class="table table-bordered mb-3">
                        <tr><th style="width:130px">Nama</th><td id="accNama"></td></tr>
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
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Ya, Setujui
                    </button>
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
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Tolak Permohonan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Anda yakin ingin menolak permohonan ini?</p>
                    <table class="table table-bordered mb-3">
                        <tr><th style="width:130px">Nama</th><td id="tolakNama"></td></tr>
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
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Ya, Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
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
