@extends('layouts.admin-master')

@section('title', 'Upload Surat')
@section('page_key', 'surat')

@php
    $namaInstansi = fn ($p) => $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '—';
    $daftarInstansi = $permohonan->map($namaInstansi)->unique()->sort()->values();
    $totalSurat = $permohonan->count();

    // Kelas Tailwind harus tertulis literal di file ini agar ikut ter-compile JIT.
    $field = 'w-full rounded-xl border bg-white px-3 py-2 text-xs text-slate-700 placeholder-slate-400 transition-all focus:outline-none focus:ring-2';
    $fieldOk = $field . ' border-slate-200 focus:border-blue-500 focus:ring-blue-500/20';
    $fieldErr = $field . ' border-rose-300 focus:border-rose-500 focus:ring-rose-500/20';
    $label = 'mb-1.5 block text-xs font-semibold text-slate-700';
@endphp

@section('content')
    {{-- ==================== HEADER HALAMAN ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#0F172A] text-blue-400 shadow-lg shadow-slate-900/20">
                <i class="fas fa-file-upload text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Upload Surat</h1>
                <p class="mt-1 max-w-2xl text-xs leading-relaxed text-slate-500">
                    Kelola unggahan revisi surat peminjaman (.docx) dan manajemen versi template tahunan.
                </p>
            </div>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <button type="button" id="tombolPanduan" aria-expanded="{{ $errors->has('template_file') ? 'true' : 'false' }}" aria-controls="panduanPlaceholder"
                    class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 shadow-sm transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                Panduan Placeholder
                <i class="fas fa-chevron-down text-[10px] transition-transform" id="ikonPanduan"></i>
            </button>
            <button type="button" id="tombolKeDaftar"
                    class="flex items-center gap-2 rounded-xl bg-[#0F172A] px-3.5 py-2 text-xs font-semibold text-white shadow-md shadow-slate-900/20 transition-all hover:bg-slate-800">
                <i class="fas fa-list text-[11px]"></i> Daftar Surat
            </button>
        </div>
    </div>

    {{-- ==================== PANDUAN PLACEHOLDER ==================== --}}
    <div id="panduanPlaceholder" class="{{ $errors->has('template_file') ? '' : 'hidden' }} overflow-hidden rounded-2xl border border-blue-200 bg-white shadow-sm">
        <div class="flex items-start gap-3 p-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-info"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-slate-900">Variabel Placeholder Wajib pada Template DOCX</h3>
                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                    Template tahunan harus memuat seluruh variabel berikut. Unggahan template yang kehilangan
                    salah satunya ditolak supaya surat tetap terisi lengkap.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($placeholderWajib as $ph)
                        <button type="button" data-salin="${{ $ph }}"
                                class="rounded-lg bg-slate-100 px-2.5 py-1 font-mono text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200 transition-all hover:bg-blue-50 hover:text-blue-700 hover:ring-blue-200">
                            ${{ $ph }}
                        </button>
                    @endforeach
                </div>
                <p class="mt-2.5 text-[11px] text-slate-400">Klik variabel untuk menyalinnya ke clipboard.</p>
            </div>
            <button type="button" data-tutup-panduan class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup panduan">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    </div>

    {{-- ==================== DUA KARTU UTAMA ==================== --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- ---------- Unggah surat ---------- --}}
        <div id="kartuUnggah" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-file-word text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Upload Surat (DOCX)</h2>
                        <p class="mt-0.5 text-[11px] text-slate-500">Unggah ulang dokumen hasil penyuntingan Word</p>
                    </div>
                </div>
                <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700 ring-1 ring-blue-100">
                    Maks. 10 MB
                </span>
            </div>

            <div class="space-y-4 p-5">
                <div class="flex gap-3 rounded-xl border border-sky-100 bg-sky-50/70 p-3.5">
                    <i class="fas fa-route mt-0.5 text-xs text-sky-500"></i>
                    <p class="text-[11px] leading-relaxed text-slate-600">
                        Alur kerja: unduh DOCX surat dari tabel di bawah, edit di Microsoft Word, lalu unggah
                        kembali di sini. Isi surat akan diperbarui dan PDF dibuat ulang otomatis.
                    </p>
                </div>

                <form action="{{ route('surat.upload.store') }}" method="POST" enctype="multipart/form-data" data-form-unggah>
                    @csrf

                    <div>
                        <label for="permohonan_id" class="{{ $label }}">Surat yang diunggah <span class="text-rose-500">*</span></label>
                        <select name="permohonan_id" id="permohonan_id" class="{{ $errors->has('permohonan_id') ? $fieldErr : $fieldOk }}">
                            <option value="">-- pilih nomor surat --</option>
                            @foreach($permohonan as $p)
                                <option value="{{ $p->id }}" @selected((int) old('permohonan_id', $terpilih) === $p->id)>
                                    {{ $p->nomor_permohonan }} — {{ $p->nama_peminjam }} ({{ $namaInstansi($p) }})
                                </option>
                            @endforeach
                        </select>
                        @error('permohonan_id')
                            <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="{{ $label }}">Berkas DOCX <span class="text-rose-500">*</span></span>
                        <div class="doc-drop" data-dropzone>
                            <input type="file" name="file" id="file" accept=".docx" class="doc-drop-input" data-max-mb="10">
                            <div class="doc-drop-idle flex flex-col items-center gap-1.5" data-drop-idle>
                                <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <i class="fas fa-cloud-upload-alt text-lg"></i>
                                </span>
                                <span class="text-xs font-semibold text-slate-700">Klik untuk memilih berkas .docx atau seret ke area ini</span>
                                <span class="text-[11px] text-slate-400">Hanya dokumen Microsoft Word (.docx) &bull; Maksimal 10 MB</span>
                            </div>
                            <div class="doc-drop-picked flex hidden items-center gap-2.5" data-drop-picked>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-file-signature"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-xs font-bold text-slate-800" data-drop-nama></span>
                                    <span class="block text-[11px] text-slate-500" data-drop-meta></span>
                                </span>
                                <button type="button" data-drop-clear class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus berkas terpilih">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                        @error('file')
                            <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                            <i class="fas fa-upload text-[11px]"></i> Unggah Surat
                        </button>
                        <a href="{{ route('surat.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition-all hover:bg-slate-50">
                            Daftar Surat Lama
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ---------- Template per tahun ---------- --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-layer-group text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Template Surat per Tahun</h2>
                        <p class="mt-0.5 text-[11px] text-slate-500">Versi aktif hanya dipakai oleh surat yang dibuat setelahnya &bull; surat lama tetap memakai versinya sendiri</p>
                    </div>
                </div>
                <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-100">
                    Versi Riwayat Aman
                </span>
            </div>

            <div class="space-y-4 p-5">
                <p class="text-[11px] leading-relaxed text-slate-500">
                    Versi lama <strong class="font-semibold text-slate-700">tetap tersimpan</strong> dan bisa
                    diaktifkan kembali kapan saja. Surat yang sudah dibuat tidak ikut berubah karena template
                    dipin per surat.
                </p>

                <form action="{{ route('surat.templates.store') }}" method="POST" enctype="multipart/form-data" data-form-template>
                    @csrf

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                        <div class="sm:col-span-4">
                            <label for="tahun" class="{{ $label }}">Tahun <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun" id="tahun" min="2000" max="2100"
                                   value="{{ old('tahun', now()->year) }}" inputmode="numeric"
                                   class="{{ $errors->has('tahun') ? $fieldErr : $fieldOk }}">
                            @error('tahun')
                                <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-8">
                            <label for="catatan" class="{{ $label }}">Catatan (opsional)</label>
                            <input type="text" name="catatan" id="catatan" maxlength="255"
                                   value="{{ old('catatan') }}" placeholder="mis. revisi kop surat"
                                   class="{{ $errors->has('catatan') ? $fieldErr : $fieldOk }}">
                            @error('catatan')
                                <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <span class="{{ $label }}">Berkas Template DOCX <span class="text-rose-500">*</span></span>
                        <div class="doc-drop doc-drop--compact" data-dropzone>
                            <input type="file" name="template_file" id="file_template" accept=".docx" class="doc-drop-input" data-max-mb="10">
                            <div class="doc-drop-idle flex items-center gap-2.5" data-drop-idle>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-cloud-upload-alt text-sm"></i>
                                </span>
                                <span class="min-w-0 flex-1 text-left">
                                    <span class="block text-xs font-semibold text-slate-700">Seret template ke sini atau klik untuk memilih</span>
                                    <span class="block text-[11px] text-slate-400">Wajib memuat semua placeholder &bull; berkas versi lama tidak tertimpa</span>
                                </span>
                                <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">Browse</span>
                            </div>
                            <div class="doc-drop-picked flex hidden items-center gap-2.5" data-drop-picked>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-file-signature"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-xs font-bold text-slate-800" data-drop-nama></span>
                                    <span class="block text-[11px] text-slate-500" data-drop-meta></span>
                                </span>
                                <button type="button" data-drop-clear class="shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus berkas terpilih">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                        @error('template_file')
                            <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-emerald-600/20 transition-all hover:bg-emerald-700">
                            <i class="fas fa-save text-[11px]"></i> Simpan Template
                        </button>
                    </div>
                </form>

                <form action="{{ route('surat.templates.scan') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-sky-200 bg-white px-4 py-2.5 text-xs font-semibold text-sky-600 transition-all hover:bg-sky-50">
                        <i class="fas fa-folder-open text-[11px]"></i> Pindai Folder Template
                    </button>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-400">
                        Mendaftarkan file <code class="rounded bg-slate-100 px-1 font-mono text-[10px]">.docx</code>
                        yang sudah ada di folder template server. File asli tidak dihapus.
                    </p>
                </form>

                <div class="admin-table-wrap overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-[#0F172A] text-[11px] font-semibold uppercase tracking-wider text-slate-200">
                            <tr>
                                <th class="px-3 py-2.5 text-center">Tahun</th>
                                <th class="px-3 py-2.5 text-center">Versi</th>
                                <th class="px-3 py-2.5">Berkas</th>
                                <th class="px-3 py-2.5 text-center">Status</th>
                                <th class="px-3 py-2.5">Diunggah</th>
                                <th class="px-3 py-2.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($templates as $template)
                                @php
                                    $arsip = $template->trashed();
                                @endphp
                                <tr class="{{ $arsip ? 'bg-slate-50/70' : '' }} transition-colors hover:bg-slate-50/80">
                                    <td class="px-3 py-3 text-center font-bold text-slate-900">{{ $template->tahun }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="font-mono text-[11px] font-semibold text-slate-600">v{{ $template->versi }}</span>
                                    </td>
                                    <td class="max-w-[10rem] px-3 py-3">
                                        <div class="truncate font-medium text-slate-800" title="{{ $template->nama_file }}">{{ $template->nama_file }}</div>
                                        @if($template->catatan)
                                            <div class="truncate text-[10px] text-slate-400" title="{{ $template->catatan }}">{{ $template->catatan }}</div>
                                        @endif
                                        <div class="truncate text-[10px] text-slate-400">{{ $template->uploader?->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @if($arsip)
                                            <span class="whitespace-nowrap rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold uppercase text-slate-600">Arsip</span>
                                        @elseif($template->is_active)
                                            <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700 ring-1 ring-emerald-200">
                                                <i class="fas fa-check text-[8px]"></i> Aktif
                                            </span>
                                        @else
                                            <form action="{{ route('surat.templates.activate', $template) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Aktifkan versi ini"
                                                        class="whitespace-nowrap rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase text-blue-700 transition-all hover:bg-blue-100">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-[11px] text-slate-500">
                                        {{ $template->created_at?->translatedFormat('d M Y') ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-center">
                                        @if($arsip)
                                            <form action="{{ route('surat.templates.restore', $template->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="Pulihkan dari arsip"
                                                        class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                                    <i class="fas fa-undo text-[13px]"></i>
                                                </button>
                                            </form>
                                            <button type="button" title="Hapus permanen (baris database + berkasnya)"
                                                    onclick="AdminUI.confirmDelete(@js(route('surat.templates.permanent', $template->id)), @js('template ' . $template->tahun . ' versi ' . $template->versi . ' beserta berkasnya'))"
                                                    class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                                <i class="fas fa-trash text-[13px]"></i>
                                            </button>
                                        @else
                                            @if($template->is_active)
                                                <form action="{{ route('surat.templates.deactivate', $template) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Nonaktifkan, tapi tetap tersimpan"
                                                            class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                                        <i class="fas fa-power-off text-[13px]"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('surat.templates.destroy', $template) }}" method="POST" class="inline"
                                                  data-arsipkan="template tahun {{ $template->tahun }} versi {{ $template->versi }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Arsipkan (file tidak dihapus)"
                                                        class="rounded-lg border border-transparent p-1.5 text-slate-500 transition-all hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600">
                                                    <i class="fas fa-archive text-[13px]"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <i class="fas fa-inbox mb-2 block text-2xl text-slate-300"></i>
                                        <p class="text-xs text-slate-500">Belum ada template tahunan. Semua surat memakai template default.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TEMPLATE UNTUK SATU SURAT ==================== --}}
    @if($suratTerpilih)
        <div class="overflow-hidden rounded-2xl border border-indigo-200 bg-white shadow-sm">
            <div class="flex items-start gap-3 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="fas fa-file-signature text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-bold text-slate-900">Template untuk Surat Terpilih</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        <code class="rounded bg-rose-50 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-rose-600">{{ $suratTerpilih->nomor_permohonan }}</code>
                        — {{ $suratTerpilih->nama_peminjam }}
                        ({{ $suratTerpilih->created_at?->translatedFormat('d M Y') ?? '-' }})
                    </p>

                    <form action="{{ route('surat.template.assign', $suratTerpilih) }}" method="POST" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                        @csrf
                        <select name="surat_template_id" class="{{ $fieldOk }} sm:max-w-md">
                            <option value="">Otomatis (template aktif tahun surat)</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}" @selected((int) old('surat_template_id', $suratTerpilih->surat_template_id) === $t->id)>
                                    {{ $t->tahun }} v{{ $t->versi }} — {{ $t->nama_file }}
                                    {{ $t->trashed() ? '(arsip)' : ($t->is_active ? '(aktif)' : '') }}
                                </option>
                            @endforeach
                        </select>
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="submit" class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700">
                                <i class="fas fa-save text-[11px]"></i> Simpan Pilihan
                            </button>
                            <a href="{{ route('surat.upload') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition-all hover:bg-slate-50">
                                Batal
                            </a>
                        </div>
                    </form>
                    <p class="mt-2 text-[11px] text-slate-400">
                        Pilih template lain untuk surat ini saja. Surat lain tidak terpengaruh.
                        Surat baru memakai template yang aktif saat surat itu dibuat; surat lama tetap memakai template yang tercatat padanya.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== DAFTAR SURAT ==================== --}}
    <div id="daftarSurat" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0F172A] text-blue-400">
                    <i class="fas fa-envelope-open-text text-base"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Surat</h2>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                        Menampilkan <span id="jumlahTampil" class="font-semibold text-slate-700">{{ $totalSurat }}</span>
                        dari {{ $totalSurat }} surat permohonan peminjaman
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative w-full sm:w-56">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[11px] text-slate-400"></i>
                    <input type="search" id="suratCari" placeholder="Cari no. surat / peminjam..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs text-slate-700 placeholder-slate-400 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <select id="suratInstansi" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:w-auto">
                    <option value="">Semua Instansi</option>
                    @foreach($daftarInstansi as $instansi)
                        <option value="{{ $instansi }}">{{ $instansi }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-1 self-start rounded-xl bg-slate-200/70 p-1 sm:self-auto">
                    <button type="button" data-status="semua" class="filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-slate-500 transition-all">Semua</button>
                    <button type="button" data-status="belum" class="filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-slate-500 transition-all">Belum Unggah</button>
                    <button type="button" data-status="sudah" class="filter-status rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-slate-500 transition-all">Sudah Unggah</button>
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
                        <th class="px-3 py-3 text-center">Tahun</th>
                        <th class="px-3 py-3 text-center">Template</th>
                        <th class="px-3 py-3 text-center">DOCX</th>
                        <th class="px-3 py-3 text-center">PDF</th>
                        <th class="px-4 py-3">Terakhir Diunggah</th>
                        <th class="min-w-[140px] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permohonan as $p)
                        @php
                            $berkasAda = $p->word_path && file_exists($p->word_path);
                            $pdfAda = $p->pdf_path && file_exists($p->pdf_path);
                            $kataCari = mb_strtolower($p->nomor_permohonan . ' ' . $p->nama_peminjam . ' ' . $namaInstansi($p));
                        @endphp
                        <tr class="surat-row group transition-colors hover:bg-slate-50/80"
                            data-instansi="{{ $namaInstansi($p) }}"
                            data-unggah="{{ $berkasAda ? 1 : 0 }}"
                            data-cari="{{ $kataCari }}">
                            <td class="no-row px-3 py-3.5 text-center font-medium text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3.5">
                                <span class="whitespace-nowrap rounded-lg bg-rose-50 px-2 py-1 font-mono text-[11px] font-semibold text-rose-600">{{ $p->nomor_permohonan }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">{{ $p->nama_peminjam }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-[11px] text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <span class="truncate font-medium text-slate-700">{{ $namaInstansi($p) }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 text-center text-slate-500">{{ $p->created_at?->year ?? '-' }}</td>
                            <td class="px-3 py-3.5 text-center">
                                @if($p->suratTemplate)
                                    <span class="whitespace-nowrap rounded-full bg-cyan-50 px-2 py-0.5 text-[10px] font-bold text-cyan-700 ring-1 ring-cyan-200" title="{{ $p->suratTemplate->nama_file }}">
                                        {{ $p->suratTemplate->tahun }} v{{ $p->suratTemplate->versi }}
                                    </span>
                                    @if($p->suratTemplate->trashed())
                                        <span class="whitespace-nowrap rounded-full bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold uppercase text-slate-600">Arsip</span>
                                    @endif
                                @else
                                    <span class="whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200">Otomatis</span>
                                @endif
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                @if($berkasAda)
                                    <a href="{{ route('surat.berkas', $p) }}" title="Unduh DOCX yang diunggah"
                                       class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700 ring-1 ring-emerald-200 transition-all hover:bg-emerald-100">
                                        <i class="fas fa-check text-[8px]"></i> Tersedia
                                    </a>
                                @elseif($p->word_path)
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
                                @if($pdfAda)
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700 ring-1 ring-emerald-200">
                                        <i class="fas fa-check text-[8px]"></i> Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-slate-500 ring-1 ring-slate-200">
                                        <i class="far fa-clock text-[8px]"></i> Belum
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-[11px] text-slate-500" title="{{ $p->last_sync_at?->translatedFormat('d M Y H:i') }}">
                                {{ $p->last_sync_at ? $p->last_sync_at->diffForHumans() : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap items-center justify-center gap-1">
                                    <button type="button" title="Pratinjau surat"
                                            data-pratinjau
                                            data-url="{{ route('surat.preview', $p) }}"
                                            data-nomor="{{ $p->nomor_permohonan }}"
                                            data-template="{{ $p->suratTemplate ? $p->suratTemplate->tahun . ' v' . $p->suratTemplate->versi : 'otomatis' }}"
                                            data-pdf="{{ $pdfAda ? 1 : 0 }}"
                                            data-docx="{{ route('surat.download.docx', $p) }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500 text-white shadow-sm shadow-sky-500/30 transition-all hover:bg-sky-600">
                                        <i class="fas fa-eye text-[12px]"></i>
                                    </button>
                                    <a href="{{ route('surat.download.docx', $p) }}" title="Unduh DOCX untuk diedit"
                                       class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm shadow-blue-600/30 transition-all hover:bg-blue-700">
                                        <i class="fas fa-file-download text-[12px]"></i>
                                    </a>
                                    <button type="button" title="Unggah surat ini"
                                            data-unggah-surat="{{ $p->id }}"
                                            data-nomor="{{ $p->nomor_permohonan }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm shadow-emerald-600/30 transition-all hover:bg-emerald-700">
                                        <i class="fas fa-upload text-[12px]"></i>
                                    </button>
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

    {{-- ==================== MODAL PRATINJAU ==================== --}}
    <div id="previewModal" class="admin-modal hidden fixed inset-0 z-[80] items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card flex h-[88vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex shrink-0 items-start justify-between gap-3 bg-[#0F172A] px-5 py-4 text-white">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600">
                        <i class="fas fa-file-alt text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Pratinjau Dokumen Surat Peminjaman</h3>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            <span class="font-mono" id="previewNomor"></span> &bull; Template <span id="previewTemplate"></span>
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    <a href="#" id="previewTabBaru" target="_blank" title="Buka di tab baru"
                       class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                        <i class="fas fa-external-link-alt text-xs"></i>
                    </a>
                    <button type="button" id="previewTutup" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white" aria-label="Tutup pratinjau">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 bg-slate-100">
                <iframe id="previewFrame" title="Pratinjau surat" class="h-full w-full border-0 bg-white"></iframe>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-white px-5 py-3">
                <p class="text-[11px] text-slate-500">
                    Status PDF: <span id="previewPdf" class="font-semibold text-emerald-600">Tersedia</span>
                </p>
                <div class="flex items-center gap-2">
                    <a href="#" id="previewUnduh" class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition-all hover:bg-slate-50">
                        <i class="fas fa-file-word text-[11px] text-blue-600"></i> Unduh DOCX
                    </a>
                    <button type="button" id="previewCetak" class="flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition-all hover:bg-slate-800">
                        <i class="fas fa-print text-[11px]"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
    (function () {
        'use strict';

        /* ---------------- Dropzone .docx ---------------- */
        function formatUkuran(byte) {
            return byte < 1024 * 1024
                ? Math.round(byte / 1024) + ' KB'
                : (byte / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function pasangDropzone(root) {
            var input = root.querySelector('.doc-drop-input');
            var idle = root.querySelector('[data-drop-idle]');
            var terpilih = root.querySelector('[data-drop-picked]');
            var elNama = root.querySelector('[data-drop-nama]');
            var elMeta = root.querySelector('[data-drop-meta]');
            var btnBersih = root.querySelector('[data-drop-clear]');
            var maxMb = parseInt(input.dataset.maxMb || '10', 10);

            function tampilkanIdle() {
                terpilih.classList.add('hidden');
                idle.classList.remove('hidden');
                input.value = '';
            }

            function terima(file) {
                if (!file) return;
                if (!/\.docx$/i.test(file.name)) {
                    AdminUI.toast('Format berkas tidak valid. Harap pilih file berformat .docx.', 'error');
                    tampilkanIdle();
                    return;
                }
                if (file.size > maxMb * 1024 * 1024) {
                    AdminUI.toast('Ukuran berkas melebihi batas maksimal ' + maxMb + ' MB.', 'error');
                    tampilkanIdle();
                    return;
                }
                elNama.textContent = file.name;
                elMeta.textContent = formatUkuran(file.size) + ' • Siap diunggah';
                idle.classList.add('hidden');
                terpilih.classList.remove('hidden');
            }

            root.tabIndex = 0;
            root.addEventListener('click', function (event) {
                if (event.target.closest('[data-drop-clear]')) return;
                input.click();
            });
            root.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    input.click();
                }
            });
            input.addEventListener('change', function () {
                terima(input.files[0]);
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                root.addEventListener(evt, function (event) {
                    event.preventDefault();
                    root.classList.add('is-dragover');
                });
            });
            root.addEventListener('dragleave', function () {
                root.classList.remove('is-dragover');
            });
            root.addEventListener('drop', function (event) {
                event.preventDefault();
                root.classList.remove('is-dragover');
                if (event.dataTransfer && event.dataTransfer.files.length) {
                    input.files = event.dataTransfer.files;
                    terima(input.files[0]);
                }
            });
            btnBersih.addEventListener('click', function (event) {
                event.stopPropagation();
                tampilkanIdle();
            });

            return {
                adaBerkas: function () { return !!(input.files && input.files.length); },
            };
        }

        var formUnggah = document.querySelector('[data-form-unggah]');
        var formTemplate = document.querySelector('[data-form-template]');
        var dropSurat = pasangDropzone(formUnggah.querySelector('[data-dropzone]'));
        var dropTemplate = pasangDropzone(formTemplate.querySelector('[data-dropzone]'));

        /* ---------------- Validasi sebelum kirim ---------------- */
        function tahanJika(form, cek) {
            form.addEventListener('submit', function (event) {
                var pesan = cek();
                if (pesan) {
                    event.preventDefault();
                    AdminUI.toast(pesan, 'error');
                }
            });
        }

        tahanJika(formUnggah, function () {
            if (!document.getElementById('permohonan_id').value) {
                return 'Silakan pilih nomor surat terlebih dahulu.';
            }
            return dropSurat.adaBerkas() ? null : 'Silakan pilih berkas .docx yang akan diunggah.';
        });

        tahanJika(formTemplate, function () {
            return dropTemplate.adaBerkas() ? null : 'Pilih berkas template .docx terlebih dahulu.';
        });

        /* ---------------- Panduan placeholder ---------------- */
        var panduan = document.getElementById('panduanPlaceholder');
        var tombolPanduan = document.getElementById('tombolPanduan');
        var ikonPanduan = document.getElementById('ikonPanduan');

        function aturPanduan(buka) {
            panduan.classList.toggle('hidden', !buka);
            tombolPanduan.setAttribute('aria-expanded', buka ? 'true' : 'false');
            ikonPanduan.style.transform = buka ? 'rotate(180deg)' : '';
        }

        tombolPanduan.addEventListener('click', function () {
            aturPanduan(panduan.classList.contains('hidden'));
        });
        document.querySelector('[data-tutup-panduan]').addEventListener('click', function () {
            aturPanduan(false);
        });

        function salin(teks) {
            var berhasil = function () { AdminUI.toast(teks + ' disalin ke clipboard.', 'info'); };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(teks).then(berhasil, function () {
                    AdminUI.toast('Gagal menyalin ' + teks, 'error');
                });
                return;
            }
            var helper = document.createElement('textarea');
            helper.value = teks;
            helper.style.position = 'fixed';
            helper.style.opacity = '0';
            document.body.appendChild(helper);
            helper.select();
            try {
                document.execCommand('copy');
                berhasil();
            } catch (e) {
                AdminUI.toast('Gagal menyalin ' + teks, 'error');
            }
            document.body.removeChild(helper);
        }

        document.querySelectorAll('[data-salin]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                salin(chip.dataset.salin);
            });
        });

        /* ---------------- Gulir antar bagian ---------------- */
        var kartuUnggah = document.getElementById('kartuUnggah');
        var selectSurat = document.getElementById('permohonan_id');

        document.getElementById('tombolKeDaftar').addEventListener('click', function () {
            document.getElementById('daftarSurat').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        /* ---------------- Unggah cepat dari baris tabel ---------------- */
        document.querySelectorAll('[data-unggah-surat]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                selectSurat.value = tombol.dataset.unggahSurat;
                kartuUnggah.scrollIntoView({ behavior: 'smooth', block: 'center' });
                kartuUnggah.classList.add('is-flash');
                setTimeout(function () { kartuUnggah.classList.remove('is-flash'); }, 1800);
                AdminUI.toast('Nomor surat ' + tombol.dataset.nomor + ' dipilih. Silakan pilih berkas .docx.', 'info');
            });
        });

        /* ---------------- Arsipkan template ---------------- */
        document.querySelectorAll('[data-arsipkan]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm('Arsipkan ' + form.dataset.arsipkan + ' File tetap tersimpan dan bisa dipulihkan.')) {
                    event.preventDefault();
                }
            });
        });

        /* ---------------- Pratinjau ---------------- */
        var frame = document.getElementById('previewFrame');
        var modalPratinjau = document.getElementById('previewModal');

        document.querySelectorAll('[data-pratinjau]').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                document.getElementById('previewNomor').textContent = tombol.dataset.nomor;
                document.getElementById('previewTemplate').textContent = tombol.dataset.template;
                document.getElementById('previewTabBaru').href = tombol.dataset.url;
                document.getElementById('previewUnduh').href = tombol.dataset.docx;

                var status = document.getElementById('previewPdf');
                var adaPdf = tombol.dataset.pdf === '1';
                status.textContent = adaPdf ? 'Tersedia' : 'Belum dibuat';
                status.className = 'font-semibold ' + (adaPdf ? 'text-emerald-600' : 'text-amber-600');

                frame.src = tombol.dataset.url;
                AdminUI.openModal('previewModal');
            });
        });

        // iframe berhenti memuat setelah modal ditutup.
        function kosongkanPratinjau() {
            frame.src = 'about:blank';
        }

        document.getElementById('previewTutup').addEventListener('click', kosongkanPratinjau);
        modalPratinjau.addEventListener('click', function (event) {
            if (event.target === modalPratinjau) kosongkanPratinjau();
        });

        document.getElementById('previewCetak').addEventListener('click', function () {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                AdminUI.toast('Pratinjau gagal dicetak. Buka lewat tab baru lalu cetak dari sana.', 'error');
            }
        });

        /* ---------------- Filter daftar surat ---------------- */
        var baris = Array.prototype.slice.call(document.querySelectorAll('.surat-row'));
        var inputCari = document.getElementById('suratCari');
        var selectInstansi = document.getElementById('suratInstansi');
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
            var instansi = selectInstansi.value;
            var tampil = 0;

            baris.forEach(function (tr) {
                var cocok = tr.dataset.cari.indexOf(kata) !== -1
                    && (instansi === '' || tr.dataset.instansi === instansi)
                    && (statusAktif === 'semua'
                        || (statusAktif === 'sudah' ? tr.dataset.unggah === '1' : tr.dataset.unggah !== '1'));

                tr.classList.toggle('hidden', !cocok);
                if (cocok) {
                    tampil++;
                    tr.querySelector('.no-row').textContent = tampil;
                }
            });

            jumlahTampil.textContent = tampil;
            daftarKosong.classList.toggle('hidden', tampil !== 0);
        }

        tombolStatus.forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                statusAktif = tombol.dataset.status;
                warnaiStatus();
                terapkanFilter();
            });
        });
        inputCari.addEventListener('input', terapkanFilter);
        selectInstansi.addEventListener('change', terapkanFilter);
        warnaiStatus();
    })();
</script>
@endpush
