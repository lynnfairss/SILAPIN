@extends('layouts.admin-master')

@section('title', 'Surat Permohonan')
@section('page_key', 'surat')

@php
    $namaInstansi = fn ($p) => $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '—';

    $totalSurat = $permohonan->count();
    $sudahDocx = $permohonan->filter(fn ($p) => $p->word_path && file_exists($p->word_path))->count();
    $sudahPdf = $permohonan->filter(fn ($p) => $p->pdf_path && file_exists($p->pdf_path))->count();

    $statusPill = [
        'Menunggu'     => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Disetujui'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Ditolak'      => 'bg-rose-50 text-rose-700 ring-rose-200',
        'Dipinjam'     => 'bg-blue-50 text-blue-700 ring-blue-200',
        'Dikembalikan' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];

    $kartu = 'rounded-2xl border border-slate-200/80 bg-white shadow-sm';
@endphp

@section('content')

    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('dashboard') }}" class="transition hover:text-slate-600">Dashboard</a>
                <span>/</span>
                <span class="font-medium text-slate-600">Surat Permohonan</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Surat Permohonan</h1>
            <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-500">
                Daftar surat yang sudah disetujui beserta status berkas DOCX dan PDF-nya.
                Unggah revisi dari halaman Upload Surat.
            </p>
        </div>

        <a href="{{ route('surat.upload') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 md:self-auto">
            <i class="fas fa-file-upload text-[11px]"></i> Upload Surat
        </a>
    </div>

    {{-- ==================== KARTU METRIK ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Total Surat</p>
                <div class="mt-1 flex items-center gap-2.5">
                    <h3 class="text-2xl font-bold text-slate-900">{{ $totalSurat }}</h3>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                        Disetujui
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Surat yang bisa diunduh &amp; diunggah ulang</p>
            </div>
            <div class="shrink-0 rounded-xl border border-slate-200 bg-slate-50 p-3 text-slate-500">
                <i class="fas fa-envelope-open-text text-lg"></i>
            </div>
        </div>

        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">DOCX Siap</p>
                <div class="mt-1 flex items-center gap-2.5">
                    <h3 class="text-2xl font-bold text-slate-900">{{ $sudahDocx }}</h3>
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                        Terunggah
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Revisi Word yang tersimpan di server</p>
            </div>
            <div class="shrink-0 rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-emerald-600">
                <i class="fas fa-file-word text-lg"></i>
            </div>
        </div>

        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">PDF Siap</p>
                <div class="mt-1 flex items-center gap-2.5">
                    <h3 class="text-2xl font-bold text-slate-900">{{ $sudahPdf }}</h3>
                    <span class="rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                        Dibuat otomatis
                    </span>
                </div>
                <p class="mt-1 text-[11px] text-slate-400">Hasil cetak dari isi surat terbaru</p>
            </div>
            <div class="shrink-0 rounded-xl border border-rose-100 bg-rose-50 p-3 text-rose-600">
                <i class="fas fa-file-pdf text-lg"></i>
            </div>
        </div>
    </div>

    {{-- ==================== DAFTAR SURAT ==================== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/50 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-blue-400">
                    <i class="fas fa-file-alt text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Surat</h2>
                    <p class="text-xs text-slate-500">
                        Menampilkan <span id="jumlahTampil" class="font-bold text-slate-700">{{ $totalSurat }}</span>
                        dari {{ $totalSurat }} surat permohonan peminjaman
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-slate-400"></i>
                    <input type="search" id="suratCari" placeholder="Cari no. surat / peminjam..."
                           class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-8 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:w-64">
                </div>

                <div class="flex items-center gap-1 self-start rounded-xl bg-slate-200/70 p-1 sm:self-auto">
                    <button type="button" data-status="semua" class="filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-slate-500 transition-all">Semua</button>
                    <button type="button" data-status="belum" class="filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-slate-500 transition-all">Belum DOCX</button>
                    <button type="button" data-status="sudah" class="filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-slate-500 transition-all">Sudah DOCX</button>
                </div>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="w-12 px-3 py-3 text-center">No</th>
                        <th class="px-4 py-3">Nomor Surat</th>
                        <th class="px-4 py-3">Peminjam</th>
                        <th class="px-4 py-3">Instansi</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-3 py-3 text-center">Template</th>
                        <th class="px-3 py-3 text-center">Status</th>
                        <th class="px-3 py-3 text-center">DOCX</th>
                        <th class="px-3 py-3 text-center">PDF</th>
                        <th class="min-w-[140px] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permohonan as $item)
                        @php
                            $adaDocx = $item->word_path && file_exists($item->word_path);
                            $adaPdf = $item->pdf_path && file_exists($item->pdf_path);
                            $kataCari = mb_strtolower($item->nomor_permohonan . ' ' . $item->nama_peminjam . ' ' . $namaInstansi($item));
                        @endphp
                        <tr class="surat-row group transition-colors hover:bg-slate-50/80"
                            data-unggah="{{ $adaDocx ? 1 : 0 }}"
                            data-cari="{{ $kataCari }}">
                            <td class="no-row px-3 py-3.5 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5">
                                <span class="whitespace-nowrap rounded-lg bg-rose-50 px-2 py-1 font-mono text-[11px] font-semibold text-rose-600">{{ $item->nomor_permohonan }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">{{ $item->nama_peminjam }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-[11px] text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <span class="truncate font-medium text-slate-700">{{ $namaInstansi($item) }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-500">{{ $item->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-3 py-3.5 text-center">
                                @if($item->suratTemplate)
                                    <span class="whitespace-nowrap rounded-full bg-cyan-50 px-2 py-0.5 text-[10px] font-bold text-cyan-700 ring-1 ring-cyan-200" title="{{ $item->suratTemplate->nama_file }}">
                                        {{ $item->suratTemplate->tahun }} v{{ $item->suratTemplate->versi }}
                                    </span>
                                    @if($item->suratTemplate->trashed())
                                        <span class="whitespace-nowrap rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-600">Arsip</span>
                                    @endif
                                @else
                                    <span class="whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200">Otomatis</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="whitespace-nowrap rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $statusPill[$item->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                @if($adaDocx)
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700 ring-1 ring-emerald-200">
                                        <i class="fas fa-check text-[8px]"></i> Ada
                                    </span>
                                @elseif($item->word_path)
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700 ring-1 ring-amber-200" title="Berkas tidak ditemukan di server">
                                        <i class="fas fa-exclamation-triangle text-[8px]"></i> Hilang
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500 ring-1 ring-slate-200">
                                        <i class="far fa-clock text-[8px]"></i> Belum
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                @if($adaPdf)
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700 ring-1 ring-emerald-200">
                                        <i class="fas fa-check text-[8px]"></i> Ada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500 ring-1 ring-slate-200">
                                        <i class="far fa-clock text-[8px]"></i> Belum
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap items-center justify-center gap-1">
                                    <a href="{{ route('surat.preview', $item->id) }}" target="_blank" rel="noopener" title="Pratinjau surat"
                                       class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500 text-white shadow-sm shadow-sky-500/30 transition-all hover:bg-sky-600">
                                        <i class="fas fa-eye text-[12px]"></i>
                                    </a>
                                    <a href="{{ route('surat.download.docx', $item->id) }}" title="Unduh DOCX untuk diedit"
                                       class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm shadow-blue-600/30 transition-all hover:bg-blue-700">
                                        <i class="fas fa-file-download text-[12px]"></i>
                                    </a>
                                    <a href="{{ route('surat.upload', ['permohonan' => $item->id]) }}#form-upload" title="Unggah surat (DOCX)"
                                       class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm shadow-emerald-600/30 transition-all hover:bg-emerald-700">
                                        <i class="fas fa-upload text-[12px]"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <i class="fas fa-inbox mb-2 block text-2xl text-slate-300"></i>
                                <p class="text-xs text-slate-500">Belum ada surat yang sudah disetujui.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="daftarKosong" class="hidden border-t border-slate-100 px-4 py-10 text-center">
            <i class="fas fa-filter mb-2 block text-2xl text-slate-300"></i>
            <p class="text-xs text-slate-500">Tidak ada surat yang cocok dengan filter ini.</p>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        'use strict';

        var baris = Array.prototype.slice.call(document.querySelectorAll('.surat-row'));
        var inputCari = document.getElementById('suratCari');
        var tombolStatus = Array.prototype.slice.call(document.querySelectorAll('.filter-status'));
        var jumlahTampil = document.getElementById('jumlahTampil');
        var daftarKosong = document.getElementById('daftarKosong');
        var statusAktif = 'semua';

        function warnaiStatus() {
            tombolStatus.forEach(function (tombol) {
                var aktif = tombol.dataset.status === statusAktif;
                tombol.className = 'filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold transition-all '
                    + (aktif
                        ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200'
                        : 'text-slate-500 hover:bg-white/70 hover:text-slate-800');
            });
        }

        function terapkanFilter() {
            var kata = inputCari.value.trim().toLowerCase();
            var tampil = 0;

            baris.forEach(function (tr) {
                var cocok = tr.dataset.cari.indexOf(kata) !== -1
                    && (statusAktif === 'semua'
                        || (statusAktif === 'sudah' ? tr.dataset.unggah === '1' : tr.dataset.unggah !== '1'));

                tr.classList.toggle('hidden', !cocok);
                if (cocok) {
                    tampil++;
                    tr.querySelector('.no-row').textContent = tampil;
                }
            });

            jumlahTampil.textContent = tampil;
            daftarKosong.classList.toggle('hidden', tampil !== 0 || baris.length === 0);
        }

        tombolStatus.forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                statusAktif = tombol.dataset.status;
                warnaiStatus();
                terapkanFilter();
            });
        });

        inputCari.addEventListener('input', terapkanFilter);
        warnaiStatus();
    })();
</script>
@endpush
