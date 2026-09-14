@extends('adminlte::page')

@section('title', 'Pengembalian Barang')

@section('content_header')
    <h1>Pengembalian Barang</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exchange-meh me-2"></i>Konfirmasi Pengembalian</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('pengembalian.proses') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nomor Permohonan <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_permohonan" class="form-control"
                            placeholder="Contoh: SP-260726-F46620"
                            required>
                    </div>
                </div>
            </div>

            <div id="permohonanDetail" class="mt-3" style="display: none;">
                <h5>Detail Permohonan</h5>
                <div class="row g-2">
                    <div class="col-6">
                        <strong>Nama Peminjam:</strong> <span id="detailNama"></span>
                    </div>
                    <div class="col-6">
                        <strong>Status:</strong> <span id="detailStatus" class="badge badge-primary"></span>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <strong>Barang:</strong> <span id="detailBarang"></span>
                    </div>
                    <div class="col-6">
                        <strong>Jumlah:</strong> <span id="detailJumlah"></span>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <div class="form-group">
                    <label>Catatan Admin <span class="text-danger">*</span></label>
                    <textarea name="catatan" class="form-control" rows="3"
                        placeholder="Masukkan alasan atau catatan pengembalian..."></textarea>
                </div>
            </div>

            <div class="mt-3">
                <div class="form-group">
                    <label>Bukti Pengembalian (opsional)</label>
                    <input type="text" name="bukti_pengembalian" class="form-control"
                        placeholder="Contoh: Foto barang yang dikembalikan">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success flex-grow-1">
                    <i class="fas fa-check me-1"></i>Konfirmasi Pengembalian
                </button>
                <a href="{{ route('permohonan.index') }}" class="btn btn-outline-secondary flex-grow-1">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#nomor_permohonan').on('change blur', function() {
            var nomor = $(this).val();
            if (nomor) {
                $.ajax({
                    url: '{{ route("permohonan.cek-nomor") }}',
                    type: 'GET',
                    data: { nomor_permohonan: nomor },
                    success: function(response) {
                        if (response.exists) {
                            $('#detailNama').text(response.nama_peminjam);
                            var statusColor = response.status === 'Disetujui' ? 'badge-success' : (response.status === 'Dipinjam' ? 'badge-primary' : 'badge-warning');
                            $('#detailStatus').removeClass('badge-primary badge-success badge-warning badge-danger').addClass(statusColor).text(response.status);
                            $('#detailBarang').text(response.barang || '-');
                            $('#detailJumlah').text(response.total_jumlah || 0);
                            $('#permohonanDetail').show();
                        } else {
                            $('#permohonanDetail').hide();
                            alert('Permohonan tidak ditemukan!');
                        }
                    },
                    error: function() {
                        $('#permohonanDetail').hide();
                        alert('Gagal mengambil data permohonan.');
                    }
                });
            } else {
                $('#permohonanDetail').hide();
            }
        });
    });
</script>

@stop