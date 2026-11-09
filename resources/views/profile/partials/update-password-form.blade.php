@php
    $inputClass = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 pr-10 text-xs text-slate-700 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20';
    $labelClass = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-500';
    $errorClass = 'mt-1 text-[11px] font-medium text-rose-600';
    $toggleClass = 'absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600';
@endphp

<form method="post" action="{{ route('password.update') }}" data-loading-submit class="space-y-5">
    @csrf
    @method('put')

    <div>
        <label for="update_password_current_password" class="{{ $labelClass }}">Kata Sandi Saat Ini <span class="text-rose-500">*</span></label>
        <div class="relative">
            <input type="password" id="update_password_current_password" name="current_password"
                   autocomplete="current-password" placeholder="Masukkan kata sandi saat ini"
                   class="{{ $inputClass }} @if($errors->updatePassword->has('current_password')) border-rose-300 focus:border-rose-400 focus:ring-rose-500/20 @endif">
            <button type="button" class="{{ $toggleClass }}" data-toggle-password="update_password_current_password"
                    aria-label="Tampilkan kata sandi" tabindex="-1">
                <i class="fas fa-eye text-[11px]"></i>
            </button>
        </div>
        @if($errors->updatePassword->has('current_password'))
            <p class="{{ $errorClass }}">{{ $errors->updatePassword->first('current_password') }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="update_password_password" class="{{ $labelClass }}">Kata Sandi Baru <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input type="password" id="update_password_password" name="password"
                       autocomplete="new-password" placeholder="Minimal 8 karakter"
                       class="{{ $inputClass }} @if($errors->updatePassword->has('password')) border-rose-300 focus:border-rose-400 focus:ring-rose-500/20 @endif">
                <button type="button" class="{{ $toggleClass }}" data-toggle-password="update_password_password"
                        aria-label="Tampilkan kata sandi" tabindex="-1">
                    <i class="fas fa-eye text-[11px]"></i>
                </button>
            </div>
            @if($errors->updatePassword->has('password'))
                <p class="{{ $errorClass }}">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="{{ $labelClass }}">Konfirmasi Kata Sandi <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input type="password" id="update_password_password_confirmation" name="password_confirmation"
                       autocomplete="new-password" placeholder="Ulangi kata sandi baru"
                       class="{{ $inputClass }}">
                <button type="button" class="{{ $toggleClass }}" data-toggle-password="update_password_password_confirmation"
                        aria-label="Tampilkan kata sandi" tabindex="-1">
                    <i class="fas fa-eye text-[11px]"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3 text-[11px] leading-relaxed text-slate-500">
        <i class="fas fa-lightbulb mr-1 text-amber-500"></i>
        Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol. Hindari memakai kata sandi yang sama dengan akun lain.
    </div>

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
        <button type="submit" data-submit-button
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-violet-600/20 transition hover:bg-violet-700">
            <i class="fas fa-key text-[11px]"></i> Perbarui Kata Sandi
        </button>

        @if (session('status') === 'password-updated')
            <p class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-600">
                <i class="fas fa-check-circle"></i> Tersimpan.
            </p>
        @endif
    </div>
</form>
