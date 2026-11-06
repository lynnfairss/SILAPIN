{{-- Modal ACC / Tolak: dipakai bersama oleh halaman daftar dan halaman detail permohonan.
     Pemicunya memakai atribut data-aksi-modal + data-action (lihat _aksi-scripts.blade.php). --}}

<div id="modalACC" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
        <form id="formACC" method="POST" action="" class="text-xs">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="Disetujui">

            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-emerald-600 p-1.5">
                        <i class="fas fa-check-circle text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Setujui Permohonan</h3>
                        <p class="text-[10px] text-slate-400">Stok barang akan berkurang setelah disetujui</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4 p-6">
                <p class="text-slate-600">Anda yakin ingin menyetujui permohonan ini?</p>

                <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-slate-50/60">
                    <div class="grid grid-cols-[110px_1fr] gap-3 px-4 py-2.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nama</span>
                        <span id="accNama" class="break-words font-semibold text-slate-800">—</span>
                    </div>
                    <div class="grid grid-cols-[110px_1fr] gap-3 px-4 py-2.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Barang</span>
                        <span id="accBarang" class="break-words text-slate-700">—</span>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">
                        Catatan <span class="font-normal text-slate-400">(opsional)</span>
                    </label>
                    <textarea name="catatan_admin" rows="2" placeholder="Tambahkan catatan jika perlu..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                <button type="button" data-modal-close
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit"
                        class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 font-medium text-white shadow-md shadow-emerald-600/20 transition-all hover:bg-emerald-700">
                    <i class="fas fa-check text-[11px]"></i>
                    <span>Ya, Setujui</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalTolak" class="admin-modal hidden fixed inset-0 z-[70] items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="admin-modal-card w-full max-w-md overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
        <form id="formTolak" method="POST" action="" class="text-xs">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="Ditolak">

            <div class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-rose-600 p-1.5">
                        <i class="fas fa-times-circle text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Tolak Permohonan</h3>
                        <p class="text-[10px] text-slate-400">Alasan penolakan wajib diisi</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-800 hover:text-white" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4 p-6">
                <p class="text-slate-600">Anda yakin ingin menolak permohonan ini?</p>

                <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-slate-50/60">
                    <div class="grid grid-cols-[110px_1fr] gap-3 px-4 py-2.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nama</span>
                        <span id="tolakNama" class="break-words font-semibold text-slate-800">—</span>
                    </div>
                    <div class="grid grid-cols-[110px_1fr] gap-3 px-4 py-2.5">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Barang</span>
                        <span id="tolakBarang" class="break-words text-slate-700">—</span>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block font-semibold text-slate-700">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan_admin" rows="3" required placeholder="Jelaskan alasan penolakan..."
                              class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 transition focus:border-rose-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                <button type="button" data-modal-close
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-medium text-slate-600 transition-all hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit"
                        class="flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2.5 font-medium text-white shadow-md shadow-rose-600/20 transition-all hover:bg-rose-700">
                    <i class="fas fa-times text-[11px]"></i>
                    <span>Ya, Tolak</span>
                </button>
            </div>
        </form>
    </div>
</div>
