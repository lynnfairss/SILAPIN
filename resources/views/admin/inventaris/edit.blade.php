@extends('adminlte::page')

@section('title', 'Edit Inventaris')

@section('content_header')
    <h1>Edit Inventaris</h1>
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

        <form action="{{ route('inventaris.update',$inventari->id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="form-group">

                <label>Kategori</label>

                <select name="kategori_id" class="form-control">

                    @foreach($kategori as $item)

                        <option
                            value="{{ $item->id }}"
                            {{ $inventari->kategori_id==$item->id ? 'selected' : '' }}>

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
                    value="{{ $inventari->kode_barang }}">

            </div>

            <div class="form-group">

                <label>Nama Barang</label>

                <input
                    type="text"
                    name="nama_barang"
                    class="form-control"
                    value="{{ $inventari->nama_barang }}">

            </div>

            <div class="form-group">

                <label>Stok</label>

                <input
                    type="number"
                    name="stok"
                    class="form-control"
                    value="{{ $inventari->stok }}">

            </div>

            <div class="form-group">

                <label>Kondisi</label>

                <select name="kondisi" class="form-control">

                    <option value="Baik"
                        {{ $inventari->kondisi=='Baik' ? 'selected' : '' }}>

                        Baik

                    </option>

                    <option value="Rusak Ringan"
                        {{ $inventari->kondisi=='Rusak Ringan' ? 'selected' : '' }}>

                        Rusak Ringan

                    </option>

                    <option value="Rusak Berat"
                        {{ $inventari->kondisi=='Rusak Berat' ? 'selected' : '' }}>

                        Rusak Berat

                    </option>

                </select>

            </div>

            <div class="form-group">

                <label>Deskripsi</label>

                <textarea
                    name="deskripsi"
                    class="form-control"
                    rows="4">{{ $inventari->deskripsi }}</textarea>

            </div>

            <div class="form-group">

                <label>Foto Baru</label>

                <input
                    type="file"
                    name="foto"
                    class="form-control">

            </div>

            @if($inventari->foto)

            <div class="mb-3">

                <img src="{{ asset('storage/'.$inventari->foto) }}"
                     width="120">

            </div>

            @endif

            <button class="btn btn-success">

                <i class="fas fa-save"></i> Update

            </button>

            <a href="{{ route('inventaris.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

        </form>

    </div>

</div>

@stop