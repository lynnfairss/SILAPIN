@extends('adminlte::page')

@section('title', 'Kelola User')

@section('content_header')
    <h1>Kelola User Admin</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-shield me-2 text-primary"></i>Daftar Admin &amp; Super Admin</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead class="text-center">
                <tr>
                    <th width="50">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td class="text-center">
                    @if($item->isSuperAdmin())
                        <span class="badge badge-soft-dark">Super Admin</span>
                    @else
                        <span class="badge badge-soft-info">Admin</span>
                    @endif
                </td>
                <td>{{ $item->created_at->format('d M Y') }}</td>
                <td class="text-center">
                    @if(!$item->isSuperAdmin())
                    <form action="{{ route('users.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" title="Hapus"
                            onclick="return confirm('Yakin ingin menghapus user ini?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Belum ada data user admin.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@stop
