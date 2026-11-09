@extends('layouts.admin-master')

@section('title', 'Profil Saya')
@section('page_key', 'profile')

@php
    $initial = strtoupper(substr($user->name ?? 'S', 0, 1));
    $roleLabel = $user->isSuperAdmin()
        ? 'Super Admin'
        : ($user->isAdmin() ? 'Admin' : 'Staff');
    $rolePill = 'bg-blue-50 text-blue-700 ring-blue-200';
    $twoFactorEnabled = $user->twoFactorEnabled();
    $emailVerifiable = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail;
    $emailVerified = $emailVerifiable && ! is_null($user->email_verified_at);
    $passkeyCount = $user->passkeys()->count();
    $bergabung = $user->created_at
        ? $user->created_at->translatedFormat('d M Y')
        : '-';
@endphp

@section('content')
    {{-- ==================== HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('dashboard') }}" class="transition hover:text-slate-600">Dashboard</a>
                <span>/</span>
                <span class="font-medium text-slate-600">Profil Saya</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profil Saya</h1>
            <p class="mt-1 text-xs text-slate-500">
                Kelola informasi akun, kata sandi, dan keamanan akses panel admin Anda.
            </p>
        </div>

        <a href="{{ route('security') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
            <i class="fas fa-shield-alt text-[11px]"></i>
            <span>Kelola Keamanan</span>
        </a>
    </div>

    {{-- ==================== HERO PROFIL ==================== --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 p-6 shadow-lg sm:p-7">
        <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-indigo-500/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-400 text-2xl font-bold text-white shadow-lg shadow-blue-900/40 ring-1 ring-inset ring-white/20">
                    {{ $initial }}
                </div>

                <div class="min-w-0">
                    <h2 class="truncate text-xl font-bold text-white">{{ $user->name }}</h2>
                    <p class="mt-0.5 truncate text-xs text-slate-300">{{ $user->email }}</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-white ring-1 ring-inset ring-white/15">
                            <i class="fas fa-user-shield text-[9px]"></i> {{ $roleLabel }}
                        </span>

                        @if($emailVerifiable)
                            @if($emailVerified)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[10px] font-semibold text-emerald-300 ring-1 ring-inset ring-emerald-500/30">
                                    <i class="fas fa-check-circle text-[9px]"></i> Email terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-2.5 py-1 text-[10px] font-semibold text-amber-300 ring-1 ring-inset ring-amber-500/30">
                                    <i class="fas fa-exclamation-circle text-[9px]"></i> Email belum terverifikasi
                                </span>
                            @endif
                        @endif

                        @if($twoFactorEnabled)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 px-2.5 py-1 text-[10px] font-semibold text-emerald-300 ring-1 ring-inset ring-emerald-500/30">
                                <i class="fas fa-lock text-[9px]"></i> 2FA aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-semibold text-slate-300 ring-1 ring-inset ring-white/15">
                                <i class="fas fa-unlock text-[9px]"></i> 2FA nonaktif
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 sm:min-w-[22rem]">
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-center backdrop-blur">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Role</p>
                    <p class="mt-0.5 truncate text-xs font-semibold text-white">{{ $roleLabel }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-center backdrop-blur">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">NIP</p>
                    <p class="mt-0.5 truncate font-mono text-[11px] font-semibold text-white">{{ $user->nip ?: '-' }}</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-center backdrop-blur">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Bergabung</p>
                    <p class="mt-0.5 truncate text-xs font-semibold text-white">{{ $bergabung }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== KONTEN ==================== --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{-- Informasi profil --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <i class="fas fa-user-edit text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Informasi Profil</h2>
                        <p class="text-[10px] text-slate-400">Perbarui nama dan alamat email akun Anda</p>
                    </div>
                </div>

                <div class="p-5">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Ubah kata sandi --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm" id="updatePasswordCard">
                <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <i class="fas fa-key text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Ubah Kata Sandi</h2>
                        <p class="text-[10px] text-slate-400">Gunakan kata sandi panjang dan acak agar akun tetap aman</p>
                    </div>
                </div>

                <div class="p-5">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <aside class="space-y-5">
            {{-- Ringkasan akun --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <i class="fas fa-id-card text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Ringkasan Akun</h2>
                        <p class="text-[10px] text-slate-400">Data akun yang terdaftar di sistem</p>
                    </div>
                </div>

                <dl class="space-y-3 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="admin-detail-label">Nama</dt>
                        <dd class="text-right text-xs font-semibold text-slate-800">{{ $user->name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="admin-detail-label">Email</dt>
                        <dd class="max-w-[65%] break-all text-right text-xs font-medium text-slate-700">{{ $user->email }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="admin-detail-label">Role</dt>
                        <dd>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $rolePill }}">
                                {{ $roleLabel }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="admin-detail-label">NIP</dt>
                        <dd class="font-mono text-xs font-semibold text-slate-700">{{ $user->nip ?: '-' }}</dd>
                    </div>
                    @if($emailVerifiable)
                        <div class="flex items-start justify-between gap-3">
                            <dt class="admin-detail-label">Status Email</dt>
                            <dd>
                                @if($emailVerified)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                        <i class="fas fa-check-circle text-[9px]"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                        <i class="fas fa-exclamation-circle text-[9px]"></i> Belum
                                    </span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    <div class="flex items-start justify-between gap-3">
                        <dt class="admin-detail-label">Bergabung</dt>
                        <dd class="text-xs font-medium text-slate-700">{{ $bergabung }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Keamanan --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Keamanan</h2>
                        <p class="text-[10px] text-slate-400">Status perlindungan akun Anda</p>
                    </div>
                </div>

                <div class="space-y-3 p-5">
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5">
                        <div class="flex items-center gap-2 text-[11px] font-medium text-slate-600">
                            <i class="fas fa-mobile-alt text-slate-400"></i> Autentikasi 2FA
                        </div>
                        @if($twoFactorEnabled)
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">Nonaktif</span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2.5">
                        <div class="flex items-center gap-2 text-[11px] font-medium text-slate-600">
                            <i class="fas fa-fingerprint text-slate-400"></i> Passkey
                        </div>
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                            {{ $passkeyCount }} terdaftar
                        </span>
                    </div>

                    <a href="{{ route('security') }}"
                       class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                        <i class="fas fa-cog text-[11px]"></i> Atur Keamanan
                    </a>
                </div>
            </div>

            {{-- Zona berbahaya --}}
            <div class="rounded-2xl border border-rose-200/80 bg-white shadow-sm">
                <div class="flex items-center gap-2.5 border-b border-rose-100 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        <i class="fas fa-exclamation-triangle text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Zona Berbahaya</h2>
                        <p class="text-[10px] text-slate-400">Tindakan permanen pada akun</p>
                    </div>
                </div>

                <div class="p-5">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </aside>
    </div>

    {{-- ==================== MODAL HAPUS AKUN ==================== --}}
    <div id="deleteAccountModal" class="admin-modal hidden fixed inset-0 z-[80] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-rose-600 px-5 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3 class="text-sm font-bold">Hapus Akun</h3>
                </div>
                <button type="button" data-modal-close class="text-white/80 transition hover:text-white" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="space-y-4 p-5">
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[11px] leading-relaxed text-rose-700">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Setelah akun dihapus, seluruh data dan sumber daya yang terkait akan terhapus permanen.
                        Tindakan ini tidak dapat dibatalkan.
                    </div>

                    <div>
                        <label for="delete_password" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" id="delete_password" required autocomplete="current-password"
                               placeholder="Masukkan kata sandi Anda"
                               class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 placeholder-slate-400 transition focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        @if($errors->userDeletion->has('password'))
                            <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <p class="text-[11px] text-slate-500">
                        Dengan menghapus akun, Anda akan keluar dari panel admin secara otomatis.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3">
                    <button type="button" data-modal-close
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-rose-600/20 transition hover:bg-rose-700">
                        <i class="fas fa-trash-alt text-[11px]"></i> Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                @if(session('status') === 'profile-updated')
                    AdminUI.toast('Informasi profil berhasil diperbarui.', 'success');
                @endif

                @if(session('status') === 'password-updated')
                    AdminUI.toast('Kata sandi berhasil diperbarui.', 'success');
                @endif

                @if($errors->userDeletion->isNotEmpty())
                    AdminUI.openModal('deleteAccountModal');
                @endif

                @if($errors->updatePassword->isNotEmpty())
                    var passwordCard = document.getElementById('updatePasswordCard');
                    if (passwordCard) passwordCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                @endif

                document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var input = document.getElementById(button.dataset.togglePassword);
                        if (!input) return;

                        var show = input.type === 'password';
                        input.type = show ? 'text' : 'password';

                        var icon = button.querySelector('i');
                        if (icon) {
                            icon.className = show ? 'fas fa-eye-slash text-[11px]' : 'fas fa-eye text-[11px]';
                        }

                        button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    });
                });

                document.querySelectorAll('form[data-loading-submit]').forEach(function (form) {
                    form.addEventListener('submit', function () {
                        var button = form.querySelector('[data-submit-button]');
                        if (!button || button.disabled) return;

                        button.disabled = true;
                        button.classList.add('opacity-70', 'cursor-wait');
                        button.innerHTML = '<i class="fas fa-spinner fa-spin text-[11px]"></i> Menyimpan...';
                    });
                });
            });
        })();
    </script>
@endpush
