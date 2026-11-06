@extends('layouts.admin-master')

@section('title', 'Edit Surat Permohonan')
@section('page_key', 'permohonan')

@php
    // Semua class Tailwind ditulis literal agar ikut ter-compile oleh JIT.
    $inputBase = 'w-full rounded-xl border bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 transition focus:bg-white focus:outline-none focus:ring-2';
    $okCls = 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20';
    $badCls = 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20';
    $labelCls = 'mb-1 block font-semibold text-slate-700';

    $statusPill = [
        'Menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Dipinjam' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Dikembalikan' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];

    $oldInv = old('inventaris');
    $selectedIds = is_array($oldInv) ? array_flip($oldInv) : $itemSelected->toArray();
    $oldQty = is_array(old('jumlah')) ? old('jumlah') : [];
@endphp

@push('css')
<style>
    .inv-card {
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        cursor: pointer;
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .inv-card:hover {
        border-color: #bfdbfe;
        background: #f8fafc;
        box-shadow: 0 4px 14px rgba(37, 99, 235, .10);
        transform: translateY(-1px);
    }
    .inv-card.is-selected {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 4px 16px rgba(37, 99, 235, .16);
    }
    .inv-card.is-invalid {
        border-color: #fb7185;
        background: #fff1f2;
    }
</style>
@endpush

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <span>Transaksi</span>
                <span>/</span>
                <a href="{{ route('permohonan.index') }}" class="transition hover:text-slate-600">Permohonan</a>
                <span>/</span>
                <span class="font-medium text-slate-600">Edit Surat</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Surat Permohonan</h1>
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 font-mono text-xs font-bold text-slate-600">
                    {{ $permohonan->nomor_permohonan }}
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $statusPill[$permohonan->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                    {{ $permohonan->status }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Perbaiki data peminjam atau ubah barang yang dipinjam beserta jumlahnya.
            </p>
        </div>

        <a href="{{ route('permohonan.show', $permohonan->id) }}"
           class="flex items-center justify-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100 sm:self-auto">
            <i class="fas fa-arrow-left text-[11px]"></i>
            <span>Kembali</span>
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-700">
            <div class="mb-1 flex items-center gap-2 text-xs font-semibold">
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

    <form action="{{ route('permohonan.update', $permohonan->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ==================== DATA PEMINJAM ==================== --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="text-sm font-bold text-slate-900">Data Peminjam</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 text-xs sm:grid-cols-2 lg:grid-cols-6">
                <div class="lg:col-span-3">
                    <label class="{{ $labelCls }}">Nama Peminjam <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam', $permohonan->nama_peminjam) }}" required
                           placeholder="Masukkan nama lengkap"
                           class="{{ $inputBase }} {{ $errors->has('nama_peminjam') ? $badCls : $okCls }}">
                    @error('nama_peminjam')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelCls }}">Nomor Identitas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nik" value="{{ old('nik', $permohonan->nik) }}" required maxlength="20"
                           placeholder="NIK / No. SIM / NIM"
                           class="{{ $inputBase }} {{ $errors->has('nik') ? $badCls : $okCls }}">
                    @error('nik')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelCls }}">Jabatan</label>
                    <input type="text" name="jabatan" value="{{ old('jabatan', $permohonan->jabatan) }}"
                           placeholder="Contoh: Kepala Subbag, Staf"
                           class="{{ $inputBase }} {{ $errors->has('jabatan') ? $badCls : $okCls }}">
                    @error('jabatan')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-3">
                    <label class="{{ $labelCls }}">Telepon <span class="text-rose-500">*</span></label>
                    <input type="text" name="telepon" value="{{ old('telepon', $permohonan->telepon) }}" required
                           placeholder="08xxxxxxxxxx"
                           class="{{ $inputBase }} {{ $errors->has('telepon') ? $badCls : $okCls }}">
                    @error('telepon')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="{{ $labelCls }}">Instansi <span class="text-rose-500">*</span></label>
                    <select name="instansi_id" required
                            class="{{ $inputBase }} {{ $errors->has('instansi_id') ? $badCls : $okCls }}">
                        <option value="">-- Pilih Instansi --</option>
                        @foreach($instansi as $item)
                            <option value="{{ $item->id }}" {{ old('instansi_id', $permohonan->instansi_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_instansi }}
                            </option>
                        @endforeach
                    </select>
                    @error('instansi_id')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="{{ $labelCls }}">Tanggal Pinjam <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', $permohonan->tanggal_pinjam) }}" required
                           class="{{ $inputBase }} {{ $errors->has('tanggal_pinjam') ? $badCls : $okCls }}">
                    @error('tanggal_pinjam')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="{{ $labelCls }}">Tanggal Kembali <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_kembali" value="{{ old('tanggal_kembali', $permohonan->tanggal_kembali) }}" required
                           class="{{ $inputBase }} {{ $errors->has('tanggal_kembali') ? $badCls : $okCls }}">
                    @error('tanggal_kembali')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-6">
                    <label class="{{ $labelCls }}">Keperluan <span class="text-rose-500">*</span></label>
                    <textarea name="keperluan" rows="3" required placeholder="Tuliskan keperluan peminjaman..."
                              class="{{ $inputBase }} {{ $errors->has('keperluan') ? $badCls : $okCls }}">{{ old('keperluan', $permohonan->keperluan) }}</textarea>
                    @error('keperluan')<p class="mt-1 text-[10px] font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ==================== BARANG DIPINJAM ==================== --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-indigo-100 bg-indigo-50 text-indigo-600">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Barang Dipinjam</h2>
                </div>
                <span id="jumlahTerpilih" class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">0 barang dipilih</span>
            </div>

            <div class="p-5">
                @error('inventaris')
                    <p class="mb-3 text-[11px] font-semibold text-rose-600">{{ $message }}</p>
                @enderror

                <div id="barangContainer" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($inventaris as $item)
                        @php
                            $checked = isset($selectedIds[$item->id]);
                            $qtyVal = $oldQty[$item->id] ?? $itemSelected[$item->id] ?? 1;
                        @endphp
                        <div data-inv-card class="inv-card p-4 {{ $checked ? 'is-selected' : '' }}{{ $errors->has('jumlah.'.$item->id) ? ' is-invalid' : '' }}">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" name="inventaris[]" value="{{ $item->id }}" data-inv-check
                                       {{ $checked ? 'checked' : '' }}
                                       class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-slate-900" data-nama-barang>{{ $item->nama_barang }}</p>
                                    <p class="mt-0.5 truncate font-mono text-[10px] text-slate-400">{{ $item->kode_barang }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                            {{ $item->kategori->nama_kategori ?? '-' }}
                                        </span>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                            Stok {{ $item->stok }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-100 pt-3">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Jumlah</span>
                                <input type="number" name="jumlah[{{ $item->id }}]" data-qty
                                       min="1" max="{{ $item->stok }}" value="{{ $qtyVal }}" {{ $checked ? '' : 'disabled' }}
                                       class="h-9 w-24 rounded-lg border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-800 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:bg-slate-100 disabled:text-slate-400">
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-10 text-center">
                            <i class="fas fa-inbox mb-2 block text-2xl text-slate-300"></i>
                            <p class="text-xs text-slate-500">Belum ada barang tersedia.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ==================== AKSI ==================== --}}
        <div class="flex flex-wrap items-center justify-end gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <a href="{{ route('permohonan.show', $permohonan->id) }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 transition-all hover:bg-slate-100">
                Batal
            </a>
            <button type="submit" onclick="return validateForm()"
                    class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                <i class="fas fa-save text-[11px]"></i>
                <span>Update Surat</span>
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function sinkronkan(card, chk, qty) {
        card.classList.toggle('is-selected', chk.checked);
        if (qty) qty.disabled = !chk.checked;
        if (!chk.checked && qty) qty.classList.remove('is-invalid');
        perbaruiCounter();
    }

    function perbaruiCounter() {
        const el = document.getElementById('jumlahTerpilih');
        if (!el) return;
        el.textContent = document.querySelectorAll('[name="inventaris[]"]:checked').length + ' barang dipilih';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-inv-card]').forEach(function (card) {
            const chk = card.querySelector('[data-inv-check]');
            const qty = card.querySelector('[data-qty]');
            if (!chk) return;

            // Klik pada checkbox atau input jumlah tidak boleh dibubarkan ke kartu,
            // kalau tidak statusnya berbalik dua kali dalam satu klik.
            card.addEventListener('click', function (event) {
                if (event.target === chk || event.target === qty) return;
                chk.checked = !chk.checked;
                sinkronkan(card, chk, qty);
            });

            chk.addEventListener('change', function () {
                sinkronkan(card, chk, qty);
            });

            sinkronkan(card, chk, qty);
        });

        perbaruiCounter();
    });

    function validateForm() {
        const terpilih = document.querySelectorAll('[name="inventaris[]"]:checked');
        if (terpilih.length === 0) {
            AdminUI.toast('Pilih minimal 1 barang.', 'error');
            return false;
        }

        for (const chk of terpilih) {
            const card = chk.closest('[data-inv-card]');
            const qty = card.querySelector('[data-qty]');
            const maxStok = parseInt(qty.getAttribute('max'), 10);
            const val = parseInt(qty.value, 10) || 0;

            if (val > maxStok) {
                const nama = card.querySelector('[data-nama-barang]').textContent.trim();
                qty.classList.add('is-invalid');
                card.classList.add('is-invalid');
                AdminUI.toast('Jumlah "' + nama + '" melebihi stok tersedia (' + maxStok + ').', 'error');
                qty.focus();
                return false;
            }
        }

        return true;
    }
</script>
@endpush
