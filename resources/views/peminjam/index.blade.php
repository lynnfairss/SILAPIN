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
                                    <input type="text" name="nama_peminjam" class="form-control" value="{{ old('nama_peminjam') }}" required placeholder="Masukkan nama lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">NIK <span class="text-danger">*</span></label>
                                    <input type="text" name="nik" class="form-control" value="{{ old('nik') }}" required placeholder="Nomor Induk Kependudukan" maxlength="20">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Jabatan</label>
                                    <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}" placeholder="Contoh: Kepala Subbag, Staf">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Telepon <span class="text-danger">*</span></label>
                                    <input type="text" name="telepon" class="form-control" value="{{ old('telepon') }}" required placeholder="08xxxxxxxxxx">
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
                                <div class="col-12">
                                    <hr>
                                    <h6 class="fw-bold text-primary"><i class="fas fa-upload me-2"></i>Upload Dokumen</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Foto KTP <span class="text-danger">*</span> <small class="text-muted">(max 2MB, jpg/png)</small></label>
                                    <input type="file" name="foto_ktp" class="form-control" accept="image/*">
                                    <div class="form-text">Upload scan/foto KTP sebagai bukti.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Surat Tugas <small class="text-muted">(opsional, max 2MB)</small></label>
                                    <input type="file" name="surat_tugas" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Upload surat tugas jika ada.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
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

                            <div class="row g-3" id="barangContainer">
                                @forelse($inventaris as $item)
                                <div class="col-md-4 col-sm-6">
                                    <div class="inventaris-card p-3" onclick="toggleBarang(this, {{ $item->id }})">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="inventaris[]" value="{{ $item->id }}" id="barang_{{ $item->id }}" onchange="toggleSelect(this, {{ $item->id }})">
                                            <label class="form-check-label fw-semibold" for="barang_{{ $item->id }}">{{ $item->nama_barang }}</label>
                                        </div>
                                        <div class="ms-1 small text-muted">
                                            <div>Kode: <code>{{ $item->kode_barang }}</code></div>
                                            <div>Kategori: {{ $item->kategori->nama_kategori ?? '-' }}</div>
                                            <div>Stok: <span class="badge bg-success">{{ $item->stok }}</span></div>
                                            <div class="mt-2">
                                                <label class="form-label" style="font-size:.8rem;">Jumlah</label>
                                                <input type="number" name="jumlah[{{ $item->id }}]" class="form-control form-control-sm" min="1" max="{{ $item->stok }}" value="1" disabled onclick="event.stopPropagation()">
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

<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0 small">&copy; 2026 SILAPIN - Sistem Informasi Peminjaman Inventaris</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentStep = 1;

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
            const ktp = document.querySelector('[name="foto_ktp"]').files.length;
            if (!nama || !nik || !telepon) {
                showError('Lengkapi data diri (Nama, NIK, Telepon).');
                return false;
            }
            if (nik.length < 16) {
                showError('NIK harus minimal 16 karakter.');
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

    function toggleSelect(chk, id) {
        const card = chk.closest('.inventaris-card');
        card.classList.toggle('selected', chk.checked);
        const jumlahInput = card.querySelector('[name^="jumlah"]');
        if (jumlahInput) jumlahInput.disabled = !chk.checked;
    }

    function toggleBarang(el, id) {
        const chk = el.querySelector('.form-check-input');
        chk.checked = !chk.checked;
        toggleSelect(chk, id);
    }

    document.getElementById('instansiSelect').addEventListener('change', function() {
        const wrapper = document.getElementById('instansiLainWrapper');
        if (this.value === 'lainnya') {
            wrapper.style.display = 'block';
            wrapper.querySelector('input').required = true;
        } else {
            wrapper.style.display = 'none';
            wrapper.querySelector('input').required = false;
        }
    });

    function updatePreview() {
        const dataDiri = [
            { label: 'Nama Lengkap', value: document.querySelector('[name="nama_peminjam"]').value },
            { label: 'NIK', value: document.querySelector('[name="nik"]').value },
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
            const card = chk.closest('.inventaris-card');
            const nama = card.querySelector('.form-check-label')?.textContent || 'Barang';
            const jumlah = card.querySelector('[name^="jumlah"]')?.value || 1;
            html += `<tr><td>${i + 1}</td><td>${nama}</td><td>${jumlah}</td></tr>`;
        });
        document.getElementById('previewBarang').innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">Belum ada barang dipilih.</td></tr>';
    }

    document.getElementById('formPeminjaman').addEventListener('submit', function(e) {
        if (!validateStep(2)) { e.preventDefault(); return; }
        document.getElementById('btnSubmit').disabled = true;
        document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    });
</script>

</body>
</html>
