<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Peminjaman - SILAPIN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="{{ asset('css/silapin-theme.css') }}?v={{ filemtime(public_path('css/silapin-theme.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/peminjam.css') }}?v={{ filemtime(public_path('css/peminjam.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/peminjam-form.css') }}?v={{ filemtime(public_path('css/peminjam-form.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/peminjam-tailwind.css') }}?v={{ filemtime(public_path('css/peminjam-tailwind.css')) ?: 1 }}" rel="stylesheet">
    <script src="{{ asset('js/security.js') }}?v={{ filemtime(public_path('js/security.js')) ?: 1 }}" defer></script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50 min-h-full flex flex-col justify-between">

@include('partials.public-navbar', ['active' => 'form'])

{{-- HERO --}}
<section class="pf-hero text-white pt-32 pb-24 relative overflow-hidden">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <nav class="flex justify-center mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2 text-xs font-medium text-slate-400 list-none p-0 m-0">
                <li><a href="{{ route('website') }}" class="hover:text-blue-300 transition-colors">Home</a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i></li>
                <li class="text-blue-400 font-semibold">Ajukan Peminjaman</li>
            </ol>
        </nav>

        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-2">
            Ajukan Peminjaman Inventaris
        </h1>
        <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto">
            Lengkapi form data diri dan unggah dokumen pendukung untuk memulai proses peminjaman fasilitas.
        </p>
    </div>
</section>

<main class="pf-main relative z-10 -mt-16 pb-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">

        @if (session('error'))
        <div class="js-alert mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-start gap-3">
            <i class="fas fa-clock mt-0.5"></i>
            <div class="flex-1">{{ session('error') }}</div>
            <button type="button" class="text-amber-500 hover:text-amber-700 text-lg leading-none" onclick="this.closest('.js-alert').remove()">&times;</button>
        </div>
        @endif

        @if ($errors->any())
        <div class="js-alert mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div class="flex-1">
                    <strong class="font-bold">Mohon perbaiki kesalahan berikut:</strong>
                    <ul class="mt-1 list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="text-red-400 hover:text-red-600 text-lg leading-none" onclick="this.closest('.js-alert').remove()">&times;</button>
            </div>
        </div>
        @endif

        {{-- STEPPER --}}
        <div class="pf-stepper bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-4 sm:p-6 mb-6">
            <div class="relative flex items-center justify-between max-w-2xl mx-auto">
                <div class="absolute left-[16.6667%] top-1/2 -translate-y-1/2 w-[66.6667%] h-1 bg-slate-100 -z-0 rounded-full"></div>
                <div id="stepProgress" class="absolute left-[16.6667%] top-1/2 -translate-y-1/2 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 -z-0 transition-all duration-500 rounded-full" style="width:0%"></div>

                <div class="step-item active relative z-10 flex flex-col items-center group cursor-pointer" data-step="1" onclick="goToStep(1)">
                    <div class="step-number w-12 h-12 rounded-xl flex items-center justify-center font-bold transition-transform group-hover:scale-105">
                        <i class="fa-solid fa-user text-lg"></i>
                    </div>
                    <span class="step-label mt-2 text-xs font-semibold">1. Data Diri</span>
                </div>

                <div class="step-item relative z-10 flex flex-col items-center group cursor-pointer" data-step="2" onclick="goToStep(2)">
                    <div class="step-number w-12 h-12 rounded-xl flex items-center justify-center font-bold transition-all group-hover:scale-105">
                        <i class="fa-solid fa-box-open text-lg"></i>
                    </div>
                    <span class="step-label mt-2 text-xs font-semibold">2. Pilih Barang</span>
                </div>

                <div class="step-item relative z-10 flex flex-col items-center group cursor-pointer" data-step="3" onclick="goToStep(3)">
                    <div class="step-number w-12 h-12 rounded-xl flex items-center justify-center font-bold transition-all group-hover:scale-105">
                        <i class="fa-solid fa-file-circle-check text-lg"></i>
                    </div>
                    <span class="step-label mt-2 text-xs font-semibold">3. Preview &amp; Submit</span>
                </div>
            </div>
        </div>

            <form method="POST" action="{{ route('peminjam.store') }}" enctype="multipart/form-data" id="formPeminjaman">
                @csrf

                {{-- STEP 1: Data Diri --}}
                <div class="step-content active" data-step="1">
                    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                        <div class="pf-card-head bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-semibold text-sm">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Informasi Pemohon</h2>
                                    <p class="text-xs text-slate-500">Isi data identitas diri Anda dengan benar</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-slate-400"><span class="text-red-500">*</span> Wajib diisi</span>
                        </div>

                        <div class="pf-card-body p-6 sm:p-8 space-y-8">
                            <div class="pf-grid grid grid-cols-1 md:grid-cols-2 gap-6">

                                {{-- Nama Lengkap --}}
                                <div>
                                    <label for="nama_peminjam" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <input type="text" name="nama_peminjam" id="nama_peminjam" value="{{ old('nama_peminjam') }}" required
                                               placeholder="Masukkan nama lengkap"
                                               class="pf-input pf-input-icon w-full text-sm @error('nama_peminjam') pf-invalid @enderror">
                                    </div>
                                    @error('nama_peminjam')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Nomor Identitas --}}
                                <div>
                                    <label for="nikField" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        <span id="labelIdentitas">Nomor Identitas</span> <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-id-badge text-sm"></i>
                                        </div>
                                        <input type="text" name="nik" id="nikField" value="{{ old('nik') }}" required
                                               placeholder="NIK / No. SIM / NIM / No. Paspor" maxlength="20"
                                               class="pf-input pf-input-icon w-full text-sm @error('nik') pf-invalid @enderror">
                                    </div>
                                    @error('nik')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Jabatan --}}
                                <div>
                                    <label for="jabatan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Jabatan / Posisi
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-briefcase text-sm"></i>
                                        </div>
                                        <input type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}"
                                               placeholder="Contoh: Staf IT, Mahasiswa, Kepala Subbag"
                                               class="pf-input pf-input-icon w-full text-sm @error('jabatan') pf-invalid @enderror">
                                    </div>
                                    @error('jabatan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Telepon --}}
                                <div>
                                    <label for="telepon" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-phone text-sm"></i>
                                        </div>
                                        <input type="tel" name="telepon" id="telepon" value="{{ old('telepon') }}" required
                                               placeholder="08xxxxxxxxxx" maxlength="15"
                                               oninput="this.value = this.value.replace(/\D/g, '')"
                                               class="pf-input pf-input-icon w-full text-sm @error('telepon') pf-invalid @enderror">
                                    </div>
                                    @error('telepon')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Instansi --}}
                                <div>
                                    <label for="instansiSelect" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Instansi / Unit Kerja
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                                            <i class="fa-solid fa-building text-sm"></i>
                                        </div>
                                        <select name="instansi_id" id="instansiSelect" class="pf-input pf-input-icon pf-select w-full text-sm @error('instansi_id') pf-invalid @enderror">
                                            <option value="">-- Pilih atau ketik instansi --</option>
                                            @foreach($instansi as $item)
                                                <option value="{{ $item->id }}" {{ old('instansi_id')==$item->id ? 'selected' : '' }}>{{ $item->nama_instansi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="mt-1.5 text-[11px] text-slate-400">Jika instansi tidak terdaftar, ketik nama baru pada kolom ini.</p>
                                    @error('instansi_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Instansi manual --}}
                                <div id="instansiLainWrapper" style="display:none">
                                    <label for="nama_instansi_lain" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Nama Instansi <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-building-circle-check text-sm"></i>
                                        </div>
                                        <input type="text" name="nama_instansi_lain" id="nama_instansi_lain" value="{{ old('nama_instansi_lain') }}"
                                               placeholder="Masukkan nama instansi"
                                               class="pf-input pf-input-icon w-full text-sm @error('nama_instansi_lain') pf-invalid @enderror">
                                        <input type="hidden" name="instansi_nama_text" id="instansiNamaText">
                                    </div>
                                    @error('nama_instansi_lain')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Tempat / Tanggal Lahir --}}
                                <div>
                                    <label for="ttlField" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Tempat / Tgl Lahir
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-calendar-days text-sm"></i>
                                        </div>
                                        <input type="text" name="tempat_tanggal_lahir" id="ttlField" value="{{ old('tempat_tanggal_lahir') }}"
                                               placeholder="Contoh: Jakarta, 12-08-1990" maxlength="120"
                                               class="pf-input pf-input-icon w-full text-sm @error('tempat_tanggal_lahir') pf-invalid @enderror">
                                    </div>
                                    @error('tempat_tanggal_lahir')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                {{-- Alamat --}}
                                <div class="md:col-span-2">
                                    <label for="alamatField" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Alamat Lengkap (Sesuai Identitas)
                                    </label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-0 pl-3.5 flex items-start pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-location-dot text-sm"></i>
                                        </div>
                                        <textarea name="alamat" id="alamatField" rows="2" maxlength="255"
                                                  placeholder="Masukkan alamat domisili atau alamat sesuai KTP/SIM..."
                                                  class="pf-input pf-input-icon pf-textarea w-full text-sm @error('alamat') pf-invalid @enderror">{{ old('alamat') }}</textarea>
                                    </div>
                                    @error('alamat')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            {{-- Upload Dokumen --}}
                            <div class="border-t border-slate-100 pt-6">
                                <div class="flex items-center space-x-3 mb-6">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-semibold text-sm">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-800">Unggah Dokumen Pendukung</h3>
                                        <p class="text-xs text-slate-500">Format yang diterima: JPG, PNG, atau PDF (Maks. 2MB)</p>
                                    </div>
                                </div>

                                <div class="pf-grid grid grid-cols-1 md:grid-cols-2 gap-6">

                                    {{-- Foto Identitas --}}
                                    <div class="upload-field space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Foto Identitas (KTP/NIM)</span>
                                            <button type="button" id="btnOcrKtp" onclick="runOcrKtp()" disabled
                                                    title="Deteksi otomatis Nama, Nomor Identitas, Alamat & TTL dari foto identitas"
                                                    class="inline-flex items-center space-x-1 text-[11px] font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed px-2.5 py-1 rounded-lg transition-colors">
                                                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                                <span>Deteksi OCR</span>
                                            </button>
                                        </div>

                                        <label for="fotoKtpInput" id="dropFotoKtp"
                                               class="file-drop-zone rounded-2xl p-5 text-center cursor-pointer flex flex-col items-center justify-center min-h-[140px] group">
                                            <input type="file" name="foto_ktp" id="fotoKtpInput" class="hidden" accept="image/*">
                                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                                <i class="fa-solid fa-camera text-base"></i>
                                            </div>
                                            <p class="text-xs font-medium text-slate-700">
                                                <span class="text-blue-600 font-semibold">Klik untuk unggah</span> atau seret file ke sini
                                            </p>
                                            <p id="namaFotoKtp" class="text-[11px] text-slate-400 mt-1">KTP / SIM / Kartu Pelajar (Maks. 2MB)</p>
                                        </label>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            <div class="scan-preview"></div>
                                            <button type="button" data-scan="foto_ktp" onclick="openCamera(this)"
                                                    title="Scan / foto kartu identitas"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                                                <i class="fa-solid fa-camera"></i> Scan Kamera
                                            </button>
                                        </div>
                                        <div class="ocr-status" id="ocrStatus"></div>
                                        <p class="text-[11px] text-slate-400">Upload atau scan/foto kartu identitas sebagai bukti (opsional).</p>
                                    </div>

                                    {{-- Surat Tugas --}}
                                    <div class="upload-field space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Surat Tugas / Rekomendasi</span>
                                            <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Opsional</span>
                                        </div>

                                        <label for="suratTugasInput" id="dropSuratTugas"
                                               class="file-drop-zone rounded-2xl p-5 text-center cursor-pointer flex flex-col items-center justify-center min-h-[140px] group">
                                            <input type="file" name="surat_tugas" id="suratTugasInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                                <i class="fa-solid fa-file-pdf text-base"></i>
                                            </div>
                                            <p class="text-xs font-medium text-slate-700">
                                                <span class="text-blue-600 font-semibold">Klik untuk unggah</span> atau seret file ke sini
                                            </p>
                                            <p id="namaSuratTugas" class="text-[11px] text-slate-400 mt-1">PDF atau Gambar (Maks. 2MB)</p>
                                        </label>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            <div class="scan-preview"></div>
                                            <button type="button" data-scan="surat_tugas" onclick="openCamera(this)"
                                                    title="Scan / foto surat tugas"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                                                <i class="fa-solid fa-camera"></i> Scan Kamera
                                            </button>
                                        </div>
                                        <p class="text-[11px] text-slate-400">Upload atau scan surat tugas jika ada.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Navigasi Step 1 --}}
                            <div class="step-nav pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <a href="{{ route('website') }}"
                                   class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 font-semibold text-xs tracking-wide transition-all inline-flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-arrow-left text-xs"></i>
                                    <span>Kembali ke Beranda</span>
                                </a>

                                <button type="button" onclick="goToStep(2)"
                                        class="w-full sm:w-auto px-7 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs tracking-wider shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all transform hover:-translate-y-0.5 inline-flex items-center justify-center gap-2">
                                    <span>Selanjutnya: Pilih Barang</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Pilih Barang --}}
                <div class="step-content" data-step="2">
                    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                        <div class="pf-card-head bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-semibold text-sm">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Jadwal &amp; Pilihan Barang</h2>
                                    <p class="text-xs text-slate-500">Tentukan tanggal pinjam, keperluan, dan barang yang dipinjam</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium text-slate-400"><span class="text-red-500">*</span> Wajib diisi</span>
                        </div>

                        <div class="pf-card-body p-6 sm:p-8 space-y-8">
                            <div class="pf-grid grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="tanggal_pinjam" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Tanggal Pinjam <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-calendar-plus text-sm"></i>
                                        </div>
                                        <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="{{ old('tanggal_pinjam', now('Asia/Jakarta')->format('Y-m-d')) }}" required
                                               class="pf-input pf-input-icon w-full text-sm @error('tanggal_pinjam') pf-invalid @enderror">
                                    </div>
                                    @error('tanggal_pinjam')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="tanggal_kembali" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Tanggal Kembali <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-calendar-check text-sm"></i>
                                        </div>
                                        <input type="date" name="tanggal_kembali" id="tanggal_kembali" value="{{ old('tanggal_kembali', now('Asia/Jakarta')->format('Y-m-d')) }}" required
                                               class="pf-input pf-input-icon w-full text-sm @error('tanggal_kembali') pf-invalid @enderror">
                                    </div>
                                    @error('tanggal_kembali')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="keperluan" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                        Keperluan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute top-3 left-0 pl-3.5 flex items-start pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </div>
                                        <textarea name="keperluan" id="keperluan" rows="3" required placeholder="Jelaskan keperluan peminjaman"
                                                  class="pf-input pf-input-icon pf-textarea w-full text-sm @error('keperluan') pf-invalid @enderror">{{ old('keperluan') }}</textarea>
                                    </div>
                                    @error('keperluan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-base font-bold text-slate-800"><i class="fa-solid fa-box-open mr-2 text-blue-600"></i>Pilih Barang</h3>
                                    <span class="text-xs text-slate-400">Pilih satu atau lebih barang</span>
                                </div>

                                {{-- Filter Search --}}
                                <div class="relative mb-4">
                                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                                    <input type="text" id="searchInput" placeholder="Cari kategori, jenis, atau nama barang..." oninput="filterBarang(this.value)"
                                           class="pf-input pf-input-icon w-full text-sm">
                                </div>

                                {{-- Filter Kategori --}}
                                <div class="filter-group mb-4">
                                    <span class="filter-label"><i class="fas fa-tags mr-1"></i>Kategori</span>
                                    <div class="filter-chips" id="filterChips">
                                        <span class="filter-chip active" data-filter="" onclick="filterByKategori(this,'')">Semua</span>
                                        @foreach($kategori as $kat)
                                        <span class="filter-chip filter-chip-cat" data-filter="{{ $kat->nama_kategori }}" onclick="filterByKategori(this,'{{ $kat->nama_kategori }}')">{{ $kat->nama_kategori }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Filter Jenis --}}
                                <div class="filter-group mb-6">
                                    <span class="filter-label"><i class="fas fa-layer-group mr-1"></i>Jenis</span>
                                    <div class="filter-chips" id="filterJenisChips">
                                        <span class="filter-chip active" data-filter="" onclick="filterByJenis(this,'')">Semua</span>
                                        @foreach($jenisList as $j)
                                        <span class="filter-chip filter-chip-jenis" data-filter="{{ $j }}" onclick="filterByJenis(this,'{{ $j }}')">{{ $j }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 lg:grid-cols-3" id="barangContainer">
                                    @forelse($inventaris as $item)
                                     <div class="barang-item" data-kategori="{{ $item->kategori->nama_kategori ?? '' }}" data-jenis="{{ $item->jenis?->nama_jenis ?? '' }}" data-nama="{{ strtolower($item->nama_barang) }}">
                                        <div class="product-card{{ $item->stok <= 0 ? ' out-of-stock' : '' }}" data-stok="{{ (int) $item->stok }}" onclick="toggleBarang(this, {{ $item->id }})">
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
                                                        <i class="fas fa-box-open"></i>
                                                        <span>Tidak ada foto</span>
                                                    </div>
                                                @endif
                                            </div>

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
                                                    @if($item->stok <= 0)
                                                        <span class="badge badge-stok-empty"><i class="fas fa-ban me-1"></i>Stok Habis</span>
                                                    @else
                                                        <span class="badge badge-stok-{{ $item->stok > 5 ? 'ample' : ($item->stok >= 2 ? 'moderate' : 'low') }}" data-stok-badge>
                                                            <i class="fas {{ $item->stok > 5 ? 'fa-check-circle' : ($item->stok >= 2 ? 'fa-exclamation-circle' : 'fa-times-circle') }} me-1"></i>Stok: <span data-stok-value>{{ (int) $item->stok }}</span><span data-stok-sisa></span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="product-action">
                                                <div class="form-check" onclick="event.stopPropagation()">
                                                    <input class="form-check-input" type="checkbox" name="inventaris[]" value="{{ $item->id }}" id="barang_{{ $item->id }}" onchange="toggleSelect(this, {{ $item->id }})" @disabled($item->stok <= 0)>
                                                    <label class="form-check-label" for="barang_{{ $item->id }}">{{ $item->stok <= 0 ? 'Stok Habis' : 'Pilih Barang' }}</label>
                                                </div>
                                                <div class="qty-stepper" onclick="event.stopPropagation()">
                                                    <button type="button" class="qty-btn qty-minus" onclick="changeQty(this, -1)" disabled>&minus;</button>
                                                    <input type="text" name="jumlah[{{ $item->id }}]" class="qty-input" value="1" min="1" max="{{ max(1, (int) $item->stok) }}" disabled oninput="validateQty(this)" onkeydown="return (event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode === 8 || event.keyCode === 46">
                                                    <button type="button" class="qty-btn qty-plus" onclick="changeQty(this, 1)">+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="col-span-full text-center py-12 text-slate-400">
                                        <i class="fas fa-inbox text-4xl block mb-3"></i>
                                        Belum ada barang tersedia.
                                    </div>
                                    @endforelse
                                </div>

                                <div class="no-results" id="noResults">
                                    <i class="fas fa-search"></i>
                                    <p>Barang tidak ditemukan</p>
                                    <small>Coba ubah filter atau kata kunci pencarian</small>
                                </div>
                            </div>

                            {{-- Navigasi Step 2 --}}
                            <div class="step-nav pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <button type="button" onclick="goToStep(1)"
                                        class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 font-semibold text-xs tracking-wide transition-all inline-flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-arrow-left text-xs"></i>
                                    <span>Sebelumnya</span>
                                </button>
                                <button type="button" onclick="goToStep(3)"
                                        class="w-full sm:w-auto px-7 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs tracking-wider shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all transform hover:-translate-y-0.5 inline-flex items-center justify-center gap-2">
                                    <span>Selanjutnya: Preview</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Preview & Submit --}}
                <div class="step-content" data-step="3">
                    <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                        <div class="pf-card-head bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-semibold text-sm">
                                    <i class="fa-solid fa-file-circle-check"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Preview Data Permohonan</h2>
                                    <p class="text-xs text-slate-500">Periksa kembali data sebelum dikirim</p>
                                </div>
                            </div>
                        </div>

                        <div class="pf-card-body p-6 sm:p-8 space-y-6">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-3"><i class="fa-solid fa-user mr-2 text-blue-600"></i>Data Diri</h3>
                                <div class="overflow-hidden rounded-xl border border-slate-200">
                                    <table class="w-full text-sm">
                                        <tbody id="previewDataDiri" class="divide-y divide-slate-100"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-3"><i class="fa-solid fa-calendar-days mr-2 text-blue-600"></i>Jadwal Peminjaman</h3>
                                <div class="overflow-hidden rounded-xl border border-slate-200">
                                    <table class="w-full text-sm">
                                        <tbody id="previewJadwal" class="divide-y divide-slate-100"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-800 mb-3"><i class="fa-solid fa-boxes-stacked mr-2 text-blue-600"></i>Barang yang Dipinjam</h3>
                                <div class="overflow-hidden rounded-xl border border-slate-200">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-800 text-white">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-semibold">No</th>
                                                <th class="px-4 py-3 text-left font-semibold">Nama Barang</th>
                                                <th class="px-4 py-3 text-left font-semibold">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewBarang" class="divide-y divide-slate-100"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800 flex items-start gap-3">
                                <i class="fas fa-info-circle mt-0.5"></i>
                                <span>Dengan mengirimkan permohonan ini, saya menyatakan bahwa data yang diisi adalah benar.</span>
                            </div>
                        </div>

                        <div class="px-6 sm:px-8 pb-6 sm:pb-8">
                            <div class="step-nav pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <button type="button" onclick="goToStep(2)"
                                        class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 font-semibold text-xs tracking-wide transition-all inline-flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-arrow-left text-xs"></i>
                                    <span>Sebelumnya</span>
                                </button>
                                <button type="submit" id="btnSubmit"
                                        class="w-full sm:w-auto px-7 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-bold text-xs tracking-wider shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 transition-all transform hover:-translate-y-0.5 inline-flex items-center justify-center gap-2">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Kirim Permohonan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
    </div>
</main>

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
        <h6 class="fw-bold mb-3 text-center"><i class="fas fa-camera me-2 text-primary"></i><span id="cameraModalTitle">Scan Identitas</span></h6>
        <div class="camera-frame">
            <video id="cameraPreview" autoplay playsinline muted></video>
            <canvas id="cameraCanvas" class="d-none"></canvas>
            <button type="button" class="torch-btn d-none" id="btnTorch"
                    onclick="toggleTorch()" title="Nyalakan flash" aria-label="Nyalakan flash">
                <i class="fas fa-bolt"></i>
            </button>
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
                <span class="ktp-label"><i class="fas fa-id-card me-1"></i>Letakkan kartu identitas di dalam bingkai, wajah di dalam kotak</span>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('vendor/jquery/jquery.min.js') }}?v={{ filemtime(public_path('vendor/jquery/jquery.min.js')) ?: 1 }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/ktp-ocr.js') }}?v={{ filemtime(public_path('js/ktp-ocr.js')) ?: 1 }}"></script>
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

        const progress = document.getElementById('stepProgress');
        if (progress) {
            progress.style.width = ((step - 1) * 100 / 3) + '%';
        }

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
        if (!chk || chk.disabled) return;
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
        updateStokDisplay(card);
    }

    function toggleBarang(el, id) {
        const chk = el.querySelector('.form-check-input');
        if (!chk || chk.disabled) return;
        chk.checked = !chk.checked;
        toggleSelect(chk, id);
    }

    // Warna + ikon badge mengikuti nilai stok yang ditampilkan. Ambangnya
    // sengaja sama dengan yang dipakai Blade agar tampilan tidak berubah
    // saat angka sisa stok ikut bergerak.
    function terapkanGayaBadge(badge, angka) {
        const gaya = angka > 5
            ? ['ample', 'fa-check-circle']
            : angka >= 2
                ? ['moderate', 'fa-exclamation-circle']
                : ['low', 'fa-times-circle'];

        badge.className = 'badge badge-stok-' + gaya[0];

        const ikon = badge.querySelector('i');
        if (ikon) ikon.className = 'fas me-1 ' + gaya[1];
    }

    // Badge "Stok" pada kartu yang sedang dipilih menampilkan sisa stok setelah
    // dikurangi jumlah yang diminta. Angka stok asli tidak diubah, hanya
    // tampilannya, sehingga kartu yang di-uncheck kembali menampilkan angka penuh.
    function updateStokDisplay(card) {
        if (!card) return;

        const badge = card.querySelector('[data-stok-badge]');
        const nilai = card.querySelector('[data-stok-value]');
        const sisa = card.querySelector('[data-stok-sisa]');
        if (!badge || !nilai || !sisa) return;

        const total = parseInt(card.dataset.stok, 10) || 0;
        const chk = card.querySelector('.form-check-input');
        const input = card.querySelector('.qty-input');
        const dipilih = chk && !chk.disabled && chk.checked;

        nilai.textContent = total;

        if (!dipilih) {
            terapkanGayaBadge(badge, total);
            sisa.textContent = '';
            return;
        }

        const jumlah = Math.min(Math.max(parseInt(input ? input.value : 1, 10) || 1, 1), total);
        const tersisa = Math.max(total - jumlah, 0);

        terapkanGayaBadge(badge, tersisa);
        sisa.textContent = ' · Sisa: ' + tersisa;
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
        updateStokDisplay(input.closest('.product-card'));
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
        updateStokDisplay(input.closest('.product-card'));
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
        let visibleCount = 0;
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
            if (show) visibleCount++;
        });

        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.classList.toggle('visible', visibleCount === 0);
        }
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

    // ========== IDENTITAS: nomor identitas (NIK / SIM / NIM / Paspor) ==========
    (function initIdentitas() {
        const label = document.getElementById('labelIdentitas');
        const field = document.getElementById('nikField');
        if (label) label.textContent = 'Nomor Identitas';
        if (field) {
            field.placeholder = 'NIK / No. SIM / NIM / No. Paspor';
            field.maxLength = 20;
        }
    })();

    // Instansi: dropdown searchable dengan tags (bisa ketik instansi baru)
    if (window.jQuery && jQuery.fn.select2 && document.getElementById('instansiSelect')) {
        jQuery('#instansiSelect').select2({
            width: '100%',
            placeholder: '-- Pilih atau ketik instansi --',
            allowClear: true,
            tags: true,
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
                if (sel.value) {
                    if (!isNaN(sel.value)) return sel.options[sel.selectedIndex]?.text || '-';
                    return sel.value;
                }
                return '-';
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
    let cameraTrack = null;
    let imageCapture = null;          // ImageCapture: foto resolusi penuh bila didukung
    let lastCaptureCanvas = null;     // canvas resolusi penuh untuk OCR (bebas kompresi JPEG)
    let lastCaptureFile = null;

    // ===== AUTO-CAPTURE KARTU IDENTITAS =====
    const AUTO_DETECT = {
        interval: 110,       // ms antar sampling
        sampleWidth: 420,    // lebar canvas sampling: ketajaman hanya terukur
                             // pada resolusi mendekati kartu aslinya
        stableFrames: 6,     // ±0,66 detik stabil sebelum capture (mencegah blur)
        minBrightness: 105,  // kecerahan rata-rata minimum area bingkai
        maxBrightness: 245,  // kecerahan rata-rata maksimum
        minEdge: 0.04,       // rasio piksel tepi minimum (teks pada kartu)
        maxEdge: 0.62,       // rasio piksel tepi maksimum
        maxGlare: 0.07,      // porsi piksel nyaris putih (pantulan cahaya)
        minSharpness: 60,    // variansi Laplacian minimum pada sampel 420px
        sharpRatio: 0.9,     // harus mendekati ketajaman terbaik sejauh ini
        maxDiff: 7,          // selisih rata-rata antar frame (stabilitas)
        minCoverage: 0.70,   // kotak kartu hasil grid menutupi minimal porsi ini
        minBusyCells: 20,    // sel grid berserat: di bawah ini tidak ada kartu
        maxOffsetX: 0.10,    // simpul pusat kartu terhadap bingkai (porsi lebar)
        maxOffsetY: 0.12,
        forceAfter: 9000,    // ms posisi sudah pas tapi fokus tak kunjung datang
        manualHintAfter: 9000, // ms sebelum menyarankan tombol Ambil Foto
    };
    let autoDetectTimer = null;
    let autoDetectFrame = null;
    let autoDetectStable = 0;
    let autoDetectPeakSharp = 0;
    let autoDetectPosMs = 0;
    let autoDetectStarted = 0;
    let autoCapturing = false;

    // Sumber crop: ukur bingkai panduan yang benar-benar terlihat, bukan
    // konstanta yang harus dijaga tetap sinkron dengan CSS.
    function getHoleSourceRect(video, frame, srcW, srcH) {
        const hole = frame.querySelector('.ktp-hole') || frame;
        const frameRect = frame.getBoundingClientRect();
        const holeRect = hole.getBoundingClientRect();
        const vw = srcW || video.videoWidth;
        const vh = srcH || video.videoHeight;
        if (!frameRect.width || !frameRect.height || !vw || !vh) {
            return { sx: 0, sy: 0, sw: vw || 1, sh: vh || 1 };
        }
        const scale = Math.max(frameRect.width / vw, frameRect.height / vh);
        const dw = vw * scale;
        const dh = vh * scale;
        const dx = (frameRect.width - dw) / 2;
        const dy = (frameRect.height - dh) / 2;

        // Bingkai sedikit diperluas agar tepi kartu yang melengkung tidak
        // terpotong, tanpa menyapu latar belakang di sekitarnya.
        const pad = Math.min(frameRect.width, frameRect.height) * 0.012;
        const hx = Math.max(0, holeRect.left - frameRect.left - pad);
        const hy = Math.max(0, holeRect.top - frameRect.top - pad);
        const hw = Math.min(frameRect.width - hx, holeRect.width + pad * 2);
        const hh = Math.min(frameRect.height - hy, holeRect.height + pad * 2);

        return {
            sx: Math.max(0, (hx - dx) / scale),
            sy: Math.max(0, (hy - dy) / scale),
            sw: Math.min(vw, hw / scale),
            sh: Math.min(vh, hh / scale),
        };
    }

    // Hitung metrik kualitas frame: kecerahan, tekstur, cakupan kartu,
    // pantulan cahaya, dan ketajaman (variansi Laplacian).
    function analyzeFrame(gray, w, h) {
        const n = w * h;
        let sum = 0, glare = 0;
        for (let i = 0; i < n; i++) {
            const v = gray[i];
            sum += v;
            if (v > 245) glare++;
        }
        const mean = sum / n;

        let edges = 0;
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const i = y * w + x;
                const right = x < w - 1 ? gray[i + 1] : gray[i];
                const down = y < h - 1 ? gray[i + w] : gray[i];
                if (Math.max(Math.abs(right - gray[i]), Math.abs(down - gray[i])) > 35) edges++;
            }
        }

        let lapSum = 0, lapSq = 0, count = 0;
        for (let y = 1; y < h - 1; y++) {
            for (let x = 1; x < w - 1; x++) {
                const i = y * w + x;
                const lap = (gray[i - 1] + gray[i + 1] + gray[i - w] + gray[i + w]) - 4 * gray[i];
                lapSum += lap;
                lapSq += lap * lap;
                count++;
            }
        }
        const lapMean = count ? lapSum / count : 0;
        const sharpness = count ? (lapSq / count) - (lapMean * lapMean) : 0;

        return {
            mean: mean,
            edgeRatio: edges / n,
            glare: glare / n,
            sharpness: sharpness,
        };
    }

    // ===== POSISI KARTU =====
    // Yang dianalisa bukan seluruh frame kamera, hanya lubang bingkai panduan
    // (lihat getHoleSourceRect). Jadi "posisi sudah sesuai" berarti kartu
    // menutupi lubang itu -- dan kartu yang benar-benar rapat justru tidak
    // menyisakan latar di tepi mana pun.
    //
    // Batas kartu dicari lewat TEKSTUR, bukan lewat terang/gelap. Sudah diukur
    // pada frame sintetis: langkah luminans baris teks dan kotak foto sama
    // curamnya dengan langkah kartu-ke-latar, jadi pencarian tepi terang-gelap
    // membaca posisi secara acak tepat pada kasus paling umum (kartu penuh di
    // bingkai). Isi KTP punya banyak tepi kecil (huruf, foto, garis Garuda)
    // sedangkan meja, lantai, dan dinding relatif rata, jadi kerapatan tekstur
    // per sel grid cukup untuk menilai cakupan dan titik tengah kartu.
    //
    // Kemiringan sengaja tidak ditahan: OCR sudah memutar balik gambar
    // (koreksi skew projection-variance di ktp-ocr.js), jadi kartu miring 10
    // derajat tetap terbaca selama seluruh isinya masuk bingkai.
    const GRID_COLS = 16, GRID_ROWS = 10;
    const CELL_BUSY_STD = 10;   // simpangan piksel dalam satu sel: ambang 'ada struktur'
    const MIN_CELL_CONTRAST = 45;

    function cardGrid(gray, w, h) {
        const cw = w / GRID_COLS, ch = h / GRID_ROWS;
        let busy = 0, total = 0, lo = 255, hi = 0;
        let c0 = GRID_COLS, c1 = -1, r0 = GRID_ROWS, r1 = -1;

        for (let gy = 0; gy < GRID_ROWS; gy++) {
            const y0 = Math.max(1, Math.floor(gy * ch));
            const y1 = Math.min(h - 1, Math.floor((gy + 1) * ch));
            for (let gx = 0; gx < GRID_COLS; gx++) {
                const x0 = Math.max(1, Math.floor(gx * cw));
                const x1 = Math.min(w - 1, Math.floor((gx + 1) * cw));
                let n = 0, sum = 0, sq = 0;
                for (let y = y0; y < y1; y++) {
                    const base = y * w;
                    for (let x = x0; x < x1; x++) {
                        const v = gray[base + x];
                        n++; sum += v; sq += v * v;
                    }
                }
                if (n < 60) continue;
                total++;
                const mean = sum / n;
                if (mean < lo) lo = mean;
                if (mean > hi) hi = mean;
                if (Math.sqrt(Math.max(0, sq / n - mean * mean)) < CELL_BUSY_STD) continue;
                busy++;
                if (gx < c0) c0 = gx;
                if (gx > c1) c1 = gx;
                if (gy < r0) r0 = gy;
                if (gy > r1) r1 = gy;
            }
        }

        const contrast = hi - lo;

        // Serat saja belum cukup: meja bergaris, karpet, atau kain bermotif
        // membuat SEMUA sel sibuk. Kartu selalu punya dua hal sekaligus, teks
        // hitam dan badan plastik putih, jadi rentang terangnya besar. Latar
        // bermotif biasanya cuma main di satu kisaran. Tanpa uji ini bingkai
        // yang diarahkan ke meja polos-berpola dianggap kartu dan memotret diri
        // sendiri.
        //
        // Kartu yang benar-benar polos (terlalu blur, terlalu jauh) tidak punya
        // sel sibuk sama sekali; itu bukan kartu, itu meja kosong.
        if (busy < AUTO_DETECT.minBusyCells || c1 - c0 < 3 || r1 - r0 < 2) {
            return { found: false, flat: false, busy: busy / (total || 1) };
        }
        if (contrast < MIN_CELL_CONTRAST) {
            return { found: false, flat: true, busy: busy / (total || 1), contrast: contrast };
        }

        // Catatan: kotak sel sibuk berasal dari teks+foto, dan KTP punya margin
        // kosong 3-5% di tiap sisi, jadi kartu yang benar-benar rapat mengukur
        // cakupan sekitar 0,75-1,00 -- bukan 1,00. Ambang minCoverage dipakai
        // relative ke situ, bukan kotak diperluas, karena perluasan satu sel
        // membuat kartu seukuran 80% bingkai lolos dan tidak ada lagi yang
        // memberi tahu pengguna untuk memajankan kartu.
        const wide = (c1 - c0 + 1) / GRID_COLS;
        const tall = (r1 - r0 + 1) / GRID_ROWS;
        return {
            found: true,
            coverage: wide * tall,
            dx: ((c0 + c1 + 1) / 2 - GRID_COLS / 2) / GRID_COLS,
            dy: ((r0 + r1 + 1) / 2 - GRID_ROWS / 2) / GRID_ROWS,
            wide: wide,
            tall: tall,
            busy: busy / (total || 1),
            contrast: contrast,
        };
    }

    function startAutoDetect() {
        stopAutoDetect();
        autoDetectFrame = null;
        autoDetectStable = 0;
        autoDetectPeakSharp = 0;
        autoDetectPosMs = 0;
        autoDetectStarted = Date.now();
        autoDetectTimer = setInterval(autoDetectTick, AUTO_DETECT.interval);
    }

    function stopAutoDetect() {
        if (autoDetectTimer) {
            clearInterval(autoDetectTimer);
            autoDetectTimer = null;
        }
        autoDetectFrame = null;
        autoDetectStable = 0;
        autoDetectPosMs = 0;
    }

    function autoDetectTick() {
        const video = document.getElementById('cameraPreview');
        const status = document.getElementById('cameraStatus');
        if (!cameraStream || !autoDetectTimer || video.videoWidth === 0 || autoCapturing) return;

        const frame = document.querySelector('.camera-frame');
        if (!frame) return;

        const rect = getHoleSourceRect(video, frame);
        if (!rect.sw || !rect.sh) return;

        const sampleW = AUTO_DETECT.sampleWidth;
        const sampleH = Math.max(1, Math.round(sampleW * (rect.sh / rect.sw)));

        const canvas = document.getElementById('cameraCanvas');
        canvas.width = sampleW;
        canvas.height = sampleH;
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, sampleW, sampleH);

        const data = ctx.getImageData(0, 0, sampleW, sampleH).data;
        const gray = new Uint8Array(sampleW * sampleH);
        for (let i = 0, p = 0; i < data.length; i += 4, p++) {
            gray[p] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        }

        const stats = analyzeFrame(gray, sampleW, sampleH);
        const pos = cardGrid(gray, sampleW, sampleH);

        let diffSum = -1;
        if (autoDetectFrame && autoDetectFrame.length === gray.length) {
            let diff = 0;
            for (let i = 0; i < gray.length; i++) diff += Math.abs(gray[i] - autoDetectFrame[i]);
            diffSum = diff / gray.length;
        }
        autoDetectFrame = gray.slice();

        // Autofokus HP butuh waktu menutup. Ambang ketajaman ikut bergerak:
        // puncak meluruh perlahan, jadi frame tertajam sejauh inilah patokannya.
        autoDetectPeakSharp = Math.max(stats.sharpness, autoDetectPeakSharp * 0.985);

        const brightnessOk = stats.mean >= AUTO_DETECT.minBrightness && stats.mean <= AUTO_DETECT.maxBrightness;
        const edgeOk = stats.edgeRatio >= AUTO_DETECT.minEdge && stats.edgeRatio <= AUTO_DETECT.maxEdge;
        const glareOk = stats.glare <= AUTO_DETECT.maxGlare;
        const sharpOk = stats.sharpness >= AUTO_DETECT.minSharpness
            && stats.sharpness >= autoDetectPeakSharp * AUTO_DETECT.sharpRatio;
        const stableOk = diffSum >= 0 && diffSum <= AUTO_DETECT.maxDiff;

        const sizeOk = pos.found && pos.coverage >= AUTO_DETECT.minCoverage;
        const centeredOk = pos.found && Math.abs(pos.dx) <= AUTO_DETECT.maxOffsetX && Math.abs(pos.dy) <= AUTO_DETECT.maxOffsetY;
        const posOk = sizeOk && centeredOk;
        // Cakupan sedikit di bawah ambang sering cuma efek lensa yang belum
        // mengunci (huruf meleleh, kotak sel sibuk menyusut). Tidak boleh
        // membuat foto otomatis mati total: selama kartu tertutup setengah
        // bingkai dan posisinya tengah, timer tetap jalan dan forceAfter di
        // bawah yang mengambil keputusannya.
        const almostOk = pos.found && centeredOk && pos.coverage >= AUTO_DETECT.minCoverage * 0.8;
        autoDetectPosMs = almostOk ? autoDetectPosMs + AUTO_DETECT.interval : 0;

        if (posOk && brightnessOk && edgeOk && glareOk && sharpOk && stableOk) {
            autoDetectStable++;
            if (autoDetectStable >= AUTO_DETECT.stableFrames) {
                status.textContent = 'Posisi sudah pas — memotret…';
                autoCapture();
            } else {
                const sisa = (AUTO_DETECT.stableFrames - autoDetectStable) * AUTO_DETECT.interval / 1000;
                status.textContent = 'Tahan posisi… ' + sisa.toFixed(1) + ' dtk';
            }
            return;
        }

        autoDetectStable = 0;

        // Posisi sudah benar lama-lama tapi autofokus tidak kunjung mengunci
        // (kartu terlalu dekat, lensa makro mentok). Lebih baik memotret
        // daripada menunggu tanpa ujung; tombol manual tetap ada.
        if (almostOk && brightnessOk && glareOk && autoDetectPosMs >= AUTO_DETECT.forceAfter) {
            status.textContent = 'Posisi sudah pas — memotret…';
            autoCapture();
            return;
        }

        let pesan;
        if (!pos.found && pos.flat) {
            pesan = 'Kartu tidak terbaca — cahaya kurang atau permukaannya memantul.';
        } else if (!pos.found) {
            pesan = 'Kartu belum terlihat di dalam bingkai.';
        } else if (!centeredOk) {
            // Ditentukan lebih dulu: kartu yang tergeser juga mengurangi
            // cakupan, tapi yang harus dilakukan pengguna tetap menggeser,
            // bukan memajukan.
            const arah = Math.abs(pos.dx) > AUTO_DETECT.maxOffsetX ? (pos.dx > 0 ? 'kiri' : 'kanan') : (pos.dy > 0 ? 'atas' : 'bawah');
            pesan = 'Geser kartu sedikit ke ' + arah + '.';
        } else if (pos.coverage < AUTO_DETECT.minCoverage) {
            pesan = 'Dekatkan kartu sampai memenuhi bingkai.';
        } else if (!brightnessOk) {
            pesan = 'Cahaya kurang atau terlalu terang. Cari tempat yang lebih terang.';
        } else if (!glareOk) {
            pesan = 'Ada pantulan cahaya — miringkan sedikit kartu atau jauhkan dari lampu.';
        } else if (!sharpOk) {
            pesan = 'Menunggu fokus — tahan HP diam sebentar.';
        } else if (!edgeOk) {
            pesan = 'Kartu belum terlihat jelas di dalam bingkai.';
        } else {
            pesan = 'Arahkan kartu identitas ke dalam bingkai, biarkan foto otomatis…';
        }
        if (Date.now() - autoDetectStarted > AUTO_DETECT.manualHintAfter) {
            pesan += ' Bisa juga tekan "Ambil Foto".';
        }
        status.textContent = pesan;
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

    // ========== FLASH (TORCH) ==========
    let torchOn = false;

    function torchTrack() {
        return cameraStream ? cameraStream.getVideoTracks()[0] : null;
    }

    function setupTorch() {
        const btn = document.getElementById('btnTorch');
        const track = torchTrack();
        const caps = track && track.getCapabilities ? track.getCapabilities() : {};
        const supported = caps && 'torch' in caps;
        btn.classList.toggle('d-none', !supported);
        if (!supported) torchOn = false;
    }

    function toggleTorch() {
        const btn = document.getElementById('btnTorch');
        const track = torchTrack();
        if (!track) { resetTorch(); return; }

        const next = !torchOn;
        track.applyConstraints({ advanced: [{ torch: next }] })
            .then(function () {
                torchOn = next;
                btn.classList.toggle('active', torchOn);
                const label = torchOn ? 'Matikan flash' : 'Nyalakan flash';
                btn.title = label;
                btn.setAttribute('aria-label', label);
            })
            .catch(function () {
                // Perangkat tidak mendukung torch sungguhan: sembunyikan tombol.
                resetTorch();
            });
    }

    function resetTorch() {
        torchOn = false;
        const btn = document.getElementById('btnTorch');
        btn.classList.add('d-none');
        btn.classList.remove('active');
        btn.title = 'Nyalakan flash';
        btn.setAttribute('aria-label', 'Nyalakan flash');
    }

    async function openCamera(btn) {
        const video = document.getElementById('cameraPreview');
        const status = document.getElementById('cameraStatus');
        const btnCapture = document.getElementById('btnCapture');
        const isKtp = btn.dataset.scan === 'foto_ktp';

        closeCamera();
        video.dataset.target = btn.dataset.scan;
        document.getElementById('ktpFrame').classList.toggle('hidden', !isKtp);
        document.getElementById('cameraModalTitle').textContent = isKtp ? 'Scan Identitas' : 'Scan Dokumen';
        document.getElementById('cameraModal').classList.add('active');
        btnCapture.disabled = true;
        status.textContent = isKtp ? 'Aktifkan kamera, lalu letakkan kartu di dalam bingkai (foto diri di kotak kanan)…' : 'Mengaktifkan kamera…';

        if (!window.isSecureContext || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (window.isSecureContext) {
                status.textContent = 'Browser tidak mendukung kamera. Gunakan upload file biasa.';
            } else {
                status.textContent = 'Kamera butuh koneksi aman (https://). ';
                const link = document.createElement('a');
                link.className = 'btn btn-sm btn-primary d-block mx-auto mt-2';
                link.href = 'https://' + location.hostname + ':8443' + location.pathname + location.search;
                link.innerHTML = '<i class="fas fa-lock me-1"></i>Buka lewat HTTPS';
                status.appendChild(link);
                const hint = document.createElement('div');
                hint.className = 'mt-1';
                hint.textContent = 'Jalankan "npm run https" di komputer, atau gunakan upload file biasa.';
                status.appendChild(hint);
            }
            return;
        }

        try {
            // Minta resolusi setinggi mungkin; banyak HP hanya memberi 640×480
            // kalau constraint terlalu longgar. Coba bertingkat agar tetap
            // berhasil di perangkat yang menolak opsi advanced.
            const attempts = [
                { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 }, advanced: [{ focusMode: 'continuous' }] }, audio: false },
                { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false },
                { video: { facingMode: 'environment', width: { ideal: 1280 } }, audio: false },
                { video: true, audio: false },
            ];
            let lastError = null;
            for (const constraints of attempts) {
                try {
                    cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                    break;
                } catch (err) {
                    lastError = err;
                }
            }
            if (!cameraStream) throw lastError || new Error('Kamera tidak tersedia');

            video.srcObject = cameraStream;
            await video.play();

            cameraTrack = cameraStream.getVideoTracks()[0] || null;
            imageCapture = null;
            if (typeof window.ImageCapture === 'function' && cameraTrack) {
                try {
                    imageCapture = new ImageCapture(cameraTrack);
                } catch (err) {
                    imageCapture = null;
                }
            }

            setupTorch();
            btnCapture.disabled = false;

            const settings = cameraTrack && cameraTrack.getSettings ? cameraTrack.getSettings() : {};
            if (isKtp && settings.width && settings.width < 1280) {
                status.textContent = 'Resolusi kamera rendah (' + settings.width + 'px). Hasil deteksi bisa kurang akurat.';
                setTimeout(startAutoDetect, 1800);
            } else if (isKtp) {
                startAutoDetect();
                status.textContent = 'Arahkan kartu identitas ke bingkai, foto otomatis saat posisi pas.';
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
        if (lbl) lbl.textContent = 'Nomor Identitas';
        showError('Silakan isi data diri secara manual.');
    }

    async function capturePhoto() {
        const video = document.getElementById('cameraPreview');
        const canvas = document.getElementById('cameraCanvas');
        const field = video.dataset.target;
        if (!cameraStream || !field || video.videoWidth === 0) return;

        stopAutoDetect();
        const frame = document.querySelector('.camera-frame');
        const isKtp = field === 'foto_ktp' && frame;

        // Sumber terbaik untuk OCR kartu adalah frame video yang baru saja
        // dinyatakan tajam oleh auto-detect. ImageCapture memang memberi lebih
        // banyak piksel, tetapi merekam momen berikutnya, setelah autofokus dan
        // tangan bergerak, sehingga hasilnya justru sering lebih lunak. Hanya
        // dipakai bila aliran video beresolusi kecil.
        let source = video;
        let srcW = video.videoWidth;
        let srcH = video.videoHeight;

        if (isKtp && imageCapture && srcW < 1000) {
            try {
                const bitmap = await imageCapture.takePhoto();
                if (bitmap && bitmap.width) {
                    const bitmapRatio = bitmap.width / bitmap.height;
                    const videoRatio = video.videoWidth / video.videoHeight;
                    if (Math.abs(bitmapRatio - videoRatio) < 0.05) {
                        source = bitmap;
                        srcW = bitmap.width;
                        srcH = bitmap.height;
                    } else if (bitmap.close) {
                        bitmap.close();
                    }
                }
            } catch (err) {
                source = video;
            }
        }

        if (isKtp) {
            // Crop sesuai bingkai panduan. Jangan di-upscale: resolusi asli
            // kamera sudah memberi detail terbaik untuk OCR.
            const rect = getHoleSourceRect(video, frame, srcW, srcH);
            canvas.width = Math.max(1, Math.round(rect.sw));
            canvas.height = Math.max(1, Math.round(rect.sh));
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(source, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, canvas.width, canvas.height);
        } else {
            canvas.width = srcW;
            canvas.height = srcH;
            canvas.getContext('2d').drawImage(source, 0, 0, srcW, srcH);
        }

        if (source !== video && source.close) source.close();

        // Simpan salinan resolusi penuh untuk OCR sebelum dikompresi ke JPEG.
        if (isKtp) {
            const ocrCanvas = document.createElement('canvas');
            ocrCanvas.width = canvas.width;
            ocrCanvas.height = canvas.height;
            ocrCanvas.getContext('2d').drawImage(canvas, 0, 0);
            lastCaptureCanvas = ocrCanvas;
        } else {
            lastCaptureCanvas = null;
        }

        canvas.toBlob(function(blob) {
            if (blob) {
                const input = document.querySelector('input[name="' + field + '"]');
                if (input) {
                    const file = new File([blob], field + '-scan.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    lastCaptureFile = file;
                    showScanPreview(field, URL.createObjectURL(file));
                    if (field === 'foto_ktp') {
                        setTimeout(runOcrKtp, 400);
                    }
                }
            }
            closeCamera();
        }, 'image/jpeg', 0.92);
    }

    function showScanPreview(field, url) {
        const input = document.querySelector('input[name="' + field + '"]');
        if (!input) return;
        const preview = input.closest('.upload-field').querySelector('.scan-preview');
        preview.innerHTML =
            '<div class="scan-thumb">' +
                '<img src="' + url + '" alt="Hasil scan" onclick="viewScan(\'' + field + '\')" title="Klik untuk lihat besar">' +
                '<button type="button" class="btn btn-sm btn-danger scan-remove" onclick="removeScan(\'' + field + '\')" title="Hapus">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</div>';
        updateUploadLabel(field);
    }

    function viewScan(field) {
        const input = document.querySelector('input[name="' + field + '"]');
        if (!input) return;
        const img = input.closest('.upload-field').querySelector('.scan-thumb img');
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
        const preview = input ? input.closest('.upload-field').querySelector('.scan-preview') : null;
        if (preview) preview.innerHTML = '';
        updateUploadLabel(field);
        if (field === 'foto_ktp') {
            lastCaptureCanvas = null;
            lastCaptureFile = null;
            setOcrStatus('', 'idle');
            updateOcrButton();
        }
    }

    function closeCamera() {
        stopAutoDetect();
        autoCapturing = false;
        resetTorch();
        document.getElementById('cameraFlash').classList.remove('active');
        if (cameraStream) {
            cameraStream.getTracks().forEach(function(t) { t.stop(); });
            cameraStream = null;
        }
        cameraTrack = null;
        imageCapture = null;
        document.getElementById('cameraPreview').srcObject = null;
        document.getElementById('ktpFrame').classList.add('hidden');
        document.getElementById('cameraModal').classList.remove('active');
    }

    // ========== OCR KARTU IDENTITAS ==========
    // Mesin, pra-proses gambar dan parser ada di public/js/ktp-ocr.js
    // (window.KtpOcr) supaya bisa diukur terpisah lewat halaman lab.

    let ocrBusy = false;
    const ocrFields = [
        { name: 'nik', label: 'Nomor Identitas', key: 'nik', max: 20 },
        { name: 'nama_peminjam', label: 'Nama', key: 'nama', max: 150 },
        { name: 'tempat_tanggal_lahir', label: 'TTL', key: 'ttl', max: 120 },
        { name: 'alamat', label: 'Alamat', key: 'alamatLengkap', max: 250 },
        { name: 'jabatan', label: 'Pekerjaan', key: 'pekerjaan', max: 100 },
    ];
    // Kolom yang sudah diketik pemohon tidak boleh ditimpa hasil OCR.
    const ocrTouched = new Set();

    function setOcrStatus(msg, type) {
        const el = document.getElementById('ocrStatus');
        el.textContent = msg;
        el.className = 'ocr-status ocr-' + (type || 'idle');
    }

    function updateOcrButton() {
        const input = document.getElementById('fotoKtpInput');
        const btn = document.getElementById('btnOcrKtp');
        const file = input && input.files && input.files[0];
        btn.disabled = !file || !file.type.startsWith('image/') || ocrBusy;
    }

    (function trackManualEdits() {
        for (const f of ocrFields) {
            const el = document.querySelector('[name="' + f.name + '"]');
            if (!el) continue;
            if (el.value.trim()) ocrTouched.add(f.name);
            el.addEventListener('input', function () {
                if (el.value.trim()) ocrTouched.add(f.name);
                else ocrTouched.delete(f.name);
            });
        }
    })();

    // Sumber OCR terbaik: salinan resolusi penuh dari kamera, yang bebas dari
    // kompresi JPEG tempat file input disimpan. Untuk unggahan manual, ya gambarnya.
    function ocrSource() {
        const input = document.getElementById('fotoKtpInput');
        const file = input && input.files && input.files[0];
        if (!file) return Promise.resolve(null);
        if (lastCaptureCanvas && file === lastCaptureFile) return Promise.resolve(lastCaptureCanvas);

        return new Promise(function (resolve, reject) {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('gambar tidak terbaca')); };
            img.src = url;
        });
    }

    async function runOcrKtp() {
        if (ocrBusy) return;
        const input = document.getElementById('fotoKtpInput');
        const file = input && input.files && input.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        if (!window.KtpOcr) {
            setOcrStatus('Mesin OCR tidak termuat. Isi data diri manual saja.', 'error');
            return;
        }

        ocrBusy = true;
        updateOcrButton();
        setOcrStatus('Membaca kartu…', 'busy');

        try {
            const source = await ocrSource();
            if (!source) { setOcrStatus('', 'idle'); return; }

            const result = await KtpOcr.recognize(source, {
                onStage: function (msg) { setOcrStatus(msg, 'busy'); },
            });

            const filled = [];
            const kept = [];
            for (const f of ocrFields) {
                const el = document.querySelector('[name="' + f.name + '"]');
                const value = String(result.fields[f.key] || '').slice(0, f.max);
                if (!el || !value) continue;
                if (ocrTouched.has(f.name)) { kept.push(f.label); continue; }
                el.value = value;
                filled.push(f.label);
            }

            if (filled.length) {
                let msg = 'Tertulis dari kartu: ' + filled.join(', ') + '. Mohon periksa sebelum lanjut.';
                if (kept.length) msg += ' Kolom yang Anda ketik sendiri (' + kept.join(', ') + ') tidak diubah.';
                setOcrStatus(msg, 'success');
            } else if (kept.length) {
                setOcrStatus('Kolom sudah Anda isi sendiri, jadi tidak ditimpa. Kosongkan kolom bila ingin diisi otomatis.', 'idle');
            } else {
                setOcrStatus('Belum ada yang terbaca. Foto kurang tajam atau kartu terlalu jauh — foto ulang atau isi manual.', 'error');
            }
        } catch (e) {
            setOcrStatus('OCR gagal: ' + (e && e.message ? e.message : e) + '. Silakan isi manual.', 'error');
        } finally {
            ocrBusy = false;
            updateOcrButton();
        }
    }

    // ========== DROP ZONE UPLOAD ==========
    const uploadLabels = {
        foto_ktp: { nameId: 'namaFotoKtp', empty: 'KTP / SIM / Kartu Pelajar (Maks. 2MB)' },
        surat_tugas: { nameId: 'namaSuratTugas', empty: 'PDF atau Gambar (Maks. 2MB)' }
    };

    function updateUploadLabel(field) {
        const input = document.querySelector('input[name="' + field + '"]');
        const cfg = uploadLabels[field];
        if (!input || !cfg) return;
        const nameEl = document.getElementById(cfg.nameId);
        const zone = input.closest('.file-drop-zone');
        const file = input.files && input.files[0];
        if (nameEl) {
            nameEl.textContent = file ? file.name : cfg.empty;
            nameEl.classList.toggle('text-blue-600', !!file);
            nameEl.classList.toggle('font-semibold', !!file);
            nameEl.classList.toggle('text-slate-400', !file);
        }
        if (zone) zone.classList.toggle('has-file', !!file);
    }

    function setupDropZone(field) {
        const input = document.querySelector('input[name="' + field + '"]');
        if (!input) return;
        const zone = input.closest('.file-drop-zone');
        if (!zone) return;

        input.addEventListener('change', function() {
            updateUploadLabel(field);
        });

        ['dragenter', 'dragover'].forEach(function(evt) {
            zone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('dragover');
            });
        });

        ['dragleave', 'dragend', 'drop'].forEach(function(evt) {
            zone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('dragover');
            });
        });

        zone.addEventListener('drop', function(e) {
            const files = e.dataTransfer && e.dataTransfer.files;
            if (!files || !files.length) return;
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    setupDropZone('foto_ktp');
    setupDropZone('surat_tugas');

    const fotoKtpInputEl = document.getElementById('fotoKtpInput');
    if (fotoKtpInputEl) {
        fotoKtpInputEl.addEventListener('change', function() {
            // Perubahan selalu berarti pilihan/seretan manual: salinan kamera
            // tidak lagi mewakili file yang sekarang terpasang.
            lastCaptureCanvas = null;
            lastCaptureFile = null;
            updateOcrButton();
            const file = this.files && this.files[0];
            if (file && file.type.startsWith('image/')) {
                runOcrKtp();
            } else {
                setOcrStatus('', 'idle');
            }
        });
    }

    updateOcrButton();

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

    // Buka modal scan kartu identitas otomatis saat pertama kali halaman form dimuat,
    // agar user memindai identitas terlebih dahulu sebelum mengisi form.
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

@include('partials.public-footer', ['compact' => true])

</body>
</html>
