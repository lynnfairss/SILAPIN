<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Peminjaman - SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/flatpickr/material_blue.css') }}" rel="stylesheet">
    <link href="{{ asset('css/peminjam.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('website') }}">
            <i class="fas fa-boxes-stacked me-2"></i>SILAPIN
        </a>
        <a href="{{ route('peminjam.cek-status') }}" class="btn btn-outline-light btn-sm">
            <i class="fas fa-search me-1"></i>Cek Status
        </a>
    </div>
</nav>

<div class="container" style="margin-top: 80px;">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Mohon perbaiki kesalahan berikut:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="text-center mb-4">
                <h3 class="fw-bold mb-1"><i class="fas fa-hand-holding-heart text-primary me-2"></i>Ajukan Peminjaman</h3>
                <p class="text-muted">Isi data diri dan pilih barang yang akan dipinjam</p>
            </div>

            <div class="step-indicator">
                <div class="step-item active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Data Diri</div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Pilih Barang</div>
                </div>
                <div class="step-item" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Preview</div>
                </div>
            </div>

            <form method="POST" action="{{ route('peminjam.store') }}" enctype="multipart/form-data" id="formPeminjaman">
                @csrf

                {{-- STEP 1: Data Diri --}}
                <div class="step-content active" data-step="1">
                    <div class="card form-card">
                        <div class="card-header">
                            <i class="fas fa-user me-2 text-primary"></i>Data Diri Pemohon
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_peminjam" id="namaField" class="form-control" value="{{ old('nama_peminjam') }}" required placeholder="Masukkan nama lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><span id="labelIdentitas">NIK</span> <span class="text-danger">*</span></label>
                                    <input type="text" name="nik" id="nikField" class="form-control" value="{{ old('nik') }}" required placeholder="Nomor Induk Kependudukan" maxlength="30">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Provinsi</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-globe-asia"></i></span>
                                        <select id="provinsiSelect" class="form-control" aria-label="Pilih provinsi">
                                            <option value="">-- Semua Provinsi --</option>
                                            @foreach($daftarKota as $prov => $kotaList)
                                                <option value="{{ $prov }}">{{ $prov }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-text">Pilih provinsi untuk mempersempit saran kota.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Tempat Lahir</label>
                                    <div class="ttl-wrapper">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                            <input type="text" name="tempat_lahir" id="tempatLahirField" class="form-control" value="{{ old('tempat_lahir') }}" maxlength="100" autocomplete="off" placeholder="Ketik kota / pilih dari saran">
                                            <button type="button" class="btn btn-outline-secondary ttl-toggle" id="ttlToggleBtn" tabindex="-1" aria-label="Buka daftar kota">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        <div class="ttl-dropdown" id="ttlDropdown"></div>
                                    </div>
                                    <div class="form-text">Ketik untuk mencari, atau klik panah untuk melihat daftar.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Tanggal Lahir</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="text" name="tanggal_lahir" id="tanggalLahirField" class="form-control" value="{{ old('tanggal_lahir') }}" maxlength="10" placeholder="DD-MM-YYYY">
                                    </div>
                                    <div class="form-text">Pilih dari kalender atau ketik manual.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Jabatan <span class="text-danger">*</span></label>
                                    <input type="text" name="jabatan" id="jabatanField" class="form-control" value="{{ old('jabatan') }}" required placeholder="Contoh: Kepala Subbag, Staf">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" name="telepon" id="teleponField" class="form-control" value="{{ old('telepon') }}" required placeholder="08xxxxxxxxxx" maxlength="15" oninput="this.value = this.value.replace(/\D/g, '')">
                                </div>
                                @php
                                    $oldInstVal = old('instansi_id', '');
                                    $instSearchVal = $oldInstVal === 'lainnya' ? 'Lainnya (isi manual)' : ($instansi->firstWhere('id', $oldInstVal)?->nama_instansi ?? '');
                                @endphp
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Instansi <span class="text-danger">*</span></label>
                                    <input type="hidden" name="instansi_id" id="instansiIdHidden" value="{{ $oldInstVal }}">
                                    <div class="ttl-wrapper">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                            <input type="text" id="instansiSearch" class="form-control" value="{{ $instSearchVal }}" autocomplete="off" placeholder="Ketik nama instansi...">
                                            <button type="button" class="btn btn-outline-secondary ttl-toggle" id="instansiToggleBtn" tabindex="-1" aria-label="Buka daftar instansi">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        <div class="ttl-dropdown" id="instansiDropdown"></div>
                                    </div>
                                    <div class="form-text">Ketik untuk mencari, atau klik panah untuk melihat daftar.</div>
                                </div>
                                <div class="col-md-6" id="instansiLainWrapper" style="{{ old('instansi_id')=='lainnya' ? '' : 'display:none' }}">
                                    <label class="form-label fw-medium">Nama Instansi <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_instansi_lain" id="namaInstansiField" class="form-control" value="{{ old('nama_instansi_lain') }}" placeholder="Masukkan nama instansi">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Tempat / Tgl Lahir</label>
                                    <input type="text" name="tempat_tanggal_lahir" id="ttlField" class="form-control" value="{{ old('tempat_tanggal_lahir') }}" placeholder="Contoh: Jakarta, 12-08-1990" maxlength="120">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Alamat</label>
                                    <textarea name="alamat" id="alamatField" class="form-control" rows="2" placeholder="Masukkan alamat sesuai KTP" maxlength="255">{{ old('alamat') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <h6 class="fw-bold text-primary"><i class="fas fa-upload me-2"></i>Upload Dokumen</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Foto KTP <span class="text-danger">*</span> <small class="text-muted">(max 2MB, jpg/png)</small></label>
                                    <div class="input-group">
                                        <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                                        <button type="button" class="btn btn-outline-primary" data-scan="foto_ktp" onclick="openCamera(this)" title="Scan / foto KTP">
                                            <i class="fas fa-camera me-1"></i>Scan
                                        </button>
                                    </div>
                                    <div class="scan-preview"></div>
                                    <div class="form-text">Upload atau scan/foto KTP sebagai bukti.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Surat Tugas <small class="text-muted">(opsional, max 2MB)</small></label>
                                    <div class="input-group">
                                        <input type="file" name="surat_tugas" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        <button type="button" class="btn btn-outline-primary" data-scan="surat_tugas" onclick="openCamera(this)" title="Foto surat tugas pakai kamera">
                                            <i class="fas fa-camera me-1"></i>Kamera
                                        </button>
                                    </div>
                                    <div class="scan-preview"></div>
                                    <div class="form-text">Upload atau scan surat tugas jika ada.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('website') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Utama
                        </a>
                        <button type="button" class="btn btn-primary btn-next px-4" onclick="goToStep(2)">
                            Selanjutnya <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: Pilih Barang --}}
                <div class="step-content" data-step="2">
                    <div class="card form-card">
                        <div class="card-header">
                            <i class="fas fa-boxes me-2 text-primary"></i>Pilih Barang & Jadwal Peminjaman
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Tanggal Pinjam <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_pinjam" class="form-control" value="{{ old('tanggal_pinjam') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Tanggal Kembali <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_kembali" class="form-control" value="{{ old('tanggal_kembali') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Keperluan <span class="text-danger">*</span></label>
                                    <textarea name="keperluan" class="form-control" rows="3" required placeholder="Jelaskan keperluan peminjaman">{{ old('keperluan') }}</textarea>
                                </div>
                            </div>

                            <hr>
                            <h6 class="fw-bold mb-3"><i class="fas fa-box me-2 text-primary"></i>Pilih Barang</h6>

                            {{-- Filter Search --}}
                            <div class="filter-search mb-3">
                                <i class="fas fa-search filter-search-icon"></i>
                                <input type="text" id="filterBarang" class="form-control" placeholder="Cari kategori atau nama barang..." oninput="filterBarang(this.value)">
                            </div>

                            {{-- Kategori Chips --}}
                            <div class="filter-chips mb-3" id="filterChips">
                                <span class="filter-chip active" data-kategori="" onclick="filterByKategori(this, '')">Semua</span>
                                @foreach($kategori as $kat)
                                <span class="filter-chip" data-kategori="{{ $kat->nama_kategori }}" onclick="filterByKategori(this, '{{ $kat->nama_kategori }}')">{{ $kat->nama_kategori }}</span>
                                @endforeach
                            </div>

                            <div class="row g-3" id="barangContainer">
                                @forelse($inventaris as $item)
                                <div class="col-md-4 col-sm-6 barang-item" data-kategori="{{ $item->kategori->nama_kategori ?? '' }}" data-nama="{{ strtolower($item->nama_barang) }}">
                                    <div class="product-card" onclick="toggleBarang(this, {{ $item->id }})">
                                        {{-- Image Slider --}}
                                        <div class="img-slider" id="slider_{{ $item->id }}">
                                            @php $fotos = $item->fotos; @endphp
                                            @if($fotos->count() > 0)
                                                @foreach($fotos as $idx => $f)
                                                <div class="slider-img {{ $idx === 0 ? 'active' : '' }}" data-img="{{ asset('storage/'.$f->foto) }}">
                                                    <img src="{{ asset('storage/'.$f->foto) }}" alt="{{ $item->nama_barang }}" onclick="event.stopPropagation(); openLightbox({{ $item->id }})">
                                                </div>
                                                @endforeach
                                                @if($fotos->count() > 1)
                                                <button type="button" class="slider-btn slider-prev" onclick="event.stopPropagation(); slideImg({{ $item->id }}, -1)"><i class="fas fa-chevron-left"></i></button>
                                                <button type="button" class="slider-btn slider-next" onclick="event.stopPropagation(); slideImg({{ $item->id }}, 1)"><i class="fas fa-chevron-right"></i></button>
                                                <div class="slider-dots">
                                                    @foreach($fotos as $idx => $f)
                                                    <span class="dot {{ $idx === 0 ? 'active' : '' }}" onclick="event.stopPropagation(); goToSlide({{ $item->id }}, {{ $idx }})"></span>
                                                    @endforeach
                                                </div>
                                                @endif
                                            @else
                                                <div class="slider-img active no-foto">
                                                    <i class="fas fa-box"></i>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Info --}}
                                        <div class="product-info">
                                            <div class="product-name">{{ $item->nama_barang }}</div>
                                            <div class="product-meta">
                                                <span>Kode: <code>{{ $item->kode_barang }}</code></span>
                                                <span class="product-kategori">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                                            </div>
                                            <div class="product-stok">
                                                <span class="badge bg-success">Stok: {{ $item->stok }}</span>
                                            </div>
                                        </div>

                                        {{-- Checkbox + Stepper --}}
                                        <div class="product-action">
                                            <div class="form-check" onclick="event.stopPropagation()">
                                                <input class="form-check-input" type="checkbox" name="inventaris[]" value="{{ $item->id }}" id="barang_{{ $item->id }}" onchange="toggleSelect(this, {{ $item->id }})">
                                                <label class="form-check-label" for="barang_{{ $item->id }}">Pilih</label>
                                            </div>
                                            <div class="qty-stepper" onclick="event.stopPropagation()">
                                                <button type="button" class="qty-btn qty-minus" onclick="changeQty(this, -1)" disabled>&minus;</button>
                                                <input type="text" name="jumlah[{{ $item->id }}]" class="qty-input" value="1" min="1" max="{{ $item->stok }}" disabled oninput="validateQty(this)" onkeydown="return (event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode === 8 || event.keyCode === 46">
                                                <button type="button" class="qty-btn qty-plus" onclick="changeQty(this, 1)">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x d-block mb-2"></i>
                                    Belum ada barang tersedia.
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-secondary btn-prev px-4" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left me-1"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-primary btn-next px-4" onclick="goToStep(3)">
                            Selanjutnya <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Preview & Submit --}}
                <div class="step-content" data-step="3">
                    <div class="card form-card">
                        <div class="card-header">
                            <i class="fas fa-eye me-2 text-primary"></i>Preview Data Permohonan
                        </div>
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user me-2"></i>Data Diri</h6>
                            <table class="table table-bordered preview-table mb-4">
                                <tbody id="previewDataDiri"></tbody>
                            </table>

                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-calendar me-2"></i>Jadwal Peminjaman</h6>
                            <table class="table table-bordered preview-table mb-4">
                                <tbody id="previewJadwal"></tbody>
                            </table>

                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-boxes me-2"></i>Barang yang Dipinjam</h6>
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody id="previewBarang"></tbody>
                            </table>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Dengan mengirimkan permohonan ini, saya menyatakan bahwa data yang diisi adalah benar.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-secondary btn-prev px-4" onclick="goToStep(2)">
                            <i class="fas fa-arrow-left me-1"></i> Sebelumnya
                        </button>
                        <button type="submit" class="btn btn-success btn-lg px-5" id="btnSubmit">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Permohonan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Lightbox Modal --}}
<div class="image-modal-overlay" id="imageModal" onclick="closeLightbox(event)">
    <div class="image-modal-content">
        <button type="button" class="modal-close" onclick="closeLightbox()">&times;</button>
        <button type="button" class="modal-nav modal-prev" onclick="navigateLightbox(-1)"><i class="fas fa-chevron-left"></i></button>
        <img id="modalImg" src="" alt="Foto Barang">
        <button type="button" class="modal-nav modal-next" onclick="navigateLightbox(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="modal-caption" id="modalCaption"></div>
        <div class="modal-dots" id="modalDots"></div>
    </div>
</div>

{{-- Camera Scan Modal --}}
<div class="image-modal-overlay" id="cameraModal" onclick="if(event.target===this)closeCamera()">
    <div class="image-modal-content camera-modal-content">
        <button type="button" class="modal-close" onclick="closeCamera()">&times;</button>
        <h6 class="fw-bold mb-3 text-center"><i class="fas fa-camera me-2 text-primary"></i><span id="cameraModalTitle">Scan Dokumen</span></h6>
        <div class="camera-frame">
            <video id="cameraPreview" autoplay playsinline muted></video>
            <canvas id="cameraCanvas" class="d-none"></canvas>
            <div class="camera-flash" id="cameraFlash"></div>
            <div class="ktp-frame hidden" id="ktpFrame">
                <div class="ktp-hole">
                    <span class="ktp-corner tl"></span>
                    <span class="ktp-corner tr"></span>
                    <span class="ktp-corner bl"></span>
                    <span class="ktp-corner br"></span>
                    <span class="ktp-face-label">Wajah</span>
                    <span class="ktp-face-box"></span>
                </div>
                <span class="ktp-label"><i class="fas fa-id-card me-1"></i>Letakkan KTP di dalam bingkai, wajah di dalam kotak</span>
            </div>
        </div>
        <div id="cameraStatus" class="text-center text-muted small py-2"></div>
        <div class="d-flex justify-content-center gap-3 mt-2">
            <button type="button" class="btn btn-outline-secondary" onclick="closeCamera()">
                <i class="fas fa-times me-1"></i>Batal
            </button>
            <button type="button" class="btn btn-success" id="btnCapture" onclick="capturePhoto()" disabled>
                <i class="fas fa-camera me-1"></i>Ambil Foto
            </button>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0 small">&copy; 2026 SILAPIN - Sistem Informasi Peminjaman Inventaris</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/l10n/id.js') }}"></script>
<script src="{{ asset('vendor/tesseract/tesseract.min.js') }}"></script>
<script>
    let currentStep = 1;

    // ========== STEP NAVIGATION ==========

    function goToStep(step) {
        if (step < 1 || step > 3) return;

        if (step > currentStep) {
            if (!validateStep(currentStep)) return;
        }

        document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-item').forEach((el, i) => {
            el.classList.remove('active', 'completed');
            if (i + 1 === step) el.classList.add('active');
            else if (i + 1 < step) el.classList.add('completed');
        });

        document.querySelector(`.step-content[data-step="${step}"]`).classList.add('active');
        currentStep = step;

        if (step === 3) updatePreview();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep(step) {
        clearToasts();
        const errors = [];

        if (step === 1) {
            const nama = document.querySelector('[name="nama_peminjam"]').value.trim();
            const nik = document.querySelector('[name="nik"]').value.trim();
            const telepon = document.querySelector('[name="telepon"]').value.trim();
            const labelId = document.getElementById('labelIdentitas').textContent;

            if (!nama) {
                errors.push('Nama belum diisi.');
                setFieldInvalid(document.getElementById('namaField'), true);
            }
            if (!nik) {
                errors.push(labelId + ' belum diisi.');
                setFieldInvalid(document.getElementById('nikField'), true);
            }
            if (!telepon) {
                errors.push('Telepon belum diisi.');
                setFieldInvalid(document.getElementById('teleponField'), true);
            }
            if (!document.getElementById('jabatanField').value.trim()) {
                errors.push('Jabatan belum diisi.');
                setFieldInvalid(document.getElementById('jabatanField'), true);
            }

            const instansiHidden = document.getElementById('instansiIdHidden');
            const instansiSearch = document.getElementById('instansiSearch');
            const instansiVal = instansiHidden ? instansiHidden.value : '';
            if (!instansiVal) {
                errors.push('Instansi belum dipilih.');
                setFieldInvalid(instansiSearch, true);
            } else if (instansiVal === 'lainnya') {
                const namaInstansi = document.getElementById('namaInstansiField');
                if (namaInstansi && !namaInstansi.value.trim()) {
                    errors.push('Nama Instansi belum diisi.');
                    setFieldInvalid(namaInstansi, true);
                }
            }

            const fotoKtp = document.getElementById('fotoKtpField');
            if (fotoKtp && (!fotoKtp.files || fotoKtp.files.length === 0)) {
                errors.push('Foto KTP belum diunggah.');
                setFieldInvalid(fotoKtp, true);
            }

            const tempat = document.getElementById('tempatLahirField');
            const tglLahir = document.getElementById('tanggalLahirField');

            if (tempat && tempat.value.trim()) {
                if (!/^[A-Za-z][A-Za-z .'-]{0,99}$/.test(tempat.value.trim())) {
                    errors.push('Tempat lahir hanya boleh berisi huruf, spasi, titik, atau tanda hubung.');
                    setFieldInvalid(tempat, true);
                }
            }

            if (tglLahir && tglLahir.value.trim()) {
                const v = tglLahir.value.trim();
                const m = v.match(/^(\d{2})-(\d{2})-(\d{4})$/);
                if (!m) {
                    errors.push('Format tanggal lahir harus DD-MM-YYYY (contoh: 15-08-1990).');
                    setFieldInvalid(tglLahir, true);
                } else {
                    const d = parseInt(m[1], 10), mo = parseInt(m[2], 10), y = parseInt(m[3], 10);
                    const dt = new Date(y, mo - 1, d);
                    const real = dt.getFullYear() === y && dt.getMonth() === mo - 1 && dt.getDate() === d;
                    if (!real) {
                        errors.push('Tanggal lahir tidak valid.');
                        setFieldInvalid(tglLahir, true);
                    } else {
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        if (dt > today) {
                            errors.push('Tanggal lahir tidak boleh di masa depan.');
                            setFieldInvalid(tglLahir, true);
                        }
                    }
                }
            }
        }

        if (step === 2) {
            const pinjam = document.querySelector('[name="tanggal_pinjam"]').value;
            const kembali = document.querySelector('[name="tanggal_kembali"]').value;
            const keperluan = document.querySelector('[name="keperluan"]').value.trim();
            const checked = document.querySelectorAll('[name="inventaris[]"]:checked');
            if (!pinjam) { errors.push('Pilih tanggal pinjam.'); }
            if (!kembali) { errors.push('Pilih tanggal kembali.'); }
            if (kembali && pinjam && kembali < pinjam) { errors.push('Tanggal kembali harus setelah tanggal pinjam.'); }
            if (!keperluan) { errors.push('Isi keperluan peminjaman.'); }
            if (checked.length === 0) { errors.push('Pilih minimal 1 barang.'); }
        }

        if (errors.length > 0) {
            errors.forEach(showError);
            return false;
        }
        return true;
    }

    function setFieldInvalid(field, invalid) {
        if (!field) return;
        field.classList.toggle('is-invalid', !!invalid);
    }

    function clearToasts() {
        document.querySelectorAll('.validation-toast').forEach(el => el.remove());
        toastCount = 0;
    }

    ['namaField', 'nikField', 'teleponField', 'jabatanField', 'instansiSearch', 'namaInstansiField', 'fotoKtpField', 'tempatLahirField', 'tanggalLahirField'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function () { el.classList.remove('is-invalid'); });
            el.addEventListener('change', function () { el.classList.remove('is-invalid'); });
        }
    });

    let toastCount = 0;

    function showError(msg) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show validation-toast position-fixed start-50 translate-middle-x mt-4';
        alert.style.zIndex = '9999';
        alert.style.top = (toastCount * 58 + 16) + 'px';
        alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alert);
        toastCount++;
        setTimeout(() => {
            alert.remove();
            toastCount = Math.max(0, toastCount - 1);
        }, 4500);
    }

    // ========== PRODUCT CARD: SELECT + STEPPER ==========

    function toggleSelect(chk, id) {
        const card = chk.closest('.product-card');
        card.classList.toggle('selected', chk.checked);
        const input = card.querySelector('.qty-input');
        const minus = card.querySelector('.qty-minus');
        const plus = card.querySelector('.qty-plus');
        if (input) {
            input.disabled = !chk.checked;
            if (chk.checked) {
                minus.disabled = parseInt(input.value) <= 1;
            } else {
                minus.disabled = true;
            }
        }
    }

    function toggleBarang(el, id) {
        const chk = el.querySelector('.form-check-input');
        chk.checked = !chk.checked;
        toggleSelect(chk, id);
    }

    function changeQty(btn, delta) {
        const input = btn.parentElement.querySelector('.qty-input');
        if (!input || input.disabled) return;
        let val = parseInt(input.value) || 1;
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 999;
        val = Math.max(min, Math.min(max, val + delta));
        input.value = val;
        btn.parentElement.querySelector('.qty-minus').disabled = val <= min;
        btn.parentElement.querySelector('.qty-plus').disabled = val >= max;
    }

    function validateQty(input) {
        let val = parseInt(input.value) || 1;
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 999;
        if (val < min) val = min;
        if (val > max) val = max;
        input.value = val;
        input.parentElement.querySelector('.qty-minus').disabled = val <= min;
        input.parentElement.querySelector('.qty-plus').disabled = val >= max;
    }

    // ========== IMAGE SLIDER PER CARD ==========

    function slideImg(itemId, dir) {
        const slider = document.getElementById('slider_' + itemId);
        if (!slider) return;
        const imgs = slider.querySelectorAll('.slider-img');
        const dots = slider.querySelectorAll('.dot');
        let active = 0;
        imgs.forEach((img, i) => { if (img.classList.contains('active')) active = i; });
        imgs[active].classList.remove('active');
        if (dots.length) dots[active].classList.remove('active');
        active = (active + dir + imgs.length) % imgs.length;
        imgs[active].classList.add('active');
        if (dots.length) dots[active].classList.add('active');
    }

    function goToSlide(itemId, idx) {
        const slider = document.getElementById('slider_' + itemId);
        if (!slider) return;
        const imgs = slider.querySelectorAll('.slider-img');
        const dots = slider.querySelectorAll('.dot');
        imgs.forEach(img => img.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        imgs[idx].classList.add('active');
        dots[idx].classList.add('active');
    }

    // ========== FILTER ==========

    function filterBarang(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.barang-item').forEach(el => {
            const kategori = el.dataset.kategori.toLowerCase();
            const nama = el.dataset.nama;
            el.style.display = (kategori.includes(q) || nama.includes(q)) ? '' : 'none';
        });
        // Deactivate chip filter when typing
        if (q) {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        }
    }

    function filterByKategori(chip, kategori) {
        document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        document.getElementById('filterBarang').value = '';
        document.querySelectorAll('.barang-item').forEach(el => {
            if (!kategori) { el.style.display = ''; return; }
            el.style.display = el.dataset.kategori === kategori ? '' : 'none';
        });
    }

    // ========== LIGHTBOX ==========

    let lightboxData = [];
    let lightboxIndex = 0;

    function openLightbox(itemId) {
        const slider = document.getElementById('slider_' + itemId);
        if (!slider) return;
        const imgs = slider.querySelectorAll('.slider-img.active img');
        if (!imgs.length) return;
        const allImgs = slider.querySelectorAll('.slider-img');
        lightboxData = [];
        allImgs.forEach(img => {
            const src = img.dataset.img;
            if (src) lightboxData.push(src);
        });
        if (!lightboxData.length) return;
        let startIdx = 0;
        allImgs.forEach((img, i) => { if (img.classList.contains('active') && img.dataset.img) startIdx = i; });

        lightboxIndex = startIdx;
        const modal = document.getElementById('imageModal');
        modal.classList.add('active');
        updateLightbox();
    }

    function navigateLightbox(dir) {
        if (!lightboxData.length) return;
        lightboxIndex = (lightboxIndex + dir + lightboxData.length) % lightboxData.length;
        updateLightbox();
    }

    function updateLightbox() {
        const img = document.getElementById('modalImg');
        img.src = lightboxData[lightboxIndex];
        const caption = document.getElementById('modalCaption');
        caption.textContent = (lightboxIndex + 1) + ' / ' + lightboxData.length;
        const dots = document.getElementById('modalDots');
        dots.innerHTML = lightboxData.map((_, i) =>
            '<span class="dot ' + (i === lightboxIndex ? 'active' : '') + '" onclick="lightboxIndex=' + i + ';updateLightbox()"></span>'
        ).join('');
        document.querySelector('.modal-prev').style.display = lightboxData.length > 1 ? '' : 'none';
        document.querySelector('.modal-next').style.display = lightboxData.length > 1 ? '' : 'none';
        dots.style.display = lightboxData.length > 1 ? '' : 'none';
    }

    function closeLightbox(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('imageModal').classList.remove('active');
    }

    document.addEventListener('keydown', function(e) {
        if (document.getElementById('imageModal').classList.contains('active')) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigateLightbox(-1);
            if (e.key === 'ArrowRight') navigateLightbox(1);
        }
    });

    // ========== IDENTITAS (NIK/NRP/NIP) ==========

    const instansiTipe = @json($instansiTipe);

    const keywordMap = [
        { keywords: ['polres','polsek','polresta','poltabes','polda'],     label: 'NRP' },
        { keywords: ['kodim','korem','koramil','mabes','tni','denma'],    label: 'NDP/NRP' },
        { keywords: ['dinas','pemkot','pemkab','kecamatan','sekretariat','pemerintah','sma','smk','sdn','smp','sd'], label: 'NIP' },
    ];

    const tipeConfig = {
        'NIK':     { placeholder: 'Nomor Induk Kependudukan', maxlength: 30 },
        'NRP':     { placeholder: 'Masukkan NRP',            maxlength: 30 },
        'NIP':     { placeholder: 'Masukkan NIP',            maxlength: 30 },
        'NDP/NRP': { placeholder: 'Masukkan NDP/NRP',        maxlength: 30 },
    };

    function detectTipeFromNama(namaInstansi) {
        const text = (namaInstansi || '').toLowerCase();
        const match = keywordMap.find(m => m.keywords.some(k => text.includes(k)));
        return match ? match.label : null;
    }

    function updateIdentitasField(instansiId, instansiNama) {
        let tipe = instansiTipe[instansiId] || null;
        if (!tipe || tipe === 'NIK') {
            const detected = detectTipeFromNama(instansiNama);
            if (detected) tipe = detected;
            else tipe = 'NIK';
        }
        const cfg = tipeConfig[tipe] || tipeConfig['NIK'];
        document.getElementById('labelIdentitas').textContent = tipe;
        document.getElementById('nikField').placeholder = cfg.placeholder;
        document.getElementById('nikField').maxLength = cfg.maxlength;
    }

    // ========== INSTANSI: auto-complete search ==========
    (function () {
        const INSTANSI_LIST = @json($instansi->map(fn ($i) => [$i->id, $i->nama_instansi]));
        const INSTANSI_NAME = {};
        INSTANSI_LIST.forEach(function (it) { INSTANSI_NAME[String(it[0])] = it[1]; });

        const searchInput = document.getElementById('instansiSearch');
        const hidden = document.getElementById('instansiIdHidden');
        const dropdown = document.getElementById('instansiDropdown');
        const toggleBtn = document.getElementById('instansiToggleBtn');
        const wrapper = document.getElementById('instansiLainWrapper');
        const manualInput = document.getElementById('namaInstansiField');
        if (!searchInput || !hidden || !dropdown) return;

        let activeIndex = -1;
        let items = [];

        function escapeHtml(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function highlight(name, q) {
            if (!q) return escapeHtml(name);
            const i = name.toLowerCase().indexOf(q);
            if (i === -1) return escapeHtml(name);
            return escapeHtml(name.slice(0, i)) + '<mark>' + escapeHtml(name.slice(i, i + q.length)) + '</mark>' + escapeHtml(name.slice(i + q.length));
        }

        function lainnyaRow() {
            return '<div class="ttl-item" data-id="lainnya" data-value="Lainnya (isi manual)">'
                + '<i class="fas fa-plus-circle ttl-item-icon"></i>'
                + '<span>Lainnya (isi manual)</span>'
                + '</div>';
        }

        function renderDropdown() {
            const q = searchInput.value.trim().toLowerCase();
            const qLen = q.length;
            let html = '';
            let total = 0;
            items = [];

            INSTANSI_LIST.forEach(function (it) {
                const nama = it[1];
                const lower = nama.toLowerCase();
                const match = qLen === 0 || lower.indexOf(q) > 0 || lower.indexOf(q) === 0;
                if (!match) return;
                total++;
                html += '<div class="ttl-item" data-id="' + it[0] + '" data-value="' + escapeHtml(nama) + '">'
                    + '<i class="fas fa-building ttl-item-icon"></i>'
                    + '<span>' + highlight(nama, q) + '</span>'
                    + '</div>';
            });

            if (total === 0) {
                html = '<div class="ttl-empty"><i class="fas fa-search me-1"></i>Instansi tidak ditemukan untuk "<strong>' + escapeHtml(q) + '"</strong></div>' + html;
            }
            html += lainnyaRow();

            dropdown.innerHTML = html;
            dropdown.querySelectorAll('.ttl-item').forEach(function (el) {
                items.push({ el: el, id: el.getAttribute('data-id'), value: el.getAttribute('data-value') });
                el.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectValue(el.getAttribute('data-id'), el.getAttribute('data-value'));
                });
            });
            dropdown.classList.add('open');
            setActive(-1);
        }

        function setActive(idx) {
            if (items.length === 0) return;
            items.forEach(function (it, i) {
                it.el.classList.toggle('active', i === idx);
            });
            activeIndex = idx;
            if (idx >= 0 && items[idx]) {
                items[idx].el.scrollIntoView({ block: 'nearest' });
            }
        }

        function selectValue(id, label) {
            hidden.value = id;
            searchInput.value = label;
            closeDropdown();
            if (id === 'lainnya') {
                wrapper.style.display = 'block';
                if (manualInput) manualInput.required = true;
                document.getElementById('labelIdentitas').textContent = 'NIK';
                document.getElementById('nikField').placeholder = 'Nomor Induk Kependudukan';
                document.getElementById('nikField').maxLength = 30;
                if (manualInput) manualInput.focus();
            } else {
                wrapper.style.display = 'none';
                if (manualInput) manualInput.required = false;
                updateIdentitasField(id, label);
                searchInput.focus();
            }
            searchInput.classList.remove('is-invalid');
            hidden.classList.remove('is-invalid');
            if (typeof updatePreview === 'function') updatePreview();
        }

        function closeDropdown() {
            dropdown.classList.remove('open');
            activeIndex = -1;
        }

        searchInput.addEventListener('input', renderDropdown);
        searchInput.addEventListener('focus', renderDropdown);

        searchInput.addEventListener('keydown', function (e) {
            if (!dropdown.classList.contains('open')) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    renderDropdown();
                }
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                setActive(Math.min(activeIndex + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    e.preventDefault();
                    selectValue(items[activeIndex].id, items[activeIndex].value);
                } else {
                    closeDropdown();
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            } else if (e.key === 'Tab') {
                closeDropdown();
            }
        });

        toggleBtn.addEventListener('click', function () {
            if (dropdown.classList.contains('open')) {
                closeDropdown();
            } else {
                renderDropdown();
            }
        });

        if (hidden.value && hidden.value !== 'lainnya') {
            updateIdentitasField(hidden.value, INSTANSI_NAME[hidden.value] || '');
        }
    })();

    // ========== PREVIEW ==========

    function updatePreview() {
        const dataDiri = [
            { label: 'Nama Lengkap', value: document.querySelector('[name="nama_peminjam"]').value },
            { label: document.getElementById('labelIdentitas').textContent, value: document.querySelector('[name="nik"]').value },
            { label: 'Jabatan', value: document.querySelector('[name="jabatan"]').value || '-' },
            { label: 'Telepon', value: document.querySelector('[name="telepon"]').value },
            { label: 'Instansi', value: (() => {
                const hid = document.getElementById('instansiIdHidden');
                const val = hid ? hid.value : '';
                if (val && val !== 'lainnya') return document.getElementById('instansiSearch')?.value || val;
                return document.querySelector('[name="nama_instansi_lain"]')?.value || '-';
            })() },
        ];

        document.getElementById('previewDataDiri').innerHTML = dataDiri.map(d =>
            `<tr><th>${d.label}</th><td>${d.value}</td></tr>`
        ).join('');

        const jadwal = [
            { label: 'Tanggal Pinjam', value: document.querySelector('[name="tanggal_pinjam"]').value },
            { label: 'Tanggal Kembali', value: document.querySelector('[name="tanggal_kembali"]').value },
            { label: 'Keperluan', value: document.querySelector('[name="keperluan"]').value },
        ];
        document.getElementById('previewJadwal').innerHTML = jadwal.map(d =>
            `<tr><th>${d.label}</th><td>${d.value}</td></tr>`
        ).join('');

        const checked = document.querySelectorAll('[name="inventaris[]"]:checked');
        let html = '';
        checked.forEach((chk, i) => {
            const card = chk.closest('.product-card');
            const nama = card.querySelector('.product-name')?.textContent || 'Barang';
            const jumlah = card.querySelector('.qty-input')?.value || 1;
            html += `<tr><td>${i + 1}</td><td>${nama}</td><td>${jumlah}</td></tr>`;
        });
        document.getElementById('previewBarang').innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">Belum ada barang dipilih.</td></tr>';
    }

    document.getElementById('formPeminjaman').addEventListener('submit', function(e) {
        if (!validateStep(2)) { e.preventDefault(); return; }
        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    });

    // ========== SCAN DOKUMEN (KAMERA) ==========

    let cameraStream = null;
    const KTP_RATIO = 85.6 / 53.98; // rasio ukuran KTP standar

    async function openCamera(btn) {
        const video = document.getElementById('cameraPreview');
        const status = document.getElementById('cameraStatus');
        const btnCapture = document.getElementById('btnCapture');
        const isKtp = btn.dataset.scan === 'foto_ktp';

        closeCamera();
        video.dataset.target = btn.dataset.scan;
        document.getElementById('ktpFrame').classList.toggle('hidden', !isKtp);
        document.getElementById('cameraModalTitle').textContent = isKtp ? 'Scan KTP' : 'Scan Dokumen';
        document.getElementById('cameraModal').classList.add('active');
        btnCapture.disabled = true;
        status.textContent = isKtp ? 'Aktifkan kamera, lalu letakkan KTP di dalam bingkai…' : 'Mengaktifkan kamera…';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            status.textContent = 'Browser tidak mendukung kamera. Gunakan upload file biasa.';
            return;
        }

        try {
            const constraintsList = [
                { video: { facingMode: 'environment', width: { ideal: 1920 } }, audio: false },
                { video: { facingMode: 'user', width: { ideal: 1920 } }, audio: false },
                { video: { width: { ideal: 1920 } }, audio: false },
            ];

            let lastErr = null;
            for (const c of constraintsList) {
                try {
                    cameraStream = await navigator.mediaDevices.getUserMedia(c);
                    break;
                } catch (err) {
                    lastErr = err;
                }
            }
            if (!cameraStream) throw lastErr;

            video.srcObject = cameraStream;
            await video.play();
            btnCapture.disabled = false;
            status.textContent = isKtp
                ? 'Letakkan KTP di dalam bingkai, lalu klik "Ambil Foto".'
                : 'Arahkan kamera ke dokumen, lalu klik "Ambil Foto".';
        } catch (e) {
            status.textContent = 'Kamera tidak tersedia / izin ditolak. Gunakan upload file biasa.';
        }
    }

    function capturePhoto() {
        const video = document.getElementById('cameraPreview');
        const canvas = document.getElementById('cameraCanvas');
        const field = video.dataset.target;
        if (!cameraStream || !field || video.videoWidth === 0) return;

        const frame = document.querySelector('.camera-frame');
        const isKtp = field === 'foto_ktp' && frame;

        if (isKtp) {
            // Mode KTP: crop tepat sesuai bingkai yang terlihat di viewfinder.
            // Mapping "cover": video memenuhi frame penuh, hitung wilayah sumber.
            const frameW = frame.offsetWidth;
            const frameH = frame.offsetHeight;
            const scale = Math.max(frameW / video.videoWidth, frameH / video.videoHeight);
            const dw = video.videoWidth * scale;
            const dh = video.videoHeight * scale;
            const dx = (frameW - dw) / 2;
            const dy = (frameH - dh) / 2;

            const hw = frameW * 0.78;
            const hh = hw / KTP_RATIO;
            const hx = (frameW - hw) / 2;
            const hy = (frameH - hh) / 2;

            const sx = (hx - dx) / scale;
            const sy = (hy - dy) / scale;
            const sw = hw / scale;
            const sh = hh / scale;

            canvas.width = Math.round(sw * 3); // 3x untuk hasil lebih tajam
            canvas.height = Math.round(sh * 3);
            canvas.getContext('2d').drawImage(video, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
        } else {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
        }

        canvas.toBlob(function(blob) {
            if (blob) {
                const input = document.querySelector('input[name="' + field + '"]');
                if (input) {
                    const file = new File([blob], field + '-scan.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    showScanPreview(field, URL.createObjectURL(file));
                }
            }
            closeCamera();
        }, 'image/jpeg', 0.9);
    }

    function showScanPreview(field, url) {
        const input = document.querySelector('input[name="' + field + '"]');
        if (!input) return;
        const preview = input.closest('.col-md-6').querySelector('.scan-preview');
        preview.innerHTML =
            '<div class="scan-thumb">' +
                '<img src="' + url + '" alt="Hasil scan" onclick="viewScan(\'' + field + '\')" title="Klik untuk lihat besar">' +
                '<button type="button" class="btn btn-sm btn-danger scan-remove" onclick="removeScan(\'' + field + '\')" title="Hapus">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</div>';
    }

    function viewScan(field) {
        const input = document.querySelector('input[name="' + field + '"]');
        if (!input) return;
        const img = input.closest('.col-md-6').querySelector('.scan-thumb img');
        if (!img || !img.src) return;
        lightboxData = [img.src];
        lightboxIndex = 0;
        const modal = document.getElementById('imageModal');
        modal.classList.add('active');
        updateLightbox();
    }

    function removeScan(field) {
        const input = document.querySelector('input[name="' + field + '"]');
        if (input) input.value = '';
        const preview = input ? input.closest('.col-md-6').querySelector('.scan-preview') : null;
        if (preview) preview.innerHTML = '';
    }

    function closeCamera() {
        document.getElementById('cameraFlash').classList.remove('active');
        if (cameraStream) {
            cameraStream.getTracks().forEach(function(t) { t.stop(); });
            cameraStream = null;
        }
        document.getElementById('cameraPreview').srcObject = null;
        document.getElementById('ktpFrame').classList.add('hidden');
        document.getElementById('cameraModal').classList.remove('active');
    }
</script>

</body>
</html>
