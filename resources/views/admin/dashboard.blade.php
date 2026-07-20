@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard SILAPIN</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Selamat Datang Di SILAPIN</h3>
    </div>

    <div class="card-body">
        <h3 class="mb-3">
            Selamat Datang,
            <strong>
                {{ Auth::user()->role == 'super_admin' ? 'Super Admin' : 'Admin' }}
            </strong> 👋
        </h3>

        <p class="text-muted">
            Selamat datang di <strong>Sistem Informasi Peminjaman Inventaris (SILAPIN)</strong>.
            Gunakan menu di sebelah kiri untuk mengelola data inventaris, permohonan peminjaman, laporan, dan aktivitas sistem.
        </p>
    </div>
</div>
@stop