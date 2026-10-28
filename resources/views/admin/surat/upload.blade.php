@extends('adminlte::page')

@section('title', 'Upload Surat')

@section('content_header')
    <h1><i class="fas fa-file-upload"></i> Upload Surat</h1>
    <div class="float-right">
        <a href="{{ route('surat.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list"></i> Daftar Surat
        </a>
    </div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle mr-1"></i> Periksa kembali berkas yang diunggah:
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $pesan)
            <li>{{ $pesan }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<div class="row">
    {{-- Upload surat per permohonan --}}
    <div class="col-lg-7">
        <div class="card card-flat" id="form-upload">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-word"></i> Upload Surat (DOCX)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Alur: unduh DOCX surat dari tabel di bawah, edit di Microsoft Word,
                    lalu unggah kembali di sini. Isi surat akan diperbarui dan PDF dibuat ulang.
                </p>

                <form action="{{ route('surat.upload.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="permohonan_id">Surat yang diunggah</label>
                        <select name="permohonan_id" id="permohonan_id" class="form-control @error('permohonan_id') is-invalid @enderror" required>
                            <option value="">-- pilih nomor surat --</option>
                            @foreach($permohonan as $p)
                                <option value="{{ $p->id }}" @selected((int) old('permohonan_id', $terpilih) === $p->id)>
                                    {{ $p->nomor_permohonan }} — {{ $p->nama_peminjam }}
                                    ({{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('permohonan_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="file">Berkas DOCX</label>
                        <div class="custom-file">
                            <input type="file" name="file" id="file"
                                   class="custom-file-input @error('file') is-invalid @enderror"
                                   accept=".docx" required>
                            <label class="custom-file-label" for="file">Pilih berkas .docx</label>
                        </div>
                        <small class="text-muted">Maksimal 10 MB.</small>
                        @error('file')
                            <div><small class="text-danger">{{ $message }}</small></div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Unggah Surat
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Template per tahun --}}
    <div class="col-lg-5">
        <div class="card card-flat">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-layer-group"></i> Template Surat per Tahun</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Surat memakai template sesuai tahun surat dibuat. Contoh: surat tahun 2026
                    memakai <code>template_peminjaman_2026.docx</code>. Kalau tahun itu belum
                    punya template, surat memakai template default.
                </p>

                <form action="{{ route('surat.templates.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="tahun">Tahun</label>
                        <input type="number" name="tahun" id="tahun" min="2000" max="2100"
                               value="{{ old('tahun', now()->year) }}"
                               class="form-control @error('tahun') is-invalid @enderror" required>
                        @error('tahun')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="file_template">Berkas template DOCX</label>
                        <div class="custom-file">
                            <input type="file" name="template_file" id="file_template"
                                   class="custom-file-input @error('template_file') is-invalid @enderror"
                                   accept=".docx" required>
                            <label class="custom-file-label" for="file_template">Pilih berkas .docx</label>
                        </div>
                        <small class="text-muted">
                            Wajib memuat placeholder surat (nomor, identitas, item barang, QR,
                            dan tanda tangan).
                        </small>
                        @error('template_file')
                            <div><small class="text-danger">{{ $message }}</small></div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Template
                    </button>
                </form>

                <hr>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>Tahun</th>
                                <th>Berkas</th>
                                <th>Diunggah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td class="text-center"><strong>{{ $template->tahun }}</strong></td>
                                    <td>
                                        <span title="{{ $template->nama_file }}">{{ $template->nama_file }}</span>
                                        <br>
                                        <small class="text-muted">{{ $template->uploader?->name ?? '-' }}</small>
                                    </td>
                                    <td class="text-nowrap">{{ $template->updated_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('surat.templates.destroy', $template) }}"
                                              method="POST" style="display:inline;"
                                              onsubmit="return confirm('Hapus template tahun {{ $template->tahun }}? Surat tahun itu akan memakai template default.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus template">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Belum ada template tahunan. Semua surat memakai template default.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Daftar surat --}}
<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-envelope-open-text"></i> Daftar Surat</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Surat</th>
                    <th>Peminjam</th>
                    <th>Instansi</th>
                    <th>Tahun</th>
                    <th>DOCX</th>
                    <th>PDF</th>
                    <th>Terakhir Diunggah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permohonan as $p)
                    @php($berkasAda = $p->word_path && file_exists($p->word_path))
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td><code>{{ $p->nomor_permohonan }}</code></td>
                        <td>{{ $p->nama_peminjam }}</td>
                        <td>{{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '-' }}</td>
                        <td class="text-center">{{ $p->created_at?->year ?? '-' }}</td>
                        <td class="text-center">
                            @if($berkasAda)
                                <a href="{{ route('surat.berkas', $p) }}" class="badge badge-success" title="Unduh DOCX yang diunggah">
                                    <i class="fas fa-check"></i> Ada
                                </a>
                            @elseif($p->word_path)
                                <span class="badge badge-warning" title="Berkas tidak ditemukan">
                                    <i class="fas fa-exclamation-triangle"></i> Hilang
                                </span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-minus"></i> Belum</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->pdf_path && file_exists($p->pdf_path))
                                <span class="badge badge-success"><i class="fas fa-check"></i> Ada</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-minus"></i> Belum</span>
                            @endif
                        </td>
                        <td>
                            {{ $p->last_sync_at ? $p->last_sync_at->diffForHumans() : '-' }}
                        </td>
                        <td class="text-nowrap text-center">
                            <a href="{{ route('surat.preview', $p) }}" class="btn btn-info btn-sm" title="Preview surat" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('surat.download.docx', $p) }}" class="btn btn-primary btn-sm" title="Unduh DOCX untuk diedit">
                                <i class="fas fa-file-word"></i>
                            </a>
                            <a href="{{ route('surat.upload', ['permohonan' => $p->id]) }}#form-upload" class="btn btn-success btn-sm" title="Unggah surat ini">
                                <i class="fas fa-upload"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data permohonan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@stop

@section('js')
<script>
    // Tampilkan nama berkas pada input custom-file.
    document.querySelectorAll('.custom-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var label = input.nextElementSibling;
            if (label && input.files.length > 0) {
                label.textContent = input.files[0].name;
            }
        });
    });
</script>
@stop
