@extends('adminlte::page')

@section('title', 'Tambah Inventaris')

@section('content_header')
    <h1>Tambah Inventaris</h1>
@stop

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">

    <div class="card-body">

        <form action="{{ route('inventaris.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="form-group">
                <label>Kategori</label>

                <select name="kategori_id" class="form-control" required>

                    <option value="">-- Pilih Kategori --</option>

                    @foreach($kategori as $item)

                        <option value="{{ $item->id }}">
                            {{ $item->nama_kategori }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="form-group">
                <label>Kode Barang</label>

                <input
                    type="text"
                    name="kode_barang"
                    class="form-control"
                    value="{{ old('kode_barang') }}"
                    required>
            </div>

            <div class="form-group">
                <label>Nama Barang</label>

                <input
                    type="text"
                    name="nama_barang"
                    class="form-control"
                    value="{{ old('nama_barang') }}"
                    required>
            </div>

            <div class="form-group">
                <label>Stok</label>

                <input
                    type="number"
                    name="stok"
                    class="form-control"
                    value="{{ old('stok') }}"
                    required>
            </div>

            <div class="form-group">
                <label>Kondisi</label>

                <select name="kondisi" class="form-control">

                    <option value="Baik">Baik</option>
                    <option value="Rusak Ringan">Rusak Ringan</option>
                    <option value="Rusak Berat">Rusak Berat</option>

                </select>

            </div>

            <div class="form-group">
                <label>Deskripsi</label>

                <textarea
                    name="deskripsi"
                    class="form-control"
                    rows="4">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-group">
                <label>Foto</label>

                <input
                    type="file"
                    name="foto"
                    class="form-control">
            </div>

            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan
            </button>

            <a href="{{ route('inventaris.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

        </form>

    </div>

</div>

@stop