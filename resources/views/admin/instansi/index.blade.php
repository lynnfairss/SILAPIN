@extends('adminlte::page')

@section('title', 'Data Instansi')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Data Instansi</h1>

        <a href="{{ route('instansi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Tambah Instansi
        </a>
    </div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">
        &times;
    </button>

    <i class="fas fa-check-circle"></i>
    {{ session('success') }}
</div>
@endif

<div class="card">

    <div class="card-header">
        <h3 class="card-title">
            Daftar Instansi
        </h3>
    </div>

    <div class="card-body">

        <table class="table table-bordered table-hover table-striped">

            <thead class="bg-primary text-white">

                <tr>
                    <th width="60">No</th>
                    <th>Nama Instansi</th>
                    <th>Alamat</th>
                    <th width="170">Telepon</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>

            </thead>

            <tbody>

            @forelse($instansi as $item)

                <tr>

                    <td class="text-center">
                        {{ $instansi->firstItem() + $loop->index }}
                    </td>

                    <td>{{ $item->nama_instansi }}</td>

                    <td>{{ $item->alamat ?? '-' }}</td>

                    <td>{{ $item->telepon ?? '-' }}</td>

                    <td class="text-center">

                        <a href="{{ route('instansi.edit',$item->id) }}"
                           class="btn btn-warning btn-sm">

                            <i class="fas fa-edit"></i>

                        </a>

                        <form action="{{ route('instansi.destroy',$item->id) }}"
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

                    <td colspan="5" class="text-center text-muted">

                        <i class="fas fa-folder-open fa-2x mb-2"></i>

                        <br>

                        Belum ada data instansi.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    @if($instansi->count())

    <div class="card-footer clearfix">

        {{ $instansi->links() }}

    </div>

    @endif

</div>

@stop