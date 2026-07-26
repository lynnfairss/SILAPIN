@extends('adminlte::page')

@section('title','Tambah Kategori')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Tambah Kategori</h1>

    <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>
@stop

@section('content')

<div class="card card-primary">

<form action="{{ route('kategori.store') }}" method="POST">

@csrf

<div class="card-body">

<div class="form-group">

<label>Nama Kategori</label>

<input
type="text"
name="nama_kategori"
class="form-control"
value="{{ old('nama_kategori') }}">

</div>

<div class="form-group">

<label>Keterangan</label>

<textarea
name="keterangan"
class="form-control"
rows="4">{{ old('keterangan') }}</textarea>

</div>

</div>

<div class="card-footer">

<button class="btn btn-success">

<i class="fas fa-save"></i>

Simpan

</button>

<a href="{{ route('kategori.index') }}"
class="btn btn-secondary">

Batal

</a>

</div>

</form>

</div>

@stop