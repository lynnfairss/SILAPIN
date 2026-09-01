@extends('adminlte::page')

@section('title', 'Data Instansi')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-end">
        <div>
            <h1>Data Instansi</h1>
            <p class="text-muted mb-0 small">Kelola data instansi yang terdaftar dalam sistem SILAPIN</p>
        </div>
    </div>
@stop

@section('css')
<style>
    .table-modern thead th {
        background: #1a1a2e;
        color: #fff;
        font-weight: 600;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        border: none;
        padding: .7rem .9rem;
        white-space: nowrap;
    }
    .table-modern tbody td {
        padding: .65rem .9rem;
        font-size: .88rem;
        vertical-align: middle;
    }
    .table-modern tbody tr { border-bottom: 1px solid #f1f3f7; }
    .table-modern tbody tr:hover { background: rgba(13,110,253,.04); }
</style>
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

<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title" id="formTitle"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Instansi</h3>
    </div>

    <form id="formInstansi" action="{{ route('instansi.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="id" id="editId" value="">

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Nama Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_instansi" id="inputNama" class="form-control"
                               value="{{ old('nama_instansi') }}" placeholder="Masukkan nama instansi" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Alamat</label>
                        <input type="text" name="alamat" id="inputAlamat" class="form-control"
                               value="{{ old('alamat') }}" placeholder="Alamat instansi">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Telepon</label>
                        <input type="tel" name="telepon" id="inputTelepon" class="form-control"
                               value="{{ old('telepon') }}" placeholder="08xxxxxxxxxx"
                               oninput="this.value = this.value.replace(/\D/g, '')" maxlength="15">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Tipe Identitas <span class="text-danger">*</span></label>
                        <select name="tipe_identitas" id="inputTipeIdentitas" class="form-control" required>
                            <option value="NIK">NIK</option>
                            <option value="NRP">NRP</option>
                            <option value="NIP">NIP</option>
                            <option value="NDP/NRP">NDP / NRP</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnCancel" onclick="resetForm()" style="display:none;">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-building me-2 text-primary"></i>Daftar Instansi</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama Instansi</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th class="text-center">Tipe Identitas</th>
                    <th class="text-center" width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($instansi as $item)
                <tr>
                    <td class="text-center">{{ $instansi->firstItem() + $loop->index }}</td>
                    <td class="fw-bold">{{ $item->nama_instansi }}</td>
                    <td>{{ $item->alamat ?? '-' }}</td>
                    <td>{{ $item->telepon ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $item->effective_tipe_identitas }}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" title="Edit"
                            onclick="editItem({{ $item->id }}, '{{ addslashes($item->nama_instansi) }}', '{{ addslashes($item->alamat ?? '') }}', '{{ addslashes($item->telepon ?? '') }}', '{{ $item->effective_tipe_identitas }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('instansi.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
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
    function editItem(id, nama, alamat, telepon, tipeIdentitas) {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>Edit Instansi';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('inputNama').value = nama;
        document.getElementById('inputAlamat').value = alamat;
        document.getElementById('inputTelepon').value = telepon;
        document.getElementById('inputTipeIdentitas').value = tipeIdentitas || 'NIK';
        document.getElementById('formInstansi').action = '{{ url("instansi") }}/' + id;
        document.getElementById('btnCancel').style.display = 'inline-block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Instansi';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputAlamat').value = '';
        document.getElementById('inputTelepon').value = '';
        document.getElementById('inputTipeIdentitas').value = 'NIK';
        document.getElementById('formInstansi').action = '{{ route("instansi.store") }}';
        document.getElementById('btnCancel').style.display = 'none';
    }
</script>

@stop
