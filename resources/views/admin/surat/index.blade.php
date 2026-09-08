@extends('adminlte::page')

@section('title', 'Surat Permohonan')

@section('content_header')
    <h1>Surat Permohonan</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="text-center">
                <tr>
                    <th>No</th>
                    <th>Nomor Surat</th>
                    <th>Nama Peminjam</th>
                    <th>Instansi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Kustomisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($permohonan as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td><code>{{ $item->nomor_permohonan }}</code></td>
                <td>{{ $item->nama_peminjam }}</td>
                <td>{{ $item->instansi?->nama_instansi ?? $item->nama_instansi_lain ?? '-' }}</td>
                <td>{{ $item->created_at->format('d M Y') }}</td>
                <td class="text-center">
                    @if($item->status == 'Menunggu')
                        <span class="badge badge-warning">Menunggu</span>
                    @elseif($item->status == 'Disetujui')
                        <span class="badge badge-success">Disetujui</span>
                    @elseif($item->status == 'Ditolak')
                        <span class="badge badge-danger">Ditolak</span>
                    @elseif($item->status == 'Dipinjam')
                        <span class="badge badge-primary">Dipinjam</span>
                    @else
                        <span class="badge badge-secondary">Dikembalikan</span>
                    @endif
                </td>
                <td class="text-center">
                    @if(!empty($item->surat_content) && count(array_filter($item->surat_content ?? [])) > 0)
                        <span class="badge badge-primary">Custom</span>
                    @else
                        <span class="badge badge-secondary">Global</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('surat.preview', $item->id) }}" class="btn btn-info btn-sm" title="Preview Surat" target="_blank">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('surat.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit Surat">
                        <i class="fas fa-pen"></i>
                    </a>
                    <a href="{{ route('peminjam.download-surat.docx', $item->id) }}" class="btn btn-success btn-sm" title="Download .docx">
                        <i class="fas fa-file-word"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">
                    Belum ada data permohonan.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop