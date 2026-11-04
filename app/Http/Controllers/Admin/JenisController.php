<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\Jenis;
use Illuminate\Http\Request;

class JenisController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $jenis = Jenis::query()
            ->withCount('inventaris')
            ->with(['inventaris' => function ($query) {
                $query->select('id', 'jenis_id', 'kode_barang', 'nama_barang', 'stok', 'kondisi')
                    ->orderBy('nama_barang');
            }])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_jenis', 'like', "%{$q}%")
                        ->orWhere('keterangan', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $terpopuler = Jenis::withCount('inventaris')
            ->orderByDesc('inventaris_count')
            ->first();

        $stats = [
            'total' => Jenis::count(),
            'aset' => Inventaris::whereNotNull('jenis_id')->count(),
            'terpopuler' => $terpopuler?->nama_jenis ?? '-',
            'tanpa_aset' => Jenis::doesntHave('inventaris')->count(),
        ];

        return view('admin.jenis.index', compact('jenis', 'stats', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|max:150|unique:jensis,nama_jenis',
            'keterangan' => 'nullable|max:255',
        ]);

        Jenis::create([
            'nama_jenis' => $request->nama_jenis,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('jenis.index')
            ->with('success', 'Data jenis berhasil ditambahkan.');
    }

    public function update(Request $request, Jenis $jeni)
    {
        $request->validate([
            'nama_jenis' => 'required|max:150|unique:jensis,nama_jenis,' . $jeni->id,
            'keterangan' => 'nullable|max:255',
        ]);

        $jeni->update([
            'nama_jenis' => $request->nama_jenis,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('jenis.index')
            ->with('success', 'Data jenis berhasil diperbarui.');
    }

    public function destroy(Jenis $jeni)
    {
        // Set jenis_id pada inventaris terkait menjadi null lalu hapus
        $jeni->inventaris()->update(['jenis_id' => null]);
        $jeni->delete();

        return redirect()->route('jenis.index')
            ->with('success', 'Data jenis berhasil dihapus.');
    }
}
