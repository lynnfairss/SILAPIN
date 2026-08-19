@extends('adminlte::page')

@section('title', 'Buat Permohonan')

@section('content_header')
    <h1>Buat Permohonan Baru</h1>
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

<form action="{{ route('permohonan.store') }}" method="POST">
    @csrf

    <div class="card card-flat">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user me-2 text-primary"></i>Data Peminjam</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nama_peminjam">Nama Peminjam <span class="text-danger">*</span></label>
                        <input type="text" name="nama_peminjam" id="nama_peminjam" class="form-control"
                               value="{{ old('nama_peminjam') }}" required placeholder="Masukkan nama lengkap">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nik">NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="nik" class="form-control"
                               value="{{ old('nik') }}" required placeholder="Nomor Induk Kependudukan" maxlength="20">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <input type="text" name="jabatan" id="jabatan" class="form-control"
                               value="{{ old('jabatan') }}" placeholder="Contoh: Kepala Subbag, Staf">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telepon">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="telepon" id="telepon" class="form-control"
                               value="{{ old('telepon') }}" required placeholder="08xxxxxxxxxx">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="instansi_id">Instansi <span class="text-danger">*</span></label>
                        <select name="instansi_id" id="instansi_id" class="form-control" required>
                            <option value="">-- Pilih Instansi --</option>
                            @foreach($instansi as $item)
                                <option value="{{ $item->id }}" {{ old('instansi_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_instansi }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tanggal_pinjam">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" class="form-control"
                               value="{{ old('tanggal_pinjam') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tanggal_kembali">Tanggal Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control"
                               value="{{ old('tanggal_kembali') }}" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="keperluan">Keperluan <span class="text-danger">*</span></label>
                <textarea name="keperluan" id="keperluan" class="form-control" rows="3" required
                          placeholder="Tuliskan keperluan peminjaman...">{{ old('keperluan') }}</textarea>
            </div>
        </div>
    </div>

    <div class="card card-flat">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-boxes me-2 text-primary"></i>Pilih Inventaris</h3>
        </div>
        <div class="card-body">
            <div class="row g-3" id="barangContainer">
                @forelse($inventaris as $item)
                <div class="col-md-4 col-sm-6">
                    <div class="inventaris-card p-3" onclick="toggleBarang(this, {{ $item->id }})">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="inventaris[]" value="{{ $item->id }}" id="barang_{{ $item->id }}" onchange="toggleSelect(this, {{ $item->id }})">
                            <label class="form-check-label fw-semibold" for="barang_{{ $item->id }}">{{ $item->nama_barang }}</label>
                        </div>
                        <div class="ms-1 small text-muted">
                            <div>Kode: <code>{{ $item->kode_barang }}</code></div>
                            <div>Kategori: {{ $item->kategori->nama_kategori ?? '-' }}</div>
                            <div>Stok: <span class="badge badge-soft-success">{{ $item->stok }}</span></div>
                            <div class="mt-2">
                                <label class="form-label small">Jumlah</label>
                                <input type="number" name="jumlah[{{ $item->id }}]" class="form-control form-control-sm" min="1" max="{{ $item->stok }}" value="1" disabled onclick="event.stopPropagation()">
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x d-block mb-2"></i>
                    Belum ada barang tersedia.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mb-4">
        <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
        <button type="submit" class="btn btn-primary" onclick="return validateForm()">
            <i class="fas fa-save me-1"></i>Simpan Permohonan
        </button>
    </div>
</form>

@stop

@section('js')
<script>
    function toggleSelect(chk, id) {
        const card = chk.closest('.inventaris-card');
        card.classList.toggle('selected', chk.checked);
        const jumlahInput = card.querySelector('[name^="jumlah"]');
        if (jumlahInput) jumlahInput.disabled = !chk.checked;
    }

    function toggleBarang(el, id) {
        const chk = el.querySelector('.form-check-input');
        chk.checked = !chk.checked;
        toggleSelect(chk, id);
    }

    function validateForm() {
        const checked = document.querySelectorAll('[name="inventaris[]"]:checked');
        if (checked.length === 0) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4';
            alert.style.zIndex = '9999';
            alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Pilih minimal 1 barang.
                <button type="button" class="close" data-dismiss="alert">&times;</button>`;
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 4000);
            return false;
        }

        for (const chk of checked) {
            const card = chk.closest('.inventaris-card');
            const jumlah = card.querySelector('[name^="jumlah"]');
            const maxStok = parseInt(jumlah.getAttribute('max'));
            const val = parseInt(jumlah.value);
            if (val > maxStok) {
                const nama = card.querySelector('.form-check-label')?.textContent || 'Barang';
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4';
                alert.style.zIndex = '9999';
                alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>Jumlah "${nama}" melebihi stok tersedia (${maxStok}).
                    <button type="button" class="close" data-dismiss="alert">&times;</button>`;
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 4000);
                return false;
            }
        }

        return true;
    }
</script>
@stop
