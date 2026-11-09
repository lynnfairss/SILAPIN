@php
    $inputClass = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20';
    $labelClass = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-500';
    $errorClass = 'mt-1 text-[11px] font-medium text-rose-600';
@endphp

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" data-loading-submit class="space-y-5">
    @csrf
    @method('patch')

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="{{ $labelClass }}">Nama Lengkap <span class="text-rose-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                   required autofocus autocomplete="name" maxlength="255"
                   placeholder="Masukkan nama lengkap"
                   class="{{ $inputClass }} @error('name') border-rose-300 focus:border-rose-400 focus:ring-rose-500/20 @enderror">
            @error('name')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="{{ $labelClass }}">Alamat Email <span class="text-rose-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                   required autocomplete="username" maxlength="255"
                   placeholder="nama@instansi.go.id"
                   class="{{ $inputClass }} @error('email') border-rose-300 focus:border-rose-400 focus:ring-rose-500/20 @enderror">
            @error('email')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>
    </div>

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[11px] leading-relaxed text-amber-700">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-triangle mt-0.5"></i>
                <div>
                    <p>Alamat email Anda belum terverifikasi.</p>
                    <button form="send-verification"
                            class="mt-1 font-semibold text-amber-800 underline decoration-amber-400 underline-offset-2 transition hover:text-amber-900">
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-emerald-700">
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
        <button type="submit" data-submit-button
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700">
            <i class="fas fa-save text-[11px]"></i> Simpan Perubahan
        </button>

        @if (session('status') === 'profile-updated')
            <p class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-600">
                <i class="fas fa-check-circle"></i> Tersimpan.
            </p>
        @endif

        <p class="ml-auto text-[10px] text-slate-400">
            <i class="fas fa-info-circle mr-1"></i>
            Mengubah email akan mereset status verifikasi.
        </p>
    </div>
</form>
