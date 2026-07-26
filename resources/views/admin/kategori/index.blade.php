@extends('adminlte::page')

@section('title', 'Data Kategori')

@section('content_header')
<div class="d-flex justify-content-between">
    <h1>Data Kategori</h1>

    <a href="{{ route('kategori.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Tambah Kategori
    </a>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">
        &times;
    </button>

    {{ session('success') }}
</div>
@endif

<div class="card">

    <div class="card-header">
        <h3 class="card-title">
            Daftar Kategori
        </h3>
    </div>

    <div class="card-body">

        <table class="table table-bordered table-hover table-striped">

            <thead class="bg-primary text-white">
                <tr>
                    <th width="60">No</th>
                    <th>Nama Kategori</th>
                    <th>Keterangan</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>

            @forelse($kategori as $item)

                <tr>

                    <td class="text-center">
                        {{ $kategori->firstItem() + $loop->index }}
                    </td>

                    <td>{{ $item->nama_kategori }}</td>

                    <td>{{ $item->keterangan ?? '-' }}</td>

                    <td class="text-center">

                        <a href="{{ route('kategori.edit',$item->id) }}"
                           class="btn btn-warning btn-sm">

                            <i class="fas fa-edit"></i>

                        </a>

                        <form action="{{ route('kategori.destroy',$item->id) }}"
                              method="POST"
                              style="display:inline-block;">

                            @csrf
                            @method('DELETE')

                            <button
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">

                                <i class="fas fa-trash"></i>

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="4" class="text-center">

                        Belum ada data kategori.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="card-footer">

        {{ $kategori->links() }}

    </div>

</div>

@stop