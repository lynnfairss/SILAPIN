@extends('adminlte::page')

@section('title', 'Data Jenis')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-end">
        <div>
            <h1>Data Jenis</h1>
            <p class="text-muted mb-0 small">Kelola jenis barang inventaris dalam sistem SILAPIN</p>
        </div>
    </div>
@stop

@section('css')
<style>
    .table-modern thead th { background: #0f172a; }
</style>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-warning alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
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

<div class="card card-flat mb-4">
    <div class="card-header">
        <h3 class="card-title" id="formTitle"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Jenis</h3>
    </div>

    <form id="formJenis" action="{{ route('jenis.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="id" id="editId" value="">

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Nama Jenis <span class="text-danger">*</span></label>
                    <input type="text" name="nama_jenis" id="inputNama" class="form-control"
                           value="{{ old('nama_jenis') }}" placeholder="Masukkan nama jenis (misal: Laptop)" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" id="inputKeterangan" class="form-control"
                           value="{{ old('keterangan') }}" placeholder="Keterangan singkat">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-actions w-100 m-0 p-0 border-0">
                        <button type="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnCancel" onclick="resetForm()" style="display:none;">
                            <i class="fas fa-times me-1"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-layer-group me-2 text-primary"></i>Daftar Jenis</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th width="60" class="text-center">No</th>
                    <th>Nama Jenis</th>
                    <th>Keterangan</th>
                    <th class="text-center" width="120">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($jenis as $item)
                <tr>
                    <td class="text-center">{{ $jenis->firstItem() + $loop->index }}</td>
                    <td class="fw-semibold">{{ $item->nama_jenis }}</td>
                    <td class="text-muted">{{ $item->keterangan ?? '-' }}</td>
                    <td class="text-center">
                        <div class="btn-actions">
                            <button class="btn btn-outline-primary" title="Edit"
                                onclick="editItem({{ $item->id }}, '{{ addslashes($item->nama_jenis) }}', '{{ addslashes($item->keterangan ?? '') }}')">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="{{ route('jenis.destroy', $item->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" title="Hapus"
                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block opacity-25"></i>
                        Belum ada data jenis.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($jenis->count())
    <div class="card-footer">{{ $jenis->links() }}</div>
    @endif
</div>

<script>
    function editItem(id, nama, keterangan) {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>Edit Jenis';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputKeterangan').value = keterangan;
        document.getElementById('formJenis').action = '{{ url("jenis") }}/' + id;
        document.getElementById('btnCancel').style.display = 'inline-block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Jenis';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputKeterangan').value = '';
        document.getElementById('formJenis').action = '{{ route("jenis.store") }}';
        document.getElementById('btnCancel').style.display = 'none';
    }
</script>

@stop
