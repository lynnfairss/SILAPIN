@extends('adminlte::page')

@section('title', 'Edit Permohonan')

@section('content_header')
<h1>Edit Permohonan</h1>
@stop

@section('content')

<div class="card">

    <div class="card-body">

        <form action="{{ route('permohonan.update',$permohonan->id) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="form-group">

                <label>Instansi</label>

                <select name="instansi_id"
                    class="form-control">

                    @foreach($instansi as $item)

                    <option value="{{ $item->id }}"
                        {{ $permohonan->instansi_id==$item->id ? 'selected':'' }}>

                        {{ $item->nama_instansi }}

                    </option>

                    @endforeach

                </select>

            </div>

            <div class="form-group">

                <label>Nama Peminjam</label>

                <input type="text"
                    name="nama_peminjam"
                    value="{{ $permohonan->nama_peminjam }}"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>NIK</label>

                <input type="text"
                    name="nik"
                    value="{{ $permohonan->nik }}"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>Jabatan</label>

                <input type="text"
                    name="jabatan"
                    value="{{ $permohonan->jabatan }}"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>Telepon</label>

                <input type="text"
                    name="telepon"
                    value="{{ $permohonan->telepon }}"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>Tanggal Pinjam</label>

                <input type="date"
                    name="tanggal_pinjam"
                    value="{{ $permohonan->tanggal_pinjam }}"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>Tanggal Kembali</label>

                <input type="date"
                    name="tanggal_kembali"
                    value="{{ $permohonan->tanggal_kembali }}"
                    class="form-control">

            </div>

            <div class="form-group">

                <label>Keperluan</label>

                <textarea
                    name="keperluan"
                    class="form-control"
                    rows="4">{{ $permohonan->keperluan }}</textarea>

            </div>

            <button class="btn btn-success">

                <i class="fas fa-save"></i>

                Update

            </button>

            <a href="{{ route('permohonan.index') }}"
                class="btn btn-secondary">

                Kembali

            </a>

        </form>

    </div>

</div>

@stop