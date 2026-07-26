@extends('adminlte::page')

@section('title', 'Data Inventaris')

@section('content_header')
    <h1>Data Inventaris</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card">

    <div class="card-header">

        <a href="{{ route('inventaris.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Inventaris
        </a>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Kondisi</th>
                    <th>Foto</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>

            <tbody>

            @forelse($inventaris as $item)

            <tr>

                <td>{{ $loop->iteration }}</td>

                <td>{{ $item->kode_barang }}</td>

                <td>{{ $item->nama_barang }}</td>

                <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>

                <td>{{ $item->stok }}</td>

                <td>{{ ucfirst($item->kondisi) }}</td>

                <td>

                    @if($item->foto)

                        <img src="{{ asset('storage/'.$item->foto) }}"
                             width="70">

                    @else

                        -

                    @endif

                </td>

                <td>

                    <a href="{{ route('inventaris.edit',$item->id) }}"
                        class="btn btn-warning btn-sm">

                        <i class="fas fa-edit"></i>

                    </a>

                    <form action="{{ route('inventaris.destroy',$item->id) }}"
                          method="POST"
                          style="display:inline;">

                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm('Hapus data?')">

                            <i class="fas fa-trash"></i>

                        </button>

                    </form>

                </td>

            </tr>

            @empty

            <tr>

                <td colspan="8" class="text-center">

                    Belum ada data inventaris.

                </td>

            </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@stop