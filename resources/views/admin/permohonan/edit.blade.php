@extends('adminlte::page')

@section('title', 'Edit Permohonan')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h1>Edit Permohonan</h1>
        <a href="{{ route('permohonan.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
@stop

@section('content')

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

<form action="{{ route('permohonan.update', $permohonan->id) }}" method="POST">
    @csrf
    @method('PUT')

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
                               value="{{ old('nama_peminjam', $permohonan->nama_peminjam) }}" required
                               placeholder="Masukkan nama lengkap">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nik">NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="nik" class="form-control"
                               value="{{ old('nik', $permohonan->nik) }}" required
                               placeholder="Nomor Induk Kependudukan" maxlength="20">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <input type="text" name="jabatan" id="jabatan" class="form-control"
                               value="{{ old('jabatan', $permohonan->jabatan) }}"
                               placeholder="Contoh: Kepala Subbag, Staf">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telepon">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="telepon" id="telepon" class="form-control"
                               value="{{ old('telepon', $permohonan->telepon) }}" required placeholder="08xxxxxxxxxx">
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
                                <option value="{{ $item->id }}" {{ old('instansi_id', $permohonan->instansi_id) == $item->id ? 'selected' : '' }}>
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
                               value="{{ old('tanggal_pinjam', $permohonan->tanggal_pinjam) }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tanggal_kembali">Tanggal Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-control"
                               value="{{ old('tanggal_kembali', $permohonan->tanggal_kembali) }}" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="keperluan">Keperluan <span class="text-danger">*</span></label>
                <textarea name="keperluan" id="keperluan" class="form-control" rows="3" required
                          placeholder="Tuliskan keperluan peminjaman...">{{ old('keperluan', $permohonan->keperluan) }}</textarea>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Update
        </button>
        <a href="{{ route('permohonan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>
</form>

@stop
