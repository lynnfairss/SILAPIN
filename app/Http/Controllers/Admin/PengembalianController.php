<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Services\StatusPermohonan;
use App\Services\StokTidakCukup;
use App\Services\TransisiStatus;
use App\Services\TransisiStatusTidakValid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    public function index(): View
    {
        return view('admin.pengembalian.index');
    }

    public function proses(Request $request, StatusPermohonan $statusPermohonan): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_permohonan' => ['required', 'string', 'max:50', 'exists:permohonans,nomor_permohonan'],
            'catatan' => ['required', 'string', 'max:1000'],
            'bukti_pengembalian' => ['nullable', 'string', 'max:255'],
        ]);

        $permohonan = Permohonan::where('nomor_permohonan', $validated['nomor_permohonan'])
            ->firstOrFail();

        try {
            $statusPermohonan->ubah(
                $permohonan,
                TransisiStatus::DIKEMBALIKAN,
                [
                    'catatan_admin' => $validated['catatan'],
                    'bukti_pengembalian' => $validated['bukti_pengembalian'] ?? null,
                    'tanggal_pengembalian' => now(),
                ],
                $validated['catatan'],
            );
        } catch (TransisiStatusTidakValid $e) {
            return back()->with('error', $e->getMessage());
        } catch (StokTidakCukup $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pengembalian.index')
            ->with('success', 'Barang berhasil dikembalikan. Status diubah menjadi Dikembalikan dan stok inventaris bertambah.');
    }
}
