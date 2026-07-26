@extends('adminlte::page')

@section('title', 'Data Instansi')

@section('content_header')
    <h1>Data Instansi</h1>
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
        <h3 class="card-title" id="formTitle"><i class="fas fa-plus me-1"></i>Tambah Instansi</h3>
    </div>

    <form id="formInstansi" action="{{ route('instansi.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="id" id="editId" value="">

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nama Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_instansi" id="inputNama" class="form-control"
                               value="{{ old('nama_instansi') }}" placeholder="Masukkan nama instansi" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat" id="inputAlamat" class="form-control"
                               value="{{ old('alamat') }}" placeholder="Alamat instansi">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="telepon" id="inputTelepon" class="form-control"
                               value="{{ old('telepon') }}" placeholder="08xxxxxxxxxx">
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
        <h3 class="card-title">Daftar Instansi</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover table-striped">
            <thead class="bg-primary text-white">
                <tr>
                    <th width="60">No</th>
                    <th>Nama Instansi</th>
                    <th>Alamat</th>
                    <th width="170">Telepon</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($instansi as $item)
                <tr>
                    <td class="text-center">{{ $instansi->firstItem() + $loop->index }}</td>
                    <td>{{ $item->nama_instansi }}</td>
                    <td>{{ $item->alamat ?? '-' }}</td>
                    <td>{{ $item->telepon ?? '-' }}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" title="Edit"
                            onclick="editItem({{ $item->id }}, '{{ addslashes($item->nama_instansi) }}', '{{ addslashes($item->alamat ?? '') }}', '{{ addslashes($item->telepon ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('instansi.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                        Belum ada data instansi.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($instansi->count())
    <div class="card-footer">{{ $instansi->links() }}</div>
    @endif
</div>

<script>
    function editItem(id, nama, alamat, telepon) {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-1"></i>Edit Instansi';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputAlamat').value = alamat;
        document.getElementById('inputTelepon').value = telepon;
        document.getElementById('formInstansi').action = '{{ url("instansi") }}/' + id;
        document.getElementById('btnCancel').style.display = 'inline-block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus me-1"></i>Tambah Instansi';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputAlamat').value = '';
        document.getElementById('inputTelepon').value = '';
        document.getElementById('formInstansi').action = '{{ route("instansi.store") }}';
        document.getElementById('btnCancel').style.display = 'none';
    }
</script>

@stop
