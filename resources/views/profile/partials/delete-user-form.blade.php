<div class="space-y-4">
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[11px] leading-relaxed text-rose-700">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <p>
                Menghapus akun bersifat permanen. Seluruh data dan sumber daya yang terkait akan hilang
                dan tidak dapat dipulihkan kembali.
            </p>
        </div>
    </div>

    <button type="button" onclick="AdminUI.openModal('deleteAccountModal')"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-medium text-white shadow-md shadow-rose-600/20 transition hover:bg-rose-700">
        <i class="fas fa-trash-alt text-[11px]"></i> Hapus Akun Saya
    </button>

    <p class="text-center text-[10px] text-slate-400">
        Anda akan diminta memasukkan kata sandi untuk konfirmasi.
    </p>
</div>
