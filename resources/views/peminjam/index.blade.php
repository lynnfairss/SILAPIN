<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Peminjaman - SILAPIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/peminjam.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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

            @if (session('error'))
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fas fa-clock me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

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
                                    <input type="text" name="nama_peminjam" class="form-control" value="{{ old('nama_peminjam') }}" required placeholder="Masukkan nama lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium"><span id="labelIdentitas">NIK</span> <span class="text-danger">*</span></label>
                                    <input type="text" name="nik" id="nikField" class="form-control" value="{{ old('nik') }}" required placeholder="Nomor Induk Kependudukan" maxlength="30">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" placeholder="Contoh: Kepala Subbag, Staf">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" name="telepon" class="form-control" value="{{ old('telepon') }}" required placeholder="08xxxxxxxxxx" maxlength="15" oninput="this.value = this.value.replace(/\D/g, '')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Instansi</label>
                                    <select name="instansi_id" class="form-control" id="instansiSelect">
                                        <option value="">-- Pilih Instansi --</option>
                                        @foreach($instansi as $item)
                                            <option value="{{ $item->id }}" {{ old('instansi_id')==$item->id ? 'selected' : '' }}>{{ $item->nama_instansi }}</option>
                                        @endforeach
                                        <option value="lainnya" {{ old('instansi_id')=='lainnya' ? 'selected' : '' }}>Lainnya (isi manual)</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="instansiLainWrapper" style="{{ old('instansi_id')=='lainnya' ? '' : 'display:none' }}">
                                    <label class="form-label fw-medium">Nama Instansi <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_instansi_lain" class="form-control" value="{{ old('nama_instansi_lain') }}" placeholder="Masukkan nama instansi">
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
                                        <input type="file" name="foto_ktp" id="fotoKtpInput" class="form-control" accept="image/*">
                                        <button type="button" class="btn btn-outline-primary" data-scan="foto_ktp" onclick="openCamera(this)" title="Scan / foto KTP">
                                            <i class="fas fa-camera me-1"></i>Scan
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <div class="scan-preview"></div>
                                        <button type="button" class="btn btn-sm btn-outline-info" id="btnOcrKtp" onclick="runOcrKtp()" disabled title="Deteksi otomatis Nama, NIK, Alamat & TTL dari foto KTP">
                                            <i class="fas fa-magic me-1"></i>Deteksi Data KTP
                                        </button>
                                    </div>
                                    <div class="ocr-status" id="ocrStatus"></div>
                                    <div class="form-text">Upload atau scan/foto KTP sebagai bukti.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Surat Tugas <small class="text-muted">(opsional, max 2MB)</small></label>
                                    <div class="input-group">
                                        <input type="file" name="surat_tugas" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        <button type="button" class="btn btn-outline-primary" data-scan="surat_tugas" onclick="openCamera(this)" title="Scan / foto surat tugas">
                                            <i class="fas fa-camera me-1"></i>Scan
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
                                <input type="text" id="searchInput" class="form-control" placeholder="Cari kategori, jenis, atau nama barang..." oninput="filterBarang(this.value)">
                            </div>

                            {{-- Filter Kategori --}}
                            <div class="filter-group">
                                <span class="filter-label"><i class="fas fa-tags me-1"></i>Kategori</span>
                                <div class="filter-chips" id="filterChips">
                                    <span class="filter-chip active" data-filter="" onclick="filterByKategori(this,'')">Semua</span>
                                    @foreach($kategori as $kat)
                                    <span class="filter-chip filter-chip-cat" data-filter="{{ $kat->nama_kategori }}" onclick="filterByKategori(this,'{{ $kat->nama_kategori }}')">{{ $kat->nama_kategori }}</span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Filter Jenis --}}
                            <div class="filter-group">
                                <span class="filter-label"><i class="fas fa-layer-group me-1"></i>Jenis</span>
                                <div class="filter-chips" id="filterJenisChips">
                                    <span class="filter-chip active" data-filter="" onclick="filterByJenis(this,'')">Semua</span>
                                    @foreach($jenisList as $j)
                                    <span class="filter-chip filter-chip-jenis" data-filter="{{ $j }}" onclick="filterByJenis(this,'{{ $j }}')">{{ $j }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3" id="barangContainer">
                                @forelse($inventaris as $item)
                                 <div class="col-md-4 col-sm-6 barang-item" data-kategori="{{ $item->kategori->nama_kategori ?? '' }}" data-jenis="{{ $item->jenis?->nama_jenis ?? '' }}" data-nama="{{ strtolower($item->nama_barang) }}">
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
                                                @if($item->jenis?->nama_jenis)
                                                <span class="product-jenis">{{ $item->jenis->nama_jenis }}</span>
                                                @endif
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
        <div class="text-center mt-3">
            <button type="button" class="btn btn-link text-muted" id="btnSkipScan" onclick="skipScan()">
                <i class="fas fa-edit me-1"></i>Lewati / Isi Manual
            </button>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0 small">&copy; 2026 SILAPIN - Sistem Informasi Peminjaman Inventaris</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        if (step === 1) {
            const nama = document.querySelector('[name="nama_peminjam"]').value.trim();
            const nik = document.querySelector('[name="nik"]').value.trim();
            const telepon = document.querySelector('[name="telepon"]').value.trim();
            const labelId = document.getElementById('labelIdentitas').textContent;
            if (!nama || !nik || !telepon) {
                showError('Lengkapi data diri (Nama, ' + labelId + ', Telepon).');
                return false;
            }
        }
        if (step === 2) {
            const pinjam = document.querySelector('[name="tanggal_pinjam"]').value;
            const kembali = document.querySelector('[name="tanggal_kembali"]').value;
            const keperluan = document.querySelector('[name="keperluan"]').value.trim();
            const checked = document.querySelectorAll('[name="inventaris[]"]:checked');
            if (!pinjam) { showError('Pilih tanggal pinjam.'); return false; }
            if (!kembali) { showError('Pilih tanggal kembali.'); return false; }
            if (kembali < pinjam) { showError('Tanggal kembali harus setelah tanggal pinjam.'); return false; }
            if (!keperluan) { showError('Isi keperluan peminjaman.'); return false; }
            if (checked.length === 0) { showError('Pilih minimal 1 barang.'); return false; }
        }
        return true;
    }

    function showError(msg) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4';
        alert.style.zIndex = '9999';
        alert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
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

    let activeKategori = '';
    let activeJenis = '';
    let activeQuery = '';

    function applyFilter() {
        document.querySelectorAll('.barang-item').forEach(el => {
            const kategori = (el.dataset.kategori || '').toLowerCase();
            const jenis = (el.dataset.jenis || '').toLowerCase();
            const nama = el.dataset.nama || '';
            const q = activeQuery.trim().toLowerCase();

            let show = true;
            if (activeKategori && kategori !== activeKategori.toLowerCase()) show = false;
            if (show && activeJenis && jenis !== activeJenis.toLowerCase()) show = false;
            if (show && q && !(kategori.includes(q) || nama.includes(q) || jenis.includes(q))) show = false;

            el.style.display = show ? '' : 'none';
        });
    }

    function filterBarang(query) {
        activeQuery = query;
        document.querySelectorAll('#filterChips .filter-chip, #filterJenisChips .filter-chip').forEach(chip => {
            chip.style.display = '';
        });
        applyFilter();
    }

    function filterByKategori(chip, kategori) {
        document.querySelectorAll('#filterChips .filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        activeKategori = kategori;
        activeJenis = '';
        document.querySelectorAll('#filterJenisChips .filter-chip').forEach(c => c.classList.remove('active'));
        const allJenis = document.querySelector('#filterJenisChips .filter-chip[data-filter=""]');
        if (allJenis) allJenis.classList.add('active');
        applyFilter();
    }

    function filterByJenis(chip, jenis) {
        document.querySelectorAll('#filterJenisChips .filter-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        activeJenis = jenis;
        applyFilter();
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

    document.getElementById('instansiSelect').addEventListener('change', function() {
        const wrapper = document.getElementById('instansiLainWrapper');
        if (this.value === 'lainnya') {
            wrapper.style.display = 'block';
            wrapper.querySelector('input').required = true;
            document.getElementById('labelIdentitas').textContent = 'NIK';
            document.getElementById('nikField').placeholder = 'Nomor Induk Kependudukan';
            document.getElementById('nikField').maxLength = 30;
        } else {
            wrapper.style.display = 'none';
            wrapper.querySelector('input').required = false;
            const nama = this.options[this.selectedIndex]?.text || '';
            updateIdentitasField(this.value, nama);
        }
    });

    const firstOption = document.getElementById('instansiSelect').options[document.getElementById('instansiSelect').selectedIndex];
    updateIdentitasField(document.getElementById('instansiSelect').value, firstOption?.text || '');

    // Instansi: dropdown searchable (Select2)
    if (window.jQuery && jQuery.fn.select2 && document.getElementById('instansiSelect')) {
        jQuery('#instansiSelect').select2({
            width: '100%',
            placeholder: '-- Pilih Instansi --',
            allowClear: false,
        });
    }

    // ========== PREVIEW ==========

    function updatePreview() {
        const dataDiri = [
            { label: 'Nama Lengkap', value: document.querySelector('[name="nama_peminjam"]').value },
            { label: document.getElementById('labelIdentitas').textContent, value: document.querySelector('[name="nik"]').value },
            { label: 'Jabatan', value: document.querySelector('[name="jabatan"]').value || '-' },
            { label: 'Telepon', value: document.querySelector('[name="telepon"]').value },
            { label: 'Instansi', value: (() => {
                const sel = document.getElementById('instansiSelect');
                if (sel.value && sel.value !== 'lainnya') return sel.options[sel.selectedIndex].text;
                return document.querySelector('[name="nama_instansi_lain"]').value || '-';
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

    // ===== AUTO-CAPTURE KTP =====
    const AUTO_DETECT = {
        interval: 100,       // ms antar sampling
        sampleWidth: 96,     // lebar canvas sampling (px)
        stableFrames: 2,     // jumlah sampel stabil berturut-turut sebelum capture (±0,2 detik)
        minBrightness: 120,  // kecerahan rata-rata minimum area bingkai
        maxBrightness: 235,  // kecerahan rata-rata maksimum
        minEdge: 0.08,       // rasio piksel tepi minimum (teks KTP)
        maxEdge: 0.55,       // rasio piksel tepi maksimum
        maxDiff: 7,          // selisih rata-rata antar frame (stabilitas)
    };
    let autoDetectTimer = null;
    let autoDetectFrame = null;
    let autoDetectStable = 0;
    let autoCapturing = false;

    function getHoleSourceRect(video, frame) {
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
        return {
            sx: (hx - dx) / scale,
            sy: (hy - dy) / scale,
            sw: hw / scale,
            sh: hh / scale,
        };
    }

    function startAutoDetect() {
        stopAutoDetect();
        autoDetectFrame = null;
        autoDetectStable = 0;
        autoDetectTimer = setInterval(autoDetectTick, AUTO_DETECT.interval);
    }

    function stopAutoDetect() {
        if (autoDetectTimer) {
            clearInterval(autoDetectTimer);
            autoDetectTimer = null;
        }
        autoDetectFrame = null;
        autoDetectStable = 0;
    }

    function autoDetectTick() {
        const video = document.getElementById('cameraPreview');
        if (!cameraStream || !autoDetectTimer || video.videoWidth === 0 || autoCapturing) return;

        const frame = document.querySelector('.camera-frame');
        if (!frame) return;

        const rect = getHoleSourceRect(video, frame);
        const sampleW = AUTO_DETECT.sampleWidth;
        const sampleH = Math.max(1, Math.round(sampleW / KTP_RATIO));

        const canvas = document.getElementById('cameraCanvas');
        canvas.width = sampleW;
        canvas.height = sampleH;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, sampleW, sampleH);

        const data = ctx.getImageData(0, 0, sampleW, sampleH).data;
        const gray = new Uint8Array(sampleW * sampleH);
        let sum = 0;
        for (let y = 0; y < sampleH; y++) {
            for (let x = 0; x < sampleW; x++) {
                const i = (y * sampleW + x) * 4;
                const g = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                gray[y * sampleW + x] = g;
                sum += g;
            }
        }
        const mean = sum / (sampleW * sampleH);

        let edgeCount = 0;
        for (let y = 0; y < sampleH; y++) {
            for (let x = 0; x < sampleW; x++) {
                const i = y * sampleW + x;
                const right = x < sampleW - 1 ? gray[i + 1] : gray[i];
                const down = y < sampleH - 1 ? gray[i + sampleW] : gray[i];
                if (Math.max(Math.abs(right - gray[i]), Math.abs(down - gray[i])) > 35) edgeCount++;
            }
        }
        const edgeRatio = edgeCount / (sampleW * sampleH);

        let diffSum = -1;
        if (autoDetectFrame) {
            let diff = 0;
            for (let i = 0; i < gray.length; i++) diff += Math.abs(gray[i] - autoDetectFrame[i]);
            diffSum = diff / gray.length;
        }
        autoDetectFrame = gray.slice();

        const brightnessOk = mean >= AUTO_DETECT.minBrightness && mean <= AUTO_DETECT.maxBrightness;
        const edgeOk = edgeRatio >= AUTO_DETECT.minEdge && edgeRatio <= AUTO_DETECT.maxEdge;
        const stableOk = diffSum >= 0 && diffSum <= AUTO_DETECT.maxDiff;

        const status = document.getElementById('cameraStatus');
        if (brightnessOk && edgeOk && stableOk) {
            autoDetectStable++;
            if (autoDetectStable >= AUTO_DETECT.stableFrames) {
                status.textContent = 'KTP terdeteksi — mengambil foto…';
                autoCapture();
            } else if (autoDetectStable === 1) {
                status.textContent = 'KTP terdeteksi…';
            }
        } else {
            autoDetectStable = 0;
            status.textContent = 'Arahkan KTP ke bingkai, biarkan foto otomatis…';
        }
    }

    function autoCapture() {
        stopAutoDetect();
        autoCapturing = true;
        const flash = document.getElementById('cameraFlash');
        flash.classList.remove('active');
        void flash.offsetWidth;
        flash.classList.add('active');
        setTimeout(function() {
            capturePhoto();
            autoCapturing = false;
        }, 150);
    }

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
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 } },
                audio: false,
            });
            video.srcObject = cameraStream;
            await video.play();
            btnCapture.disabled = false;
            if (isKtp) {
                startAutoDetect();
                status.textContent = 'Arahkan KTP ke bingkai, foto otomatis saat posisi pas.';
            } else {
                status.textContent = 'Arahkan kamera ke dokumen, lalu klik "Ambil Foto".';
            }
        } catch (e) {
            status.textContent = 'Kamera tidak tersedia / izin ditolak. Gunakan upload file biasa.';
        }
    }

    function skipScan() {
        closeCamera();
        setOcrStatus('', 'idle');
        const btn = document.querySelector('[name="foto_ktp"]');
        if (btn) btn.value = '';
        const lbl = document.getElementById('labelIdentitas');
        if (lbl) lbl.textContent = 'NIK';
        showError('Silakan isi data diri secara manual.');
    }

    function capturePhoto() {
        const video = document.getElementById('cameraPreview');
        const canvas = document.getElementById('cameraCanvas');
        const field = video.dataset.target;
        if (!cameraStream || !field || video.videoWidth === 0) return;

        stopAutoDetect();
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

            canvas.width = Math.round(sw * 2); // 2x untuk hasil lebih tajam
            canvas.height = Math.round(sh * 2);
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
                    if (field === 'foto_ktp') {
                        setTimeout(runOcrKtp, 400);
                    }
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
        if (field === 'foto_ktp') {
            setOcrStatus('', 'idle');
            updateOcrButton();
        }
    }

    function closeCamera() {
        stopAutoDetect();
        autoCapturing = false;
        document.getElementById('cameraFlash').classList.remove('active');
        if (cameraStream) {
            cameraStream.getTracks().forEach(function(t) { t.stop(); });
            cameraStream = null;
        }
        document.getElementById('cameraPreview').srcObject = null;
        document.getElementById('ktpFrame').classList.add('hidden');
        document.getElementById('cameraModal').classList.remove('active');
    }

    // ========== OCR KTP (TESSERACT.JS) ==========

    let ocrWorker = null;
    let ocrBusy = false;

    function ocrSupported() {
        return typeof Tesseract !== 'undefined' && !!Tesseract.createWorker;
    }

    async function getOcrWorker() {
        if (ocrWorker) return ocrWorker;
        const worker = await Tesseract.createWorker('ind');
        await worker.setParameters({ preserve_interword_spaces: '1' });
        ocrWorker = worker;
        return ocrWorker;
    }

    function setOcrStatus(msg, type) {
        const el = document.getElementById('ocrStatus');
        el.textContent = msg;
        el.className = 'ocr-status ocr-' + type;
    }

    function updateOcrButton() {
        const input = document.getElementById('fotoKtpInput');
        const btn = document.getElementById('btnOcrKtp');
        const hasImg = input && input.files && input.files.length > 0 && input.files[0].type.startsWith('image/');
        btn.disabled = !hasImg || ocrBusy;
    }

    function preprocessForOcr(img) {
        const maxDim = 1200;
        let w = img.naturalWidth || img.width;
        let h = img.naturalHeight || img.height;
        const scale = Math.min(1, maxDim / Math.max(w, h));
        w = Math.round(w * scale);
        h = Math.round(h * scale);
        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        const data = ctx.getImageData(0, 0, w, h);
        const d = data.data;
        for (let i = 0; i < d.length; i += 4) {
            let gray = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
            gray = (gray - 128) * 1.4 + 128;
            gray = Math.max(0, Math.min(255, gray));
            d[i] = d[i + 1] = d[i + 2] = gray;
        }
        ctx.putImageData(data, 0, 0);
        return canvas;
    }

    function cleanValue(s) {
        return s.replace(/[ \t]+/g, ' ').replace(/\s*\n\s*/g, ' ').trim();
    }

    function grabValue(lines, labelRe) {
        for (let i = 0; i < lines.length; i++) {
            const m = lines[i].match(labelRe);
            if (!m) continue;
            const same = m[1] ? m[1].trim() : '';
            if (same.replace(/[^a-z0-9]/gi, '').length >= 3) {
                return cleanValue(same);
            }
            const val = [];
            for (let j = i + 1; j < lines.length; j++) {
                const nxt = lines[j];
                if (/^(tempat|tgl|jenis|golongan|rt|kel|kecamatan|agama|status|pekerjaan|kewarganegaraan|berlaku|nik|provinsi|alamat|nama)/i.test(nxt.replace(/\s+/g, ''))) break;
                val.push(nxt);
                if (val.length >= 3) break;
            }
            if (val.length) {
                const joined = cleanValue(val.join(' '));
                if (joined.replace(/[^a-z0-9]/gi, '').length >= 3) return joined;
            }
        }
        return '';
    }

    function parseKtpText(raw) {
        const result = { nik: '', nama: '', alamat: '', tempatTglLahir: '' };
        if (!raw) return result;
        const text = raw.replace(/\r/g, '\n').replace(/[ \t]+/g, ' ');
        const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);

        const nikMatch = text.replace(/\s+/g, '').match(/[0-9]{16}/);
        if (nikMatch) result.nik = nikMatch[0];

        let nama = grabValue(lines, /^n\s*a\s*m\s*a\s*:?\s*(.*)/i);
        if (!nama) {
            const im = text.match(/nama\s*:?\s*([A-Z][A-Za-z .'-]{3,})/i);
            if (im) nama = im[1];
        }
        if (nama) {
            nama = nama.replace(/[^\w .'-]/g, '').replace(/\s+/g, ' ').trim();
            if (nama.length >= 3 && nama.length <= 80) result.nama = nama;
        }

        result.tempatTglLahir = grabValue(lines, /^tempat\s*\/?\s*tgl\s*lahir\s*:?\s*(.*)/i);
        result.alamat = grabValue(lines, /^alamat\s*:?\s*(.*)/i);
        return result;
    }

    async function runOcrKtp() {
        const input = document.getElementById('fotoKtpInput');
        const file = input && input.files && input.files[0];
        if (ocrBusy || !file || !file.type.startsWith('image/')) return;
        if (!ocrSupported()) {
            setOcrStatus('Lib OCR tidak termuat (butuh internet saat pertama kali). Isi manual saja.', 'error');
            return;
        }

        ocrBusy = true;
        updateOcrButton();
        setOcrStatus('Mendeteksi data KTP…', 'busy');

        try {
            const url = URL.createObjectURL(file);
            const img = new Image();
            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = reject;
                img.src = url;
            });
            const canvas = preprocessForOcr(img);
            URL.revokeObjectURL(url);

            const worker = await getOcrWorker();
            const { data } = await worker.recognize(canvas);

            const parsed = parseKtpText(data.text);
            let filled = 0;
            if (parsed.nik) { document.querySelector('[name="nik"]').value = parsed.nik; filled++; }
            if (parsed.nama) { document.querySelector('[name="nama_peminjam"]').value = parsed.nama; filled++; }
            if (parsed.alamat) { document.querySelector('[name="alamat"]').value = parsed.alamat; filled++; }
            if (parsed.tempatTglLahir) { document.querySelector('[name="tempat_tanggal_lahir"]').value = parsed.tempatTglLahir; filled++; }

            if (filled === 0) {
                setOcrStatus('Data KTP tidak terdeteksi. Perbaiki foto lalu coba lagi, atau isi manual.', 'error');
            } else {
                setOcrStatus('Data terisi dari KTP (Nama, NIK, Alamat, TTL). Mohon periksa kembali sebelum lanjut.', 'success');
            }
        } catch (e) {
            setOcrStatus('OCR gagal: ' + (e && e.message ? e.message : e) + '. Isi manual saja.', 'error');
        } finally {
            ocrBusy = false;
            updateOcrButton();
        }
    }

    const fotoKtpInputEl = document.getElementById('fotoKtpInput');
    if (fotoKtpInputEl) {
        fotoKtpInputEl.addEventListener('change', function() {
            updateOcrButton();
            const file = this.files && this.files[0];
            if (file && file.type.startsWith('image/')) {
                runOcrKtp();
            } else {
                setOcrStatus('', 'idle');
            }
        });
    }

    // CSRF Token Refresh — refresh tiap 30 menit agar tidak expired
    setInterval(function() {
        fetch('/peminjam/form', { credentials: 'same-origin' })
            .then(response => response.text())
            .then(html => {
                const match = html.match(/name="csrf-token"\s+content="([^"]+)"/);
                if (match) {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', match[1]);
                    document.querySelector('input[name="_token"]').value = match[1];
                }
            }).catch(() => {});
    }, 30 * 60 * 1000);

    // Buka modal scan KTP otomatis saat pertama kali halaman form dimuat,
    // agar user memindai KTP terlebih dahulu sebelum mengisi form.
    window.addEventListener('load', function() {
        const scanBtn = document.querySelector('[data-scan="foto_ktp"]');
        if (scanBtn) {
            setTimeout(function() {
                if (!document.getElementById('fotoKtpInput').files.length) {
                    openCamera(scanBtn);
                }
            }, 400);
        }
    });

    // Tangkap error 419 dan tampilkan pesan yang jelas
    window.addEventListener('pageshow', function(e) {
        const params = new URLSearchParams(window.location.search);
        if (params.get('error') === '419') {
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4';
            alert.style.zIndex = '9999';
            alert.innerHTML = '<i class="fas fa-clock me-2"></i>Sesi telah berakhir. Silakan refresh halaman dan isi form kembali.'
                + ' <a href="' + window.location.pathname + '" class="alert-link">Klik di sini untuk refresh.</a>'
                + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.body.appendChild(alert);
        }
    });
</script>

</body>
</html>
