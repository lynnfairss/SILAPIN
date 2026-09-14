<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    public function index()
    {
        return view('admin.pengembalian.index');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'nomor_permohonan' => 'required|string|exists:permohonans,nomor_permohonan',
            'catatan'          => 'required|string',
        ]);

        $permohonan = Permohonan::where('nomor_permohonan', $request->nomor_permohonan)->firstOrFail();

        if (!in_array($permohonan->status, ['Disetujui', 'Dipinjam'])) {
            return back()
                ->with('error', 'Permohonan ini tidak dapat dikembalikan karena status saat ini: ' . $permohonan->status);
        }

        $statusLama = $permohonan->status;

        DB::transaction(function () use ($permohonan, $request, $statusLama) {
            $permohonan->update([
                'status' => 'Dikembalikan',
                'catatan_admin' => $request->catatan,
            ]);

            $permohonan->statusLogs()->create([
                'status_lama' => $statusLama,
                'status_baru' => 'Dikembalikan',
                'catatan' => $request->catatan ?? 'Barang dikembalikan oleh admin',
                'user_id' => auth()->id(),
            ]);

            foreach ($permohonan->detailPermohonan as $detail) {
                $detail->inventaris->increment('stok', $detail->jumlah);
            }

            $permohonan->update([
                'bukti_pengembalian' => $request->bukti_pengembalian ?? null,
                'tanggal_pengembalian' => now(),
            ]);
        });

        return redirect()->route('pengembalian.index')
            ->with('success', 'Barang berhasil dikembalikan. Status permohonan diubah menjadi Dikembalikan dan stok inventory dikembalikan.');
    }
}