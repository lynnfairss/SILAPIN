@extends('adminlte::page')

@section('title','Edit Kategori')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Edit Kategori</h1>

    <a href="{{ route('kategori.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>
@stop

@section('content')

<div class="card card-warning">

<form action="{{ route('kategori.update',$kategori->id) }}" method="POST">

@csrf
@method('PUT')

<div class="card-body">

<div class="form-group">

<label>Nama Kategori</label>

<input
type="text"
name="nama_kategori"
class="form-control"
value="{{ old('nama_kategori',$kategori->nama_kategori) }}">

</div>

<div class="form-group">

<label>Keterangan</label>

<textarea
name="keterangan"
class="form-control"
rows="4">{{ old('keterangan',$kategori->keterangan) }}</textarea>

</div>

</div>

<div class="card-footer">

<button class="btn btn-warning">

<i class="fas fa-save"></i>

Update

</button>

<a href="{{ route('kategori.index') }}"
class="btn btn-secondary">

Batal

</a>

</div>

</form>

</div>

@stop