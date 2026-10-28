@extends('adminlte::page')

@section('title', 'Pengembalian Barang')

@section('content_header')
    <h1>Pengembalian Barang</h1>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="fas fa-exclamation-triangle me-1"></i>Pengembalian belum terkonfirmasi.</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
@endif

<style>
    .scan-input-wrap { display: flex; gap: .5rem; align-items: stretch; }
    .scan-input-wrap .form-control { flex: 1; }
    .btn-scan {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        min-width: 120px;
        height: 40px;
        border-radius: 8px;
        font-weight: 600;
        font-size: .875rem;
        border: 1px solid #93c5fd;
        background: #eff6ff;
        color: #2563eb;
        white-space: nowrap;
        transition: all .15s ease;
    }
    .btn-scan:hover { background: #dbeafe; border-color: #60a5fa; color: #1d4ed8; }
    #scanReader {
        width: 100%;
        min-height: 280px;
        border-radius: 10px;
        overflow: hidden;
        background: #0f172a;
    }
    #scanReader video { border-radius: 10px; }
    .scan-status { min-height: 1.4rem; }
    .scan-guide {
        text-align: center;
        font-size: .75rem;
        color: #64748b;
        margin-top: .35rem;
    }

    /* ===== Panel hasil scan ===== */
    .card-flat {
        border-radius: 14px; border: 1px solid #eef0f4;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
    }
    .card-flat .card-header {
        background: transparent; border-bottom: 1px solid #eef0f4;
        border-radius: 14px 14px 0 0;
    }
    .card-flat .card-title { font-weight: 700; color: #1a1a2e; font-size: .95rem; }
    .detail-label {
        font-size: .72rem; text-transform: uppercase; letter-spacing: .5px;
        color: #6c757d; font-weight: 600;
    }
    .detail-value { font-size: .95rem; color: #1a1a2e; font-weight: 600; word-break: break-word; }
    .table-modern thead th {
        background: #1a1a2e; color: #fff; font-weight: 600; font-size: .78rem;
        text-transform: uppercase; letter-spacing: .4px; border: none; padding: .7rem .9rem;
    }
    .table-modern tbody td { padding: .65rem .9rem; font-size: .88rem; vertical-align: middle; }
    .table-modern tbody tr { border-bottom: 1px solid #f1f3f7; }
    .table-modern tfoot th { background: #f8f9fc; font-size: .85rem; }

    /* ===== Lampiran ===== */
    .lampiran-flex { display: flex; flex-wrap: wrap; gap: 20px; }
    .lampiran-flex > div { min-width: 130px; }
    .lampiran-box {
        border: 1px dashed #dee2e6; border-radius: 12px;
        padding: 14px; text-align: center; background: #f8f9fc;
        transition: all .2s ease; display: block; color: #495057;
        text-decoration: none;
    }
    .lampiran-box:hover { border-color: #6610f2; background: #f3f0ff; color: #6610f2; text-decoration: none; }
    .lampiran-box i { font-size: 1.8rem; }
    .lampiran-box.is-empty { opacity: .5; }
    .lampiran-box.is-empty:hover { border-color: #dee2e6; background: #f8f9fc; color: #495057; }

    .ktp-thumb {
        display: block; width: 150px; height: auto;
        border-radius: 8px; border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        cursor: zoom-in; background: #fff;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .ktp-thumb:hover {
        transform: translateY(-2px); border-color: #6610f2;
        box-shadow: 0 6px 16px rgba(102,16,242,.18);
    }
    .lampiran-hint { font-size: .72rem; color: #adb5bd; margin-top: .35rem; }

    /* ===== Lightbox (gambar & PDF) ===== */
    .lightbox-overlay {
        position: fixed; inset: 0; z-index: 10500;
        background: rgba(0,0,0,.9); display: none;
        align-items: center; justify-content: center;
        backdrop-filter: blur(3px);
    }
    .lightbox-overlay.active { display: flex; }
    .lightbox-content {
        max-width: 92vw; max-height: 92vh;
        display: flex; align-items: center; justify-content: center;
    }
    .lightbox-content img {
        max-width: 92vw; max-height: 92vh; object-fit: contain;
        border-radius: 8px; box-shadow: 0 10px 50px rgba(0,0,0,.6);
    }
    .lightbox-content iframe {
        width: 92vw; height: 92vh; border: 0; border-radius: 8px;
        background: #fff; box-shadow: 0 10px 50px rgba(0,0,0,.6);
    }
    .lightbox-close {
        position: absolute; top: 18px; right: 24px;
        width: 44px; height: 44px; border-radius: 50%; border: none;
        background: rgba(255,255,255,.15); color: #fff; font-size: 1.2rem;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .2s;
    }
    .lightbox-close:hover { background: rgba(255,255,255,.35); transform: rotate(90deg); }
</style>

<div class="card card-flat">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exchange-alt me-2"></i>Konfirmasi Pengembalian</h3>
        <div class="card-tools">
            <span class="badge badge-primary" title="Akses Super Admin & Admin">
                <i class="fas fa-user-shield me-1"></i>Super Admin / Admin
            </span>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('pengembalian.proses') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Nomor Permohonan <span class="text-danger">*</span></label>
                        <div class="scan-input-wrap">
                            <input type="text" name="nomor_permohonan" id="nomor_permohonan" class="form-control @error('nomor_permohonan') is-invalid @enderror"
                                placeholder="Contoh: SP-220926-67ACAA"
                                value="{{ old('nomor_permohonan') }}"
                                autocomplete="off"
                                required>
                            <button type="button" class="btn-scan" id="btnOpenScanner"
                                title="Buka kamera untuk scan QR nomor permohonan"
                                data-toggle="modal" data-target="#scanModal">
                                <i class="fas fa-qrcode"></i>
                                Scan QR
                            </button>
                        </div>
                        @error('nomor_permohonan')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Scan QR code dengan kamera, atau ketik manual nomor permohonan (mis. SP-220926-67ACAA).</small>
                        <div id="lookupMsg" class="mt-2" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <div id="permohonanDetail" class="mt-3" style="display: none;">
                <div class="alert alert-success py-2 mb-3" id="scanOkBanner">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>QR terbaca &amp; permohonan ditemukan.</strong>
                    Periksa identitas peminjam dan barang, lalu isi <em>Catatan Admin</em> dan tekan
                    <strong>Konfirmasi Pengembalian</strong>.
                </div>

                {{-- Data Peminjam --}}
                <div class="card card-flat mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user me-2 text-primary"></i>Data Peminjam</h3>
                        <div class="card-tools">
                            <span id="detailStatus" class="badge badge-primary">-</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="detail-label">Nama Peminjam</div>
                                <div class="detail-value" id="detailNama">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Nomor Identitas</div>
                                <div class="detail-value" id="detailNik">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Telepon</div>
                                <div class="detail-value" id="detailTelepon">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Instansi</div>
                                <div class="detail-value" id="detailInstansi">-</div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Alamat</div>
                                <div class="detail-value" id="detailAlamat">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Tanggal Pinjam</div>
                                <div class="detail-value" id="detailTglPinjam">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Tanggal Kembali</div>
                                <div class="detail-value" id="detailTglKembali">-</div>
                            </div>
                            <div class="col-12">
                                <div class="detail-label">Keperluan</div>
                                <div class="detail-value" id="detailKeperluan">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Barang Dipinjam --}}
                <div class="card card-flat mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-boxes me-2 text-primary"></i>Barang Dipinjam</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:48px;">No</th>
                                        <th>Nama Barang</th>
                                        <th>Kode</th>
                                        <th class="text-center" style="width:90px;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody id="detailBarangBody">
                                    <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada barang.</td></tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-center" id="detailJumlah">0</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Lampiran --}}
                <div class="card card-flat mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-paperclip me-2 text-primary"></i>Lampiran</h3>
                    </div>
                    <div class="card-body">
                        <div class="lampiran-flex">
                            <div>
                                <div class="detail-label mb-2">Foto Identitas <span class="text-muted">(opsional)</span></div>
                                <div id="lampiranKtp"></div>
                            </div>
                            <div>
                                <div class="detail-label mb-2">Surat Tugas <span class="text-muted">(opsional)</span></div>
                                <div id="lampiranSuratTugas"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="detailWarn" class="alert alert-warning py-2 mb-0" style="display:none;"></div>
            </div>

            <div class="mt-3">
                <div class="form-group">
                    <label>Catatan Admin <span class="text-danger">*</span></label>
                    <textarea name="catatan" id="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3"
                        placeholder="Wajib diisi — mis. Barang diterima lengkap, kondisi baik">{{ old('catatan') }}</textarea>
                    @error('catatan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Wajib diisi. Jika kosong, konfirmasi akan ditolak.</small>
                </div>
            </div>

            <div class="mt-3">
                <div class="form-group">
                    <label>Bukti Pengembalian (opsional)</label>
                    <input type="text" name="bukti_pengembalian" class="form-control"
                        value="{{ old('bukti_pengembalian') }}"
                        placeholder="Contoh: Foto barang yang dikembalikan">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success flex-grow-1" id="btnConfirmReturn">
                    <i class="fas fa-check me-1"></i>Konfirmasi Pengembalian
                </button>
                <a href="{{ route('permohonan.index') }}" class="btn btn-outline-secondary flex-grow-1">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- Lightbox lampiran: mendukung gambar dan PDF --}}
<div class="lightbox-overlay" id="lightbox">
    <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Tutup">
        <i class="fas fa-times"></i>
    </button>
    <div class="lightbox-content">
        <img src="" alt="Pratinjau lampiran" id="lightboxImg" style="display:none;">
        <iframe src="" title="Pratinjau PDF" id="lightboxFrame" style="display:none;"></iframe>
    </div>
</div>

{{-- Modal Scan QR --}}
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-qrcode me-2 text-primary"></i>Scan QR Code Nomor Permohonan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup" id="btnCloseScanTop">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="scanReader"></div>
                <div id="scanStatus" class="scan-status text-center text-muted small mt-2">
                    Siapkan kamera, lalu arahkan ke QR di atas nomor permohonan.
                </div>
                <div class="scan-guide">
                    <i class="fas fa-info-circle me-1"></i>
                    Mendukung QR code — contoh: SP-220926-67ACAA. Batal scan = isi manual.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="btnCancelScan">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (function () {
        var html5QrCode = null;
        var isScanning = false;
        var inited = false;
        var cekNomorUrl = @json(route('permohonan.cek-nomor'));

        function setScanStatus(msg, isError) {
            var el = document.getElementById('scanStatus');
            if (!el) return;
            el.textContent = msg;
            el.className = 'scan-status text-center small mt-2 ' + (isError ? 'text-danger' : 'text-muted');
        }

        function stopScanner() {
            if (!html5QrCode || !isScanning) {
                if (html5QrCode) {
                    try { html5QrCode.clear(); } catch (e) {}
                    html5QrCode = null;
                }
                return Promise.resolve();
            }
            isScanning = false;
            return html5QrCode.stop().then(function () {
                try { html5QrCode.clear(); } catch (e) {}
                html5QrCode = null;
            }).catch(function () {
                try { html5QrCode.clear(); } catch (e) {}
                html5QrCode = null;
            });
        }

        function setLookupMsg(msg, type) {
            var el = document.getElementById('lookupMsg');
            if (!el) return;
            if (!msg) {
                el.style.display = 'none';
                el.innerHTML = '';
                return;
            }
            var cls = type === 'error' ? 'alert-danger' : (type === 'ok' ? 'alert-success' : 'alert-info');
            el.style.display = 'block';
            el.className = 'alert py-2 mb-0 ' + cls;
            el.innerHTML = msg;
        }

        // Isi teks dengan aman — data dari server tidak pernah masuk innerHTML.
        function isiTeks(id, nilai) {
            var el = document.getElementById(id);
            if (!el) return;
            el.textContent = (nilai === null || nilai === undefined || nilai === '') ? '-' : String(nilai);
        }

        function renderBarang(items, total) {
            var body = document.getElementById('detailBarangBody');
            if (!body) return;

            while (body.firstChild) body.removeChild(body.firstChild);

            if (!items || !items.length) {
                var kosong = document.createElement('tr');
                var tdKosong = document.createElement('td');
                tdKosong.colSpan = 4;
                tdKosong.className = 'text-center text-muted py-3';
                tdKosong.textContent = 'Tidak ada barang.';
                kosong.appendChild(tdKosong);
                body.appendChild(kosong);
            } else {
                items.forEach(function (item, index) {
                    var tr = document.createElement('tr');
                    var sel = [
                        String(index + 1),
                        item.nama || '-',
                        item.kode || '-',
                        String(item.jumlah || 0)
                    ];

                    sel.forEach(function (teks, i) {
                        var td = document.createElement('td');
                        td.textContent = teks;
                        if (i === 3) td.className = 'text-center';
                        tr.appendChild(td);
                    });

                    body.appendChild(tr);
                });
            }

            var totalEl = document.getElementById('detailJumlah');
            if (totalEl) totalEl.textContent = String(total || 0);
        }

        /**
         * Lampiran: gambar menjadi thumbnail yang bisa diperbesar,
         * PDF dibuka di lightbox (iframe), berkas lain dibuka di tab baru.
         */
        function renderLampiran(targetId, info, label, kosongLabel) {
            var target = document.getElementById(targetId);
            if (!target) return;

            while (target.firstChild) target.removeChild(target.firstChild);

            if (!info || !info.url) {
                var kosong = document.createElement('div');
                kosong.className = 'lampiran-box is-empty';
                kosong.innerHTML = '<i class="fas fa-file mb-1"></i>';
                var judulKosong = document.createElement('div');
                judulKosong.className = 'fw-semibold';
                judulKosong.textContent = kosongLabel;
                kosong.appendChild(judulKosong);
                target.appendChild(kosong);
                return;
            }

            if (info.is_image) {
                var img = document.createElement('img');
                img.src = info.url;
                img.alt = label;
                img.title = 'Klik untuk memperbesar';
                img.className = 'ktp-thumb';
                img.setAttribute('data-src', info.url);
                img.setAttribute('data-type', 'image');
                target.appendChild(img);

                var hint = document.createElement('div');
                hint.className = 'lampiran-hint';
                hint.textContent = 'Klik untuk memperbesar';
                target.appendChild(hint);
                return;
            }

            var tautan = document.createElement('a');
            tautan.href = info.url;
            tautan.target = '_blank';
            tautan.rel = 'noopener';
            tautan.className = 'lampiran-box';
            tautan.innerHTML = info.is_pdf
                ? '<i class="fas fa-file-pdf mb-1"></i>'
                : '<i class="fas fa-file mb-1"></i>';

            if (info.is_pdf) {
                tautan.setAttribute('data-src', info.url);
                tautan.setAttribute('data-type', 'pdf');
            }

            var judul = document.createElement('div');
            judul.className = 'fw-semibold';
            judul.textContent = label;
            tautan.appendChild(judul);
            target.appendChild(tautan);
        }

        function openLightbox(src, type) {
            var overlay = document.getElementById('lightbox');
            var img = document.getElementById('lightboxImg');
            var frame = document.getElementById('lightboxFrame');
            if (!overlay || !img || !frame || !src) return;

            if (type === 'pdf') {
                img.style.display = 'none';
                img.removeAttribute('src');
                frame.src = src;
                frame.style.display = 'block';
            } else {
                frame.style.display = 'none';
                frame.removeAttribute('src');
                img.src = src;
                img.style.display = 'block';
            }

            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            var overlay = document.getElementById('lightbox');
            var img = document.getElementById('lightboxImg');
            var frame = document.getElementById('lightboxFrame');
            if (!overlay) return;

            overlay.classList.remove('active');
            document.body.style.overflow = '';
            if (img) img.removeAttribute('src');
            if (frame) frame.removeAttribute('src');
        }

        function lookupNomor(nomor) {
            if (!nomor || typeof $ === 'undefined') return;
            setLookupMsg('Mencari data permohonan…', 'info');
            $.ajax({
                url: cekNomorUrl,
                type: 'GET',
                data: { nomor_permohonan: nomor },
                success: function (response) {
                    if (response.exists) {
                        setLookupMsg('');

                        isiTeks('detailNama', response.nama_peminjam);
                        isiTeks('detailNik', response.nik);
                        isiTeks('detailTelepon', response.telepon);
                        isiTeks('detailInstansi', response.instansi);
                        isiTeks('detailAlamat', response.alamat);
                        isiTeks('detailTglPinjam', response.tanggal_pinjam);
                        isiTeks('detailTglKembali', response.tanggal_kembali);
                        isiTeks('detailKeperluan', response.keperluan);

                        var statusColor = response.status === 'Disetujui'
                            ? 'badge-success'
                            : (response.status === 'Dipinjam' ? 'badge-primary' : 'badge-warning');
                        $('#detailStatus')
                            .removeClass('badge-primary badge-success badge-warning badge-danger badge-secondary')
                            .addClass(statusColor)
                            .text(response.status);

                        renderBarang(response.items, response.total_jumlah);
                        renderLampiran('lampiranKtp', response.foto_ktp, 'Foto Identitas', 'Tidak ada foto identitas');
                        renderLampiran('lampiranSuratTugas', response.surat_tugas, 'Surat Tugas', 'Tidak ada surat tugas');

                        $('#permohonanDetail').show();

                        var $warn = $('#detailWarn');
                        if (response.status === 'Disetujui' || response.status === 'Dipinjam') {
                            $warn.hide().text('');
                        } else {
                            $warn.show().html(
                                '<i class="fas fa-exclamation-triangle me-1"></i>Status <strong>' +
                                response.status +
                                '</strong> tidak dapat dikembalikan lagi.'
                            );
                        }
                    } else {
                        $('#permohonanDetail').hide();
                        setLookupMsg('<i class="fas fa-exclamation-circle me-1"></i>Permohonan tidak ditemukan. Periksa nomor: <strong>' + nomor + '</strong>', 'error');
                    }
                },
                error: function (xhr) {
                    $('#permohonanDetail').hide();
                    var msg = 'Gagal mengambil data permohonan.';
                    if (xhr && xhr.status === 403) {
                        msg = 'Akses ditolak. Login sebagai Admin/Super Admin.';
                    } else if (xhr && xhr.status === 404) {
                        msg = 'Endpoint tidak ditemukan. Muat ulang halaman (Ctrl+Shift+R).';
                    }
                    setLookupMsg('<i class="fas fa-exclamation-circle me-1"></i>' + msg, 'error');
                }
            });
        }

        function onScanSuccess(decodedText) {
            if (!decodedText) return;
            var cleaned = String(decodedText).trim().replace(/\s+/g, '');
            var input = document.getElementById('nomor_permohonan');
            if (input) {
                input.value = cleaned;
            }
            setScanStatus('Berhasil scan: ' + cleaned, false);
            stopScanner().then(function () {
                if (typeof $ !== 'undefined') $('#scanModal').modal('hide');
                lookupNomor(cleaned);
                setTimeout(function () {
                    var catatan = document.getElementById('catatan');
                    if (catatan) catatan.focus();
                }, 400);
            });
        }

        function startScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                setScanStatus('Library scanner gagal dimuat. Muat ulang halaman atau isi manual.', true);
                return;
            }
            if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (window.isSecureContext) {
                    setScanStatus('Browser tidak mendukung kamera. Gunakan input manual.', true);
                } else {
                    setScanStatus('Scanner butuh koneksi aman (https://). Buka lewat https:// atau isi manual.', true);
                    var scanEl = document.getElementById('scanStatus');
                    var httpsLink = document.createElement('a');
                    httpsLink.className = 'btn btn-sm btn-primary d-block mx-auto mt-2';
                    httpsLink.href = 'https://' + location.hostname + ':8443' + location.pathname + location.search;
                    httpsLink.innerHTML = '<i class="fas fa-lock me-1"></i>Buka lewat HTTPS';
                    scanEl.appendChild(httpsLink);
                }
                return;
            }

            stopScanner().then(function () {
                var formats = [Html5QrcodeSupportedFormats.QR_CODE];

                html5QrCode = new Html5Qrcode('scanReader', {
                    formatsToSupport: formats,
                    useBarCodeDetectorIfSupported: true,
                    verbose: false
                });
                isScanning = true;
                setScanStatus('Mengaktifkan kamera…', false);

                html5QrCode.start(
                    { facingMode: 'environment' },
                    {
                        fps: 15,
                        qrbox: function (w, h) {
                            var side = Math.min(w, h) - 40;
                            side = Math.max(Math.min(side, 280), 180);
                            return { width: side, height: side };
                        },
                        disableFlip: false
                    },
                    onScanSuccess,
                    function () {}
                ).then(function () {
                    setScanStatus('Arahkan kamera ke QR nomor permohonan.', false);
                }).catch(function (err) {
                    isScanning = false;
                    setScanStatus('Kamera tidak tersedia atau izin ditolak. Gunakan input manual.', true);
                    console.warn('Scanner start failed', err);
                });
            });
        }

        function openScannerModal() {
            if (typeof $ === 'undefined' || typeof $.fn.modal === 'undefined') {
                alert('Komponen modal belum siap. Muat ulang halaman.');
                return;
            }
            $('#scanModal').modal('show');
        }

        function initScanPage() {
            if (inited || typeof $ === 'undefined') return;
            inited = true;

            $('#btnOpenScanner').off('click.pengScan').on('click.pengScan', function (e) {
                e.preventDefault();
                openScannerModal();
            });

            $('#scanModal').off('shown.bs.modal.pengScan').on('shown.bs.modal.pengScan', function () {
                startScanner();
            });

            $('#scanModal').off('hidden.bs.modal.pengScan').on('hidden.bs.modal.pengScan', function () {
                stopScanner();
            });

            $('#btnCancelScan, #btnCloseScanTop').off('click.pengScan').on('click.pengScan', function () {
                stopScanner();
            });

            // Lightbox lampiran (dipasang dengan delegasi karena thumbnail
            // dibuat dinamis setelah data hasil scan datang).
            $(document).off('click.pengLightbox', '[data-src]')
                .on('click.pengLightbox', '[data-src]', function (e) {
                    e.preventDefault();
                    openLightbox(this.getAttribute('data-src'), this.getAttribute('data-type'));
                });

            $('#lightboxClose').off('click.pengLightbox').on('click.pengLightbox', function () {
                closeLightbox();
            });

            $('#lightbox').off('click.pengLightbox').on('click.pengLightbox', function (e) {
                if (e.target === this) closeLightbox();
            });

            $(document).off('keydown.pengLightbox').on('keydown.pengLightbox', function (e) {
                if (e.key === 'Escape') closeLightbox();
            });

            if (new URLSearchParams(window.location.search).get('scan') === '1') {
                setTimeout(openScannerModal, 350);
            }

            $('#nomor_permohonan').off('change.pengScan blur.pengScan')
                .on('change.pengScan blur.pengScan', function () {
                    lookupNomor($(this).val());
                });

            $('form').off('submit.pengScan').on('submit.pengScan', function (e) {
                var nomor = $.trim($('#nomor_permohonan').val() || '');
                var catatan = $.trim($('#catatan').val() || '');
                if (!nomor) {
                    e.preventDefault();
                    alert('Nomor permohonan wajib diisi (scan QR atau ketik manual).');
                    $('#nomor_permohonan').focus();
                    return false;
                }
                if (!catatan) {
                    e.preventDefault();
                    alert('Catatan Admin wajib diisi agar pengembalian dapat dikonfirmasi.');
                    $('#catatan').focus();
                    return false;
                }
                var statusText = $.trim($('#detailStatus').text() || '');
                if (statusText && statusText !== 'Disetujui' && statusText !== 'Dipinjam') {
                    e.preventDefault();
                    alert('Status "' + statusText + '" tidak dapat dikembalikan.');
                    return false;
                }
                return true;
            });
        }

        // Content script bisa jalan sebelum jQuery (full load) atau sesudahnya (PJAX)
        if (typeof $ !== 'undefined') {
            initScanPage();
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                initScanPage();
            });
            window.addEventListener('load', function () {
                initScanPage();
            });
        }
    })();
</script>

@stop
