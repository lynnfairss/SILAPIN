@extends('adminlte::page')

@section('title', 'Data Permohonan')

@section('content_header')
    <h1>Data Permohonan</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card">
    <div class="card-body">

        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th>No</th>
                    <th>Instansi</th>
                    <th>Nama</th>
                    <th>Identitas</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($permohonan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->instansi->nama_instansi ?? '-' }}</td>
                <td>{{ $item->nama_peminjam }}</td>
                <td>
                    <div>{{ $item->nik }}</div>
                    <small class="text-muted">({{ $item->instansi->effective_tipe_identitas }})</small>
                </td>
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
                    @if($item->status == 'Menunggu')
                        <button type="button" class="btn btn-success btn-sm"
                            data-toggle="modal" data-target="#modalACC"
                            data-id="{{ $item->id }}"
                            data-nama="{{ $item->nama_peminjam }}"
                            data-barang="{{ $item->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ') }}">
                            <i class="fas fa-check"></i> ACC
                        </button>

                        <button type="button" class="btn btn-danger btn-sm"
                            data-toggle="modal" data-target="#modalTolak"
                            data-id="{{ $item->id }}"
                            data-nama="{{ $item->nama_peminjam }}"
                            data-barang="{{ $item->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ') }}">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    @endif

                    <form action="{{ route('permohonan.destroy', $item->id) }}"
                        method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Belum ada data permohonan.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>

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

@push('scripts')
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
@endpush
