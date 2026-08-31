@extends('adminlte::page')

@section('title', 'Data Inventaris')

@section('content_header')
    <h1>Data Inventaris</h1>
@stop

@section('css')
<style>
    .btn-foto-hapus {
        position: absolute;
        top: -6px;
        right: -6px;
        border-radius: 50%;
        padding: .1rem .35rem;
        font-size: .65rem;
        line-height: 1;
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title" id="formTitle"><i class="fas fa-plus me-1"></i>Tambah Inventaris</h3>
    </div>

    <form id="formInventaris" action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="id" id="editId" value="">
        <input type="hidden" name="hapus_foto" id="hapusFotoHidden" value="">

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
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="nama_barang" id="inputNama" class="form-control"
                               placeholder="Nama barang" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Jenis Barang <small class="text-muted">(detail)</small></label>
                        <select name="jenis_id" id="inputJenis" class="form-control">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($jenisList as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stok" id="inputStok" class="form-control"
                               min="1" value="1" required>
                    </div>
                </div>
                <div class="col-md-1">
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
                        <label>Foto <small class="text-muted">(jpg/png, max 2MB per foto, max 5 foto)</small></label>
                        <div id="fotoSlots">
                            @for($i = 0; $i < 5; $i++)
                            <div class="d-flex align-items-center mb-1">
                                <input type="file" name="foto[]" id="inputFoto{{ $i }}" class="form-control form-control-sm" accept="image/*" onchange="previewFoto({{ $i }}, this)">
                                <span class="badge bg-secondary ms-2" id="fotoLabel{{ $i }}">Foto {{ $i + 1 }}</span>
                                <div id="fotoPreview{{ $i }}" class="ms-2"></div>
                            </div>
                            @endfor
                        </div>
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
        <form method="GET" action="{{ route('inventaris.index') }}" class="mb-3 d-flex align-items-center gap-2 flex-wrap">
            <label class="fw-bold mb-0"><i class="fas fa-tags me-1"></i>Jenis:</label>
            <select name="jenis" class="form-control" style="max-width:260px" onchange="this.form.submit()">
                <option value="">Semua Jenis ({{ $inventaris->total() }})</option>
                @foreach($jenisList as $j)
                    <option value="{{ $j->id }}" {{ request('jenis')==$j->id ? 'selected' : '' }}>
                        {{ $j->nama_jenis }} ({{ $jenisCount[$j->id] ?? 0 }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
            @if(request('jenis'))
                <a href="{{ route('inventaris.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i>Reset</a>
            @endif
        </form>
        @if(request('jenis'))
        <div class="alert alert-info py-2 mb-3">
            <i class="fas fa-info-circle me-1"></i>Menampilkan barang dengan jenis: <strong>{{ optional($jenisList->firstWhere('id', request('jenis')))->nama_jenis }}</strong>
        </div>
        @endif
        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Jenis</th>
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
                    <td>{{ $item->jenis?->nama_jenis ?? '-' }}</td>
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
                        @php $ft = $item->fotos; @endphp
                        @if($ft->count() > 0)
                            <div class="foto-cell" onclick='openFoto({{ json_encode($ft->pluck("foto")->map(fn($p) => asset("storage/".$p))) }})' style="cursor:pointer;position:relative;display:inline-block;">
                                <img src="{{ asset('storage/'.$ft->first()->foto) }}" width="56" height="56" class="rounded object-fit-cover border">
                                @if($ft->count() > 1)
                                <span class="foto-count-badge">+{{ $ft->count() - 1 }}</span>
                                @endif
                                <div class="foto-hover"><i class="fas fa-search-plus"></i></div>
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" title="Edit"
                            onclick='editItem({!! json_encode($item->load('fotos')) !!})'>
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
                    <td colspan="9" class="text-center text-muted">Belum ada data inventaris.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($inventaris->count())
    <div class="card-footer">{{ $inventaris->links() }}</div>
    @endif
</div>

<div class="image-modal-overlay" id="imageModal" onclick="closeFoto(event)">
    <div class="image-modal-content">
        <button type="button" class="modal-close" onclick="closeFoto()">&times;</button>
        <button type="button" class="modal-nav modal-prev" onclick="navFoto(-1)"><i class="fas fa-chevron-left"></i></button>
        <img id="modalFotoImg" src="" alt="Foto Barang">
        <button type="button" class="modal-nav modal-next" onclick="navFoto(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="modal-caption" id="modalFotoCaption"></div>
        <div class="modal-dots" id="modalFotoDots"></div>
    </div>
</div>

<script>
    let fotoList = [];
    let fotoIndex = 0;

    let fotoDihapus = [];
    let fotoIdSlot = {};

    function openFoto(urls) {
        fotoList = urls;
        fotoIndex = 0;
        document.getElementById('imageModal').classList.add('active');
        updateFotoModal();
    }

    function navFoto(dir) {
        if (!fotoList.length) return;
        fotoIndex = (fotoIndex + dir + fotoList.length) % fotoList.length;
        updateFotoModal();
    }

    function updateFotoModal() {
        const img = document.getElementById('modalFotoImg');
        img.src = fotoList[fotoIndex];
        document.getElementById('modalFotoCaption').textContent = (fotoIndex + 1) + ' / ' + fotoList.length;
        const dots = document.getElementById('modalFotoDots');
        dots.innerHTML = fotoList.map((_, i) =>
            '<span class="dot ' + (i === fotoIndex ? 'active' : '') + '" onclick="fotoIndex=' + i + ';updateFotoModal()"></span>'
        ).join('');
    }

    function closeFoto(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('imageModal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (document.getElementById('imageModal').classList.contains('active')) {
            if (e.key === 'Escape') closeFoto();
            if (e.key === 'ArrowLeft') navFoto(-1);
            if (e.key === 'ArrowRight') navFoto(1);
        }
    });

    function previewFoto(i, input) {
        const el = document.getElementById('fotoPreview' + i);
        if (input.files && input.files[0]) {
            // Jika slot ini sebelumnya ditandai hapus, batal hapus (karena akan diganti file baru)
            if (fotoIdSlot[i]) {
                fotoDihapus = fotoDihapus.filter(id => id !== fotoIdSlot[i]);
            }
            el.innerHTML = '<img src="' + URL.createObjectURL(input.files[0]) + '" width="50" height="50" class="rounded object-fit-cover border" style="object-fit:cover;">';
        } else {
            el.innerHTML = '';
        }
    }

    function hapusFoto(i, fotoId) {
        if (!fotoId) return;
        if (!fotoDihapus.includes(fotoId)) {
            fotoDihapus.push(fotoId);
        }
        const el = document.getElementById('fotoPreview' + i);
        el.innerHTML = '<span class="badge bg-danger">Akan dihapus</span>';
        document.getElementById('inputFoto' + i).value = '';
    }

    function editItem(item) {
        document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit me-1"></i>Edit Inventaris';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = item.id;
        document.getElementById('inputKategori').value = item.kategori_id;
        document.getElementById('inputKode').value = item.kode_barang;
        document.getElementById('inputNama').value = item.nama_barang;
        document.getElementById('inputJenis').value = item.jenis_id || '';
        document.getElementById('inputStok').value = item.stok;
        document.getElementById('inputKondisi').value = item.kondisi;
        document.getElementById('inputDeskripsi').value = item.deskripsi || '';
        document.getElementById('formInventaris').action = '{{ url("inventaris") }}/' + item.id;
        document.getElementById('btnCancel').style.display = 'inline-block';

        fotoDihapus = [];
        fotoIdSlot = {};
        for (let i = 0; i < 5; i++) {
            const preview = document.getElementById('fotoPreview' + i);
            const input = document.getElementById('inputFoto' + i);
            input.value = '';
            if (item.fotos && item.fotos[i]) {
                fotoIdSlot[i] = item.fotos[i].id;
                preview.innerHTML =
                    '<div class="position-relative d-inline-block">' +
                        '<img src="{{ asset("storage") }}/' + item.fotos[i].foto + '" width="50" height="50" class="rounded object-fit-cover border">' +
                        '<button type="button" class="btn btn-sm btn-danger btn-foto-hapus" title="Hapus foto" onclick="hapusFoto(' + i + ', ' + item.fotos[i].id + ')">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</div>';
            } else {
                preview.innerHTML = '';
            }
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
        document.getElementById('inputJenis').value = '';
        document.getElementById('inputStok').value = '1';
        document.getElementById('inputKondisi').value = 'Baik';
        document.getElementById('inputDeskripsi').value = '';
        document.getElementById('formInventaris').action = '{{ route("inventaris.store") }}';
        document.getElementById('btnCancel').style.display = 'none';
        fotoDihapus = [];
        fotoIdSlot = {};
        document.getElementById('hapusFotoHidden').value = '';
        for (let i = 0; i < 5; i++) {
            document.getElementById('inputFoto' + i).value = '';
            document.getElementById('fotoPreview' + i).innerHTML = '';
        }
    }

    document.getElementById('formInventaris').addEventListener('submit', function() {
        document.getElementById('hapusFotoHidden').value = fotoDihapus.join(',');
    });
</script>

@stop
