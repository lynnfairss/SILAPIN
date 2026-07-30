@extends('adminlte::page')

@section('title', 'Data Kategori')

@section('content_header')
    <h1>Data Kategori</h1>
@stop

@section('content')

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

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title" id="formTitle"><i class="fas fa-plus me-1"></i>Tambah Kategori</h3>
    </div>

    <form id="formKategori" action="{{ route('kategori.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="id" id="editId" value="">

        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" id="inputNama" class="form-control"
                               value="{{ old('nama_kategori') }}" placeholder="Masukkan nama kategori" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" id="inputKeterangan" class="form-control"
                               value="{{ old('keterangan') }}" placeholder="Keterangan singkat">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-success" id="btnSubmit">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary" id="btnCancel" onclick="resetForm()" style="display:none;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Kategori</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped">
            <thead class="text-center">
                <tr>
                    <th width="60">No</th>
                    <th>Nama Kategori</th>
                    <th>Keterangan</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($kategori as $item)
                <tr>
                    <td class="text-center">{{ $kategori->firstItem() + $loop->index }}</td>
                    <td>{{ $item->nama_kategori }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" title="Edit"
                            onclick="editItem({{ $item->id }}, '{{ addslashes($item->nama_kategori) }}', '{{ addslashes($item->keterangan ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">Belum ada data kategori.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($kategori->count())
    <div class="card-footer">{{ $kategori->links() }}</div>
    @endif
</div>

<script>
    function editItem(id, nama, keterangan) {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-1"></i>Edit Kategori';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputKeterangan').value = keterangan;
        document.getElementById('formKategori').action = '{{ url("kategori") }}/' + id;
        document.getElementById('btnCancel').style.display = 'inline-block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus me-1"></i>Tambah Kategori';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputKeterangan').value = '';
        document.getElementById('formKategori').action = '{{ route("kategori.store") }}';
        document.getElementById('btnCancel').style.display = 'none';
    }
</script>

@stop
