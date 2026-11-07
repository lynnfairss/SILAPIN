@extends('layouts.admin-master')

@section('title', 'Data Inventaris')
@section('page_key', 'inventaris')

@php
    $inventarisPayload = $inventaris->getCollection()->map(function ($item) {
        return [
            'id' => $item->id,
            'kode' => $item->kode_barang,
            'nama' => $item->nama_barang,
            'kategori_id' => $item->kategori_id,
            'kategori' => $item->kategori?->nama_kategori,
            'jenis_id' => $item->jenis_id,
            'jenis' => $item->jenis?->nama_jenis,
            'stok' => (int) $item->stok,
            'dipinjam' => (int) ($item->dipinjam ?? 0),
            'total' => (int) $item->stok + (int) ($item->dipinjam ?? 0),
            'kondisi' => $item->kondisi,
            'deskripsi' => $item->deskripsi,
            'fotos' => $item->fotos->map(function ($foto) {
                return [
                    'id' => $foto->id,
                    'url' => asset('storage/' . $foto->foto),
                ];
            })->values(),
        ];
    })->values();

    // route() dengan lebih dari satu argumen tidak bisa ditulis di dalam
    // @json(), karena direktif itu memecah argumen pada koma pertama.
    $exportUrl = route('inventaris.export', array_filter(request()->only(['q', 'jenis', 'kategori', 'kondisi'])));
@endphp

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <span>Master Data</span>
                <span>/</span>
                <span class="font-medium text-slate-600">Data Inventaris</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Inventaris Barang</h1>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                    {{ $stats['total_barang'] }} Barang
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Kelola daftar barang inventaris, ketersediaan stok, kondisi, dan foto produk SILAPIN.
            </p>
        </div>

        <button type="button" onclick="openCreate()"
                class="flex items-center justify-center gap-2 self-start rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 sm:self-auto">
            <i class="fas fa-plus text-[11px]"></i>
            <span>Tambah Inventaris</span>
        </button>
    </div>

    {{-- ==================== STATS ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                <i class="fas fa-box text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Inventaris</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['total_barang'] }} <span class="text-xs font-normal text-slate-400">Barang</span>
                </h3>
                <p class="mt-0.5 text-[10px] font-medium text-emerald-600">Tersedia {{ $stats['tersedia'] }} unit</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Kondisi Baik</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['kondisi_baik'] }} <span class="text-xs font-normal text-slate-400">Barang</span>
                </h3>
                <p class="mt-0.5 text-[10px] font-medium text-emerald-600">Siap digunakan</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600">
                <i class="fas fa-clock text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Sedang Dipinjam</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['dipinjam'] }} <span class="text-xs font-normal text-slate-400">Unit</span>
                </h3>
                <p class="mt-0.5 text-[10px] text-slate-400">Aktif dipinjam instansi</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Kapasitas</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['total_unit'] }} <span class="text-xs font-normal text-slate-400">Unit</span>
                </h3>
                <p class="mt-0.5 text-[10px] text-slate-400">Keseluruhan fisik aset</p>
            </div>
        </div>
    </div>

    {{-- ==================== DATA CARD ==================== --}}
    <div id="inventarisDataCard" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        {{-- Toolbar --}}
        <form method="GET" action="{{ route('inventaris.index') }}"
              class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative min-w-[200px] flex-1 sm:max-w-xs">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="search" name="q" value="{{ $q }}"
                           placeholder="Cari nama, kode, atau deskripsi..."
                           class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-9 text-xs text-slate-700 placeholder-slate-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    @if($q !== '')
                        <a href="{{ route('inventaris.index', request()->except('q', 'page')) }}"
                           class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                           title="Hapus pencarian">
                            <i class="fas fa-times text-xs"></i>
                        </a>
                    @endif
                </div>

                <select name="kategori" onchange="this.form.submit()"
                        class="shrink-0 min-w-[130px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Kategori: Semua</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat->id }}" @selected((string) request('kategori') === (string) $kat->id)>
                            {{ $kat->nama_kategori }} ({{ $kategoriCount[$kat->id] ?? 0 }})
                        </option>
                    @endforeach
                </select>

                <select name="jenis" onchange="this.form.submit()"
                        class="shrink-0 min-w-[130px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Jenis: Semua</option>
                    @foreach($jenisList as $j)
                        <option value="{{ $j->id }}" @selected((string) request('jenis') === (string) $j->id)>
                            {{ $j->nama_jenis }} ({{ $jenisCount[$j->id] ?? 0 }})
                        </option>
                    @endforeach
                </select>

                <select name="kondisi" onchange="this.form.submit()"
                        class="shrink-0 min-w-[130px] rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Kondisi: Semua</option>
                    <option value="Baik" @selected(request('kondisi') === 'Baik')>Baik</option>
                    <option value="Rusak Ringan" @selected(request('kondisi') === 'Rusak Ringan')>Rusak Ringan</option>
                    <option value="Rusak Berat" @selected(request('kondisi') === 'Rusak Berat')>Rusak Berat</option>
                </select>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div data-view-group data-view-target="#inventarisDataCard"
                         class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-200/70 p-1">
                        <button type="button" data-view="table" class="view-toggle-btn is-active" title="Tampilan tabel">
                            <i class="fas fa-list text-[12px]"></i>
                            <span class="hidden sm:inline">Tabel</span>
                        </button>
                        <button type="button" data-view="grid" class="view-toggle-btn" title="Tampilan kartu">
                            <i class="fas fa-th-large text-[12px]"></i>
                            <span class="hidden sm:inline">Kartu</span>
                        </button>
                    </div>

                    <a href="{{ route('inventaris.index') }}"
                       class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition-all hover:bg-slate-100"
                       title="Reset semua filter">
                        <i class="fas fa-sync text-[12px]"></i>
                    </a>
                </div>

                <button type="button" onclick="exportData()"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100">
                    <i class="fas fa-download text-[11px]"></i>
                    <span class="hidden sm:inline">Ekspor Excel</span>
                </button>
            </div>
        </form>

        {{-- TABLE VIEW --}}
        <div data-view-panel="table" class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">No</th>
                        <th class="px-4 py-3.5">Kode</th>
                        <th class="px-4 py-3.5 text-center">Foto</th>
                        <th class="px-5 py-3.5">Nama Barang</th>
                        <th class="px-4 py-3.5">Kategori &amp; Jenis</th>
                        <th class="px-4 py-3.5 text-center">Stok</th>
                        <th class="px-4 py-3.5 text-center">Kondisi</th>
                        <th class="w-32 px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventaris as $item)
                        @php
                            $dipinjam = (int) ($item->dipinjam ?? 0);
                            $totalStok = (int) $item->stok + $dipinjam;
                            $fotoUrls = $item->fotos->map(fn ($f) => asset('storage/' . $f->foto))->values();
                            $kondisiClass = match ($item->kondisi) {
                                'Baik' => 'border-emerald-200/60 bg-emerald-50 text-emerald-700',
                                'Rusak Ringan' => 'border-amber-200 bg-amber-50 text-amber-700',
                                default => 'border-rose-200 bg-rose-50 text-rose-700',
                            };
                            $kondisiDot = match ($item->kondisi) {
                                'Baik' => 'bg-emerald-500',
                                'Rusak Ringan' => 'bg-amber-500',
                                default => 'bg-rose-500',
                            };
                        @endphp
                        <tr class="group transition-colors hover:bg-slate-50/80">
                            <td class="px-4 py-3.5 text-center font-medium text-slate-400">
                                {{ $inventaris->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="whitespace-nowrap rounded-md border border-rose-100 bg-rose-50 px-2 py-1 font-mono text-[11px] font-semibold text-rose-500">
                                    {{ $item->kode_barang }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($item->fotos->count() > 0)
                                    <div class="relative inline-block cursor-pointer"
                                         data-foto-urls='@json($fotoUrls)'
                                         onclick="openFoto(JSON.parse(this.dataset.fotoUrls), 0)"
                                         title="Lihat foto">
                                        <img src="{{ asset('storage/' . $item->fotos->first()->foto) }}"
                                             alt="{{ $item->nama_barang }}"
                                             class="h-10 w-10 rounded-xl border border-slate-200 object-cover shadow-sm transition-transform group-hover:scale-105">
                                        @if($item->fotos->count() > 1)
                                            <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full border border-white bg-blue-600 text-[9px] font-bold text-white">
                                                +{{ $item->fotos->count() - 1 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <button type="button" onclick="openDetail({{ $item->id }})"
                                        class="text-xs font-bold text-slate-900 transition-colors hover:text-blue-600">
                                    {{ $item->nama_barang }}
                                </button>
                                <p class="mt-0.5 max-w-xs truncate text-[11px] text-slate-400">
                                    {{ $item->deskripsi ?: 'Tanpa deskripsi' }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700">
                                        {{ $item->kategori?->nama_kategori ?? 'Tanpa Kategori' }}
                                    </span>
                                    <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">
                                        {{ $item->jenis?->nama_jenis ?? 'Tanpa Jenis' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="inline-flex flex-col items-center whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-800">{{ $totalStok }} Unit</span>
                                    <span class="text-[10px] font-medium text-emerald-600">{{ $item->stok }} Tersedia</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $kondisiClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $kondisiDot }}"></span>
                                    {{ $item->kondisi }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openDetail({{ $item->id }})" title="Lihat Detail"
                                            class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                        <i class="fas fa-eye text-[13px]"></i>
                                    </button>
                                    <button type="button" onclick="openEdit({{ $item->id }})" title="Edit Inventaris"
                                            class="rounded-lg border border-transparent p-1.5 text-blue-600 transition-all hover:border-blue-200 hover:bg-blue-50">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('inventaris.destroy', $item->id)), @js($item->nama_barang))"
                                            title="Hapus Inventaris"
                                            class="rounded-lg border border-transparent p-1.5 text-rose-600 transition-all hover:border-rose-200 hover:bg-rose-50">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                <i class="fas fa-box-open mb-2 text-4xl text-slate-300"></i>
                                <p class="text-xs font-medium text-slate-500">Tidak ada data inventaris ditemukan</p>
                                <p class="mt-1 text-[11px] text-slate-400">Coba ubah filter atau tambah inventaris baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- GRID VIEW --}}
        <div data-view-panel="grid" class="hidden p-5">
            @if($inventaris->count() > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($inventaris as $item)
                        @php
                            $dipinjam = (int) ($item->dipinjam ?? 0);
                            $totalStok = (int) $item->stok + $dipinjam;
                            $fotoUrls = $item->fotos->map(fn ($f) => asset('storage/' . $f->foto))->values();
                            $kondisiSolid = match ($item->kondisi) {
                                'Baik' => 'bg-emerald-500',
                                'Rusak Ringan' => 'bg-amber-500',
                                default => 'bg-rose-500',
                            };
                        @endphp
                        <div class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all hover:border-blue-200 hover:shadow-md">
                            <div>
                                <div class="relative h-44 overflow-hidden bg-slate-100">
                                    @if($item->fotos->count() > 0)
                                        <img src="{{ asset('storage/' . $item->fotos->first()->foto) }}"
                                             alt="{{ $item->nama_barang }}"
                                             class="h-full w-full cursor-pointer object-cover transition-transform duration-300 group-hover:scale-105"
                                             data-foto-urls='@json($fotoUrls)'
                                             onclick="openFoto(JSON.parse(this.dataset.fotoUrls), 0)">
                                    @else
                                        <div class="flex h-full items-center justify-center text-slate-300">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    @endif

                                    <span class="absolute left-3 top-3 rounded-md bg-slate-900/80 px-2 py-0.5 font-mono text-[10px] font-bold text-white backdrop-blur-sm">
                                        {{ $item->kode_barang }}
                                    </span>
                                    <span class="absolute right-3 top-3 rounded-full px-2 py-0.5 text-[10px] font-semibold text-white {{ $kondisiSolid }}">
                                        {{ $item->kondisi }}
                                    </span>
                                </div>

                                <div class="space-y-2 p-4">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                                            {{ $item->kategori?->nama_kategori ?? 'Tanpa Kategori' }}
                                        </span>
                                        <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">
                                            {{ $item->jenis?->nama_jenis ?? 'Tanpa Jenis' }}
                                        </span>
                                    </div>
                                    <button type="button" onclick="openDetail({{ $item->id }})"
                                            class="line-clamp-1 text-left text-xs font-bold text-slate-900 transition-colors group-hover:text-blue-600">
                                        {{ $item->nama_barang }}
                                    </button>
                                    <p class="line-clamp-2 text-[11px] text-slate-400">
                                        {{ $item->deskripsi ?: 'Tanpa deskripsi.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2 border-t border-slate-100 p-4 pt-0">
                                <div class="my-3 flex items-center justify-between text-xs">
                                    <div>
                                        <span class="block text-[10px] font-semibold text-slate-400">Tersedia</span>
                                        <span class="font-bold text-emerald-600">{{ $item->stok }} Unit</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-[10px] font-semibold text-slate-400">Total Stok</span>
                                        <span class="font-bold text-slate-800">{{ $totalStok }} Unit</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 border-t border-slate-100 pt-2">
                                    <button type="button" onclick="openDetail({{ $item->id }})"
                                            class="flex flex-1 items-center justify-center gap-1 rounded-xl bg-slate-100 py-1.5 text-xs font-medium text-slate-700 transition-all hover:bg-slate-200">
                                        <i class="fas fa-eye text-[12px]"></i> Detail
                                    </button>
                                    <button type="button" onclick="openEdit({{ $item->id }})" title="Edit"
                                            class="rounded-xl p-1.5 text-blue-600 transition-all hover:bg-blue-50">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('inventaris.destroy', $item->id)), @js($item->nama_barang))"
                                            title="Hapus"
                                            class="rounded-xl p-1.5 text-rose-600 transition-all hover:bg-rose-50">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <i class="fas fa-box-open mb-2 text-4xl text-slate-300"></i>
                    <p class="text-xs font-medium text-slate-500">Tidak ada data inventaris ditemukan</p>
                </div>
            @endif
        </div>

        {{ $inventaris->links('vendor.pagination.admin') }}
    </div>

    {{-- ==================== FORM MODAL ==================== --}}
    <div id="inventarisFormModal" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card my-8 w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-blue-600 p-1.5 text-white">
                        <i class="fas fa-box text-sm"></i>
                    </div>
                    <div>
                        <h3 id="formModalTitle" class="text-sm font-bold">Tambah Inventaris Baru</h3>
                        <p class="text-[10px] text-slate-400">Lengkapi detail spesifikasi &amp; foto fisik produk</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="formInventaris" method="POST" action="{{ route('inventaris.store') }}"
                  enctype="multipart/form-data" class="max-h-[80vh] space-y-4 overflow-y-auto p-6 text-xs">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="edit_id" id="editId" value="{{ old('edit_id') }}">
                <input type="hidden" name="hapus_foto" id="hapusFotoHidden" value="">

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-700">
                        <div class="mb-1 flex items-center gap-2 font-semibold">
                            <i class="fas fa-exclamation-triangle"></i>
                            Periksa kembali data Anda
                        </div>
                        <ul class="list-inside list-disc space-y-0.5 text-[11px]">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700">Kategori Utama</label>
                        <select name="kategori_id" id="inputKategori"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-medium text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat->id }}" @selected((string) old('kategori_id') === (string) $kat->id)>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700">
                            Jenis Barang <span class="text-rose-500">*</span>
                        </label>
                        <select name="jenis_id" id="inputJenis" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-medium text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($jenisList as $j)
                                <option value="{{ $j->id }}" @selected((string) old('jenis_id') === (string) $j->id)>
                                    {{ $j->nama_jenis }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block font-semibold text-slate-700">
                            Nama Barang Inventaris <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_barang" id="inputNama"
                               value="{{ old('nama_barang') }}" required maxlength="150"
                               placeholder="Contoh: Laptop ASUS VivoBook 14"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-medium text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700">
                            Kode Barang <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="kode_barang" id="inputKode"
                               value="{{ old('kode_barang') }}" required maxlength="30"
                               placeholder="ELK-001"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-xs text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700">
                            Jumlah Stok Fisik <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="stok" id="inputStok"
                               value="{{ old('stok', 1) }}" min="0" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-semibold text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    <div>
                        <label class="mb-1 block font-semibold text-slate-700">
                            Kondisi Barang <span class="text-rose-500">*</span>
                        </label>
                        <select name="kondisi" id="inputKondisi" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="Baik" @selected(old('kondisi', 'Baik') === 'Baik')>Baik (Siap Digunakan)</option>
                            <option value="Rusak Ringan" @selected(old('kondisi') === 'Rusak Ringan')>Rusak Ringan</option>
                            <option value="Rusak Berat" @selected(old('kondisi') === 'Rusak Berat')>Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Deskripsi / Spesifikasi Barang</label>
                    <textarea name="deskripsi" id="inputDeskripsi" rows="3" maxlength="2000"
                              placeholder="Tuliskan spesifikasi lengkap, nomor seri, kelengkapan aksesoris..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ old('deskripsi') }}</textarea>
                </div>

                {{-- FOTO --}}
                <div>
                    <label class="mb-1 block font-semibold text-slate-700">
                        Foto Fisik Barang <span class="font-normal text-slate-400">(Maks. 5 Foto)</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @for($i = 0; $i < 5; $i++)
                            <label class="foto-slot"
                                   ondragover="event.preventDefault(); this.classList.add('is-dragover')"
                                   ondragleave="this.classList.remove('is-dragover')"
                                   ondrop="handleDrop(event, {{ $i }}); this.classList.remove('is-dragover')">
                                <input type="file" name="foto[]" id="inputFoto{{ $i }}" accept="image/*"
                                       class="sr-only" onchange="previewFoto({{ $i }}, this)">
                                <div id="fotoPreview{{ $i }}" class="foto-slot-preview"></div>
                                <span class="text-[10px] font-semibold text-slate-500">Foto {{ $i + 1 }}</span>
                                <span class="text-[9px] text-slate-400">Klik / tarik gambar</span>
                            </label>
                        @endfor
                    </div>
                    <p class="mt-1 text-[10px] text-slate-400">Format JPG/PNG, ukuran maksimal 2MB per file.</p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" data-modal-close
                            class="rounded-xl border border-slate-200 px-4 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                        <i class="fas fa-check text-[11px]"></i>
                        <span id="btnSubmitText">Simpan Inventaris</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== DETAIL DRAWER ==================== --}}
    <div id="inventarisDetailDrawer" class="admin-drawer hidden fixed inset-0 z-[70] bg-slate-900/60 backdrop-blur-sm">
        <div class="admin-drawer-panel flex h-full w-full max-w-md flex-col overflow-hidden bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2">
                    <i class="fas fa-box text-blue-400"></i>
                    <h3 class="text-sm font-bold">Detail Inventaris</h3>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto p-6 text-xs">
                <div class="relative h-48 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                    <img id="detailMainFoto" src="" alt="Foto barang" class="hidden h-full w-full cursor-pointer object-cover">
                    <div id="detailNoFoto" class="flex h-full items-center justify-center text-slate-300">
                        <i class="fas fa-image text-4xl"></i>
                    </div>
                    <span id="detailKode" class="absolute bottom-3 left-3 rounded-md bg-slate-900/80 px-2 py-0.5 font-mono text-[10px] font-bold text-white backdrop-blur-sm">
                        —
                    </span>
                </div>

                <div id="detailThumbs" class="flex flex-wrap gap-2"></div>

                <div>
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                        <span id="detailKategori" class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">—</span>
                        <span id="detailJenis" class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700">—</span>
                        <span id="detailKondisi" class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold">—</span>
                    </div>
                    <h2 id="detailNama" class="text-lg font-bold text-slate-900">—</h2>
                    <p id="detailDeskripsi" class="mt-1 text-xs leading-relaxed text-slate-500">—</p>
                </div>

                <div class="grid grid-cols-3 gap-2 rounded-2xl border border-slate-100 bg-slate-50 p-3.5 text-center">
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400">Total</span>
                        <p id="detailTotal" class="mt-0.5 text-sm font-bold text-slate-800">0 Unit</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400">Tersedia</span>
                        <p id="detailTersedia" class="mt-0.5 text-sm font-bold text-emerald-600">0 Unit</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400">Dipinjam</span>
                        <p id="detailDipinjam" class="mt-0.5 text-sm font-bold text-blue-600">0 Unit</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700">
                            <i class="fas fa-info-circle text-lg"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Data fisik inventaris</p>
                            <p class="text-[10px] text-slate-400">Stok tersedia diperbarui otomatis saat peminjaman diproses.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 p-4">
                <button type="button" data-modal-close
                        class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-medium text-slate-700 transition-all hover:bg-slate-300">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== LIGHTBOX ==================== --}}
    <div class="image-modal-overlay" id="imageModal" onclick="closeFoto(event)">
        <div class="image-modal-content">
            <button type="button" class="modal-close" onclick="closeFoto()">&times;</button>
            <button type="button" class="modal-nav modal-prev" onclick="navFoto(-1)"><i class="fas fa-chevron-left"></i></button>
            <img id="modalFotoImg" src="" alt="Foto Barang">
            <button type="button" class="modal-nav modal-next" onclick="navFoto(1)"><i class="fas fa-chevron-right"></i></button>
            <div class="modal-caption" id="modalFotoCaption"></div>
            <div class="modal-dots" id="modalFotoDots"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const INVENTARIS = @json($inventarisPayload);
    const INVENTARIS_BASE = @json(url('inventaris'));
    const INVENTARIS_STORE = @json(route('inventaris.store'));
    const INVENTARIS_EXPORT = @json($exportUrl);

    let fotoDihapus = [];
    let fotoIdSlot = {};
    let fotoList = [];
    let fotoIndex = 0;

    /* -------------------- Form -------------------- */
    function openCreate(preserveOld = false) {
        const form = document.getElementById('formInventaris');
        form.action = INVENTARIS_STORE;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('formModalTitle').textContent = 'Tambah Inventaris Baru';
        document.getElementById('btnSubmitText').textContent = 'Simpan Inventaris';

        if (!preserveOld) {
            document.getElementById('inputKategori').value = '';
            document.getElementById('inputKode').value = '';
            document.getElementById('inputNama').value = '';
            document.getElementById('inputJenis').value = '';
            document.getElementById('inputStok').value = '1';
            document.getElementById('inputKondisi').value = 'Baik';
            document.getElementById('inputDeskripsi').value = '';
        }

        resetFotoState(null);
        AdminUI.openModal('inventarisFormModal');
    }

    function openEdit(id, preserveOld = false) {
        const item = INVENTARIS.find(function (row) { return row.id === id; });
        if (!item) return;

        const form = document.getElementById('formInventaris');
        form.action = INVENTARIS_BASE + '/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('formModalTitle').textContent = 'Edit Data Inventaris';
        document.getElementById('btnSubmitText').textContent = 'Simpan Perubahan';

        if (!preserveOld) {
            document.getElementById('inputKategori').value = item.kategori_id || '';
            document.getElementById('inputKode').value = item.kode || '';
            document.getElementById('inputNama').value = item.nama || '';
            document.getElementById('inputJenis').value = item.jenis_id || '';
            document.getElementById('inputStok').value = item.stok;
            document.getElementById('inputKondisi').value = item.kondisi || 'Baik';
            document.getElementById('inputDeskripsi').value = item.deskripsi || '';
        }

        resetFotoState(item);
        AdminUI.openModal('inventarisFormModal');
    }

    /* -------------------- Foto -------------------- */
    function resetFotoState(item) {
        fotoDihapus = [];
        fotoIdSlot = {};

        const hidden = document.getElementById('hapusFotoHidden');
        if (hidden) hidden.value = '';

        for (let i = 0; i < 5; i++) {
            const preview = document.getElementById('fotoPreview' + i);
            const input = document.getElementById('inputFoto' + i);
            if (input) input.value = '';
            if (!preview) continue;

            if (item && item.fotos && item.fotos[i]) {
                fotoIdSlot[i] = item.fotos[i].id;
                preview.innerHTML =
                    '<div class="relative h-full w-full">' +
                        '<img src="' + item.fotos[i].url + '" alt="Foto ' + (i + 1) + '">' +
                        '<button type="button" class="absolute -right-1.5 -top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-[10px] text-white shadow" title="Hapus foto" onclick="hapusFoto(' + i + ', ' + item.fotos[i].id + ')">' +
                            '<i class="fas fa-times"></i>' +
                        '</button>' +
                    '</div>';
            } else {
                preview.innerHTML = '';
            }
        }
    }

    function previewFoto(i, input) {
        const el = document.getElementById('fotoPreview' + i);
        if (!el) return;

        if (input.files && input.files[0]) {
            if (fotoIdSlot[i]) {
                fotoDihapus = fotoDihapus.filter(function (id) { return id !== fotoIdSlot[i]; });
            }
            el.innerHTML = '<img src="' + URL.createObjectURL(input.files[0]) + '" alt="Preview">';
        } else {
            el.innerHTML = '';
        }
    }

    function handleDrop(event, i) {
        event.preventDefault();
        const input = document.getElementById('inputFoto' + i);
        if (!input || !event.dataTransfer || !event.dataTransfer.files.length) return;

        try {
            input.files = event.dataTransfer.files;
            previewFoto(i, input);
        } catch (error) {
            // Browser lama tidak mengizinkan assignment FileList; fallback ke klik.
        }
    }

    function hapusFoto(i, fotoId) {
        if (!fotoId) return;
        if (!fotoDihapus.includes(fotoId)) {
            fotoDihapus.push(fotoId);
        }
        const el = document.getElementById('fotoPreview' + i);
        if (el) {
            el.innerHTML = '<span class="rounded bg-rose-100 px-2 py-1 text-[10px] font-semibold text-rose-600">Akan dihapus</span>';
        }
        const input = document.getElementById('inputFoto' + i);
        if (input) input.value = '';
    }

    document.getElementById('formInventaris').addEventListener('submit', function () {
        document.getElementById('hapusFotoHidden').value = fotoDihapus.join(',');
    });

    /* -------------------- Detail -------------------- */
    function openDetail(id) {
        const item = INVENTARIS.find(function (row) { return row.id === id; });
        if (!item) return;

        document.getElementById('detailKode').textContent = item.kode || '—';
        document.getElementById('detailNama').textContent = item.nama || '—';
        document.getElementById('detailKategori').textContent = item.kategori || 'Tanpa Kategori';
        document.getElementById('detailJenis').textContent = item.jenis || 'Tanpa Jenis';
        document.getElementById('detailDeskripsi').textContent = item.deskripsi || 'Tidak ada deskripsi.';
        document.getElementById('detailTotal').textContent = (item.total || 0) + ' Unit';
        document.getElementById('detailTersedia').textContent = (item.stok || 0) + ' Unit';
        document.getElementById('detailDipinjam').textContent = (item.dipinjam || 0) + ' Unit';

        const kondisi = document.getElementById('detailKondisi');
        kondisi.textContent = item.kondisi || '—';
        kondisi.className = 'rounded-full border px-2.5 py-0.5 text-[10px] font-semibold ' + (
            item.kondisi === 'Baik'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : item.kondisi === 'Rusak Ringan'
                    ? 'border-amber-200 bg-amber-50 text-amber-700'
                    : 'border-rose-200 bg-rose-50 text-rose-700'
        );

        const main = document.getElementById('detailMainFoto');
        const noFoto = document.getElementById('detailNoFoto');
        const thumbs = document.getElementById('detailThumbs');
        thumbs.innerHTML = '';

        const urls = (item.fotos || []).map(function (foto) { return foto.url; });

        if (urls.length) {
            main.src = urls[0];
            main.classList.remove('hidden');
            noFoto.classList.add('hidden');
            main.onclick = function () { openFoto(urls, 0); };

            urls.forEach(function (url, index) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'h-12 w-12 overflow-hidden rounded-lg border border-slate-200 transition hover:ring-2 hover:ring-blue-400';
                button.onclick = function () { openFoto(urls, index); };

                const image = document.createElement('img');
                image.src = url;
                image.alt = 'Foto ' + (index + 1);
                image.className = 'h-full w-full object-cover';

                button.appendChild(image);
                thumbs.appendChild(button);
            });
        } else {
            main.classList.add('hidden');
            main.removeAttribute('src');
            noFoto.classList.remove('hidden');
        }

        AdminUI.openDrawer('inventarisDetailDrawer');
    }

    /* -------------------- Lightbox -------------------- */
    function openFoto(urls, start) {
        fotoList = urls || [];
        fotoIndex = start || 0;
        if (!fotoList.length) return;
        document.getElementById('imageModal').classList.add('active');
        updateFotoModal();
    }

    function navFoto(dir) {
        if (!fotoList.length) return;
        fotoIndex = (fotoIndex + dir + fotoList.length) % fotoList.length;
        updateFotoModal();
    }

    function updateFotoModal() {
        const img = document.getElementById('modalFotoImg');
        img.src = fotoList[fotoIndex];
        document.getElementById('modalFotoCaption').textContent = (fotoIndex + 1) + ' / ' + fotoList.length;

        const dots = document.getElementById('modalFotoDots');
        dots.innerHTML = fotoList.map(function (_, index) {
            return '<span class="dot ' + (index === fotoIndex ? 'active' : '') + '" onclick="fotoIndex=' + index + ';updateFotoModal()"></span>';
        }).join('');
    }

    function closeFoto(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('imageModal').classList.remove('active');
    }

    document.addEventListener('keydown', function (event) {
        if (!document.getElementById('imageModal').classList.contains('active')) return;
        if (event.key === 'Escape') closeFoto();
        if (event.key === 'ArrowLeft') navFoto(-1);
        if (event.key === 'ArrowRight') navFoto(1);
    });

    /* -------------------- Export -------------------- */
    function exportData() {
        window.location.assign(INVENTARIS_EXPORT);
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->any())
            const oldEditId = @json(old('edit_id'));
            if (oldEditId) {
                openEdit(Number(oldEditId), true);
            } else {
                openCreate(true);
            }
        @endif
    });
</script>
@endpush
