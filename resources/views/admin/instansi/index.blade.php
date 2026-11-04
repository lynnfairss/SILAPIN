@extends('layouts.admin-master')

@section('title', 'Data Instansi')
@section('page_key', 'instansi')

@php
    $instansiPayload = $instansi->getCollection()->map(function ($item) {
        return [
            'id' => $item->id,
            'nama' => $item->nama_instansi,
            'alamat' => $item->alamat,
            'telepon' => $item->telepon,
            'peminjaman' => $item->permohonan_count ?? 0,
            'kode' => 'INS-' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT),
            'terdaftar' => $item->created_at?->format('d M Y') ?? '-',
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
                <span class="font-medium text-slate-600">Data Instansi</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Instansi</h1>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                    {{ $stats['total'] }} Terdaftar
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Kelola daftar instansi, dinas, dan lembaga peminjam barang inventaris SILAPIN.
            </p>
        </div>

        <button type="button" onclick="openCreate()"
                class="flex items-center justify-center gap-2 self-start rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 sm:self-auto">
            <i class="fas fa-plus text-[11px]"></i>
            <span>Tambah Instansi</span>
        </button>
    </div>

    {{-- ==================== STATS ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                <i class="fas fa-building text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Instansi</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">{{ $stats['total'] }}</h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                <i class="fas fa-address-book text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Punya Kontak</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['kontak'] }} <span class="text-xs font-normal text-slate-400">Instansi</span>
                </h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600">
                <i class="fas fa-file-alt text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Total Riwayat Peminjaman</p>
                <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                    {{ $stats['peminjaman'] }} <span class="text-xs font-normal text-slate-400">Kali</span>
                </h3>
            </div>
        </div>
    </div>

    {{-- ==================== DATA CARD ==================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        {{-- Toolbar --}}
        <form method="GET" action="{{ route('instansi.index') }}"
              class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-4 md:flex-row md:items-center md:justify-between">
            <div class="relative w-full max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Cari nama instansi, alamat, atau telepon..."
                       class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-9 text-xs text-slate-700 placeholder-slate-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                @if($q !== '')
                    <a href="{{ route('instansi.index') }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                       title="Hapus pencarian">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('instansi.index') }}"
                   class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100"
                   title="Reset filter">
                    <i class="fas fa-sync text-[11px]"></i>
                    <span class="hidden sm:inline">Refresh</span>
                </a>
                <button type="button" onclick="exportData()"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100">
                    <i class="fas fa-download text-[11px]"></i>
                    <span class="hidden sm:inline">Ekspor Data</span>
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">No</th>
                        <th class="px-5 py-3.5">Nama Instansi</th>
                        <th class="px-5 py-3.5">Alamat</th>
                        <th class="px-5 py-3.5">Telepon</th>
                        <th class="px-4 py-3.5 text-center">Peminjaman</th>
                        <th class="px-4 py-3.5 text-center">Terdaftar</th>
                        <th class="w-32 px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($instansi as $item)
                        <tr class="group transition-colors hover:bg-slate-50/80">
                            <td class="px-4 py-4 text-center font-medium text-slate-400">
                                {{ $instansi->firstItem() + $loop->index }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">{{ $item->nama_instansi }}</div>
                                        <div class="font-mono text-[10px] text-slate-400">
                                            INS-{{ str_pad((string) $item->id, 3, '0', STR_PAD_LEFT) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="max-w-xs px-5 py-4 text-slate-600">
                                <div class="flex items-start gap-1.5">
                                    <i class="fas fa-map-marker-alt mt-0.5 shrink-0 text-[11px] text-slate-400"></i>
                                    <span>{{ $item->alamat ?: '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 font-medium text-slate-700">
                                    <i class="fas fa-phone text-[11px] text-slate-400"></i>
                                    <span>{{ $item->telepon ?: '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                    {{ $item->permohonan_count ?? 0 }} Kali
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center text-slate-500">
                                {{ $item->created_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openDetail({{ $item->id }})"
                                            title="Detail Instansi"
                                            class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                        <i class="fas fa-eye text-[13px]"></i>
                                    </button>
                                    <button type="button" onclick="openEdit({{ $item->id }})"
                                            title="Edit Instansi"
                                            class="rounded-lg border border-transparent p-1.5 text-blue-600 transition-all hover:border-blue-200 hover:bg-blue-50">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                    <button type="button"
                                            onclick="AdminUI.confirmDelete(@js(route('instansi.destroy', $item->id)), @js($item->nama_instansi))"
                                            title="Hapus Instansi"
                                            class="rounded-lg border border-transparent p-1.5 text-rose-600 transition-all hover:border-rose-200 hover:bg-rose-50">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-building text-4xl text-slate-300"></i>
                                    <p class="text-xs font-medium text-slate-500">Tidak ada instansi ditemukan</p>
                                    <p class="text-[11px] text-slate-400">Coba kata kunci lain atau tambah instansi baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $instansi->links('vendor.pagination.admin') }}
    </div>

    {{-- ==================== FORM MODAL ==================== --}}
    <div id="instansiFormModal" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-lg overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-blue-600 p-1.5 text-white">
                        <i class="fas fa-building text-sm"></i>
                    </div>
                    <div>
                        <h3 id="formModalTitle" class="text-sm font-bold">Tambah Instansi Baru</h3>
                        <p class="text-[10px] text-slate-400">Lengkapi data instansi peminjam</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="instansiForm" method="POST" action="{{ route('instansi.store') }}"
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

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block font-semibold text-slate-700">
                            Nama Instansi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_instansi" id="inputNama"
                               value="{{ old('nama_instansi') }}" required maxlength="100"
                               placeholder="Contoh: Dinas Pendidikan"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-medium text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div>
                        <label class="mb-1 block font-semibold text-slate-700">Kode</label>
                        <input type="text" id="inputKode" value="Otomatis" readonly
                               class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 font-mono text-xs text-slate-500">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Alamat</label>
                    <textarea name="alamat" id="inputAlamat" rows="3"
                              placeholder="Masukkan jalan, nomor gedung, kecamatan..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ old('alamat') }}</textarea>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">Nomor Telepon</label>
                    <input type="tel" name="telepon" id="inputTelepon"
                           value="{{ old('telepon') }}" maxlength="20" inputmode="numeric"
                           oninput="this.value = this.value.replace(/\D/g, '')"
                           placeholder="08xxxxxxxxxx"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <p class="mt-1 text-[10px] text-slate-400">Hanya angka, tanpa spasi atau tanda hubung.</p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" data-modal-close
                            class="rounded-xl border border-slate-200 px-4 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                        <i class="fas fa-check text-[11px]"></i>
                        <span id="btnSubmitText">Simpan Instansi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== DETAIL MODAL ==================== --}}
    <div id="instansiDetailModal" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2">
                    <i class="fas fa-building text-blue-400"></i>
                    <h3 class="text-sm font-bold">Detail Instansi</h3>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4 p-6 text-xs">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <div>
                        <h4 id="detailNama" class="text-sm font-bold text-slate-900">—</h4>
                        <span id="detailKode" class="font-mono text-[10px] text-slate-400">—</span>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-semibold uppercase text-slate-400">Alamat</p>
                    <p id="detailAlamat" class="mt-0.5 font-medium text-slate-800">—</p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <p class="text-[10px] font-semibold uppercase text-slate-400">No. Telepon</p>
                        <p id="detailTelepon" class="mt-0.5 font-medium text-slate-800">—</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase text-slate-400">Terdaftar</p>
                        <p id="detailTerdaftar" class="mt-0.5 font-medium text-slate-800">—</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <div>
                        <p class="text-[10px] font-semibold uppercase text-slate-400">Total Peminjaman</p>
                        <p id="detailPeminjaman" class="text-sm font-bold text-blue-600">0 Kali</p>
                    </div>
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
                        <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Terdaftar
                    </span>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" data-modal-close
                            class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-medium text-slate-700 transition-all hover:bg-slate-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const INSTANSI = @json($instansiPayload);
    const INSTANSI_BASE = @json(url('instansi'));
    const INSTANSI_STORE = @json(route('instansi.store'));

    function openCreate(preserveOld = false) {
        const form = document.getElementById('instansiForm');
        form.action = INSTANSI_STORE;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('editId').value = '';
        document.getElementById('formModalTitle').textContent = 'Tambah Instansi Baru';
        document.getElementById('btnSubmitText').textContent = 'Simpan Instansi';
        document.getElementById('inputKode').value = 'Otomatis';

        if (!preserveOld) {
            document.getElementById('inputNama').value = '';
            document.getElementById('inputAlamat').value = '';
            document.getElementById('inputTelepon').value = '';
        }

        AdminUI.openModal('instansiFormModal');
    }

    function openEdit(id, preserveOld = false) {
        const item = INSTANSI.find(function (row) { return row.id === id; });
        if (!item) return;

        const form = document.getElementById('instansiForm');
        form.action = INSTANSI_BASE + '/' + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('editId').value = id;
        document.getElementById('formModalTitle').textContent = 'Edit Data Instansi';
        document.getElementById('btnSubmitText').textContent = 'Simpan Perubahan';
        document.getElementById('inputKode').value = item.kode;

        if (!preserveOld) {
            document.getElementById('inputNama').value = item.nama || '';
            document.getElementById('inputAlamat').value = item.alamat || '';
            document.getElementById('inputTelepon').value = item.telepon || '';
        }

        AdminUI.openModal('instansiFormModal');
    }

    function openDetail(id) {
        const item = INSTANSI.find(function (row) { return row.id === id; });
        if (!item) return;

        document.getElementById('detailNama').textContent = item.nama || '—';
        document.getElementById('detailKode').textContent = item.kode || '—';
        document.getElementById('detailAlamat').textContent = item.alamat || 'Belum diisi';
        document.getElementById('detailTelepon').textContent = item.telepon || '—';
        document.getElementById('detailTerdaftar').textContent = item.terdaftar || '—';
        document.getElementById('detailPeminjaman').textContent = (item.peminjaman || 0) + ' Kali';

        AdminUI.openModal('instansiDetailModal');
    }

    function exportData() {
        AdminUI.exportCsv(
            'data-instansi.csv',
            ['Kode', 'Nama Instansi', 'Alamat', 'Telepon', 'Peminjaman', 'Terdaftar'],
            INSTANSI.map(function (item) {
                return [item.kode, item.nama, item.alamat || '', item.telepon || '', item.peminjaman, item.terdaftar];
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
