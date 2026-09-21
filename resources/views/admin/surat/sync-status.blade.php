@extends('adminlte::page')

@section('title', 'Status Sync Word')

@section('content_header')
    <h1><i class="fas fa-sync-alt"></i> Status Sync Word (OneDrive)</h1>
@stop

@section('content')
    {{-- Folder Status --}}
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon {{ $folderExists ? 'bg-success' : 'bg-danger' }}">
                    <i class="fas {{ $folderExists ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Folder OneDrive</span>
                    <span class="info-box-number">{{ $folderExists ? 'Terhubung' : 'Tidak Ditemukan' }}</span>
                    <small class="text-muted">{{ $folderPath }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info">
                    <i class="fas fa-file-word"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">File DOCX</span>
                    <span class="info-box-number">{{ $fileCount }} file</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Last Sync</span>
                    <span class="info-box-number">{{ $lastResult['timestamp'] ?? 'Belum pernah' }}</span>
                    @if($lastResult)
                        <small class="text-muted">
                            {{ $lastResult['synced'] }} synced, {{ $lastResult['skipped'] }} skipped, {{ $lastResult['failed'] }} failed
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="card card-flat">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cogs"></i> Aksi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('surat.sync-now') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync"></i> Sync Sekarang
                </button>
            </form>
            <a href="{{ route('surat.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Sync History --}}
    <div class="card card-flat">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Sync</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Nomor Surat</th>
                        <th>Peminjam</th>
                        <th>Instansi</th>
                        <th>File Word</th>
                        <th>Terakhir Di-sync</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permohonan as $p)
                    <tr>
                        <td><strong>{{ $p->nomor_permohonan }}</strong></td>
                        <td>{{ $p->nama_peminjam }}</td>
                        <td>{{ $p->instansi?->nama_instansi ?? $p->nama_instansi_lain ?? '-' }}</td>
                        <td>
                            @if($p->word_path && file_exists($p->word_path))
                                <span class="badge badge-success"><i class="fas fa-check"></i> Ada di OneDrive</span>
                            @elseif($p->word_path)
                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> File tidak ditemukan</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-minus"></i> Belum dikirim</span>
                            @endif
                        </td>
                        <td>{{ $p->last_sync_at ? $p->last_sync_at->diffForHumans() : '-' }}</td>
                        <td>
                            @if(!$p->word_path)
                                <form action="{{ route('surat.generate-word', $p->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Kirim ke Word">
                                        <i class="fas fa-file-word"></i> Kirim ke Word
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('surat.preview', $p->id) }}" class="btn btn-info btn-sm" title="Preview" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada data surat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- How it works --}}
    <div class="card card-flat">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-question-circle"></i> Cara Kerja</h3>
        </div>
        <div class="card-body">
            <ol>
                <li>Klik <strong>"Kirim ke Word"</strong> untuk generate DOCX ke folder OneDrive.</li>
                <li>Buka file <code>.docx</code> dari folder OneDrive di komputer kamu.</li>
                <li>Edit isi surat langsung di <strong>Microsoft Word</strong>.</li>
                <li><strong>Save</strong> — OneDrive akan otomatis sync perubahan.</li>
                <li>Script sync akan otomatis mendeteksi perubahan dan update database (setiap 1 menit).</li>
                <li>Atau klik <strong>"Sync Sekarang"</strong> untuk sync manual.</li>
            </ol>
        </div>
    </div>
@stop
