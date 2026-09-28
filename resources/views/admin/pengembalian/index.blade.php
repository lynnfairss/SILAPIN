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
                <div class="alert alert-success py-2 mb-2" id="scanOkBanner">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>QR terbaca &amp; permohonan ditemukan.</strong>
                    Isi <em>Catatan Admin</em>, lalu tekan <strong>Konfirmasi Pengembalian</strong>.
                </div>
                <h5>Detail Permohonan</h5>
                <div class="row g-2">
                    <div class="col-6">
                        <strong>Nama Peminjam:</strong> <span id="detailNama"></span>
                    </div>
                    <div class="col-6">
                        <strong>Status:</strong> <span id="detailStatus" class="badge badge-primary"></span>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <strong>Barang:</strong> <span id="detailBarang"></span>
                    </div>
                    <div class="col-6">
                        <strong>Jumlah:</strong> <span id="detailJumlah"></span>
                    </div>
                </div>
                <div id="detailWarn" class="alert alert-warning py-2 mt-2 mb-0" style="display:none;"></div>
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
                        $('#detailNama').text(response.nama_peminjam);
                        var statusColor = response.status === 'Disetujui'
                            ? 'badge-success'
                            : (response.status === 'Dipinjam' ? 'badge-primary' : 'badge-warning');
                        $('#detailStatus')
                            .removeClass('badge-primary badge-success badge-warning badge-danger badge-secondary')
                            .addClass(statusColor)
                            .text(response.status);
                        $('#detailBarang').text(response.barang || '-');
                        $('#detailJumlah').text(response.total_jumlah || 0);
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
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setScanStatus('Browser tidak mendukung kamera. Gunakan input manual.', true);
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
