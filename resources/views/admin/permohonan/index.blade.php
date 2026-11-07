@extends('layouts.admin-master')

@section('title', $pageTitle ?? 'Data Permohonan')
@section('page_key', 'permohonan')

@php
    // Class Tailwind harus literal agar ikut ter-compile oleh JIT.
    $statusPill = [
        'Menunggu' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Ditolak' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Dipinjam' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Dikembalikan' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $statusIcon = [
        'Menunggu' => 'fa-hourglass-half',
        'Disetujui' => 'fa-check-circle',
        'Ditolak' => 'fa-times-circle',
        'Dipinjam' => 'fa-boxes',
        'Dikembalikan' => 'fa-undo',
    ];
    $q = isset($q) ? trim((string) $q) : '';
    $activeStatus = $activeStatus ?? 'Semua';
    $tabCounts = $tabCounts ?? [];
    $stats = $stats ?? [];

    // Tab Menunggu memakai warna notifikasi merah saat ada antrean, dan
    // kembali netral saat tidak ada yang perlu disetujui.
    $menungguAda = ($tabCounts['Menunggu'] ?? 0) > 0;

    $tabDot = [
        'Semua' => 'bg-slate-400',
        'Menunggu' => $menungguAda ? 'bg-rose-500' : 'bg-slate-300',
        'Disetujui' => 'bg-emerald-500',
        'Ditolak' => 'bg-rose-500',
        'Dikembalikan' => 'bg-slate-400',
    ];
    $tabs = ['Semua', 'Menunggu', 'Disetujui', 'Ditolak', 'Dikembalikan'];

    $tabHref = function (string $tab) use ($q) {
        $params = $q !== '' ? ['q' => $q] : [];

        return $tab === 'Semua'
            ? route('permohonan.index', $params)
            : route('permohonan.byStatus', array_merge(['status' => $tab], $params));
    };

    // Form pencarian harus mengarah ke daftar yang sedang dibuka, bukan selalu /permohonan.
    $listingAction = $activeStatus === 'Semua'
        ? route('permohonan.index')
        : route('permohonan.byStatus', ['status' => $activeStatus]);

    // route() dengan lebih dari satu argumen tidak bisa ditulis di dalam
    // @json(), karena direktif itu memecah argumen pada koma pertama.
    $exportUrl = route('permohonan.export', array_filter([
        'q'      => $q,
        'status' => $activeStatus === 'Semua' ? null : $activeStatus,
    ]));
@endphp

@push('css')
<style>
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
        #adminSidebar, #sidebarOverlay, #adminToast, #deleteModal,
        .admin-modal, #adminMain > header, #adminMain > footer { display: none !important; }
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
                <span class="font-medium text-slate-600">{{ $pageTitle ?? 'Data Permohonan' }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $pageTitle ?? 'Data Permohonan' }}</h1>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                    {{ $permohonan->total() }} Data
                </span>
            </div>
            <p class="mt-1 text-xs text-slate-500">
                Kelola permohonan peminjaman barang inventaris, setujui atau tolak, dan unduh suratnya.
            </p>
        </div>
    </div>

    {{-- ==================== STATISTIK ==================== --}}
    @php
        $tiles = [
            ['icon' => 'fa-file-alt', 'box' => 'border-blue-100 bg-blue-50 text-blue-600', 'label' => 'Total Permohonan', 'value' => $stats['total'] ?? 0],
            ['icon' => 'fa-hourglass-half', 'box' => 'border-amber-100 bg-amber-50 text-amber-600', 'label' => 'Menunggu Persetujuan', 'value' => $stats['menunggu'] ?? 0],
            ['icon' => 'fa-boxes', 'box' => 'border-emerald-100 bg-emerald-50 text-emerald-600', 'label' => 'Disetujui & Dipinjam', 'value' => $stats['aktif'] ?? 0],
            ['icon' => 'fa-times-circle', 'box' => 'border-rose-100 bg-rose-50 text-rose-600', 'label' => 'Ditolak', 'value' => $stats['tolak'] ?? 0],
        ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($tiles as $tile)
            <div class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl border {{ $tile['box'] }}">
                    <i class="fas {{ $tile['icon'] }} text-lg"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $tile['label'] }}</p>
                    <h3 class="mt-0.5 text-xl font-bold text-slate-900">{{ number_format($tile['value'], 0, ',', '.') }}</h3>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ==================== TAB STATUS ==================== --}}
    <nav class="flex flex-wrap items-center gap-1 rounded-2xl border border-slate-200/80 bg-slate-100/70 p-1.5">
        @foreach($tabs as $tab)
            @php
                $on = $activeStatus === $tab;
                $isMenungguAlert = $tab === 'Menunggu' && $menungguAda;
            @endphp
            <a href="{{ $tabHref($tab) }}"
               class="flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-semibold transition-all {{ $on ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:bg-white/70 hover:text-slate-800' }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $tabDot[$tab] }} {{ $isMenungguAlert ? 'ring-2 ring-rose-200' : '' }}"></span>
                <span>{{ $tab }}</span>
                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isMenungguAlert ? 'bg-rose-100 text-rose-700 ring-1 ring-rose-200' : ($on ? 'bg-blue-50 text-blue-700' : 'bg-white text-slate-500') }}">
                    {{ $tabCounts[$tab] ?? 0 }}
                </span>
            </a>
        @endforeach
    </nav>

    {{-- ==================== DATA CARD ==================== --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        {{-- Toolbar --}}
        <form method="GET" action="{{ $listingAction }}"
              class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-4 md:flex-row md:items-center md:justify-between">
            <div class="relative w-full max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Cari nomor, nama, NIK, instansi, atau keperluan..."
                       class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-9 text-xs text-slate-700 placeholder-slate-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                @if($q !== '')
                    <a href="{{ $listingAction }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                       title="Hapus pencarian">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="{{ $tabHref($activeStatus) }}"
                   class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm transition-all hover:bg-slate-100"
                   title="Muat ulang">
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

        {{-- Tabel --}}
        <div class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-4 py-3.5 text-center">No</th>
                        <th class="px-5 py-3.5">Instansi</th>
                        <th class="px-5 py-3.5">Nama</th>
                        <th class="px-5 py-3.5">Nomor Identitas</th>
                        <th class="px-5 py-3.5">Tanggal Pinjam</th>
                        <th class="px-5 py-3.5">Tanggal Kembali</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="min-w-[220px] px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permohonan as $item)
                        @php
                            $bisaSurat = $item->bolehLihatSurat();
                            $bisaQr = in_array($item->status, \App\Services\TransisiStatus::SURAT_BISA_DILIHAT, true);
                            $namaBarang = $item->detailPermohonan->pluck('inventaris.nama_barang')->implode(', ');
                        @endphp
                        <tr class="group transition-colors hover:bg-slate-50/80">
                            <td class="px-4 py-4 text-center font-medium text-slate-400">
                                {{ $permohonan->firstItem() + $loop->index }}
                            </td>
                            <td class="max-w-xs px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-bold text-slate-900">
                                            {{ $item->instansi?->nama_instansi ?? $item->nama_instansi_lain ?? '—' }}
                                        </div>
                                        <div class="truncate font-mono text-[10px] text-slate-400">{{ $item->nomor_permohonan }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-800">{{ $item->nama_peminjam }}</div>
                                <div class="text-[10px] text-slate-400">{{ $item->jabatan ?: '—' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-mono text-[11px] text-slate-700">{{ $item->nik ?: '—' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-calendar text-[11px] text-slate-400"></i>
                                    <span>{{ $item->tanggal_pinjam ? \Carbon\Carbon::parse($item->tanggal_pinjam)->translatedFormat('d M Y') : '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-calendar-check text-[11px] text-slate-400"></i>
                                    <span>{{ $item->tanggal_kembali ? \Carbon\Carbon::parse($item->tanggal_kembali)->translatedFormat('d M Y') : '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[10px] font-semibold ring-1 {{ $statusPill[$item->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    <i class="fas {{ $statusIcon[$item->status] ?? 'fa-circle' }} mr-1 text-[9px]"></i>{{ $item->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center justify-center gap-0.5">
                                    <a href="{{ route('permohonan.show', $item->id) }}" title="Lihat Detail"
                                       class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                        <i class="fas fa-eye text-[13px]"></i>
                                    </a>
                                    @if($bisaQr)
                                        <button type="button" onclick="bukaBarcodeModal(@js($item->nomor_permohonan))" title="Lihat QR Code"
                                                class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600">
                                            <i class="fas fa-qrcode text-[13px]"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('permohonan.edit', $item->id) }}" title="Edit Surat"
                                       class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </a>
                                    @if($bisaSurat)
                                        <a href="{{ route('surat.preview', $item->id) }}" target="_blank" title="Lihat Surat"
                                           class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-slate-200 hover:bg-slate-100 hover:text-slate-700">
                                            <i class="fas fa-file-alt text-[13px]"></i>
                                        </a>
                                        <a href="{{ route('surat.download.docx', $item->id) }}" target="_blank" title="Download DOCX"
                                           class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-file-word text-[13px]"></i>
                                        </a>
                                        <a href="{{ route('surat.download.pdf', $item->id) }}" target="_blank" title="Download PDF"
                                           class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                            <i class="fas fa-file-pdf text-[13px]"></i>
                                        </a>
                                    @endif
                                    @if($item->status == 'Menunggu')
                                        <button type="button" title="Setujui"
                                                data-aksi-modal="acc"
                                                data-nama="{{ $item->nama_peminjam }}"
                                                data-barang="{{ $namaBarang }}"
                                                data-action="{{ route('permohonan.status', $item->id) }}"
                                                class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                            <i class="fas fa-check text-[13px]"></i>
                                        </button>
                                        <button type="button" title="Tolak"
                                                data-aksi-modal="tolak"
                                                data-nama="{{ $item->nama_peminjam }}"
                                                data-barang="{{ $namaBarang }}"
                                                data-action="{{ route('permohonan.status', $item->id) }}"
                                                class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                            <i class="fas fa-times text-[13px]"></i>
                                        </button>
                                    @endif
                                    <button type="button" title="Hapus"
                                            onclick="AdminUI.confirmDelete(@js(route('permohonan.destroy', $item->id)), @js($item->nomor_permohonan))"
                                            class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                        <i class="fas fa-trash text-[13px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <i class="fas fa-inbox mb-2 block text-2xl text-slate-300"></i>
                                <p class="text-xs text-slate-500">Belum ada data permohonan dengan status ini.</p>
                                @if($q !== '')
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        Tidak ditemukan hasil untuk pencarian "<span class="font-semibold text-slate-600">{{ $q }}</span>".
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permohonan->hasPages())
            {{ $permohonan->links('vendor.pagination.admin') }}
        @endif
    </div>

    {{-- ==================== MODAL QR CODE ==================== --}}
    <div id="modalBarcode" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-sm overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-blue-600 p-1.5">
                        <i class="fas fa-qrcode text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">QR Code Permohonan</h3>
                        <p class="text-[10px] text-slate-400">Untuk halaman Pengembalian Barang</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-2 p-6 text-center">
                <div id="rowBarcode" class="mx-auto inline-flex h-40 w-40 items-center justify-center rounded-xl border border-slate-200 bg-white"></div>
                <p class="pt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Permohonan</p>
                <p id="rowBarcodeText" class="font-mono text-sm font-bold text-slate-900">—</p>
                <p class="text-[11px] leading-relaxed text-slate-500">
                    Scan QR ini di halaman <strong>Pengembalian Barang</strong> untuk memudahkan pendataan barang kembali.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4 text-xs">
                <button type="button" data-modal-close
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-100">
                    Tutup
                </button>
                <button type="button" onclick="cetakBarcodeModal()"
                        class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 font-medium text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                    <i class="fas fa-print text-[11px]"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Area khusus hasil cetak QR; tersembunyi di layar, dipakai oleh @media print. --}}
    <div id="printBarcodeArea" style="display:none;">
        <p style="font-size:13px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:8px;">QR Nomor Permohonan</p>
        <div id="printBarcode" style="width:180px;height:180px;display:inline-block;"></div>
        <p id="printBarcodeNomor" style="font-family:Consolas,Monaco,monospace;font-size:18px;font-weight:700;margin-top:10px;"></p>
    </div>

    {{-- ==================== MODAL ACC / TOLAK ==================== --}}
    @include('admin.permohonan._aksi-modals')
@endsection

@push('scripts')
<script src="{{ asset('vendor/qrcode/qrcode.min.js') }}?v={{ @filemtime(public_path('vendor/qrcode/qrcode.min.js')) ?: 1 }}"></script>
<script>
    const PERMOHONAN_EXPORT = @json($exportUrl);

    function renderQr(selector, nomor, size, dark) {
        const el = document.querySelector(selector);
        if (!el || typeof QRCode === 'undefined') return;

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

    function bukaBarcodeModal(nomor) {
        document.getElementById('rowBarcodeText').textContent = nomor;
        renderQr('#rowBarcode', nomor, 160, '#0f172a');
        AdminUI.openModal('modalBarcode');
    }

    /* 'body * { visibility: hidden }' pada @media print ikut menyembunyikan modalnya,
       jadi pencetakan memakai area clone di luar modal. */
    function cetakBarcodeModal() {
        const nomor = document.getElementById('rowBarcodeText').textContent;
        if (!nomor || nomor === '—') return;

        document.getElementById('printBarcodeNomor').textContent = nomor;
        renderQr('#printBarcode', nomor, 180, '#000000');

        const area = document.getElementById('printBarcodeArea');
        area.style.display = 'block';
        window.print();
        setTimeout(function () {
            area.style.display = 'none';
        }, 500);
    }

    function exportData() {
        window.location.assign(PERMOHONAN_EXPORT);
    }
</script>
@include('admin.permohonan._aksi-scripts')
@endpush
