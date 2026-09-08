@extends('adminlte::page')

@section('title', 'Edit Surat - ' . $permohonan->nomor_permohonan)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <h1>Edit Surat: {{ $permohonan->nomor_permohonan }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('surat.preview', $permohonan->id) }}" class="btn btn-info btn-sm" target="_blank">
                <i class="fas fa-eye me-1"></i>Preview
            </a>
            <a href="{{ route('surat.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@php
    $fieldStatus = function($key) use ($perSurat, $global) {
        if (!empty($perSurat[$key])) return 'custom';
        if (!empty($global[$key])) return 'template';
        return 'default';
    };
    $badge = function($status) {
        return match($status) {
            'custom'   => '<span class="badge badge-primary ml-1" style="font-size:.65rem">Custom</span>',
            'template' => '<span class="badge badge-success ml-1" style="font-size:.65rem">Dari Template</span>',
            default    => '<span class="badge badge-secondary ml-1" style="font-size:.65rem">Default</span>',
        };
    };
@endphp

<form action="{{ route('surat.update', $permohonan->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-8">

            {{-- 1. Hal --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-heading me-2 text-primary"></i>1. Hal {!! $badge($fieldStatus('hal')) !!}</h3>
                    <small class="text-muted">Kosongkan untuk otomatis (nama barang)</small>
                </div>
                <div class="card-body py-2">
                    <input type="text" name="hal" class="form-control"
                           placeholder="Contoh: Permohonan Peminjaman Laptop"
                           value="{{ old('hal', $content['hal']) }}">
                </div>
            </div>

            {{-- 2. Kepada --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tie me-2 text-primary"></i>2. Kepada {!! $badge($fieldStatus('kepada_yth')) !!}</h3>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Yth.</label>
                            <input type="text" name="kepada_yth" class="form-control form-control-sm"
                                   value="{{ old('kepada_yth', $content['kepada_yth']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Kabupaten</label>
                            <input type="text" name="kepada_kab" class="form-control form-control-sm"
                                   value="{{ old('kepada_kab', $content['kepada_kab']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Di tempat</label>
                            <input type="text" name="kepada_tempat" class="form-control form-control-sm"
                                   value="{{ old('kepada_tempat', $content['kepada_tempat']) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Pembuka --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-handshake me-2 text-primary"></i>3. Pembuka {!! $badge($fieldStatus('pembuka')) !!}</h3>
                </div>
                <div class="card-body py-2">
                    <input type="text" name="pembuka" class="form-control"
                           value="{{ old('pembuka', $content['pembuka']) }}">
                </div>
            </div>

            {{-- 4. Identitas Peminjam --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card me-2 text-primary"></i>4. Identitas Peminjam {!! $badge($fieldStatus('saya_yang')) !!}</h3>
                    <small class="text-muted">Kosongkan untuk otomatis dari data peminjam</small>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Saya yang bertanda tangan</label>
                            <input type="text" name="saya_yang" class="form-control form-control-sm"
                                   value="{{ old('saya_yang', $content['saya_yang']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Bermaksud</label>
                            <input type="text" name="bermaksud" class="form-control form-control-sm"
                                   value="{{ old('bermaksud', $content['bermaksud']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Nama Peminjam</label>
                            <input type="text" name="nama_peminjam" class="form-control form-control-sm"
                                   placeholder="Kosongkan = dari data peminjam"
                                   value="{{ old('nama_peminjam', $content['nama_peminjam']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">NIK / NRP</label>
                            <input type="text" name="nik" class="form-control form-control-sm"
                                   placeholder="Kosongkan = dari data peminjam"
                                   value="{{ old('nik', $content['nik']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control form-control-sm"
                                   placeholder="Kosongkan = dari data peminjam"
                                   value="{{ old('jabatan', $content['jabatan']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Instansi</label>
                            <input type="text" name="instansi" class="form-control form-control-sm"
                                   placeholder="Kosongkan = dari data peminjam"
                                   value="{{ old('instansi', $content['instansi']) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. Isi Surat --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-align-left me-2 text-primary"></i>5. Isi Surat {!! $badge($fieldStatus('isi')) !!}</h3>
                    <small class="text-muted">Antara identitas peminjam dan jadwal pelaksanaan</small>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Untuk keperluan</label>
                            <input type="text" name="untuk_keperluan" class="form-control form-control-sm"
                                   value="{{ old('untuk_keperluan', $content['untuk_keperluan']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Rencana pelaksanaan</label>
                            <input type="text" name="rencana" class="form-control form-control-sm"
                                   value="{{ old('rencana', $content['rencana']) }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Isi / paragraf tambahan</label>
                            <textarea name="isi" class="form-control" rows="3"
                                      placeholder="Kosongkan jika tidak perlu">{{ old('isi', $content['isi']) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 6. Jadwal --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar me-2 text-primary"></i>6. Label Jadwal {!! $badge($fieldStatus('hari_label')) !!}</h3>
                    <small class="text-muted">Label kolom pada tabel jadwal pelaksanaan</small>
                </div>
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Label Hari</label>
                            <input type="text" name="hari_label" class="form-control form-control-sm"
                                   value="{{ old('hari_label', $content['hari_label']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Label Tanggal</label>
                            <input type="text" name="tanggal_label" class="form-control form-control-sm"
                                   value="{{ old('tanggal_label', $content['tanggal_label']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Label Tempat</label>
                            <input type="text" name="tempat_label" class="form-control form-control-sm"
                                   value="{{ old('tempat_label', $content['tempat_label']) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7. Penutup --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-align-right me-2 text-primary"></i>7. Penutup {!! $badge($fieldStatus('penutup')) !!}</h3>
                </div>
                <div class="card-body py-2">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Paragraf penutup</label>
                        <textarea name="penutup" class="form-control" rows="3">{{ old('penutup', $content['penutup']) }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Ucapan terima kasih</label>
                        <textarea name="terima_kasih" class="form-control" rows="2">{{ old('terima_kasih', $content['terima_kasih']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            {{-- TTD Kiri --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-signature me-2 text-primary"></i>TTD Kiri {!! $badge($fieldStatus('ttd_kiri_label')) !!}</h3>
                    <small class="text-muted">Yang menyerahkan</small>
                </div>
                <div class="card-body py-2">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Label</label>
                        <input type="text" name="ttd_kiri_label" class="form-control form-control-sm"
                               value="{{ old('ttd_kiri_label', $content['ttd_kiri_label']) }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Nama</label>
                        <input type="text" name="ttd_kiri_nama" class="form-control form-control-sm"
                               placeholder="Kosongkan = dari data peminjam"
                               value="{{ old('ttd_kiri_nama', $content['ttd_kiri_nama']) }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">NRP</label>
                        <input type="text" name="ttd_kiri_nrp" class="form-control form-control-sm"
                               placeholder="Kosongkan = dari data peminjam"
                               value="{{ old('ttd_kiri_nrp', $content['ttd_kiri_nrp']) }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Jabatan</label>
                        <input type="text" name="ttd_kiri_jabatan" class="form-control form-control-sm"
                               placeholder="Kosongkan = dari data peminjam"
                               value="{{ old('ttd_kiri_jabatan', $content['ttd_kiri_jabatan']) }}">
                    </div>
                </div>
            </div>

            {{-- TTD Kanan --}}
            <div class="card card-flat">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-signature me-2 text-success"></i>TTD Kanan {!! $badge($fieldStatus('ttd_kanan_label')) !!}</h3>
                    <small class="text-muted">Yang menerima</small>
                </div>
                <div class="card-body py-2">
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Label</label>
                        <input type="text" name="ttd_kanan_label" class="form-control form-control-sm"
                               value="{{ old('ttd_kanan_label', $content['ttd_kanan_label']) }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Nama</label>
                        <input type="text" name="ttd_kanan_nama" class="form-control form-control-sm"
                               placeholder="Kosongkan = dari data peminjam"
                               value="{{ old('ttd_kanan_nama', $content['ttd_kanan_nama']) }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">NRP</label>
                        <input type="text" name="ttd_kanan_nrp" class="form-control form-control-sm"
                               placeholder="Kosongkan = dari data peminjam"
                               value="{{ old('ttd_kanan_nrp', $content['ttd_kanan_nrp']) }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Jabatan</label>
                        <input type="text" name="ttd_kanan_jabatan" class="form-control form-control-sm"
                               placeholder="Kosongkan = dari data peminjam"
                               value="{{ old('ttd_kanan_jabatan', $content['ttd_kanan_jabatan']) }}">
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="card card-flat">
                <div class="card-body text-muted" style="font-size:.83rem">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Keterangan badge:</strong><br>
                    <span class="badge badge-primary" style="font-size:.65rem">Custom</span> = diubah khusus untuk surat ini<br>
                    <span class="badge badge-success" style="font-size:.65rem">Dari Template</span> = mengikuti template global<br>
                    <span class="badge badge-secondary" style="font-size:.65rem">Default</span> = nilai default sistem
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 mb-4">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Simpan
        </button>
        <a href="{{ route('surat.preview', $permohonan->id) }}" class="btn btn-info" target="_blank">
            <i class="fas fa-eye me-1"></i>Preview
        </a>
    </div>
</form>

@stop
