<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Services\TransisiStatus;
use Illuminate\Http\Request;

class InstansiController extends Controller
{
    public function index()
    {
        $instansi = Instansi::latest()->paginate(10);

        return view('admin.instansi.index', compact('instansi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20|regex:/^[0-9]+$/',
        ]);

        Instansi::create([
            'nama_instansi' => $request->nama_instansi,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Instansi $instansi)
    {
        $request->validate([
            'nama_instansi' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20|regex:/^[0-9]+$/',
        ]);

        $instansi->update([
            'nama_instansi' => $request->nama_instansi,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        $dipakai = $instansi->permohonan()
            ->whereIn('status', TransisiStatus::SEDANG_DIPAKAI)
            ->count();

        if ($dipakai > 0) {
            return redirect()->route('instansi.index')
                ->with('error', "Instansi masih dipakai oleh {$dipakai} peminjaman yang sedang berjalan. Selesaikan atau pindahkan permohonan tersebut lebih dulu.");
        }

        // instansi_id sekarang ON DELETE SET NULL, jadi berkas lama tetap
        // utuh dan hanya kehilangan label instansi.
        $instansi->delete();

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil dihapus. Riwayat permohonan terkait tetap tersimpan tanpa nama instansi.');
    }
}
