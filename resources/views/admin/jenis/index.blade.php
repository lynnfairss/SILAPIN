@extends('layouts.admin-master')

@section('title', 'Data Jenis')
@section('page_key', 'jenis')

@php
    $jenisIcon = function (string $nama): string {
        $n = strtolower($nama);
        if (str_contains($n, 'laptop')) return 'fa-laptop';
        if (str_contains($n, 'proyektor') || str_contains($n, 'projector')) return 'fa-video';
        if (str_contains($n, 'printer')) return 'fa-print';
        if (str_contains($n, 'monitor')) return 'fa-desktop';
        if (str_contains($n, 'kabel')) return 'fa-plug';
        if (str_contains($n, 'kamera') || str_contains($n, 'tripod')) return 'fa-camera';
        if (str_contains($n, 'meja')) return 'fa-table';
        if (str_contains($n, 'kursi')) return 'fa-chair';
        return 'fa-boxes';
    };

    $jenisPayload = $jenis->getCollection()->map(function ($item) {
        return [
            'id' => $item->id,
            'nama' => $item->nama_jenis,
            'keterangan' => $item->keterangan,
            'total' => $item->inventaris_count ?? 0,
            'inventaris' => $item->inventaris->map(function ($inv) {
                return [
                    'kode' => $inv->kode_barang,
                    'nama' => $inv->nama_barang,
                    'stok' => (int) $inv->stok,
                    'kondisi' => $inv->kondisi,
                ];
            })->values(),
        ];
    })->values();

    // route() dengan lebih dari satu argumen tidak bisa ditulis di dalam
    // @json(), karena direktif itu memecah argumen pada koma pertama.
    $exportUrl = route('jenis.export', array_filter(request()->only(['q'])));
@endphp

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <span>Master Data</span>
                <span>/</span>
                <span class="font-medium text-slate-600">Data Jenis</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Jenis Barang</h1>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                    {{ $stats['total'] }} Tipe
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Kelola jenis dan klasifikasi spesifik barang inventaris dalam sistem SILAPIN.
            </p>
        </div>

        <button type="button" onclick="openCreate()"
                class="flex items-center justify-center gap-2 self-start rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 sm:self-auto">
            <i class="fas fa-plus text-[11px]"></i>
            <span>Tambah Jenis Baru</span>
        </button>
    </div>

    {{-- ==================== STATS ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Jenis</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['total'] }} <span class="text-xs font-normal text-slate-400">Klasifikasi</span>
                </h3>
                <p class="mt-0.5 text-[10px] font-medium text-emerald-600">Terdaftar secara aktif</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600">
                <i class="fas fa-box text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Aset Terhubung</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['aset'] }} <span class="text-xs font-normal text-slate-400">Barang</span>
                </h3>
                <p class="mt-0.5 text-[10px] text-slate-400">Barang fisik inventaris</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600">
                <i class="fas fa-chart-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Jenis Paling Populer</p>
                <h3 class="mt-0.5 max-w-[130px] truncate text-xl font-bold text-slate-900">{{ $stats['terpopuler'] }}</h3>
                <p class="mt-0.5 text-[10px] font-medium text-amber-600">Paling banyak aset</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-purple-100 bg-purple-50 text-purple-600">
                <i class="fas fa-sitemap text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Jenis Tanpa Aset</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['tanpa_aset'] }} <span class="text-xs font-normal text-slate-400">Jenis</span>
                </h3>
                <p class="mt-0.5 text-[10px] text-slate-400">Belum terhubung inventaris</p>
            </div>
        </div>
    </div>

    {{-- ==================== DATA CARD ==================== --}}
    <div id="jenisDataCard" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        {{-- Toolbar --}}
        <form method="GET" action="{{ route('jenis.index') }}"
              class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full max-w-xs">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Cari nama jenis barang..."
                       class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-9 text-xs text-slate-700 placeholder-slate-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                @if($q !== '')
                    <a href="{{ route('jenis.index') }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                       title="Hapus pencarian">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 sm:justify-end">
                <div data-view-group data-view-target="#jenisDataCard"
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

                <a href="{{ route('jenis.index') }}"
                   class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition-all hover:bg-slate-100"
                   title="Reset filter">
                    <i class="fas fa-sync text-[12px]"></i>
                </a>

                <button type="button" onclick="exportData()"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100">
                    <i class="fas fa-download text-[11px]"></i>
                    <span class="hidden sm:inline">Ekspor</span>
                </button>
            </div>
        </form>

        {{-- TABLE VIEW --}}
        <div data-view-panel="table" class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">No</th>
                        <th class="px-5 py-3.5">Nama Jenis</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5 text-center">Total Aset</th>
                        <th class="w-28 px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jenis as $item)
                        <tr class="group transition-colors hover:bg-slate-50/80">
                            <td class="px-4 py-3.5 text-center font-medium text-slate-400">
                                {{ $jenis->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600">
                                        <i class="fas {{ $jenisIcon($item->nama_jenis) }} text-[13px]"></i>
                                    </div>
                                    <button type="button" onclick="openDetail({{ $item->id }})"
                                            class="text-xs font-bold text-slate-900 transition-colors hover:text-blue-600">
                                        {{ $item->nama_jenis }}
                                    </button>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                <p class="max-w-xs truncate">{{ $item->keterangan ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-xs font-bold text-slate-800">{{ $item->inventaris_count ?? 0 }} Barang</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openDetail({{ $item->id }})" title="Lihat Detail"
                                            class="rounded-lg p-1.5 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600">
                                        <i class="fas fa-eye text-[13px]"></i>
                                    </button>
                                    <button type="button" onclick="openEdit({{ $item->id }})" title="Edit Jenis"
                                            class="rounded-lg p-1.5 text-blue-600 transition-all hover:bg-blue-50">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('jenis.destroy', $item->id)), @js($item->nama_jenis))"
                                            title="Hapus Jenis"
                                            class="rounded-lg p-1.5 text-rose-600 transition-all hover:bg-rose-50">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                                <i class="fas fa-boxes mb-2 text-4xl text-slate-300"></i>
                                <p class="text-xs font-medium text-slate-500">Data jenis barang tidak ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- GRID VIEW --}}
        <div data-view-panel="grid" class="hidden p-5">
            @if($jenis->count() > 0)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($jenis as $item)
                        <div class="group flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:border-blue-200 hover:shadow-md">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 transition-transform group-hover:scale-105">
                                        <i class="fas {{ $jenisIcon($item->nama_jenis) }} text-lg"></i>
                                    </div>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-medium text-slate-600">
                                        {{ $item->inventaris_count ?? 0 }} Aset
                                    </span>
                                </div>

                                <div>
                                    <button type="button" onclick="openDetail({{ $item->id }})"
                                            class="text-sm font-bold text-slate-900 transition-colors hover:text-blue-600">
                                        {{ $item->nama_jenis }}
                                    </button>
                                    <p class="mt-1 line-clamp-2 text-[11px] text-slate-400">
                                        {{ $item->keterangan ?: 'Belum ada keterangan.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                                <div>
                                    <span class="block text-[10px] font-medium text-slate-400">Total Aset</span>
                                    <span class="text-xs font-bold text-slate-800">{{ $item->inventaris_count ?? 0 }} Barang</span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="openDetail({{ $item->id }})" title="Detail"
                                            class="rounded-lg p-1.5 text-slate-500 transition-all hover:bg-blue-50 hover:text-blue-600">
                                        <i class="fas fa-eye text-[13px]"></i>
                                    </button>
                                    <button type="button" onclick="openEdit({{ $item->id }})" title="Edit"
                                            class="rounded-lg p-1.5 text-blue-600 transition-all hover:bg-blue-50">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('jenis.destroy', $item->id)), @js($item->nama_jenis))"
                                            title="Hapus"
                                            class="rounded-lg p-1.5 text-rose-600 transition-all hover:bg-rose-50">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <i class="fas fa-boxes mb-2 text-4xl text-slate-300"></i>
                    <p class="text-xs font-medium text-slate-500">Data jenis barang tidak ditemukan</p>
                </div>
            @endif
        </div>

        {{ $jenis->links('vendor.pagination.admin') }}
    </div>

    {{-- ==================== FORM MODAL ==================== --}}
    <div id="jenisFormModal" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-blue-600 p-1.5 text-white">
                        <i class="fas fa-boxes text-sm"></i>
                    </div>
                    <div>
                        <h3 id="formModalTitle" class="text-sm font-bold">Tambah Jenis Barang Baru</h3>
                        <p class="text-[10px] text-slate-400">Atur klasifikasi barang dalam SILAPIN</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="jenisForm" method="POST" action="{{ route('jenis.store') }}"
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
                        Nama Jenis Barang <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_jenis" id="inputNama"
                           value="{{ old('nama_jenis') }}" required maxlength="150"
                           placeholder="Misal: Laptop, Proyektor, Kamera..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-medium text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Keterangan Singkat</label>
                    <textarea name="keterangan" id="inputKeterangan" rows="3" maxlength="255"
                              placeholder="Tuliskan keterangan peruntukan jenis barang ini..."
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
                        <span id="btnSubmitText">Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== DETAIL DRAWER ==================== --}}
    <div id="jenisDetailDrawer" class="admin-drawer hidden fixed inset-0 z-[70] bg-slate-900/60 backdrop-blur-sm">
        <div class="admin-drawer-panel flex h-full w-full max-w-md flex-col overflow-hidden bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2">
                    <i class="fas fa-boxes text-blue-400"></i>
                    <h3 class="text-sm font-bold">Detail Jenis Barang</h3>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto p-6 text-xs">
                <div class="flex items-start gap-4 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50/50 p-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/30">
                        <i id="detailIcon" class="fas fa-boxes text-xl"></i>
                    </div>
                    <div>
                        <span class="rounded-full border border-blue-100 bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-600 shadow-sm">
                            Jenis Barang
                        </span>
                        <h2 id="detailNama" class="mt-1 text-lg font-bold text-slate-900">—</h2>
                        <p id="detailKeterangan" class="mt-0.5 text-xs text-slate-500">—</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 text-center">
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400">Total Aset</span>
                        <p id="detailTotal" class="mt-0.5 text-base font-bold text-slate-800">0 Barang</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400">Status Data</span>
                        <p class="mt-0.5 text-base font-bold text-blue-600">Terdaftar</p>
                    </div>
                </div>

                <div>
                    <h4 class="mb-2.5 flex items-center gap-2 font-bold text-slate-800">
                        <i class="fas fa-box text-blue-600"></i>
                        Barang Terdaftar (<span id="detailCount">0</span>)
                    </h4>
                    <div id="detailInventarisList" class="space-y-2"></div>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 bg-slate-50 p-4">
                <button type="button" data-modal-close
                        class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-medium text-slate-700 transition-all hover:bg-slate-300">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const JENIS = @json($jenisPayload);
    const JENIS_BASE = @json(url('jenis'));
    const JENIS_STORE = @json(route('jenis.store'));
    const JENIS_EXPORT = @json($exportUrl);

    function iconFor(nama) {
        const n = (nama || '').toLowerCase();
        if (n.includes('laptop')) return 'fa-laptop';
        if (n.includes('proyektor') || n.includes('projector')) return 'fa-video';
        if (n.includes('printer')) return 'fa-print';
        if (n.includes('monitor')) return 'fa-desktop';
        if (n.includes('kabel')) return 'fa-plug';
        if (n.includes('kamera') || n.includes('tripod')) return 'fa-camera';
        if (n.includes('meja')) return 'fa-table';
        if (n.includes('kursi')) return 'fa-chair';
        return 'fa-boxes';
    }

    function openCreate(preserveOld = false) {
        const form = document.getElementById('jenisForm');
        form.action = JENIS_STORE;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('formModalTitle').textContent = 'Tambah Jenis Barang Baru';
        document.getElementById('btnSubmitText').textContent = 'Simpan Data';

        if (!preserveOld) {
            document.getElementById('inputNama').value = '';
            document.getElementById('inputKeterangan').value = '';
        }

        AdminUI.openModal('jenisFormModal');
    }

    function openEdit(id, preserveOld = false) {
        const item = JENIS.find(function (row) { return row.id === id; });
        if (!item) return;

        const form = document.getElementById('jenisForm');
        form.action = JENIS_BASE + '/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('formModalTitle').textContent = 'Edit Jenis Barang';
        document.getElementById('btnSubmitText').textContent = 'Simpan Perubahan';

        if (!preserveOld) {
            document.getElementById('inputNama').value = item.nama || '';
            document.getElementById('inputKeterangan').value = item.keterangan || '';
        }

        AdminUI.openModal('jenisFormModal');
    }

    function openDetail(id) {
        const item = JENIS.find(function (row) { return row.id === id; });
        if (!item) return;

        document.getElementById('detailIcon').className = 'fas ' + iconFor(item.nama) + ' text-xl';
        document.getElementById('detailNama').textContent = item.nama || '—';
        document.getElementById('detailKeterangan').textContent = item.keterangan || 'Belum ada keterangan.';
        document.getElementById('detailTotal').textContent = (item.total || 0) + ' Barang';
        document.getElementById('detailCount').textContent = item.inventaris ? item.inventaris.length : 0;

        const list = document.getElementById('detailInventarisList');
        list.innerHTML = '';

        if (item.inventaris && item.inventaris.length) {
            item.inventaris.forEach(function (inv) {
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between rounded-xl border border-slate-200/80 bg-slate-50 p-3';

                const left = document.createElement('div');
                const nama = document.createElement('p');
                nama.className = 'text-xs font-bold text-slate-800';
                nama.textContent = inv.nama;
                const kode = document.createElement('span');
                kode.className = 'font-mono text-[10px] font-semibold text-rose-500';
                kode.textContent = inv.kode;
                left.appendChild(nama);
                left.appendChild(kode);

                const right = document.createElement('div');
                right.className = 'text-right';
                const stok = document.createElement('span');
                stok.className = 'rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700';
                stok.textContent = inv.stok + ' Unit';
                const kondisi = document.createElement('p');
                kondisi.className = 'mt-0.5 text-[10px] text-slate-400';
                kondisi.textContent = inv.kondisi;
                right.appendChild(stok);
                right.appendChild(kondisi);

                row.appendChild(left);
                row.appendChild(right);
                list.appendChild(row);
            });
        } else {
            const empty = document.createElement('p');
            empty.className = 'py-2 text-xs italic text-slate-400';
            empty.textContent = 'Belum ada item fisik khusus yang didaftarkan pada jenis ini.';
            list.appendChild(empty);
        }

        AdminUI.openDrawer('jenisDetailDrawer');
    }

    function exportData() {
        window.location.assign(JENIS_EXPORT);
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
