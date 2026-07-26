@extends('adminlte::page')

@section('title', 'Edit Instansi')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Edit Instansi</h1>

    <a href="{{ route('instansi.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
</div>
@stop

@section('content')

<div class="card card-warning">

    <div class="card-header">
        <h3 class="card-title">Form Edit Instansi</h3>
    </div>

    <form action="{{ route('instansi.update',$instansi->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">

                <label>Nama Instansi <span class="text-danger">*</span></label>

                <input
                    type="text"
                    name="nama_instansi"
                    class="form-control @error('nama_instansi') is-invalid @enderror"
                    value="{{ old('nama_instansi',$instansi->nama_instansi) }}">

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
                    class="form-control">{{ old('alamat',$instansi->alamat) }}</textarea>

            </div>

            <div class="form-group">

                <label>Telepon</label>

                <input
                    type="text"
                    name="telepon"
                    class="form-control"
                    value="{{ old('telepon',$instansi->telepon) }}">

            </div>

        </div>

        <div class="card-footer">

            <button class="btn btn-warning">

                <i class="fas fa-save"></i>

                Update

            </button>

            <a href="{{ route('instansi.index') }}"
               class="btn btn-secondary">

                Batal

            </a>

        </div>

    </form>

</div>

@stop