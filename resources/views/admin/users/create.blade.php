@extends('layouts.admin-master')

@section('title', 'Tambah Admin')
@section('page_key', 'users')

@php
    // Semua class Tailwind ditulis literal agar ikut ter-compile oleh JIT.
    $inputBase = 'w-full rounded-xl border bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 transition focus:bg-white focus:outline-none focus:ring-2';
    $okCls = 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20';
    $badCls = 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20';
    $labelCls = 'mb-1 block font-semibold text-slate-700';
    $kartu = 'rounded-2xl border border-slate-200/80 bg-white shadow-sm';
@endphp

@section('content')

    {{-- ==================== JUDUL ==================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3.5">
            <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0F172A] text-blue-400 shadow-md">
                <i class="fas fa-user-plus text-lg"></i>
            </div>
            <div>
                <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('users.index') }}" class="transition hover:text-slate-600">Kelola User</a>
                    <span>/</span>
                    <span class="font-medium text-slate-600">Tambah Admin</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Tambah Akun Admin</h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    Buat akun administrator baru beserta NIP yang akan tercetak pada kolom &ldquo;Yang menyerahkan&rdquo; di surat.
                </p>
            </div>
        </div>

        <a href="{{ route('users.index') }}"
           class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100 md:self-auto">
            <i class="fas fa-arrow-left text-[11px]"></i> Kembali
        </a>
    </div>

    {{-- ==================== RINGKASAN ERROR ==================== --}}
    @if($errors->any())
        <div class="flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <div>
                <p class="font-semibold">Akun belum bisa disimpan.</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" autocomplete="off" class="space-y-6">
        @csrf

        {{-- ==================== DATA AKUN ==================== --}}
        <div class="{{ $kartu }} overflow-hidden">
            <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 p-5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-blue-400">
                    <i class="fas fa-id-card text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Data Akun</h2>
                    <p class="text-xs text-slate-500">Identitas administrator yang akan masuk ke panel SILAPIN.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                <div>
                    <label for="name" class="{{ $labelCls }}">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                           required maxlength="255" placeholder="Masukkan nama lengkap"
                           class="{{ $inputBase }} {{ $errors->has('name') ? $badCls : $okCls }}">
                    @error('name')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="{{ $labelCls }}">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           required maxlength="255" placeholder="nama@contoh.go.id"
                           class="{{ $inputBase }} {{ $errors->has('email') ? $badCls : $okCls }}">
                    @error('email')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nip" class="{{ $labelCls }}">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" id="nip" inputmode="numeric" pattern="\d{16,18}"
                           value="{{ old('nip') }}" required maxlength="18" placeholder="198001012005011001"
                           class="{{ $inputBase }} {{ $errors->has('nip') ? $badCls : $okCls }}">
                    @error('nip')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
                        16&ndash;18 digit. NIP ini yang dicetak pada kolom &ldquo;Yang menyerahkan&rdquo; di surat peminjaman.
                    </p>
                </div>

                <div>
                    <label for="role" class="{{ $labelCls }}">Role <span class="text-rose-500">*</span></label>
                    <select name="role" id="role" required
                            class="{{ $inputBase }} {{ $errors->has('role') ? $badCls : $okCls }}">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>
                                {{ $role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
                        <strong class="font-semibold text-slate-600">Admin</strong>: kelola permohonan, pengembalian, dan surat.
                        <strong class="font-semibold text-slate-600">Super Admin</strong>: seluruh hak akses Admin ditambah master data dan manajemen akun.
                    </p>
                </div>
            </div>
        </div>

        {{-- ==================== KATA SANDI ==================== --}}
        <div class="{{ $kartu }} overflow-hidden">
            <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 p-5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-blue-400">
                    <i class="fas fa-key text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Kata Sandi</h2>
                    <p class="text-xs text-slate-500">Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                <div>
                    <label for="password" class="{{ $labelCls }}">Kata Sandi <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" id="password" required
                           class="{{ $inputBase }} {{ $errors->has('password') ? $badCls : $okCls }}">
                    @error('password')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="{{ $labelCls }}">Konfirmasi Kata Sandi <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="{{ $inputBase }} {{ $errors->has('password_confirmation') ? $badCls : $okCls }}">
                    @error('password_confirmation')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4">
                <p class="flex items-start gap-2 text-[11px] leading-relaxed text-slate-500">
                    <i class="fas fa-info-circle mt-0.5 text-blue-500"></i>
                    Setelah akun dibuat, pengguna dapat mengaktifkan Passkey dan 2FA dari menu Keamanan.
                </p>
            </div>
        </div>

        {{-- ==================== AKSI ==================== --}}
        <div class="flex flex-col-reverse items-stretch gap-2.5 sm:flex-row sm:items-center sm:justify-end">
            <a href="{{ route('users.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                <i class="fas fa-times text-[11px]"></i> Batal
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700">
                <i class="fas fa-check text-[11px]"></i> Simpan Akun
            </button>
        </div>
    </form>

@endsection
