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
<section class="bg-gradient-to-b from-brand-950 via-brand-900 to-slate-900 text-white pt-32 pb-24 relative overflow-hidden">
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

<main class="relative z-10 -mt-16 pb-20 px-4 sm:px-6 lg:px-8">
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
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-4 sm:p-6 mb-6">
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
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
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

                        <div class="p-6 sm:p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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
                            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <a href="{{ route('website') }}"
                                   class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 font-semibold text-xs tracking-wide transition-all inline-flex items-center justify-center gap-2">
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
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
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

                        <div class="p-6 sm:p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" id="barangContainer">
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
                            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <button type="button" onclick="goToStep(1)"
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 font-semibold text-xs tracking-wide transition-all inline-flex items-center justify-center gap-2">
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
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
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

                        <div class="p-6 sm:p-8 space-y-6">
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
                            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <button type="button" onclick="goToStep(2)"
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 hover:text-slate-800 font-semibold text-xs tracking-wide transition-all inline-flex items-center justify-center gap-2">
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
    const KTP_RATIO = 85.6 / 53.98; // rasio kartu identitas standar (ISO ID-1: KTP/SIM/KTM)

    // ===== AUTO-CAPTURE KARTU IDENTITAS =====
    const AUTO_DETECT = {
        interval: 100,       // ms antar sampling
        sampleWidth: 96,     // lebar canvas sampling (px)
        stableFrames: 2,     // jumlah sampel stabil berturut-turut sebelum capture (±0,2 detik)
        minBrightness: 120,  // kecerahan rata-rata minimum area bingkai
        maxBrightness: 235,  // kecerahan rata-rata maksimum
        minEdge: 0.08,       // rasio piksel tepi minimum (teks pada kartu)
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
                status.textContent = 'Kartu identitas terdeteksi — mengambil foto…';
                autoCapture();
            } else if (autoDetectStable === 1) {
                status.textContent = 'Kartu identitas terdeteksi…';
            }
        } else {
            autoDetectStable = 0;
            status.textContent = 'Arahkan kartu identitas ke bingkai, biarkan foto otomatis…';
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
        status.textContent = isKtp ? 'Aktifkan kamera, lalu letakkan kartu identitas di dalam bingkai…' : 'Mengaktifkan kamera…';

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
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 } },
                audio: false,
            });
            video.srcObject = cameraStream;
            await video.play();
            setupTorch();
            btnCapture.disabled = false;
            if (isKtp) {
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

    function capturePhoto() {
        const video = document.getElementById('cameraPreview');
        const canvas = document.getElementById('cameraCanvas');
        const field = video.dataset.target;
        if (!cameraStream || !field || video.videoWidth === 0) return;

        stopAutoDetect();
        const frame = document.querySelector('.camera-frame');
        const isKtp = field === 'foto_ktp' && frame;

        if (isKtp) {
            // Mode kartu identitas: crop tepat sesuai bingkai yang terlihat di viewfinder.
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
        document.getElementById('cameraPreview').srcObject = null;
        document.getElementById('ktpFrame').classList.add('hidden');
        document.getElementById('cameraModal').classList.remove('active');
    }

    // ========== OCR KARTU IDENTITAS (TESSERACT.JS) ==========

    let ocrWorker = null;
    let ocrBusy = false;

    // Tesseract dimuat saat pertama kali OCR dipakai, bukan saat halaman dibuka.
    let ocrScriptPromise = null;

    function ocrSupported() {
        return true;
    }

    function loadTesseractScript() {
        if (typeof Tesseract !== 'undefined' && Tesseract.createWorker) {
            return Promise.resolve();
        }
        if (ocrScriptPromise) return ocrScriptPromise;

        ocrScriptPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
            script.async = true;
            script.onload = () => resolve();
            script.onerror = () => {
                ocrScriptPromise = null;
                reject(new Error('Gagal memuat mesin OCR (butuh internet)'));
            };
            document.head.appendChild(script);
        });

        return ocrScriptPromise;
    }

    async function getOcrWorker() {
        if (ocrWorker) return ocrWorker;
        await loadTesseractScript();
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
        setOcrStatus('Mendeteksi data identitas…', 'busy');

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
                setOcrStatus('Data identitas tidak terdeteksi. Perbaiki foto lalu coba lagi, atau isi manual.', 'error');
            } else {
                setOcrStatus('Data terisi dari identitas (Nama, Nomor Identitas, Alamat, TTL). Mohon periksa kembali sebelum lanjut.', 'success');
            }
        } catch (e) {
            setOcrStatus('OCR gagal: ' + (e && e.message ? e.message : e) + '. Isi manual saja.', 'error');
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
