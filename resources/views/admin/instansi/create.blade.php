@extends('adminlte::page')

@section('title', 'Tambah Instansi')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Tambah Instansi</h1>

    <a href="{{ route('instansi.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
</div>
@stop

@section('content')

<div class="card card-primary">

    <div class="card-header">
        <h3 class="card-title">Form Tambah Instansi</h3>
    </div>

    <form action="{{ route('instansi.store') }}" method="POST">

        @csrf

        <div class="card-body">

            <div class="form-group">
                <label>Nama Instansi <span class="text-danger">*</span></label>

                <input type="text"
                       name="nama_instansi"
                       class="form-control @error('nama_instansi') is-invalid @enderror"
                       value="{{ old('nama_instansi') }}"
                       placeholder="Masukkan nama instansi">

                @error('nama_instansi')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <div class="form-group">

                <label>Alamat</label>

                <textarea
                    name="alamat"
                    rows="4"
                    class="form-control"
                    placeholder="Masukkan alamat instansi">{{ old('alamat') }}</textarea>

            </div>

            <div class="form-group">

                <label>Telepon</label>

                <input
                    type="text"
                    name="telepon"
                    class="form-control"
                    value="{{ old('telepon') }}"
                    placeholder="08xxxxxxxxxx">

            </div>

        </div>

        <div class="card-footer">

            <button class="btn btn-success">

                <i class="fas fa-save"></i>

                Simpan

            </button>

            <a href="{{ route('instansi.index') }}"
               class="btn btn-secondary">

                Batal

            </a>

        </div>

    </form>

</div>

@stop