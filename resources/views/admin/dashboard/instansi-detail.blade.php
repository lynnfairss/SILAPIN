@extends('layouts.admin-master')

@section('title', 'Detail Instansi - ' . $instansi->nama_instansi)
@section('page_key', 'dashboard')

@section('topbar_search_action', route('instansi.index'))
@section('topbar_search_placeholder', 'Cari instansi...')

@php
    // Class Tailwind harus literal agar ikut ter-compile oleh JIT.
    $statusPill = [
        'Menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Dipinjam' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Dikembalikan' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $statusDot = [
        'Menunggu' => 'bg-amber-500',
        'Disetujui' => 'bg-emerald-500',
        'Ditolak' => 'bg-rose-500',
        'Dipinjam' => 'bg-blue-500',
        'Dikembalikan' => 'bg-slate-400',
    ];
    $cardClass = 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm';

    $rankClass = [
        1 => 'bg-gradient-to-br from-amber-400 to-amber-600 text-white',
        2 => 'bg-gradient-to-br from-slate-300 to-slate-500 text-white',
        3 => 'bg-gradient-to-br from-orange-400 to-orange-700 text-white',
    ];

    $metrics = [
        ['icon' => 'fa-file-alt', 'box' => 'border-blue-100 bg-blue-50 text-blue-600', 'label' => 'Total Permohonan', 'value' => $totalPermohonan],
        ['icon' => 'fa-boxes', 'box' => 'border-emerald-100 bg-emerald-50 text-emerald-600', 'label' => 'Total Barang Dipinjam', 'value' => $totalBarang],
        ['icon' => 'fa-users', 'box' => 'border-violet-100 bg-violet-50 text-violet-600', 'label' => 'Peminjam Unik', 'value' => $peminjamUniq],
    ];
@endphp

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('dashboard') }}" class="transition hover:text-slate-600">Dashboard</a>
                <span>/</span>
                <span class="font-medium text-slate-600">Detail Instansi</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $instansi->nama_instansi }}</h1>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                    {{ number_format($totalPermohonan, 0, ',', '.') }} Permohonan
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Detail barang yang pernah dipinjam oleh instansi ini.</p>

            @if(filled($instansi->alamat) || filled($instansi->telepon))
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-400">
                    @if(filled($instansi->alamat))
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-map-marker-alt text-[10px]"></i>{{ $instansi->alamat }}
                        </span>
                    @endif
                    @if(filled($instansi->telepon))
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-phone text-[10px]"></i>{{ $instansi->telepon }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <a href="{{ route('dashboard') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm transition-all hover:bg-slate-50 hover:text-slate-800">
            <i class="fas fa-arrow-left text-[11px]"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    {{-- ==================== RINGKASAN ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach($metrics as $metric)
            <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $metric['box'] }}">
                    <i class="fas {{ $metric['icon'] }} text-lg"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $metric['label'] }}</p>
                    <h3 class="mt-0.5 text-xl font-bold text-slate-900">{{ number_format($metric['value'], 0, ',', '.') }}</h3>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ==================== BARANG + DAFTAR PERMOHONAN ==================== --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        {{-- Barang paling sering dipinjam --}}
        <section class="{{ $cardClass }} lg:col-span-2">
            <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                <i class="fas fa-fire text-sm text-amber-500"></i>
                <h2 class="text-sm font-bold text-slate-900">Barang Paling Sering Dipinjam</h2>
            </div>
            <div class="admin-table-wrap">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                        <tr>
                            <th class="w-12 px-3 py-3.5 text-center">#</th>
                            <th class="px-4 py-3.5">Barang</th>
                            <th class="w-20 px-4 py-3.5 text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($barangList as $i => $b)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="px-3 py-3.5 text-center">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold {{ $rankClass[$i + 1] ?? 'bg-slate-100 text-slate-500' }}">
                                        {{ $i + 1 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-slate-800">{{ $b['nama'] }}</div>
                                    <div class="mt-0.5 font-mono text-[10px] text-slate-400">{{ $b['kode'] }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700 ring-1 ring-blue-200">
                                        {{ $b['total'] }}x
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center">
                                    <i class="fas fa-box-open mb-2 block text-xl text-slate-300"></i>
                                    <span class="text-xs text-slate-400">Belum ada barang yang dipinjam.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Daftar permohonan --}}
        <section class="{{ $cardClass }} lg:col-span-3">
            <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-list text-sm text-blue-600"></i>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Permohonan</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
                    {{ number_format($permohonan->count(), 0, ',', '.') }} data
                </span>
            </div>
            <div class="admin-table-wrap">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">No. Permohonan</th>
                            <th class="px-5 py-3.5">Peminjam</th>
                            <th class="px-5 py-3.5">Tgl Pinjam</th>
                            <th class="px-5 py-3.5">Tgl Kembali</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($permohonan as $p)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-[11px] font-semibold text-slate-800">
                                    {{ $p->nomor_permohonan }}
                                </td>
                                <td class="px-5 py-3.5 font-medium text-slate-700">{{ $p->nama_peminjam }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-slate-500">
                                    {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-slate-500">
                                    {{ \Carbon\Carbon::parse($p->tanggal_kembali)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusPill[$p->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusDot[$p->status] ?? 'bg-slate-400' }}"></span>
                                        {{ $p->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <i class="fas fa-inbox mb-2 block text-xl text-slate-300"></i>
                                    <span class="text-xs text-slate-400">Belum ada permohonan dari instansi ini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- ==================== DETAIL BARANG ==================== --}}
    <section class="{{ $cardClass }}">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
            <div class="flex items-center gap-2">
                <i class="fas fa-boxes text-sm text-blue-600"></i>
                <h2 class="text-sm font-bold text-slate-900">Detail Barang yang Dipinjam</h2>
            </div>
            <span class="flex items-center gap-1.5 text-[11px] text-slate-400">
                <i class="fas fa-sort-amount-down text-[10px]"></i>
                Diurutkan dari barang paling sering dipinjam
            </span>
        </div>

        <div class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">No</th>
                        <th class="px-5 py-3.5">Barang</th>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="w-20 px-4 py-3.5 text-center">Jumlah</th>
                        <th class="px-5 py-3.5">Peminjam</th>
                        <th class="px-5 py-3.5">Tgl Pinjam</th>
                        <th class="px-5 py-3.5">Tgl Kembali</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($detailList as $i => $d)
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td class="px-4 py-3.5 text-center font-medium text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $d['barang'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-mono text-[10px] text-slate-400">{{ $d['kode'] }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700 ring-1 ring-blue-200">
                                    {{ $d['jumlah'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-700">{{ $d['peminjam'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-slate-500">
                                {{ \Carbon\Carbon::parse($d['tanggal_pinjam'])->translatedFormat('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-slate-500">
                                {{ \Carbon\Carbon::parse($d['tanggal_kembali'])->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusPill[$d['status']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot[$d['status']] ?? 'bg-slate-400' }}"></span>
                                    {{ $d['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <i class="fas fa-box-open mb-2 block text-2xl text-slate-300"></i>
                                <span class="text-xs text-slate-400">Tidak ada data barang.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
