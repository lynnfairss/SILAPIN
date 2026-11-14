@extends('layouts.admin-master')

@section('title', $user->isSuperAdmin() ? 'Edit Super Admin' : 'Edit Admin')
@section('page_key', 'users')

@php
    // Semua class Tailwind ditulis literal agar ikut ter-compile oleh JIT.
    $inputBase = 'w-full rounded-xl border bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 transition focus:bg-white focus:outline-none focus:ring-2';
    $okCls = 'border-slate-200 focus:border-blue-500 focus:ring-blue-500/20';
    $badCls = 'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-500/20';
    $labelCls = 'mb-1 block font-semibold text-slate-700';
    $kartu = 'rounded-2xl border border-slate-200/80 bg-white shadow-sm';

    $inisial = (function (string $nama): string {
        $kata = array_filter(preg_split('/\s+/', trim($nama)));
        $huruf = array_map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)), array_slice($kata, 0, 2));

        return $huruf ? implode('', $huruf) : '-';
    })($user->name);
@endphp

@section('content')

    {{-- ==================== JUDUL ==================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3.5">
            <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#0F172A] text-blue-400 shadow-md">
                <i class="fas fa-user-edit text-lg"></i>
            </div>
            <div>
                <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('users.index') }}" class="transition hover:text-slate-600">Kelola User</a>
                    <span>/</span>
                    <span class="font-medium text-slate-600">Edit Admin</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Edit Akun {{ $user->name }}</h1>
                <p class="mt-0.5 text-xs text-slate-500">
                    Perbarui identitas akun dan NIP yang tercetak pada kolom &ldquo;Yang menyerahkan&rdquo; di surat.
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
                <p class="font-semibold">Perubahan belum bisa disimpan.</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST" autocomplete="off" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ==================== DATA AKUN ==================== --}}
        <div class="{{ $kartu }} overflow-hidden">
            <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 p-5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-blue-400">
                    <i class="fas fa-id-card text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Data Akun</h2>
                    <p class="text-xs text-slate-500">Identitas administrator yang tersimpan di sistem SILAPIN.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
                <div>
                    <label for="name" class="{{ $labelCls }}">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                           required maxlength="255" placeholder="Masukkan nama lengkap"
                           class="{{ $inputBase }} {{ $errors->has('name') ? $badCls : $okCls }}">
                    @error('name')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="{{ $labelCls }}">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                           required maxlength="255" placeholder="nama@contoh.go.id"
                           class="{{ $inputBase }} {{ $errors->has('email') ? $badCls : $okCls }}">
                    @error('email')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nip" class="{{ $labelCls }}">NIP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nip" id="nip" inputmode="numeric" pattern="\d{16,18}"
                           value="{{ old('nip', $user->nip) }}" required maxlength="18" placeholder="198001012005011001"
                           class="{{ $inputBase }} {{ $errors->has('nip') ? $badCls : $okCls }}">
                    @error('nip')
                        <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
                        16&ndash;18 digit. NIP ini yang dicetak pada kolom &ldquo;Yang menyerahkan&rdquo; di surat peminjaman.
                    </p>
                </div>

                <div>
                    <span class="{{ $labelCls }}">Role</span>
                    <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#0F172A] text-[10px] font-bold text-white">
                            {{ $inisial }}
                        </span>
                        @if($user->isSuperAdmin())
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700 ring-1 ring-inset ring-indigo-200">
                                <i class="fas fa-shield-alt text-[9px]"></i> Super Admin
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-0.5 text-[11px] font-bold text-teal-700 ring-1 ring-inset ring-teal-200">
                                <i class="fas fa-user-check text-[9px]"></i> Admin
                            </span>
                        @endif
                        <i class="fas fa-lock ml-auto text-[10px] text-slate-400" title="Role tidak dapat diubah"></i>
                    </div>
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-400">
                        Role tidak dapat diubah dari halaman ini.
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4">
                <p class="flex items-start gap-2 text-[11px] leading-relaxed text-slate-500">
                    <i class="fas fa-info-circle mt-0.5 text-blue-500"></i>
                    Perubahan kata sandi, Passkey, dan 2FA dilakukan sendiri oleh pemilik akun melalui menu Keamanan.
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
                <i class="fas fa-check text-[11px]"></i> Simpan Perubahan
            </button>
        </div>
    </form>

@endsection
