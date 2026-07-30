<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\InventarisFoto;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventarisController extends Controller
{
    public function index()
    {
        $inventaris = Inventaris::with('kategori', 'fotos')
            ->latest()
            ->paginate(10);

        $kategori = Kategori::all();

        return view('admin.inventaris.index', compact(
            'inventaris',
            'kategori'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required',
            'kode_barang' => 'required|unique:inventaris,kode_barang',
            'nama_barang' => 'required',
            'stok' => 'required|integer|min:1',
            'kondisi' => 'required',
            'deskripsi' => 'nullable',
            'foto' => 'nullable|array',
            'foto.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $inventaris = Inventaris::create([
            'kategori_id' => $request->kategori_id,
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'stok' => $request->stok,
            'kondisi' => $request->kondisi,
            'deskripsi' => $request->deskripsi,
        ]);

        $this->simpanFotos($request, $inventaris);

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil ditambahkan.');
    }

    public function update(Request $request, Inventaris $inventari)
    {
        $request->validate([
            'kategori_id' => 'required',
            'kode_barang' => 'required|unique:inventaris,kode_barang,' . $inventari->id,
            'nama_barang' => 'required',
            'stok' => 'required|integer|min:1',
            'kondisi' => 'required',
            'deskripsi' => 'nullable',
            'foto' => 'nullable|array',
            'foto.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $inventari->update([
            'kategori_id' => $request->kategori_id,
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'stok' => $request->stok,
            'kondisi' => $request->kondisi,
            'deskripsi' => $request->deskripsi,
        ]);

        $this->simpanFotos($request, $inventari);

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    public function destroy(Inventaris $inventari)
    {
        foreach ($inventari->fotos as $foto) {
            if (Storage::disk('public')->exists($foto->foto)) {
                Storage::disk('public')->delete($foto->foto);
            }
        }

        $inventari->delete();

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil dihapus.');
    }

    public function destroyFoto(InventarisFoto $foto)
    {
        if (Storage::disk('public')->exists($foto->foto)) {
            Storage::disk('public')->delete($foto->foto);
        }
        $foto->delete();

        return response()->json(['success' => true]);
    }

    private function simpanFotos(Request $request, Inventaris $inventaris)
    {
        if ($request->hasFile('foto')) {
            $fotos = $request->file('foto');

            foreach ($fotos as $urutan => $file) {
                if (!$file || !$file->isValid()) continue;

                $path = $file->store('inventaris', 'public');

                InventarisFoto::create([
                    'inventaris_id' => $inventaris->id,
                    'foto' => $path,
                    'urutan' => $urutan,
                ]);

                // Foto pertama jadi thumbnail
                if ($urutan === 0) {
                    $inventaris->update(['foto' => $path]);
                }
            }
        }
    }
}
