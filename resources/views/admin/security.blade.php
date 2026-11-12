@extends('layouts.admin-master')

@section('title', 'Keamanan Akun')
@section('page_key', 'security')

@php
    $aktif2fa = $user->twoFactorEnabled();
    $pending2fa = ! $aktif2fa && filled($user->two_factor_secret);
    $kodeBaru = session('recovery_codes');
    $sisaKode = $aktif2fa ? $user->recoveryCodeCount() : 0;
    $passkeyUtama = $passkeys->first();

    $kartu = 'rounded-2xl border border-slate-200/80 bg-white shadow-sm';
    $kepalaKartu = 'flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-5 py-4';
    $label = 'mb-1.5 block text-xs font-semibold text-slate-700';
@endphp

@section('content')

    {{-- ==================== JUDUL ==================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="flex items-start gap-3.5">
            <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0F172A] text-blue-400 shadow-md">
                <i class="fas fa-shield-alt text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Keamanan Akun</h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    Kelola autentikasi dua faktor dan perangkat passkey untuk melindungi akses administrator.
                </p>
            </div>
        </div>

        @if($aktif2fa)
            <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 md:self-auto">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Perlindungan Optimal Aktif
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 md:self-auto">
                <span class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
                Rekomendasi: Aktifkan 2FA
            </span>
        @endif
    </div>

    {{-- ==================== KARTU METRIK ==================== --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Status Keamanan Akun</p>
                <h3 class="mt-1 text-lg font-bold {{ $aktif2fa ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $aktif2fa ? 'Terlindungi Penuh' : 'Perlu Aktivasi 2FA' }}
                </h3>
                <p class="mt-1 text-[11px] text-slate-400">
                    @if($aktif2fa)
                        2FA aktif, {{ $passkeys->count() }} passkey terdaftar
                    @elseif($passkeyUtama)
                        Passkey aktif, belum ada lapisan OTP
                    @else
                        Belum ada lapisan verifikasi tambahan
                    @endif
                </p>
            </div>
            <div class="shrink-0 rounded-xl border p-3 {{ $aktif2fa ? 'border-emerald-100 bg-emerald-50 text-emerald-600' : 'border-amber-100 bg-amber-50 text-amber-600' }}">
                <i class="fas {{ $aktif2fa ? 'fa-shield-alt' : 'fa-exclamation-triangle' }} text-lg"></i>
            </div>
        </div>

        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Autentikasi Dua Faktor (2FA)</p>
                <div class="mt-1 flex items-center gap-2">
                    <h3 class="text-lg font-bold text-slate-900">{{ $aktif2fa ? 'Aktif' : 'Nonaktif' }}</h3>
                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $aktif2fa ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-600' }}">
                        TOTP App
                    </span>
                </div>
                <p class="mt-1 truncate text-[11px] text-slate-400">
                    @if($aktif2fa)
                        {{ $sisaKode }} dari {{ \App\Models\User::RECOVERY_CODE_COUNT }} kode pemulihan tersisa
                    @elseif($pending2fa)
                        Menunggu konfirmasi kode pertama
                    @else
                        Google Authenticator / Authy
                    @endif
                </p>
            </div>
            <div class="shrink-0 rounded-xl border border-blue-100 bg-blue-50 p-3 text-blue-600">
                <i class="fas fa-mobile-alt text-lg"></i>
            </div>
        </div>

        <div class="{{ $kartu }} flex items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Perangkat Passkey Terdaftar</p>
                <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $passkeys->count() }} Perangkat</h3>
                <p class="mt-1 truncate text-[11px] text-slate-400">
                    {{ $passkeyUtama ? 'Terakhir ditambahkan: ' . $passkeyUtama->name : 'Belum ada perangkat terdaftar' }}
                </p>
            </div>
            <div class="shrink-0 rounded-xl border border-indigo-100 bg-indigo-50 p-3 text-indigo-600">
                <i class="fas fa-fingerprint text-lg"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- ==================== KARTU 2FA ==================== --}}
        <div class="{{ $kartu }} flex flex-col lg:col-span-6">
            <div class="{{ $kepalaKartu }} rounded-t-2xl">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600">
                        <i class="fas fa-mobile-alt text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Autentikasi Dua Faktor (2FA)</h2>
                        <p class="text-[11px] text-slate-500">Verifikasi kode OTP berbasis waktu (TOTP)</p>
                    </div>
                </div>
                <span class="whitespace-nowrap rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $aktif2fa ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-600' }}">
                    {{ $aktif2fa ? '2FA Aktif' : 'Belum Aktif' }}
                </span>
            </div>

            <div class="flex-1 space-y-5 p-5">

                {{-- ---- 2FA aktif ---- --}}
                @if($aktif2fa)
                    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/80 p-4">
                        <i class="fas fa-check-circle mt-0.5 shrink-0 text-base text-emerald-600"></i>
                        <div>
                            <h3 class="text-xs font-bold text-emerald-900">Autentikasi Dua Faktor Telah Aktif</h3>
                            <p class="mt-1 text-xs leading-relaxed text-emerald-800">
                                Setiap masuk dengan kata sandi kini diminta kode 6 digit dari aplikasi authenticator,
                                atau salah satu kode pemulihan Anda.
                            </p>
                        </div>
                    </div>

                    {{-- Kode pemulihan --}}
                    <div class="space-y-2.5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                <i class="fas fa-key text-[11px] text-blue-600"></i> Kode Pemulihan
                            </span>
                            <span class="text-[10px] text-slate-500">
                                @if($kodeBaru)
                                    Simpan sekarang, hanya ditampilkan sekali
                                @else
                                    {{ $sisaKode }} kode masih berlaku
                                @endif
                            </span>
                        </div>

                        @if($kodeBaru)
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($kodeBaru as $kode)
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-center font-mono text-xs font-semibold text-slate-700">
                                        {{ $kode }}
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <button type="button" id="salinSemuaKode"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                    <i class="fas fa-copy text-[10px]"></i>
                                    <span>Salin Semua</span>
                                </button>
                                <p class="text-[11px] font-medium text-rose-600">
                                    Daftar ini tidak muncul lagi setelah halaman dimuat ulang.
                                </p>
                            </div>
                        @else
                            <p class="text-[11px] leading-relaxed text-slate-500">
                                Kode dipakai untuk masuk saat aplikasi authenticator tidak tersedia. Setiap kode hanya
                                bisa dipakai satu kali.
                            </p>
                            @if($sisaKode === 0)
                                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[11px] font-medium text-amber-700">
                                    Semua kode sudah terpakai. Buat daftar baru agar tetap bisa masuk tanpa authenticator.
                                </p>
                            @endif
                        @endif

                        <form method="POST" action="{{ route('security.2fa.recovery') }}" class="pt-1">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                                <i class="fas fa-sync-alt text-[10px]"></i>
                                {{ $kodeBaru ? 'Buat Ulang Daftar Kode' : ($sisaKode === 0 ? 'Buat Kode Pemulihan Baru' : 'Buat Kode Baru (kode lama tidak berlaku)') }}
                            </button>
                        </form>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                        <span class="text-[11px] text-slate-400">
                            Diaktifkan pada {{ $user->two_factor_confirmed_at?->translatedFormat('d M Y H:i') ?? '-' }}
                        </span>
                        <button type="button" id="tombolNonaktifkan"
                                class="rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                            Nonaktifkan 2FA
                        </button>
                    </div>

                {{-- ---- menunggu konfirmasi kode pertama ---- --}}
                @elseif($pending2fa)
                    <div class="flex items-start gap-3 rounded-xl border border-amber-200/90 bg-amber-50/80 p-3.5">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-sm text-amber-600"></i>
                        <p class="text-xs leading-relaxed text-amber-900">
                            Pindai kode QR di bawah menggunakan aplikasi <span class="font-bold">Google Authenticator</span>
                            atau <span class="font-bold">Authy</span>, lalu masukkan 6 digit kode OTP untuk menyelesaikan
                            aktivasi.
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center gap-4 py-1">
                        <div class="relative rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <span class="absolute left-2 top-2 h-3.5 w-3.5 rounded-tl-md border-l-2 border-t-2 border-blue-600"></span>
                            <span class="absolute right-2 top-2 h-3.5 w-3.5 rounded-tr-md border-r-2 border-t-2 border-blue-600"></span>
                            <span class="absolute bottom-2 left-2 h-3.5 w-3.5 rounded-bl-md border-b-2 border-l-2 border-blue-600"></span>
                            <span class="absolute bottom-2 right-2 h-3.5 w-3.5 rounded-br-md border-b-2 border-r-2 border-blue-600"></span>

                            <img src="data:image/svg+xml;base64,{{ base64_encode($user->getTwoFactorQrCode()) }}"
                                 alt="QR Code Autentikasi Dua Faktor" class="h-40 w-40">
                        </div>

                        <div class="w-full max-w-sm">
                            <p class="mb-1.5 text-center text-[11px] text-slate-500">
                                Tidak bisa memindai QR? Salin kunci manual berikut:
                            </p>
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2">
                                <code class="select-all break-all font-mono text-xs font-bold tracking-wider text-rose-600">{{ $user->two_factor_secret }}</code>
                                <button type="button" data-salin="{{ $user->two_factor_secret }}"
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                    <i class="fas fa-copy text-[10px]"></i>
                                    <span>Salin</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('security.2fa.confirm') }}" class="pt-1">
                        @csrf
                        <label for="kodeAktivasi" class="{{ $label }}">Kode Verifikasi OTP (6 Digit)</label>
                        <div class="flex flex-col gap-2.5 sm:flex-row">
                            <div class="relative flex-1">
                                <i class="fas fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input type="text" id="kodeAktivasi" name="code" inputmode="numeric" maxlength="6"
                                       autocomplete="one-time-code" placeholder="Contoh: 482910"
                                       class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 font-mono text-sm tracking-widest text-slate-800 placeholder:font-sans placeholder:text-xs placeholder:tracking-normal focus:outline-none focus:ring-2 @error('code') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-300 focus:border-blue-500 focus:ring-blue-500/20 @enderror">
                            </div>
                            <button type="submit"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700">
                                <i class="fas fa-check-circle text-[11px]"></i> Aktifkan 2FA
                            </button>
                        </div>
                        @error('code')
                            <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </form>

                {{-- ---- belum pernah diaktifkan ---- --}}
                @else
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                        <i class="fas fa-info-circle mt-0.5 shrink-0 text-sm text-indigo-600"></i>
                        <p class="text-xs leading-relaxed text-slate-600">
                            2FA menambahkan lapisan keamanan: selain kata sandi, Anda memasukkan kode sekali pakai dari
                            aplikasi <span class="font-semibold text-slate-800">Google Authenticator</span> atau
                            <span class="font-semibold text-slate-800">Authy</span>. Setelah aktif, halaman ini
                            menampilkan kode pemulihan cadangan.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('security.2fa.enable') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:w-auto">
                            <i class="fas fa-qrcode text-[11px]"></i> Mulai Aktivasi 2FA
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ==================== KARTU PASSKEY ==================== --}}
        <div class="{{ $kartu }} flex flex-col lg:col-span-6">
            <div class="{{ $kepalaKartu }} rounded-t-2xl">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-indigo-100 bg-indigo-50 text-indigo-600">
                        <i class="fas fa-fingerprint text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Passkey (Masuk Tanpa Kata Sandi)</h2>
                        <p class="text-[11px] text-slate-500">Autentikasi biometrik &amp; kunci keamanan perangkat</p>
                    </div>
                </div>
                <button type="button" id="tombolTambahPasskey"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                    <i class="fas fa-plus text-[11px]"></i> Tambah Passkey
                </button>
            </div>

            <div class="flex-1 space-y-4 p-5">
                <div class="flex items-start gap-3 rounded-xl border border-slate-200/80 bg-slate-50 p-3.5">
                    <i class="fas fa-info-circle mt-0.5 shrink-0 text-sm text-indigo-600"></i>
                    <p class="text-xs leading-relaxed text-slate-600">
                        Passkey memungkinkan Anda masuk dengan
                        <span class="font-semibold text-slate-800">sidik jari, pengenalan wajah, atau PIN perangkat</span>
                        tanpa mengetik kata sandi. Daftarkan passkey hanya dari perangkat pribadi yang Anda percaya.
                    </p>
                </div>

                <div class="admin-table-wrap overflow-hidden rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-[#0F172A] text-[10px] font-bold uppercase tracking-wider text-slate-200">
                            <tr>
                                <th class="px-4 py-3">Perangkat / Nama</th>
                                <th class="whitespace-nowrap px-4 py-3">Didaftarkan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="min-w-[80px] px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($passkeys as $key)
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-blue-600">
                                                <i class="fas fa-fingerprint text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-bold text-slate-800">{{ $key->name }}</p>
                                                <p class="truncate text-[10px] text-slate-400">
                                                    Digunakan: {{ $key->last_used_at?->diffForHumans() ?? 'belum pernah dipakai' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3.5 text-[11px] text-slate-600">
                                        {{ $key->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <button type="button" data-hapus-passkey
                                                data-url="{{ route('passkey.destroy', $key) }}"
                                                data-nama="{{ $key->name }}"
                                                title="Hapus Passkey"
                                                class="rounded-lg border border-rose-200 bg-rose-50 p-1.5 text-rose-600 transition-all hover:border-rose-600 hover:bg-rose-600 hover:text-white">
                                            <i class="fas fa-trash text-[11px]"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-xs text-slate-400">
                                        Belum ada passkey terdaftar. Klik "Tambah Passkey" untuk mendaftarkan perangkat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 rounded-b-2xl border-t border-slate-100 bg-slate-50/70 px-5 py-3 text-[11px] text-slate-500">
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-key text-slate-400"></i> Standar WebAuthn / FIDO2
                </span>
                <span>{{ $passkeys->count() }} perangkat terhubung ke akun ini</span>
            </div>
        </div>
    </div>

    {{-- ==================== AKTIVITAS PASSKEY ==================== --}}
    <div class="{{ $kartu }} overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-blue-400">
                    <i class="fas fa-clock text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Aktivitas Passkey</h2>
                    <p class="text-xs text-slate-500">Perangkat terdaftar dan terakhir kali dipakai masuk ke akun ini</p>
                </div>
            </div>
            <p class="text-[11px] leading-relaxed text-slate-400">
                Aplikasi ini belum mencatat riwayat login lengkap (IP &amp; browser), jadi tabel menampilkan kunci
                autentikasi yang tersimpan.
            </p>
        </div>

        <div class="admin-table-wrap">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#0F172A] text-[11px] font-bold uppercase tracking-wider text-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Perangkat / Autenticator</th>
                        <th class="whitespace-nowrap px-4 py-3.5">Jenis Kunci</th>
                        <th class="whitespace-nowrap px-4 py-3.5">Pencadangan</th>
                        <th class="whitespace-nowrap px-4 py-3.5">Terakhir Dipakai</th>
                        <th class="px-5 py-3.5 text-center">Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $isi)
                        <tr class="transition-colors hover:bg-slate-50/90">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-blue-600">
                                        <i class="fas fa-fingerprint text-[13px]"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">{{ $isi['nama'] }}</p>
                                        <p class="truncate text-[10px] text-slate-400">{{ $isi['autenticator'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-medium text-slate-600">{{ $isi['jenis'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-slate-500">{{ $isi['cadangan'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-[11px] text-slate-500">
                                @if($isi['terpakai'])
                                    {{ $isi['terpakai']->translatedFormat('d M Y H:i') }}
                                    <span class="block text-[10px] text-slate-400">{{ $isi['terpakai']->diffForHumans() }}</span>
                                @else
                                    <span class="text-slate-400">Belum pernah dipakai</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($isi['verifikasi'])
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                        <i class="fas fa-check text-[8px]"></i> Pengguna terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center whitespace-nowrap rounded-full border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                                        Tanpa verifikasi
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">
                                Belum ada aktivitas passkey. Daftarkan satu perangkat untuk masuk tanpa kata sandi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== MODAL NONAKTIFKAN 2FA ==================== --}}
    <div id="modalNonaktifkan" class="admin-modal hidden fixed inset-0 z-[80] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <form method="POST" action="{{ route('security.2fa.disable') }}"
              class="admin-modal-card w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
            @csrf
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-amber-600">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Nonaktifkan 2FA?</h3>
                    <p class="text-xs text-slate-500">Tingkat keamanan akun akan berkurang</p>
                </div>
            </div>

            <p class="text-xs leading-relaxed text-slate-600">
                Apakah Anda yakin ingin menonaktifkan Autentikasi Dua Faktor untuk akun
                <span class="font-bold text-slate-900">{{ $user->name }}</span>?
                Semua kode pemulihan juga akan dihapus.
            </p>

            <div>
                <label for="kodeNonaktifkan" class="{{ $label }}">Konfirmasi dengan kode OTP atau kode pemulihan</label>
                <input type="text" id="kodeNonaktifkan" name="code" maxlength="14" autocomplete="one-time-code"
                       placeholder="482910 atau SLPN-XXXX-XXXX" required
                       class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 font-mono text-xs tracking-wider text-slate-800 placeholder:font-sans placeholder:text-xs placeholder:tracking-normal focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                @error('code')
                    <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" data-modal-close
                        class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-rose-700">
                    Nonaktifkan
                </button>
            </div>
        </form>
    </div>

    {{-- ==================== MODAL TAMBAH PASSKEY ==================== --}}
    <div id="modalPasskey" class="admin-modal hidden fixed inset-0 z-[80] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-[#0F172A] px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <i class="fas fa-fingerprint text-blue-400"></i>
                    <h3 class="text-sm font-bold">Daftarkan Passkey Perangkat Baru</h3>
                </div>
                <button type="button" data-modal-close
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <form id="formPasskey" class="space-y-4 p-6">
                <p class="text-xs leading-relaxed text-slate-600">
                    Beri nama perangkat ini agar mudah dikenali di daftar keamanan. Setelah itu browser akan meminta
                    verifikasi biometrik atau PIN perangkat.
                </p>

                <div id="passkeyPeringatan" class="hidden rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5">
                    <p id="passkeyPeringatanTeks" class="text-[11px] leading-relaxed text-amber-800"></p>
                </div>

                <div>
                    <label for="passkeyNama" class="{{ $label }}">Nama Perangkat <span class="text-rose-500">*</span></label>
                    <input type="text" id="passkeyNama" name="name" maxlength="60" required
                           placeholder="Contoh: Laptop HP x360 atau HP Pribadi"
                           class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none">
                </div>

                <p class="text-[11px] leading-relaxed text-slate-400">
                    Jenis kunci (platform, sinkron, atau kunci fisik) dideteksi otomatis dari kredensial WebAuthn,
                    jadi tidak perlu dipilih manual.
                </p>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" data-modal-close
                            class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                        Batal
                    </button>
                    <button type="submit" id="tombolDaftarPasskey"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas fa-fingerprint text-[11px]"></i> Lanjutkan Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/passkeys.js')
    <script>
        (function () {
            'use strict';

            var flashStatus = @json(session('status'));
            var daftarKodeBaru = @json($kodeBaru);

            if (flashStatus === 'passkey-deleted') {
                AdminUI.toast('Passkey berhasil dihapus dari akun ini.', 'info');
            }

            /* ---------------- Salin ke clipboard ---------------- */

            function salin(teks, label, setelahSukses) {
                function selesai() {
                    AdminUI.toast((label || 'Teks') + ' berhasil disalin ke clipboard.', 'info');
                    if (setelahSukses) setelahSukses();
                }

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(teks).then(selesai, function () {
                        AdminUI.toast('Gagal menyalin. Salin manual dari layar.', 'error');
                    });
                    return;
                }

                var sementara = document.createElement('textarea');
                sementara.value = teks;
                sementara.setAttribute('readonly', 'readonly');
                sementara.style.position = 'fixed';
                sementara.style.left = '-9999px';
                document.body.appendChild(sementara);
                sementara.select();

                var berhasil = false;
                try {
                    berhasil = document.execCommand('copy');
                } catch (error) {
                    berhasil = false;
                }

                document.body.removeChild(sementara);

                if (berhasil) {
                    selesai();
                } else {
                    AdminUI.toast('Salin otomatis butuh HTTPS. Salin manual dari layar.', 'error');
                }
            }

            document.querySelectorAll('[data-salin]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    var label = tombol.querySelector('span');

                    salin(tombol.dataset.salin, 'Kunci manual', function () {
                        if (!label) return;

                        var asal = label.textContent;
                        label.textContent = 'Tersalin';
                        setTimeout(function () { label.textContent = asal; }, 2000);
                    });
                });
            });

            var tombolSalinKode = document.getElementById('salinSemuaKode');
            if (tombolSalinKode && daftarKodeBaru && daftarKodeBaru.length) {
                tombolSalinKode.addEventListener('click', function () {
                    salin(daftarKodeBaru.join('\n'), 'Kode pemulihan');
                });
            }

            /* ---------------- Modal nonaktifkan 2FA ---------------- */

            var tombolNonaktif = document.getElementById('tombolNonaktifkan');
            if (tombolNonaktif) {
                tombolNonaktif.addEventListener('click', function () {
                    AdminUI.openModal('modalNonaktifkan');
                    var input = document.getElementById('kodeNonaktifkan');
                    if (input) input.focus();
                });
            }

            @if($aktif2fa && $errors->has('code'))
                AdminUI.openModal('modalNonaktifkan');
            @endif

            /* ---------------- Passkey ---------------- */

            var rute = {
                options: @json(route('passkey.registration-options', absolute: false)),
                submit: @json(route('passkey.store', absolute: false)),
            };

            var formPasskey = document.getElementById('formPasskey');
            var inputNama = document.getElementById('passkeyNama');
            var tombolDaftar = document.getElementById('tombolDaftarPasskey');
            var peringatan = document.getElementById('passkeyPeringatan');
            var peringatanTeks = document.getElementById('passkeyPeringatanTeks');

            function tampilkanPeringatan(pesan) {
                if (!pesan) {
                    peringatan.classList.add('hidden');
                    return;
                }

                peringatanTeks.textContent = pesan;
                peringatan.classList.remove('hidden');
            }

            /* WebAuthn menolak alamat IP, jadi arahkan ke nama domain yang sah. */
            function peringatanHost() {
                var host = location.hostname;

                if (!/^\d{1,3}(\.\d{1,3}){3}$/.test(host)) {
                    if (location.protocol === 'https:' || host === 'localhost') {
                        return null;
                    }
                    return 'Passkey hanya berfungsi lewat https:// atau localhost. Halaman ini dibuka lewat '
                        + location.protocol + '// — gunakan alamat yang aman.';
                }

                if (/^127\./.test(host)) {
                    return 'Passkey tidak mendukung alamat IP. Buka halaman ini lewat http://localhost'
                        + (location.port ? ':' + location.port : '') + ' (bukan ' + host + ').';
                }

                return 'Passkey tidak mendukung alamat IP. Buka halaman ini lewat https://'
                    + host.split('.').join('-') + '.nip.io:8443 (domain pengganti untuk IP ' + host + ').';
            }

            document.getElementById('tombolTambahPasskey').addEventListener('click', function () {
                tampilkanPeringatan(peringatanHost());
                AdminUI.openModal('modalPasskey');
                if (inputNama) inputNama.focus();
            });

            if (formPasskey) {
                formPasskey.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    var nama = (inputNama.value || '').trim();

                    if (!nama) {
                        AdminUI.toast('Harap masukkan nama perangkat passkey.', 'error');
                        return;
                    }

                    var masalahHost = peringatanHost();
                    if (masalahHost) {
                        tampilkanPeringatan(masalahHost);
                        AdminUI.toast('Alamat ini tidak mendukung passkey.', 'error');
                        return;
                    }

                    if (!window.Passkeys) {
                        AdminUI.toast('Fitur passkey belum siap. Muat ulang halaman lalu coba lagi.', 'error');
                        return;
                    }

                    if (!window.Passkeys.isSupported()) {
                        AdminUI.toast('Browser atau perangkat ini tidak mendukung passkey.', 'error');
                        return;
                    }

                    tombolDaftar.disabled = true;
                    tombolDaftar.innerHTML = '<i class="fas fa-circle-notch fa-spin text-[11px]"></i> Menunggu verifikasi...';

                    try {
                        await window.Passkeys.register({ name: nama, routes: rute });
                        AdminUI.toast('Passkey "' + nama + '" berhasil didaftarkan!', 'success');
                        setTimeout(function () { window.location.reload(); }, 900);
                    } catch (error) {
                        AdminUI.toast(
                            typeof window.passkeyErrorMessage === 'function'
                                ? window.passkeyErrorMessage(error)
                                : 'Terjadi kesalahan. Silakan coba lagi.',
                            'error'
                        );

                        tombolDaftar.disabled = false;
                        tombolDaftar.innerHTML = '<i class="fas fa-fingerprint text-[11px]"></i> Lanjutkan Verifikasi';
                    }
                });
            }

            /* ---------------- Hapus passkey lewat modal hapus bersama ---------------- */

            document.querySelectorAll('[data-hapus-passkey]').forEach(function (tombol) {
                tombol.addEventListener('click', function () {
                    AdminUI.confirmDelete(tombol.dataset.url, 'passkey ' + tombol.dataset.nama);
                });
            });
        })();
    </script>
@endpush
