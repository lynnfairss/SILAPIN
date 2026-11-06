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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PengembalianController extends Controller
{
    private const KONDISI = ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'];

    public function index(): View
    {
        $aktif = Permohonan::query()->whereIn('status', TransisiStatus::SEDANG_DIPAKAI);

        $stats = [
            'dipinjam' => (clone $aktif)->count(),
            'menunggu_kembali' => (clone $aktif)
                ->whereDate('tanggal_kembali', '>=', today())
                ->count(),
            'kembali_hari_ini' => Permohonan::where('status', TransisiStatus::DIKEMBALIKAN)
                ->whereDate('tanggal_pengembalian', today())
                ->count(),
            'terlambat' => (clone $aktif)
                ->whereDate('tanggal_kembali', '<', today())
                ->count(),
        ];

        $pinjamAktif = (clone $aktif)
            ->with('instansi')
            ->orderBy('tanggal_kembali')
            ->take(6)
            ->get();

        $riwayat = Permohonan::with('instansi')
            ->where('status', TransisiStatus::DIKEMBALIKAN)
            ->orderByDesc('tanggal_pengembalian')
            ->take(5)
            ->get();

        return view('admin.pengembalian.index', compact('stats', 'pinjamAktif', 'riwayat'));
    }

    public function proses(Request $request, StatusPermohonan $statusPermohonan): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_permohonan' => ['required', 'string', 'max:50', 'exists:permohonans,nomor_permohonan'],
            'kondisi_barang' => ['required', Rule::in(self::KONDISI)],
            'catatan' => ['required', 'string', 'max:1000'],
            'bukti_pengembalian' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $permohonan = Permohonan::where('nomor_permohonan', $validated['nomor_permohonan'])
            ->firstOrFail();

        $buktiPath = $request->hasFile('bukti_pengembalian')
            ? $request->file('bukti_pengembalian')->store('bukti-pengembalian', 'public')
            : null;

        try {
            $statusPermohonan->ubah(
                $permohonan,
                TransisiStatus::DIKEMBALIKAN,
                [
                    'kondisi_barang' => $validated['kondisi_barang'],
                    'catatan_admin' => $validated['catatan'],
                    'bukti_pengembalian' => $buktiPath,
                    'tanggal_pengembalian' => now(),
                ],
                $validated['catatan'],
            );
        } catch (TransisiStatusTidakValid $e) {
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }

            return back()->with('error', $e->getMessage());
        } catch (StokTidakCukup $e) {
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }

            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pengembalian.index')
            ->with('success', 'Barang berhasil dikembalikan. Status diubah menjadi Dikembalikan dan stok inventaris bertambah.');
    }
}
