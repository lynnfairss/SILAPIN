@extends('adminlte::page')

@section('title', 'Data Inventaris')

@section('content_header')
    <h1>Data Inventaris</h1>
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
        <h3 class="card-title" id="formTitle"><i class="fas fa-plus me-1"></i>Tambah Inventaris</h3>
    </div>

    <form id="formInventaris" action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="id" id="editId" value="">

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_id" id="inputKategori" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" name="kode_barang" id="inputKode" class="form-control"
                               placeholder="ELK-001" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" id="inputNama" class="form-control"
                               placeholder="Nama barang" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stok" id="inputStok" class="form-control"
                               min="1" value="1" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kondisi <span class="text-danger">*</span></label>
                        <select name="kondisi" id="inputKondisi" class="form-control" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success btn-block" id="btnSubmit">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="inputDeskripsi" class="form-control" rows="2"
                                  placeholder="Deskripsi barang"></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label>Foto <small class="text-muted">(jpg/png, max 2MB)</small></label>
                        <input type="file" name="foto" id="inputFoto" class="form-control" accept="image/*">
                        <div id="fotoPreview" class="mt-1"></div>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
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
        <h3 class="card-title">Daftar Inventaris</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Kondisi</th>
                    <th>Foto</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($inventaris as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code>{{ $item->kode_barang }}</code></td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                    <td class="text-center">{{ $item->stok }}</td>
                    <td>
                        @if($item->kondisi == 'Baik')
                            <span class="badge bg-success">{{ $item->kondisi }}</span>
                        @elseif($item->kondisi == 'Rusak Ringan')
                            <span class="badge bg-warning text-dark">{{ $item->kondisi }}</span>
                        @else
                            <span class="badge bg-danger">{{ $item->kondisi }}</span>
                        @endif
                    </td>
                    <td>
                        @if($item->foto)
                            <img src="{{ asset('storage/'.$item->foto) }}" width="50" height="50" class="rounded object-fit-cover">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" title="Edit"
                            onclick='editItem({!! json_encode($item) !!})'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="{{ route('inventaris.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Belum ada data inventaris.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($inventaris->count())
    <div class="card-footer">{{ $inventaris->links() }}</div>
    @endif
</div>

<script>
    function editItem(item) {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-1"></i>Edit Inventaris';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = item.id;
        document.getElementById('inputKategori').value = item.kategori_id;
        document.getElementById('inputKode').value = item.kode_barang;
        document.getElementById('inputNama').value = item.nama_barang;
        document.getElementById('inputStok').value = item.stok;
        document.getElementById('inputKondisi').value = item.kondisi;
        document.getElementById('inputDeskripsi').value = item.deskripsi || '';
        document.getElementById('formInventaris').action = '{{ url("inventaris") }}/' + item.id;
        document.getElementById('btnCancel').style.display = 'inline-block';

        if (item.foto) {
            document.getElementById('fotoPreview').innerHTML =
                '<img src="{{ asset("storage") }}/' + item.foto + '" width="60" height="60" class="rounded object-fit-cover">';
        } else {
            document.getElementById('fotoPreview').innerHTML = '';
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus me-1"></i>Tambah Inventaris';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('inputKategori').value = '';
        document.getElementById('inputKode').value = '';
        document.getElementById('inputNama').value = '';
        document.getElementById('inputStok').value = '1';
        document.getElementById('inputKondisi').value = 'Baik';
        document.getElementById('inputDeskripsi').value = '';
        document.getElementById('inputFoto').value = '';
        document.getElementById('fotoPreview').innerHTML = '';
        document.getElementById('formInventaris').action = '{{ route("inventaris.store") }}';
        document.getElementById('btnCancel').style.display = 'none';
    }
</script>

@stop
