@extends('adminlte::page')

@section('title', 'Data Permohonan')

@section('content_header')
    <h1>Data Permohonan</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card">

    {{-- Tombol Tambah Permohonan dihilangkan.
         Permohonan dibuat oleh peminjam melalui website publik,
         bukan oleh Admin/Super Admin. --}}

    <div class="card-body">

        <table class="table table-bordered table-striped">

            <thead class="text-center">
                <tr>
                    <th>No</th>
                    <th>Instansi</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>

            <tbody>

            @forelse($permohonan as $item)

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->instansi->nama_instansi }}</td>
                <td>{{ $item->nama_peminjam }}</td>
                <td>{{ $item->nik }}</td>
                <td>{{ $item->tanggal_pinjam }}</td>
                <td>{{ $item->tanggal_kembali }}</td>

                <td>
                    @if($item->status=='Menunggu')
                        <span class="badge badge-warning">Menunggu</span>
                    @elseif($item->status=='Disetujui')
                        <span class="badge badge-success">Disetujui</span>
                    @elseif($item->status=='Ditolak')
                        <span class="badge badge-danger">Ditolak</span>
                    @elseif($item->status=='Dipinjam')
                        <span class="badge badge-primary">Dipinjam</span>
                    @else
                        <span class="badge badge-secondary">Dikembalikan</span>
                    @endif
                </td>

                <td class="text-center">

                    <a href="{{ route('permohonan.edit',$item->id) }}"
                        class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>

                    <form action="{{ route('permohonan.destroy',$item->id) }}"
                        method="POST"
                        style="display:inline;">

                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm"
                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                            <i class="fas fa-trash"></i>
                        </button>

                    </form>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="8" class="text-center">
                    Belum ada data permohonan.
                </td>
            </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@stop