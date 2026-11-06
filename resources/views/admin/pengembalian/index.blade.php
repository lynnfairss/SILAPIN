@extends('layouts.admin-master')

@section('title', 'Pengembalian Barang')
@section('page_key', 'pengembalian')

@section('topbar_search_action', route('permohonan.index'))
@section('topbar_search_placeholder', 'Cari nomor permohonan, peminjam...')

@php
    $kondisiOptions = [
        ['label' => 'Baik', 'desc' => 'Sesuai semula', 'icon' => 'fa-check-circle', 'tone' => 'emerald'],
        ['label' => 'Rusak Ringan', 'desc' => 'Baret / lecet', 'icon' => 'fa-exclamation-circle', 'tone' => 'amber'],
        ['label' => 'Rusak Berat', 'desc' => 'Tidak berfungsi', 'icon' => 'fa-times-circle', 'tone' => 'rose'],
        ['label' => 'Hilang', 'desc' => 'Perlu ganti rugi', 'icon' => 'fa-question-circle', 'tone' => 'purple'],
    ];
    $oldKondisi = old('kondisi_barang', 'Baik');

    $kondisiBadge = [
        'Baik' => 'bg-emerald-100 text-emerald-700',
        'Rusak Ringan' => 'bg-amber-100 text-amber-700',
        'Rusak Berat' => 'bg-rose-100 text-rose-700',
        'Hilang' => 'bg-purple-100 text-purple-700',
    ];
@endphp

@push('css')
<style>
    #scanReader { min-height: 220px; }
    #scanReader video { width: 100% !important; height: 100% !important; object-fit: cover; border-radius: .75rem; }
    #scanReader img { display: none; }
</style>
@endpush

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <span>Transaksi</span>
                <span>/</span>
                <span class="font-medium text-slate-600">Pengembalian Barang</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengembalian Barang</h1>
                <span class="flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                    <i class="fas fa-check-circle text-[11px]"></i> Modul Aktif
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Proses konfirmasi fisik pengembalian barang inventaris dan unggah dokumen bukti serah terima.
            </p>
        </div>
    </div>

    {{-- ==================== STATISTIK ==================== --}}
    @php
        $tiles = [
            [
                'icon' => 'fa-box',
                'box' => 'border-blue-100 bg-blue-50 text-blue-600',
                'label' => 'Total Dipinjam',
                'value' => $stats['dipinjam'] ?? 0,
                'hint' => 'Aset di luar gudang',
                'hintClass' => 'text-blue-600',
            ],
            [
                'icon' => 'fa-clock',
                'box' => 'border-amber-100 bg-amber-50 text-amber-600',
                'label' => 'Menunggu Kembali',
                'value' => $stats['menunggu_kembali'] ?? 0,
                'hint' => 'Belum jatuh tempo',
                'hintClass' => 'text-amber-600',
            ],
            [
                'icon' => 'fa-file-circle-check',
                'box' => 'border-emerald-100 bg-emerald-50 text-emerald-600',
                'label' => 'Kembali Hari Ini',
                'value' => $stats['kembali_hari_ini'] ?? 0,
                'hint' => 'Sudah diverifikasi',
                'hintClass' => 'text-emerald-600',
            ],
            [
                'icon' => 'fa-exclamation-circle',
                'box' => 'border-rose-100 bg-rose-50 text-rose-600',
                'label' => 'Terlambat',
                'value' => $stats['terlambat'] ?? 0,
                'hint' => 'Melewati batas waktu',
                'hintClass' => 'text-rose-600',
            ],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($tiles as $tile)
            <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $tile['box'] }}">
                    <i class="fas {{ $tile['icon'] }} text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $tile['label'] }}</p>
                    <h3 class="mt-0.5 text-xl font-bold text-slate-900">
                        {{ number_format($tile['value'], 0, ',', '.') }}
                        <span class="text-xs font-normal text-slate-400">Permohonan</span>
                    </h3>
                    <p class="mt-0.5 text-[10px] font-medium {{ $tile['hintClass'] }}">{{ $tile['hint'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ==================== KONTEN UTAMA ==================== --}}
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

        {{-- ---------- KOLOM KIRI: FORM KONFIRMASI ---------- --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm lg:col-span-7 xl:col-span-8">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-5">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                        <i class="fas fa-undo text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Konfirmasi Pengembalian Barang</h2>
                        <p class="text-[11px] text-slate-400">Isi nomor permohonan atau scan kode QR peminjaman</p>
                    </div>
                </div>
                <span class="rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">
                    {{ auth()->user()?->isSuperAdmin() ? 'Super Admin' : 'Admin' }}
                </span>
            </div>

            <form id="formPengembalian" action="{{ route('pengembalian.proses') }}" method="POST"
                  enctype="multipart/form-data" class="space-y-5 p-6 text-xs" novalidate>
                @csrf

                {{-- Nomor Permohonan --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="nomor_permohonan" class="font-semibold text-slate-700">
                            Nomor Permohonan <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400">Pilih dari permohonan aktif</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="nomor_permohonan" id="nomor_permohonan"
                                   value="{{ old('nomor_permohonan') }}"
                                   placeholder="Contoh: SP-220926-67ACAA"
                                   autocomplete="off" required
                                   class="w-full rounded-xl border @error('nomor_permohonan') border-rose-400 @else border-slate-200 @enderror bg-slate-50 py-2.5 pl-9 pr-3.5 font-mono font-bold text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <button type="button" id="btnOpenScanner"
                                class="flex shrink-0 items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2.5 font-medium text-blue-700 transition hover:bg-blue-100">
                            <i class="fas fa-qrcode"></i>
                            <span class="hidden sm:inline">Scan QR</span>
                        </button>
                    </div>

                    @error('nomor_permohonan')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror

                    {{-- Quick pick dari permohonan aktif --}}
                    @if($pinjamAktif->isNotEmpty())
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] font-medium text-slate-400">Quick Pick:</span>
                            @foreach($pinjamAktif as $aktif)
                                <button type="button" data-quick-pick="{{ $aktif->nomor_permohonan }}"
                                        title="{{ $aktif->nama_peminjam }} — {{ $aktif->instansi?->nama_instansi ?? $aktif->nama_instansi_lain ?? '-' }}"
                                        class="quick-pick rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-600 transition hover:bg-slate-200">
                                    {{ $aktif->nomor_permohonan }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div id="lookupMsg" class="mt-2 hidden"></div>
                </div>

                {{-- Kondisi Barang --}}
                <div>
                    <label class="mb-1.5 block font-semibold text-slate-700">
                        Kondisi Barang Saat Dikembalikan <span class="text-rose-500">*</span>
                    </label>
                    <div id="kondisiGrid" class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach($kondisiOptions as $opt)
                            <label data-kondisi-card data-tone="{{ $opt['tone'] }}"
                                   class="cursor-pointer rounded-xl border p-2.5 text-left transition-all">
                                <input type="radio" name="kondisi_barang" value="{{ $opt['label'] }}"
                                       class="sr-only" @checked($oldKondisi === $opt['label'])>
                                <div class="flex items-center justify-between gap-1">
                                    <span data-kondisi-title class="text-[11px] font-bold">{{ $opt['label'] }}</span>
                                    <i data-kondisi-check class="fas fa-check-circle hidden text-blue-600"></i>
                                </div>
                                <span class="mt-0.5 block text-[10px] text-slate-400">{{ $opt['desc'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('kondisi_barang')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Catatan Admin --}}
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label for="catatan" class="font-semibold text-slate-700">
                            Catatan Admin / Petugas <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400">Wajib diisi untuk arsip</span>
                    </div>
                    <textarea name="catatan" id="catatan" rows="3" required
                              placeholder="Wajib diisi — misal: Barang diterima lengkap, kondisi fisik mulus dan siap disimpan ke gudang."
                              class="w-full rounded-xl border @error('catatan') border-rose-400 @else border-slate-200 @enderror bg-slate-50 px-3.5 py-2.5 leading-relaxed text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bukti Pengembalian --}}
                <div>
                    <label class="mb-1.5 block font-semibold text-slate-700">
                        Bukti Serah Terima / Foto Barang <span class="font-normal text-slate-400">(Opsional)</span>
                    </label>

                    <label for="bukti_pengembalian"
                           class="group block cursor-pointer rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-4 text-center transition hover:border-blue-400 hover:bg-blue-50/30">
                        <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 transition group-hover:text-blue-600"></i>
                        <p class="mt-1 text-xs font-semibold text-slate-700">Unggah Foto Fisik Barang</p>
                        <p class="text-[10px] text-slate-400">Format JPG, PNG, WEBP maks 2MB</p>
                        <input type="file" name="bukti_pengembalian" id="bukti_pengembalian"
                               accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                    @error('bukti_pengembalian')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror

                    <div id="buktiPreview" class="mt-3 hidden flex-wrap items-center gap-2"></div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" id="btnReset"
                            class="rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                        Batal / Reset
                    </button>
                    <button type="submit" id="btnConfirmReturn"
                            class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-xs font-semibold text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-700">
                        <i class="fas fa-check-circle"></i>
                        <span>Konfirmasi Pengembalian</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ---------- KOLOM KANAN: DETAIL & RIWAYAT ---------- --}}
        <div class="space-y-6 lg:col-span-5 xl:col-span-4">

            {{-- Kartu Detail Peminjaman --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-4">
                    <span class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                        <i class="fas fa-user-check text-blue-600"></i>
                        Detail Peminjaman &amp; Item
                    </span>
                    <span id="detailStatus" class="hidden rounded-full px-2 py-0.5 text-[10px] font-bold"></span>
                </div>

                <div class="p-5 text-xs">
                    {{-- Placeholder --}}
                    <div id="detailEmpty" class="space-y-2 py-8 text-center text-slate-400">
                        <i class="fas fa-qrcode text-3xl text-slate-300"></i>
                        <p class="text-xs font-medium text-slate-500">
                            Pilih atau scan nomor permohonan untuk melihat detail peminjam.
                        </p>
                    </div>

                    {{-- Hasil lookup --}}
                    <div id="permohonanDetail" class="hidden space-y-4">
                        {{-- Identitas --}}
                        <div class="space-y-1 rounded-xl border border-blue-100 bg-blue-50/60 p-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase text-blue-600">Instansi</span>
                                <span id="detailNomor" class="font-mono text-[10px] text-slate-500"></span>
                            </div>
                            <h4 id="detailInstansi" class="text-sm font-bold text-slate-900">-</h4>
                            <p class="font-medium text-slate-600">
                                Peminjam: <span id="detailNama" class="font-bold text-slate-800">-</span>
                            </p>
                            <p class="text-[11px] text-slate-400">
                                NIK: <span id="detailNik">-</span>
                                &middot; Telp: <span id="detailTelepon">-</span>
                            </p>
                            <p class="text-[11px] text-slate-400">
                                Alamat: <span id="detailAlamat">-</span>
                            </p>
                        </div>

                        {{-- Tanggal --}}
                        <div class="grid grid-cols-2 gap-2 rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-[11px]">
                            <div>
                                <span class="block text-[10px] text-slate-400">Tgl Pinjam</span>
                                <span id="detailTglPinjam" class="font-semibold text-slate-700">-</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400">Jatuh Tempo</span>
                                <span id="detailTglKembali" class="font-semibold text-slate-700">-</span>
                            </div>
                        </div>

                        {{-- Keperluan --}}
                        <div>
                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Keperluan</span>
                            <p id="detailKeperluan" class="rounded-xl border border-slate-100 bg-slate-50 p-2.5 leading-relaxed text-slate-600">-</p>
                        </div>

                        {{-- Daftar Barang --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-800">Daftar Barang Dipinjam</span>
                                <span class="text-[10px] font-semibold text-slate-400">
                                    Total: <span id="detailJumlah">0</span> unit
                                </span>
                            </div>
                            <div class="space-y-2" id="detailBarangBody">
                                <p class="rounded-xl border border-slate-200/80 bg-slate-50 p-2.5 text-center text-slate-400">
                                    Tidak ada barang.
                                </p>
                            </div>
                        </div>

                        {{-- Lampiran --}}
                        <div>
                            <span class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Lampiran</span>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="mb-1 block text-[10px] text-slate-400">Foto Identitas</span>
                                    <div id="lampiranKtp"></div>
                                </div>
                                <div>
                                    <span class="mb-1 block text-[10px] text-slate-400">Surat Tugas</span>
                                    <div id="lampiranSuratTugas"></div>
                                </div>
                            </div>
                        </div>

                        <div id="detailWarn" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-2.5 text-[11px] font-medium text-amber-700"></div>
                    </div>
                </div>
            </div>

            {{-- Kartu Riwayat --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 p-4">
                    <span class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                        <i class="fas fa-history text-emerald-600"></i>
                        Riwayat Pengembalian Terbaru
                    </span>
                    <span class="text-[10px] font-medium text-slate-400">5 terakhir</span>
                </div>

                <div class="space-y-3 p-4">
                    @forelse($riwayat as $log)
                        <div class="space-y-1 rounded-xl border border-slate-200/70 bg-slate-50 p-3 text-xs transition hover:border-blue-200">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate font-mono text-[11px] font-bold text-slate-900">{{ $log->nomor_permohonan }}</span>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold {{ $kondisiBadge[$log->kondisi_barang] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $log->kondisi_barang ?? 'Tidak dicatat' }}
                                </span>
                            </div>
                            <p class="font-semibold text-slate-800">
                                {{ $log->instansi?->nama_instansi ?? $log->nama_instansi_lain ?? '-' }}
                                <span class="font-normal text-slate-500">({{ $log->nama_peminjam }})</span>
                            </p>
                            <p class="line-clamp-2 text-[10px] leading-relaxed text-slate-400">{{ $log->catatan_admin ?: 'Tanpa catatan.' }}</p>
                            <p class="text-[10px] font-medium text-slate-400">
                                <i class="far fa-calendar-check mr-1"></i>
                                {{ $log->tanggal_pengembalian ? \Illuminate\Support\Carbon::parse($log->tanggal_pengembalian)->translatedFormat('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                    @empty
                        <div class="space-y-2 py-8 text-center text-slate-400">
                            <i class="fas fa-box-open text-3xl text-slate-300"></i>
                            <p class="text-xs font-medium text-slate-500">Belum ada riwayat pengembalian.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL SCANNER QR ==================== --}}
    <div id="scanModal" class="admin-modal hidden fixed inset-0 z-[80] items-center justify-center bg-slate-900/70 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-sm space-y-4 rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-900">
                    <i class="fas fa-qrcode text-blue-600"></i> Scanner QR Code
                </span>
                <button type="button" data-close-scan class="text-slate-400 transition hover:text-slate-600" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="relative h-56 overflow-hidden rounded-xl border-2 border-blue-500 bg-slate-900">
                <div id="scanReader" class="h-full w-full"></div>
                <div class="pointer-events-none absolute inset-4 rounded-lg border border-dashed border-blue-400/40">
                    <div class="absolute left-0 top-1/2 h-0.5 w-full animate-pulse bg-rose-500 shadow-lg shadow-rose-500"></div>
                </div>
                <span class="pointer-events-none absolute bottom-2 left-1/2 -translate-x-1/2 rounded bg-slate-800/80 px-2 py-0.5 text-[10px] text-slate-300">
                    Arahkan kamera ke kode QR
                </span>
            </div>

            <div id="scanStatus" class="min-h-[1.25rem] text-center text-[11px] text-slate-500">
                Siapkan kamera, lalu arahkan ke QR di atas nomor permohonan.
            </div>

            <p class="text-center text-[10px] leading-relaxed text-slate-400">
                <i class="fas fa-info-circle mr-1"></i>
                Mendukung QR berisi nomor permohonan, contoh: SP-220926-67ACAA.
            </p>

            <button type="button" data-close-scan
                    class="w-full rounded-xl border border-slate-200 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                Tutup
            </button>
        </div>
    </div>

    {{-- ==================== LIGHTBOX LAMPIRAN ==================== --}}
    <div id="lightbox" class="fixed inset-0 z-[85] hidden items-center justify-center bg-black/90 p-4 backdrop-blur-sm">
        <button type="button" id="lightboxClose"
                class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white transition hover:rotate-90 hover:bg-white/30"
                aria-label="Tutup pratinjau">
            <i class="fas fa-times"></i>
        </button>
        <img id="lightboxImg" src="" alt="Pratinjau lampiran" class="hidden max-h-[90vh] max-w-[92vw] rounded-lg object-contain shadow-2xl">
        <iframe id="lightboxFrame" src="" title="Pratinjau PDF" class="hidden h-[90vh] w-[92vw] rounded-lg bg-white shadow-2xl"></iframe>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (function () {
        'use strict';

        var cekNomorUrl = @json(route('permohonan.cek-nomor'));

        var form = document.getElementById('formPengembalian');
        var inputNomor = document.getElementById('nomor_permohonan');
        var inputCatatan = document.getElementById('catatan');
        var inputBukti = document.getElementById('bukti_pengembalian');
        var detailWrap = document.getElementById('permohonanDetail');
        var detailEmpty = document.getElementById('detailEmpty');
        var detailStatus = document.getElementById('detailStatus');
        var detailNomor = document.getElementById('detailNomor');
        var lookupMsg = document.getElementById('lookupMsg');
        var scanModal = document.getElementById('scanModal');
        var scanStatus = document.getElementById('scanStatus');
        var previewWrap = document.getElementById('buktiPreview');

        var html5QrCode = null;
        var isScanning = false;
        var scanActive = false;
        var currentStatus = '';
        var previewUrl = null;

        /* ---------------- Util ---------------- */
        function setText(id, value) {
            var el = document.getElementById(id);
            if (!el) return;
            el.textContent = (value === null || value === undefined || value === '') ? '-' : String(value);
        }

        function setLookupMsg(message, type) {
            if (!lookupMsg) return;
            if (!message) {
                lookupMsg.classList.add('hidden');
                lookupMsg.textContent = '';
                return;
            }
            var tone = type === 'error'
                ? 'border-rose-200 bg-rose-50 text-rose-700'
                : (type === 'ok' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-blue-200 bg-blue-50 text-blue-700');
            lookupMsg.className = 'mt-2 rounded-xl border px-3 py-2 text-[11px] font-medium ' + tone;
            lookupMsg.textContent = message;
        }

        function toast(message, type) {
            if (window.AdminUI && window.AdminUI.toast) {
                window.AdminUI.toast(message, type || 'success');
            }
        }

        /* ---------------- Kondisi barang ---------------- */
        var kondisiCards = Array.prototype.slice.call(document.querySelectorAll('[data-kondisi-card]'));

        function syncKondisi() {
            kondisiCards.forEach(function (card) {
                var radio = card.querySelector('input[type="radio"]');
                var title = card.querySelector('[data-kondisi-title]');
                var check = card.querySelector('[data-kondisi-check]');
                var active = radio && radio.checked;

                card.classList.toggle('border-blue-600', active);
                card.classList.toggle('bg-blue-50/80', active);
                card.classList.toggle('ring-2', active);
                card.classList.toggle('ring-blue-500/20', active);
                card.classList.toggle('border-slate-200', !active);
                card.classList.toggle('bg-white', !active);

                if (title) {
                    title.classList.toggle('text-blue-700', active);
                    title.classList.toggle('text-slate-800', !active);
                }
                if (check) check.classList.toggle('hidden', !active);
            });
        }

        kondisiCards.forEach(function (card) {
            var radio = card.querySelector('input[type="radio"]');
            if (radio) radio.addEventListener('change', syncKondisi);
        });
        syncKondisi();

        /* ---------------- Render barang ---------------- */
        function renderBarang(items, total) {
            var body = document.getElementById('detailBarangBody');
            if (!body) return;
            body.textContent = '';

            if (!items || !items.length) {
                var kosong = document.createElement('p');
                kosong.className = 'rounded-xl border border-slate-200/80 bg-slate-50 p-2.5 text-center text-slate-400';
                kosong.textContent = 'Tidak ada barang.';
                body.appendChild(kosong);
            } else {
                items.forEach(function (item) {
                    var row = document.createElement('div');
                    row.className = 'flex items-center justify-between gap-2 rounded-xl border border-slate-200/80 bg-slate-50 p-2.5';

                    var info = document.createElement('div');
                    info.className = 'min-w-0';

                    var nama = document.createElement('p');
                    nama.className = 'truncate font-bold text-slate-800';
                    nama.textContent = item.nama || '-';
                    info.appendChild(nama);

                    var kode = document.createElement('span');
                    kode.className = 'font-mono text-[10px] font-semibold text-rose-500';
                    kode.textContent = item.kode || '-';
                    info.appendChild(kode);

                    var qty = document.createElement('span');
                    qty.className = 'shrink-0 rounded bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700';
                    qty.textContent = (item.jumlah || 0) + ' Unit';

                    row.appendChild(info);
                    row.appendChild(qty);
                    body.appendChild(row);
                });
            }

            var totalEl = document.getElementById('detailJumlah');
            if (totalEl) totalEl.textContent = String(total || 0);
        }

        /* ---------------- Render lampiran ---------------- */
        function renderLampiran(targetId, info, label) {
            var target = document.getElementById(targetId);
            if (!target) return;
            target.textContent = '';

            if (!info || !info.url) {
                var empty = document.createElement('div');
                empty.className = 'flex h-16 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-[10px] text-slate-400';
                empty.textContent = 'Tidak ada';
                target.appendChild(empty);
                return;
            }

            if (info.is_image) {
                var img = document.createElement('img');
                img.src = info.url;
                img.alt = label;
                img.title = 'Klik untuk memperbesar';
                img.className = 'h-16 w-full cursor-zoom-in rounded-xl border border-slate-200 object-cover shadow-sm transition hover:border-blue-400';
                img.setAttribute('data-src', info.url);
                img.setAttribute('data-type', 'image');
                target.appendChild(img);
                return;
            }

            var link = document.createElement('a');
            link.href = info.url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.className = 'flex h-16 flex-col items-center justify-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-[10px] font-semibold text-slate-500 transition hover:border-blue-400 hover:text-blue-600';
            link.innerHTML = '<i class="fas ' + (info.is_pdf ? 'fa-file-pdf' : 'fa-file') + ' text-lg"></i>';

            if (info.is_pdf) {
                link.setAttribute('data-src', info.url);
                link.setAttribute('data-type', 'pdf');
                link.removeAttribute('target');
            }

            var caption = document.createElement('span');
            caption.textContent = label;
            link.appendChild(caption);
            target.appendChild(link);
        }

        /* ---------------- Lightbox ---------------- */
        var lightbox = document.getElementById('lightbox');
        var lightboxImg = document.getElementById('lightboxImg');
        var lightboxFrame = document.getElementById('lightboxFrame');

        function openLightbox(src, type) {
            if (!lightbox || !src) return;
            if (type === 'pdf') {
                lightboxImg.classList.add('hidden');
                lightboxImg.removeAttribute('src');
                lightboxFrame.src = src;
                lightboxFrame.classList.remove('hidden');
            } else {
                lightboxFrame.classList.add('hidden');
                lightboxFrame.removeAttribute('src');
                lightboxImg.src = src;
                lightboxImg.classList.remove('hidden');
            }
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        }

        function closeLightbox() {
            if (!lightbox) return;
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            if (lightboxImg) lightboxImg.removeAttribute('src');
            if (lightboxFrame) lightboxFrame.removeAttribute('src');
        }

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-src]');
            if (trigger) {
                event.preventDefault();
                openLightbox(trigger.getAttribute('data-src'), trigger.getAttribute('data-type'));
                return;
            }
            if (event.target === lightbox || event.target.closest('#lightboxClose')) {
                closeLightbox();
            }
        });

        /* ---------------- Lookup ---------------- */
        function setStatusBadge(status) {
            if (!detailStatus) return;
            var tones = {
                Disetujui: 'bg-emerald-100 text-emerald-700',
                Dipinjam: 'bg-blue-100 text-blue-700',
                Menunggu: 'bg-amber-100 text-amber-700',
                Ditolak: 'bg-rose-100 text-rose-700',
                Dikembalikan: 'bg-slate-100 text-slate-600'
            };
            detailStatus.className = 'rounded-full px-2 py-0.5 text-[10px] font-bold ' + (tones[status] || 'bg-slate-100 text-slate-600');
            detailStatus.textContent = status || '-';
        }

        function lookupNomor(nomor) {
            nomor = String(nomor || '').trim();
            if (!nomor) {
                detailWrap.classList.add('hidden');
                detailEmpty.classList.remove('hidden');
                setLookupMsg('', null);
                currentStatus = '';
                return;
            }

            setLookupMsg('Mencari data permohonan...', 'info');

            fetch(cekNomorUrl + '?nomor_permohonan=' + encodeURIComponent(nomor), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error(response.status === 403 ? 'Akses ditolak. Login sebagai Admin/Super Admin.' : 'Gagal mengambil data permohonan.');
                }
                return response.json();
            }).then(function (data) {
                if (!data.exists) {
                    currentStatus = '';
                    detailWrap.classList.add('hidden');
                    detailEmpty.classList.remove('hidden');
                    setLookupMsg('Permohonan tidak ditemukan. Periksa nomor: ' + nomor, 'error');
                    return;
                }

                currentStatus = data.status || '';
                setLookupMsg('');

                setText('detailNama', data.nama_peminjam);
                setText('detailNik', data.nik);
                setText('detailTelepon', data.telepon);
                setText('detailInstansi', data.instansi);
                setText('detailAlamat', data.alamat);
                setText('detailTglPinjam', data.tanggal_pinjam);
                setText('detailTglKembali', data.tanggal_kembali);
                setText('detailKeperluan', data.keperluan);
                setText('detailNomor', nomor);

                setStatusBadge(currentStatus);
                detailStatus.classList.remove('hidden');

                renderBarang(data.items, data.total_jumlah);
                renderLampiran('lampiranKtp', data.foto_ktp, 'Foto Identitas');
                renderLampiran('lampiranSuratTugas', data.surat_tugas, 'Surat Tugas');

                detailEmpty.classList.add('hidden');
                detailWrap.classList.remove('hidden');

                var warn = document.getElementById('detailWarn');
                if (currentStatus === 'Disetujui' || currentStatus === 'Dipinjam') {
                    warn.classList.add('hidden');
                    warn.textContent = '';
                } else {
                    warn.classList.remove('hidden');
                    warn.textContent = 'Status "' + currentStatus + '" tidak dapat diproses sebagai pengembalian.';
                }
            }).catch(function (error) {
                currentStatus = '';
                detailWrap.classList.add('hidden');
                detailEmpty.classList.remove('hidden');
                setLookupMsg(error.message || 'Gagal mengambil data permohonan.', 'error');
            });
        }

        /* ---------------- Scanner QR ---------------- */
        function setScanStatus(message, isError) {
            if (!scanStatus) return;
            scanStatus.textContent = message;
            scanStatus.className = 'min-h-[1.25rem] text-center text-[11px] ' + (isError ? 'font-medium text-rose-600' : 'text-slate-500');
        }

        function stopScanner() {
            if (!html5QrCode) return Promise.resolve();
            isScanning = false;
            var instance = html5QrCode;
            html5QrCode = null;
            return instance.stop().then(function () {
                try { instance.clear(); } catch (e) {}
            }).catch(function () {
                try { instance.clear(); } catch (e) {}
            });
        }

        function closeScanner() {
            scanActive = false;
            stopScanner();
            if (window.AdminUI) window.AdminUI.closeModal('scanModal');
        }

        function startScanner() {
            if (!scanActive) return;

            if (typeof Html5Qrcode === 'undefined') {
                setScanStatus('Library scanner gagal dimuat. Muat ulang halaman atau isi manual.', true);
                return;
            }
            if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (!window.isSecureContext) {
                    setScanStatus('Scanner butuh koneksi aman (https://). Isi nomor secara manual bila perlu.', true);
                } else {
                    setScanStatus('Browser tidak mendukung kamera. Gunakan input manual.', true);
                }
                return;
            }

            stopScanner().then(function () {
                if (!scanActive) return;

                html5QrCode = new Html5Qrcode('scanReader', {
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                    useBarCodeDetectorIfSupported: true,
                    verbose: false
                });
                isScanning = true;
                setScanStatus('Mengaktifkan kamera...', false);

                html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 15,
                        qrbox: function (w, h) {
                            var side = Math.max(Math.min(Math.min(w, h) - 40, 260), 160);
                            return { width: side, height: side };
                        },
                        disableFlip: false
                    },
                    function (decodedText) {
                        if (!decodedText || !isScanning) return;
                        isScanning = false;
                        scanActive = false;
                        var cleaned = String(decodedText).trim().replace(/\s+/g, '');
                        if (inputNomor) inputNomor.value = cleaned;
                        setScanStatus('Berhasil scan: ' + cleaned, false);
                        stopScanner().then(function () {
                            if (window.AdminUI) window.AdminUI.closeModal('scanModal');
                            lookupNomor(cleaned);
                            toast('QR Code terdeteksi: ' + cleaned, 'success');
                            setTimeout(function () {
                                if (inputCatatan) inputCatatan.focus();
                            }, 350);
                        });
                    },
                    function () {}
                ).then(function () {
                    if (!scanActive) {
                        stopScanner();
                        return;
                    }
                    setScanStatus('Arahkan kamera ke QR nomor permohonan.', false);
                }).catch(function (error) {
                    isScanning = false;
                    setScanStatus('Kamera tidak tersedia atau izin ditolak. Gunakan input manual.', true);
                    console.warn('Scanner start failed', error);
                });
            });
        }

        function openScanner() {
            scanActive = true;
            if (window.AdminUI) window.AdminUI.openModal('scanModal');
            setScanStatus('Mengaktifkan kamera...', false);
            setTimeout(startScanner, 200);
        }

        var btnScan = document.getElementById('btnOpenScanner');
        if (btnScan) btnScan.addEventListener('click', openScanner);

        if (scanModal) {
            scanModal.querySelectorAll('[data-close-scan]').forEach(function (btn) {
                btn.addEventListener('click', closeScanner);
            });
            scanModal.addEventListener('click', function (event) {
                if (event.target === scanModal) closeScanner();
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                if (lightbox && !lightbox.classList.contains('hidden')) closeLightbox();
                if (scanModal && !scanModal.classList.contains('hidden')) closeScanner();
            }
        });

        /* ---------------- Quick pick ---------------- */
        document.querySelectorAll('[data-quick-pick]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var nomor = chip.getAttribute('data-quick-pick');
                if (inputNomor) inputNomor.value = nomor;
                lookupNomor(nomor);
            });
        });

        /* ---------------- Auto lookup ---------------- */
        if (inputNomor) {
            inputNomor.addEventListener('change', function () { lookupNomor(inputNomor.value); });
            inputNomor.addEventListener('blur', function () { lookupNomor(inputNomor.value); });
            if (inputNomor.value.trim() !== '') lookupNomor(inputNomor.value);
        }

        /* ---------------- Upload preview ---------------- */
        if (inputBukti) {
            inputBukti.addEventListener('change', function () {
                if (previewWrap) previewWrap.textContent = '';
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                var file = inputBukti.files && inputBukti.files[0];
                if (!file) {
                    if (previewWrap) previewWrap.classList.add('hidden');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    toast('Ukuran foto melebihi 2MB.', 'error');
                    inputBukti.value = '';
                    if (previewWrap) previewWrap.classList.add('hidden');
                    return;
                }

                previewUrl = URL.createObjectURL(file);

                var box = document.createElement('div');
                box.className = 'group relative h-16 w-16 overflow-hidden rounded-xl border border-slate-200 shadow-sm';

                var img = document.createElement('img');
                img.src = previewUrl;
                img.alt = 'Pratinjau bukti';
                img.className = 'h-full w-full object-cover';

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'absolute right-1 top-1 rounded-full bg-slate-900/80 p-1 text-white opacity-0 transition group-hover:opacity-100';
                remove.setAttribute('aria-label', 'Hapus foto');
                remove.innerHTML = '<i class="fas fa-times text-[9px]"></i>';
                remove.addEventListener('click', function () {
                    inputBukti.value = '';
                    if (previewUrl) {
                        URL.revokeObjectURL(previewUrl);
                        previewUrl = null;
                    }
                    if (previewWrap) {
                        previewWrap.textContent = '';
                        previewWrap.classList.add('hidden');
                    }
                });

                box.appendChild(img);
                box.appendChild(remove);
                if (previewWrap) {
                    previewWrap.appendChild(box);
                    previewWrap.classList.remove('hidden');
                    previewWrap.classList.add('flex');
                }
            });
        }

        /* ---------------- Reset ---------------- */
        var btnReset = document.getElementById('btnReset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                if (form) form.reset();
                if (previewWrap) {
                    previewWrap.textContent = '';
                    previewWrap.classList.add('hidden');
                }
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }
                detailWrap.classList.add('hidden');
                detailEmpty.classList.remove('hidden');
                detailStatus.classList.add('hidden');
                setLookupMsg('', null);
                currentStatus = '';
                syncKondisi();
                if (inputNomor) inputNomor.focus();
            });
        }

        /* ---------------- Validasi submit ---------------- */
        if (form) {
            form.addEventListener('submit', function (event) {
                var nomor = inputNomor ? inputNomor.value.trim() : '';
                var catatan = inputCatatan ? inputCatatan.value.trim() : '';
                var kondisi = form.querySelector('input[name="kondisi_barang"]:checked');

                if (!nomor) {
                    event.preventDefault();
                    toast('Nomor permohonan wajib diisi (scan QR atau ketik manual).', 'error');
                    if (inputNomor) inputNomor.focus();
                    return;
                }
                if (!kondisi) {
                    event.preventDefault();
                    toast('Pilih kondisi barang saat dikembalikan.', 'error');
                    return;
                }
                if (!catatan) {
                    event.preventDefault();
                    toast('Catatan Admin wajib diisi agar pengembalian dapat dikonfirmasi.', 'error');
                    if (inputCatatan) inputCatatan.focus();
                    return;
                }
                if (currentStatus && currentStatus !== 'Disetujui' && currentStatus !== 'Dipinjam') {
                    event.preventDefault();
                    toast('Status "' + currentStatus + '" tidak dapat dikembalikan.', 'error');
                    return;
                }

                var btn = document.getElementById('btnConfirmReturn');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('cursor-not-allowed', 'opacity-70');
                    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i><span>Memproses...</span>';
                }
            });
        }

        /* ---------------- Buka scanner via ?scan=1 ---------------- */
        if (new URLSearchParams(window.location.search).get('scan') === '1') {
            setTimeout(openScanner, 350);
        }
    })();
</script>
@endpush
