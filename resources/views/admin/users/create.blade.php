@extends('adminlte::page')

@section('title', 'Tambah Admin')

@section('content_header')
    <h1>Tambah Akun Admin</h1>
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

<form action="{{ route('users.store') }}" method="POST" autocomplete="off">
    @csrf

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus me-2"></i>Data Akun</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required maxlength="255" placeholder="Masukkan nama lengkap">
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required maxlength="255" placeholder="nama@contoh.go.id">
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>
                                    {{ $role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            <strong>Admin</strong>: kelola permohonan, pengembalian, dan surat.
                            <strong>Super Admin</strong>: seluruh hak akses Admin ditambah master data dan manajemen akun.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-key me-2"></i>Kata Sandi</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 px-3">
                <i class="fas fa-info-circle me-1"></i>
                Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.
                Setelah akun dibuat, pengguna dapat mengaktifkan Passkey dan 2FA dari menu Keamanan.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control @error('password_confirmation') is-invalid @enderror" required>
                        @error('password_confirmation')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check me-1"></i>Simpan Akun
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Batal
            </a>
        </div>
    </div>

</form>

@stop
