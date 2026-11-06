@extends('layouts.admin-master')

@section('title', 'Detail Permohonan')
@section('page_key', 'permohonan')

@php
    // Semua class Tailwind ditulis literal agar ikut ter-compile oleh JIT.
    $statusPill = [
        'Menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Dipinjam' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Dikembalikan' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $statusIcon = [
        'Menunggu' => 'fa-clock',
        'Disetujui' => 'fa-check-circle',
        'Ditolak' => 'fa-times-circle',
        'Dipinjam' => 'fa-box',
        'Dikembalikan' => 'fa-undo',
    ];

    $showBarcode = in_array($permohonan->status, \App\Services\TransisiStatus::SURAT_BISA_DILIHAT, true);
    $namaBarang = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ');

    $extGambar = ['jpg', 'jpeg', 'png', 'webp'];
    $ktpExt = $permohonan->foto_ktp ? strtolower(pathinfo($permohonan->foto_ktp, PATHINFO_EXTENSION)) : '';
    $suratExt = $permohonan->surat_tugas ? strtolower(pathinfo($permohonan->surat_tugas, PATHINFO_EXTENSION)) : '';
    $ktpGambar = (bool) $permohonan->foto_ktp && in_array($ktpExt, $extGambar, true);
    $suratGambar = (bool) $permohonan->surat_tugas && in_array($suratExt, $extGambar, true);

    $fotoUrls = [];
    if ($ktpGambar) $fotoUrls[] = asset('storage/' . $permohonan->foto_ktp);
    if ($suratGambar) $fotoUrls[] = asset('storage/' . $permohonan->surat_tugas);
    $suratIdx = $ktpGambar ? 1 : 0;

    $labelCls = 'text-[10px] font-semibold uppercase tracking-wider text-slate-400';
    $valueCls = 'mt-0.5 text-xs font-semibold text-slate-800';
@endphp

@push('css')
<style>
    .ktp-thumb {
        display: block;
        width: 110px;
        height: auto;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .08);
        cursor: zoom-in;
        background: #fff;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .ktp-thumb:hover {
        transform: translateY(-2px);
        border-color: #93c5fd;
        box-shadow: 0 6px 16px rgba(37, 99, 235, .18);
    }

    .timeline { position: relative; padding-left: 28px; }
    .timeline::before {
        content: ''; position: absolute; left: 9px; top: 6px; bottom: 6px;
        width: 2px; background: #e2e8f0;
    }
    .tl-item { position: relative; padding-bottom: 18px; }
    .tl-item:last-child { padding-bottom: 0; }
    .tl-dot {
        position: absolute; left: -25px; top: 4px; width: 14px; height: 14px;
        border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px #cbd5e1;
        background: #fff;
    }
    .tl-item.active .tl-dot {
        box-shadow: 0 0 0 2px #2563eb;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
    }
    .tl-item.rejected .tl-dot { box-shadow: 0 0 0 2px #f43f5e; background: #f43f5e; }
    .tl-title { font-weight: 700; color: #0f172a; font-size: .8rem; }
    .tl-desc { color: #64748b; font-size: .72rem; }
    .tl-who { font-size: .68rem; color: #94a3b8; }

    .permohonan-barcode {
        width: 150px;
        height: 150px;
        display: block;
        position: relative;
        overflow: hidden;
        margin: 0 auto .35rem;
    }
    .permohonan-barcode canvas {
        width: 150px !important;
        height: 150px !important;
        display: block !important;
    }
    .permohonan-barcode img {
        position: absolute;
        top: 0;
        left: 0;
        width: 150px !important;
        height: 150px !important;
        display: none !important;
    }
    #printBarcode { position: relative; overflow: hidden; margin: 0 auto; }
    #printBarcode canvas {
        display: block !important;
        width: 180px !important;
        height: 180px !important;
    }
    #printBarcode img {
        position: absolute;
        top: 0;
        left: 0;
        width: 180px !important;
        height: 180px !important;
        display: none !important;
    }

    @media print {
        body * { visibility: hidden !important; }
        #printBarcodeArea, #printBarcodeArea * { visibility: visible !important; }
        #printBarcodeArea {
            position: absolute !important;
            left: 0; top: 0;
            width: 100%;
            padding: 24px;
            text-align: center;
            background: #fff !important;
        }
        /* Elemen shell admin-master yang tidak boleh ikut tercetak. */
        #adminSidebar, #sidebarOverlay, #adminToast, #deleteModal,
        .admin-modal, .image-modal-overlay, .no-print,
        #adminMain > header, #adminMain > footer { display: none !important; }
        #adminMain { padding-left: 0 !important; }
        html, body { background: #fff !important; }
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
                <span class="font-medium text-slate-600">Detail</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-mono text-2xl font-bold tracking-tight text-slate-900">{{ $permohonan->nomor_permohonan }}</h1>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusPill[$permohonan->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                    <i class="fas {{ $statusIcon[$permohonan->status] ?? 'fa-circle' }} mr-1 text-[10px]"></i>{{ $permohonan->status }}
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Diajukan {{ $permohonan->created_at?->translatedFormat('d M Y H:i') }} ·
                {{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? 'Tanpa instansi' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('permohonan.edit', $permohonan->id) }}"
               class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                <i class="fas fa-pen text-[11px]"></i>
                <span>Edit Surat</span>
            </a>
            <a href="{{ route('permohonan.index') }}"
               class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100">
                <i class="fas fa-arrow-left text-[11px]"></i>
                <span>Kembali</span>
            </a>
        </div>
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

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- ==================== KOLOM KIRI ==================== --}}
        <div class="space-y-6 lg:col-span-7">
            {{-- Data pemohon --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Data Pemohon</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    <div>
                        <p class="{{ $labelCls }}">Nama Peminjam</p>
                        <p class="{{ $valueCls }}">{{ $permohonan->nama_peminjam }}</p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Instansi</p>
                        <p class="{{ $valueCls }}">{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Nomor Identitas</p>
                        <p class="{{ $valueCls }} font-mono">{{ $permohonan->nik ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Jabatan</p>
                        <p class="{{ $valueCls }}">{{ $permohonan->jabatan ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Telepon</p>
                        <p class="{{ $valueCls }}">{{ $permohonan->telepon ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Tempat / Tgl Lahir</p>
                        <p class="{{ $valueCls }}">{{ $permohonan->tempat_tanggal_lahir ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="{{ $labelCls }}">Alamat</p>
                        <p class="{{ $valueCls }}">{{ $permohonan->alamat ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Tanggal Pinjam</p>
                        <p class="{{ $valueCls }}">
                            <i class="fas fa-calendar mr-1 text-[10px] text-slate-400"></i>
                            {{ $permohonan->tanggal_pinjam ? \Carbon\Carbon::parse($permohonan->tanggal_pinjam)->translatedFormat('d M Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="{{ $labelCls }}">Tanggal Kembali</p>
                        <p class="{{ $valueCls }}">
                            <i class="fas fa-calendar-check mr-1 text-[10px] text-slate-400"></i>
                            {{ $permohonan->tanggal_kembali ? \Carbon\Carbon::parse($permohonan->tanggal_kembali)->translatedFormat('d M Y') : '—' }}
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="{{ $labelCls }}">Keperluan</p>
                        <p class="mt-1 rounded-xl border border-slate-100 bg-slate-50/70 p-3 text-xs leading-relaxed text-slate-700">
                            {{ $permohonan->keperluan ?: '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Barang dipinjam --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-indigo-100 bg-indigo-50 text-indigo-600">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">Barang Dipinjam</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">
                        {{ $permohonan->detailPermohonan->sum('jumlah') }} item
                    </span>
                </div>
                <div class="admin-table-wrap">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">No</th>
                                <th class="px-5 py-3">Barang</th>
                                <th class="w-24 px-4 py-3 text-center">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($permohonan->detailPermohonan as $d)
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 text-center text-slate-400">{{ $loop->iteration }}</td>
                                    <td class="px-5 py-3">
                                        <div class="font-semibold text-slate-800">{{ $d->inventaris->nama_barang ?? 'Barang #' . $d->inventaris_id }}</div>
                                        @if($d->inventaris?->kode_barang)
                                            <div class="font-mono text-[10px] text-slate-400">{{ $d->inventaris->kode_barang }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">
                                            {{ $d->jumlah }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                                        <i class="fas fa-box-open mb-2 block text-xl text-slate-300"></i>
                                        Tidak ada barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Surat --}}
            @if($permohonan->bolehLihatSurat())
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">Surat Permohonan</h2>
                    </div>
                    <div class="flex flex-wrap gap-2 p-5">
                        <a href="{{ route('surat.preview', $permohonan->id) }}" target="_blank"
                           class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                            <i class="fas fa-eye text-[11px]"></i>
                            <span>Lihat Surat</span>
                        </a>
                        <a href="{{ route('surat.download.docx', $permohonan->id) }}" target="_blank"
                           class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                            <i class="fas fa-file-word text-[11px]"></i>
                            <span>Format DOCX</span>
                        </a>
                        <a href="{{ route('surat.download.pdf', $permohonan->id) }}" target="_blank"
                           class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                            <i class="fas fa-file-pdf text-[11px]"></i>
                            <span>Format PDF</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- ==================== KOLOM KANAN ==================== --}}
        <div class="space-y-6 lg:col-span-5">
            {{-- QR code --}}
            @if($showBarcode)
                <div class="no-print overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900">QR Code Permohonan</h2>
                        </div>
                        <button type="button" onclick="printPermohonanBarcode()" title="Cetak QR code"
                                class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-600 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                            <i class="fas fa-print text-[10px]"></i>
                            <span>Cetak</span>
                        </button>
                    </div>
                    <div class="space-y-1 p-5 text-center">
                        <div id="permohonanBarcode" class="permohonan-barcode"></div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Permohonan</p>
                        <p class="font-mono text-sm font-bold text-slate-900">{{ $permohonan->nomor_permohonan }}</p>
                        <p class="text-[11px] leading-relaxed text-slate-500">
                            Scan QR ini di halaman <strong>Pengembalian Barang</strong> untuk memudahkan pendataan barang kembali.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Lampiran --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-violet-100 bg-violet-50 text-violet-600">
                        <i class="fas fa-paperclip"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Lampiran</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
                    <div>
                        <p class="{{ $labelCls }} mb-2">Foto Identitas</p>
                        @if($ktpGambar)
                            <img src="{{ asset('storage/' . $permohonan->foto_ktp) }}"
                                 class="ktp-thumb" onclick="openFoto(@js($fotoUrls), 0)"
                                 alt="Foto Identitas" title="Klik untuk memperbesar">
                        @elseif($permohonan->foto_ktp)
                            <a href="{{ asset('storage/' . $permohonan->foto_ktp) }}" target="_blank"
                               class="flex flex-col items-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-slate-600 transition-all hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-600">
                                <i class="fas fa-file text-xl"></i>
                                <span class="text-[11px] font-semibold">Buka File</span>
                            </a>
                        @else
                            <div class="flex flex-col items-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-slate-400">
                                <i class="fas fa-id-card text-xl"></i>
                                <span class="text-[11px] font-semibold">Tidak ada</span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="{{ $labelCls }} mb-2">Surat Tugas</p>
                        @if($suratGambar)
                            <img src="{{ asset('storage/' . $permohonan->surat_tugas) }}"
                                 class="ktp-thumb" onclick="openFoto(@js($fotoUrls), {{ $suratIdx }})"
                                 alt="Surat Tugas" title="Klik untuk memperbesar">
                        @elseif($permohonan->surat_tugas)
                            <a href="{{ asset('storage/' . $permohonan->surat_tugas) }}" target="_blank"
                               class="flex flex-col items-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-slate-600 transition-all hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-600">
                                <i class="fas fa-file-alt text-xl"></i>
                                <span class="text-[11px] font-semibold">Buka File</span>
                            </a>
                        @else
                            <div class="flex flex-col items-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4 text-slate-400">
                                <i class="fas fa-file-alt text-xl"></i>
                                <span class="text-[11px] font-semibold">Tidak ada</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Catatan admin --}}
            @if($permohonan->catatan_admin)
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-amber-100 bg-amber-50 text-amber-600">
                            <i class="fas fa-sticky-note"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">Catatan Admin</h2>
                    </div>
                    <p class="p-5 text-xs leading-relaxed text-slate-700">{{ $permohonan->catatan_admin }}</p>
                </div>
            @endif

            {{-- Aksi --}}
            @if($permohonan->status == 'Menunggu')
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h2 class="text-sm font-bold text-slate-900">Aksi</h2>
                    </div>
                    <div class="flex flex-wrap gap-2 p-5">
                        <button type="button" data-aksi-modal="acc"
                                data-nama="{{ $permohonan->nama_peminjam }}"
                                data-barang="{{ $namaBarang }}"
                                data-action="{{ route('permohonan.status', $permohonan->id) }}"
                                class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-emerald-600/20 transition-all hover:bg-emerald-700">
                            <i class="fas fa-check text-[11px]"></i>
                            <span>ACC / Setujui</span>
                        </button>
                        <button type="button" data-aksi-modal="tolak"
                                data-nama="{{ $permohonan->nama_peminjam }}"
                                data-barang="{{ $namaBarang }}"
                                data-action="{{ route('permohonan.status', $permohonan->id) }}"
                                class="flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-rose-600/20 transition-all hover:bg-rose-700">
                            <i class="fas fa-times text-[11px]"></i>
                            <span>Tolak</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Riwayat status --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600">
                        <i class="fas fa-history"></i>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Status</h2>
                </div>
                <div class="p-5">
                    @if($permohonan->statusLogs->isEmpty())
                        <p class="text-center text-xs text-slate-500">Belum ada riwayat.</p>
                    @else
                        <div class="timeline">
                            @foreach($permohonan->statusLogs as $log)
                                <div class="tl-item {{ $log->status_baru === 'Ditolak' ? 'rejected' : 'active' }}">
                                    <div class="tl-dot"></div>
                                    <div class="tl-title">
                                        @if($log->status_baru === 'Menunggu')
                                            Permohonan Diajukan
                                        @else
                                            {{ $log->status_lama }}
                                            <i class="fas fa-arrow-right mx-1 text-[10px] text-slate-400"></i>
                                            {{ $log->status_baru }}
                                        @endif
                                    </div>
                                    <div class="tl-desc">{{ $log->catatan ?? 'Status diperbarui.' }}</div>
                                    <div class="tl-who">
                                        <i class="fas fa-clock mr-1"></i>{{ $log->created_at?->translatedFormat('d M Y H:i') }}
                                        @if($log->user)
                                            · <i class="fas fa-user-shield mr-1"></i>{{ $log->user->name }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Area cetak QR (hanya terlihat saat print) --}}
    @if($showBarcode)
        <div id="printBarcodeArea" style="display:none;">
            <div id="printBarcode" style="width:180px;height:180px;"></div>
            <div style="font-family:Arial,sans-serif;font-size:11px;color:#666;text-transform:uppercase;letter-spacing:1px;margin-top:6px;">Nomor Permohonan</div>
            <div style="font-family:Consolas,Monaco,monospace;font-size:16px;font-weight:700;margin-top:2px;">{{ $permohonan->nomor_permohonan }}</div>
            <div style="font-family:Arial,sans-serif;font-size:11px;color:#666;">SILAPIN — Scan untuk pendataan pengembalian</div>
        </div>
    @endif

    {{-- Lightbox lampiran (komponen global dari public/css/custom.css) --}}
    <div class="image-modal-overlay" id="imageModal" onclick="closeFoto(event)">
        <div class="image-modal-content">
            <button type="button" class="modal-close" onclick="closeFoto()">&times;</button>
            @if(count($fotoUrls) > 1)
                <button type="button" class="modal-nav modal-prev" onclick="navFoto(-1)"><i class="fas fa-chevron-left"></i></button>
            @endif
            <img id="modalFotoImg" src="" alt="Pratinjau Lampiran">
            @if(count($fotoUrls) > 1)
                <button type="button" class="modal-nav modal-next" onclick="navFoto(1)"><i class="fas fa-chevron-right"></i></button>
            @endif
            <div class="modal-caption" id="modalFotoCaption"></div>
            <div class="modal-dots" id="modalFotoDots"></div>
        </div>
    </div>

    {{-- MODAL ACC / TOLAK --}}
    @include('admin.permohonan._aksi-modals')
@endsection

@push('scripts')
@if($showBarcode)
    <script src="{{ asset('vendor/qrcode/qrcode.min.js') }}?v={{ @filemtime(public_path('vendor/qrcode/qrcode.min.js')) ?: 1 }}"></script>
    <script>
        (function renderQr() {
            const nomor = @js($permohonan->nomor_permohonan);

            function draw(target, size, dark) {
                if (typeof QRCode === 'undefined') return;
                const el = document.querySelector(target);
                if (!el) return;

                try {
                    el.innerHTML = '';
                    new QRCode(el, {
                        text: nomor,
                        width: size,
                        height: size,
                        colorDark: dark,
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                    el.querySelectorAll('img').forEach(function (img) {
                        img.style.display = 'none';
                    });
                } catch (err) {
                    console.warn('Gagal render QR', err);
                }
            }

            draw('#permohonanBarcode', 150, '#0f172a');
            draw('#printBarcode', 180, '#000000');
        })();

        function printPermohonanBarcode() {
            const area = document.getElementById('printBarcodeArea');
            if (!area) return;
            area.style.display = 'block';
            window.print();
            setTimeout(function () {
                area.style.display = 'none';
            }, 500);
        }
    </script>
@endif

<script>
    /* -------------------- Lightbox lampiran -------------------- */
    let fotoList = [];
    let fotoIndex = 0;

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
        document.getElementById('modalFotoImg').src = fotoList[fotoIndex];
        document.getElementById('modalFotoCaption').textContent = (fotoIndex + 1) + ' / ' + fotoList.length;

        document.getElementById('modalFotoDots').innerHTML = fotoList.map(function (_, index) {
            return '<span class="dot ' + (index === fotoIndex ? 'active' : '') + '" onclick="fotoIndex=' + index + ';updateFotoModal()"></span>';
        }).join('');
    }

    function closeFoto(event) {
        if (event && event.target !== event.currentTarget) return;
        document.getElementById('imageModal').classList.remove('active');
    }

    // Lightbox bukan .admin-modal, jadi Esc-nya ditangani sendiri dan hanya aktif
    // saat lightbox terbuka agar tidak ikut menutup modal ACC/Tolak.
    document.addEventListener('keydown', function (event) {
        const modal = document.getElementById('imageModal');
        if (!modal || !modal.classList.contains('active')) return;
        if (event.key === 'Escape') closeFoto();
        if (event.key === 'ArrowLeft') navFoto(-1);
        if (event.key === 'ArrowRight') navFoto(1);
    });
</script>
@include('admin.permohonan._aksi-scripts')
@endpush
