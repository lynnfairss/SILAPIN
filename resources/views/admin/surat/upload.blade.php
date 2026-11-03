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
                    Setiap tahun boleh punya beberapa versi template. Unggahan baru otomatis
                    aktif, sedangkan versi lama <strong>tetap tersimpan</strong> dan bisa
                    diaktifkan kembali. Surat yang sudah dibuat tetap memakai template yang
                    dipakai saat surat itu dibuat.
                </p>

                <form action="{{ route('surat.templates.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-row">
                        <div class="form-group col-5">
                            <label for="tahun">Tahun</label>
                            <input type="number" name="tahun" id="tahun" min="2000" max="2100"
                                   value="{{ old('tahun', now()->year) }}"
                                   class="form-control @error('tahun') is-invalid @enderror" required>
                            @error('tahun')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group col-7">
                            <label for="catatan">Catatan (opsional)</label>
                            <input type="text" name="catatan" id="catatan" maxlength="255"
                                   value="{{ old('catatan') }}"
                                   class="form-control @error('catatan') is-invalid @enderror"
                                   placeholder="mis. revisi kop surat">
                            @error('catatan')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
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
                            dan tanda tangan). Berkas disimpan dengan nama acak agar versi lama
                            tidak tertimpa.
                        </small>
                        @error('template_file')
                            <div><small class="text-danger">{{ $message }}</small></div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Template
                    </button>
                </form>

                <form action="{{ route('surat.templates.scan') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-search"></i> Pindai Folder Template
                    </button>
                    <small class="text-muted d-block mt-1">
                        Mendaftarkan file <code>.docx</code> yang sudah ada di folder template.
                        File asli tidak dihapus.
                    </small>
                </form>

                <hr>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>Tahun</th>
                                <th>Versi</th>
                                <th>Berkas</th>
                                <th>Status</th>
                                <th>Diunggah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                @php($arsip = $template->trashed())
                                <tr class="{{ $arsip ? 'text-muted' : '' }}">
                                    <td class="text-center"><strong>{{ $template->tahun }}</strong></td>
                                    <td class="text-center">v{{ $template->versi }}</td>
                                    <td>
                                        <span title="{{ $template->nama_file }}">{{ \Illuminate\Support\Str::limit($template->nama_file, 24) }}</span>
                                        @if($template->catatan)
                                            <br><small class="text-muted">{{ $template->catatan }}</small>
                                        @endif
                                        <br><small class="text-muted">{{ $template->uploader?->name ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($arsip)
                                            <span class="badge badge-dark">Arsip</span>
                                        @elseif($template->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Tidak aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $template->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="text-center text-nowrap">
                                        @if($arsip)
                                            <form action="{{ route('surat.templates.restore', $template->id) }}"
                                                  method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm" title="Pulihkan dari arsip">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if(! $template->is_active)
                                                <form action="{{ route('surat.templates.activate', $template) }}"
                                                      method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Aktifkan versi ini">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('surat.templates.destroy', $template) }}"
                                                  method="POST" style="display:inline;"
                                                  onsubmit="return confirm('Arsipkan template tahun {{ $template->tahun }} versi {{ $template->versi }}? File tetap tersimpan dan bisa dipulihkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-warning btn-sm" title="Arsipkan (file tidak dihapus)">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada template tahunan. Semua surat memakai template default.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($suratTerpilih)
                    <hr>
                    <h5 class="mt-3"><i class="fas fa-file-signature"></i> Template untuk Surat Terpilih</h5>
                    <p class="text-muted small mb-2">
                        <code>{{ $suratTerpilih->nomor_permohonan }}</code> —
                        {{ $suratTerpilih->nama_peminjam }}
                        ({{ $suratTerpilih->created_at?->format('d M Y') }})
                    </p>
                    <form action="{{ route('surat.template.assign', $suratTerpilih) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <select name="surat_template_id" class="form-control">
                                <option value="">Otomatis (template aktif tahun surat)</option>
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}"
                                        @selected((int) old('surat_template_id', $suratTerpilih->surat_template_id) === $t->id)>
                                        {{ $t->tahun }} v{{ $t->versi }} — {{ $t->nama_file }}
                                        {{ $t->trashed() ? '(arsip)' : ($t->is_active ? '(aktif)' : '') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Pilih template lama untuk surat ini saja. Surat lain tidak terpengaruh.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Simpan Pilihan
                        </button>
                        <a href="{{ route('surat.upload') }}" class="btn btn-outline-secondary btn-sm">
                            Batal
                        </a>
                    </form>
                @endif
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
                    <th>Template</th>
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
                            @if($p->suratTemplate)
                                <span class="badge badge-info" title="{{ $p->suratTemplate->nama_file }}">
                                    {{ $p->suratTemplate->tahun }} v{{ $p->suratTemplate->versi }}
                                </span>
                                @if($p->suratTemplate->trashed())
                                    <span class="badge badge-dark">Arsip</span>
                                @endif
                            @else
                                <span class="badge badge-secondary">Otomatis</span>
                            @endif
                        </td>
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
                        <td colspan="10" class="text-center text-muted">Belum ada surat yang sudah disetujui.</td>
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
