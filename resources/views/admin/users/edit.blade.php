@extends('adminlte::page')

@section('title', 'Edit Admin')

@section('content_header')
    <h1>Edit Akun {{ $user->name }}</h1>
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

<form action="{{ route('users.update', $user->id) }}" method="POST" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-edit me-2"></i>Data Akun</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required maxlength="255" placeholder="Masukkan nama lengkap">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required maxlength="255" placeholder="nama@contoh.go.id">
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nik">NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="nik" inputmode="numeric" pattern="\d{16}"
                               class="form-control @error('nik') is-invalid @enderror"
                               value="{{ old('nik', $user->nik) }}" required maxlength="16" placeholder="3500000000000009">
                        @error('nik')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            16 digit. NIK ini yang dicetak pada kolom &ldquo;Yang menyerahkan&rdquo; di surat peminjaman.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Role</label>
                        <p class="pt-1">
                            @if($user->isSuperAdmin())
                                <span class="badge badge-dark">Super Admin</span>
                            @else
                                <span class="badge badge-info">Admin</span>
                            @endif
                        </p>
                        <small class="form-text text-muted">
                            Role tidak dapat diubah dari halaman ini.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check me-1"></i>Simpan Perubahan
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Batal
            </a>
        </div>
    </div>

</form>

@stop