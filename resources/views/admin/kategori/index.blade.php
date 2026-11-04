@extends('layouts.admin-master')

@section('title', 'Data Kategori')
@section('page_key', 'kategori')

@php
    $palette = [
        ['dot' => 'bg-blue-500', 'badge' => 'border-blue-200/80 bg-blue-50 text-blue-700'],
        ['dot' => 'bg-indigo-500', 'badge' => 'border-indigo-200/80 bg-indigo-50 text-indigo-700'],
        ['dot' => 'bg-amber-500', 'badge' => 'border-amber-200/80 bg-amber-50 text-amber-700'],
        ['dot' => 'bg-emerald-500', 'badge' => 'border-emerald-200/80 bg-emerald-50 text-emerald-700'],
        ['dot' => 'bg-rose-500', 'badge' => 'border-rose-200/80 bg-rose-50 text-rose-700'],
        ['dot' => 'bg-purple-500', 'badge' => 'border-purple-200/80 bg-purple-50 text-purple-700'],
    ];

    $kategoriPayload = $kategori->getCollection()->map(function ($item) {
        return [
            'id' => $item->id,
            'nama' => $item->nama_kategori,
            'keterangan' => $item->keterangan,
            'kode' => 'KAT-' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT),
            'total' => $item->inventaris_count ?? 0,
        ];
    })->values();
@endphp

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <span>Master Data</span>
                <span>/</span>
                <span class="font-medium text-slate-600">Data Kategori</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Kategori</h1>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                    {{ $stats['total'] }} Kategori
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Kelola pengelompokan jenis dan kategori barang inventaris dalam sistem SILAPIN.
            </p>
        </div>

        <button type="button" onclick="openCreate()"
                class="flex items-center justify-center gap-2 self-start rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 sm:self-auto">
            <i class="fas fa-plus text-[11px]"></i>
            <span>Tambah Kategori</span>
        </button>
    </div>

    {{-- ==================== STATS ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                <i class="fas fa-tags text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Kategori</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">{{ $stats['total'] }}</h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Kategori Terpakai</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">{{ $stats['terpakai'] }}</h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600">
                <i class="fas fa-box text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Item Terikat</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['total_item'] }} <span class="text-xs font-normal text-slate-500">Barang</span>
                </h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600">
                <i class="fas fa-chart-line text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Paling Banyak Item</p>
                <h3 class="mt-0.5 truncate text-sm font-bold text-slate-900">{{ $stats['terpopuler'] }}</h3>
            </div>
        </div>
    </div>

    {{-- ==================== DATA CARD ==================== --}}
    <div id="kategoriDataCard" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        {{-- Toolbar --}}
        <form method="GET" action="{{ route('kategori.index') }}"
              class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-4 md:flex-row md:items-center md:justify-between">
            <div class="relative w-full max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Cari nama kategori, kode, atau keterangan..."
                       class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-9 text-xs text-slate-700 placeholder-slate-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                @if($q !== '')
                    <a href="{{ route('kategori.index') }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                       title="Hapus pencarian">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 md:justify-end">
                <div data-view-group data-view-target="#kategoriDataCard"
                     class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-200/70 p-1">
                    <button type="button" data-view="table" class="view-toggle-btn is-active">
                        <i class="fas fa-list text-[12px]"></i>
                        <span class="hidden sm:inline">Tabel</span>
                    </button>
                    <button type="button" data-view="grid" class="view-toggle-btn">
                        <i class="fas fa-th-large text-[12px]"></i>
                        <span class="hidden sm:inline">Kartu</span>
                    </button>
                </div>

                <button type="button" onclick="exportData()"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100">
                    <i class="fas fa-download text-[11px]"></i>
                    <span class="hidden sm:inline">Ekspor Data</span>
                </button>
            </div>
        </form>

        {{-- TABLE VIEW --}}
        <div data-view-panel="table" class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">No</th>
                        <th class="px-5 py-3.5">Kode</th>
                        <th class="px-5 py-3.5">Nama Kategori</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">Total Item</th>
                        <th class="w-28 px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kategori as $item)
                        @php $color = $palette[$item->id % count($palette)]; @endphp
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td class="px-4 py-4 text-center font-medium text-slate-400">
                                {{ $kategori->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="whitespace-nowrap rounded-md border border-slate-200 bg-slate-100 px-2 py-1 font-mono text-[11px] font-semibold text-slate-700">
                                    KAT-{{ str_pad((string) $item->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="h-3 w-3 rounded-full {{ $color['dot'] }}"></span>
                                    <span class="text-xs font-semibold text-slate-900">{{ $item->nama_kategori }}</span>
                                </div>
                            </td>
                            <td class="max-w-sm truncate px-5 py-4 text-slate-500">
                                {{ $item->keterangan ?: '— Tidak ada keterangan —' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="whitespace-nowrap rounded-full border border-slate-200/80 bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                    {{ $item->inventaris_count ?? 0 }} barang
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openEdit({{ $item->id }})"
                                            title="Edit Kategori"
                                            class="rounded-lg border border-transparent p-1.5 text-blue-600 transition-all hover:border-blue-200 hover:bg-blue-50">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('kategori.destroy', $item->id)), @js($item->nama_kategori))"
                                            title="Hapus Kategori"
                                            class="rounded-lg border border-transparent p-1.5 text-rose-600 transition-all hover:border-rose-200 hover:bg-rose-50">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-folder-open text-4xl text-slate-300"></i>
                                    <p class="text-xs font-medium text-slate-500">Tidak ada kategori ditemukan</p>
                                    <p class="text-[11px] text-slate-400">Coba kata kunci lain atau buat kategori baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- GRID VIEW --}}
        <div data-view-panel="grid" class="hidden p-6">
            @if($kategori->count() > 0)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($kategori as $item)
                        @php $color = $palette[$item->id % count($palette)]; @endphp
                        <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                            <div class="absolute left-0 top-0 h-1.5 w-full {{ $color['dot'] }}"></div>

                            <div>
                                <div class="mb-3 flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <div class="rounded-xl border p-2 {{ $color['badge'] }}">
                                            <i class="fas fa-tag text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-900">{{ $item->nama_kategori }}</h3>
                                            <span class="font-mono text-[10px] text-slate-400">
                                                KAT-{{ str_pad((string) $item->id, 3, '0', STR_PAD_LEFT) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <p class="mb-4 line-clamp-2 text-xs leading-relaxed text-slate-500">
                                    {{ $item->keterangan ?: 'Tidak ada keterangan deskripsi.' }}
                                </p>
                            </div>

                            <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-3">
                                <div class="rounded-lg border border-slate-200/60 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                    <i class="fas fa-box mr-1 text-[10px]"></i>
                                    {{ $item->inventaris_count ?? 0 }} Barang Terikat
                                </div>

                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="openEdit({{ $item->id }})" title="Edit"
                                            class="rounded-lg p-1.5 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('kategori.destroy', $item->id)), @js($item->nama_kategori))"
                                            title="Hapus"
                                            class="rounded-lg p-1.5 text-slate-500 transition-all hover:bg-rose-50 hover:text-rose-600">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <i class="fas fa-folder-open mb-2 text-4xl text-slate-300"></i>
                    <p class="text-xs font-medium text-slate-500">Tidak ada kategori ditemukan</p>
                </div>
            @endif
        </div>

        {{ $kategori->links('vendor.pagination.admin') }}
    </div>

    {{-- ==================== FORM MODAL ==================== --}}
    <div id="kategoriFormModal" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-blue-600 p-1.5 text-white">
                        <i class="fas fa-tags text-sm"></i>
                    </div>
                    <div>
                        <h3 id="formModalTitle" class="text-sm font-bold">Tambah Kategori Baru</h3>
                        <p class="text-[10px] text-slate-400">Kelompokkan barang inventaris</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="kategoriForm" method="POST" action="{{ route('kategori.store') }}"
                  class="max-h-[75vh] space-y-4 overflow-y-auto p-6 text-xs">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="edit_id" id="editId" value="{{ old('edit_id') }}">

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

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_kategori" id="inputNama"
                           value="{{ old('nama_kategori') }}" required maxlength="100"
                           placeholder="Contoh: Elektronik, Furniture, Aksesori..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-medium text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Kode / Slug Kategori</label>
                    <input type="text" id="inputKode" value="Otomatis" readonly
                           class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 font-mono text-xs text-slate-500">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Keterangan / Deskripsi</label>
                    <textarea name="keterangan" id="inputKeterangan" rows="3" maxlength="255"
                              placeholder="Deskripsi singkat jenis barang dalam kategori ini..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ old('keterangan') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" data-modal-close
                            class="rounded-xl border border-slate-200 px-4 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                        <i class="fas fa-check text-[11px]"></i>
                        <span id="btnSubmitText">Simpan Kategori</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const KATEGORI = @json($kategoriPayload);
    const KATEGORI_BASE = @json(url('kategori'));
    const KATEGORI_STORE = @json(route('kategori.store'));

    function openCreate(preserveOld = false) {
        const form = document.getElementById('kategoriForm');
        form.action = KATEGORI_STORE;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('formModalTitle').textContent = 'Tambah Kategori Baru';
        document.getElementById('btnSubmitText').textContent = 'Simpan Kategori';
        document.getElementById('inputKode').value = 'Otomatis';

        if (!preserveOld) {
            document.getElementById('inputNama').value = '';
            document.getElementById('inputKeterangan').value = '';
        }

        AdminUI.openModal('kategoriFormModal');
    }

    function openEdit(id, preserveOld = false) {
        const item = KATEGORI.find(function (row) { return row.id === id; });
        if (!item) return;

        const form = document.getElementById('kategoriForm');
        form.action = KATEGORI_BASE + '/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('formModalTitle').textContent = 'Edit Data Kategori';
        document.getElementById('btnSubmitText').textContent = 'Simpan Perubahan';
        document.getElementById('inputKode').value = item.kode;

        if (!preserveOld) {
            document.getElementById('inputNama').value = item.nama || '';
            document.getElementById('inputKeterangan').value = item.keterangan || '';
        }

        AdminUI.openModal('kategoriFormModal');
    }

    function exportData() {
        AdminUI.exportCsv(
            'data-kategori.csv',
            ['Kode', 'Nama Kategori', 'Keterangan', 'Total Item'],
            KATEGORI.map(function (item) {
                return [item.kode, item.nama, item.keterangan || '', item.total];
            })
        );
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
