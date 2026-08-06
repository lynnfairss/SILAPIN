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
            'hapus_foto' => 'nullable|string',
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
        // 1. Hapus foto yang ditandai lewat tombol ✕ (daftar id dipisah koma)
        $hapusIds = [];
        if ($request->filled('hapus_foto')) {
            $hapusIds = array_values(array_filter(array_map('intval', explode(',', $request->hapus_foto))));
        }

        foreach ($inventaris->fotos as $foto) {
            if (in_array($foto->id, $hapusIds)) {
                if (Storage::disk('public')->exists($foto->foto)) {
                    Storage::disk('public')->delete($foto->foto);
                }
                $foto->delete();
            }
        }

        // 2. Upload / ganti foto per slot (urutan 0-4)
        if ($request->hasFile('foto')) {
            $fotos = $request->file('foto');
            $inventaris->load('fotos');

            foreach ($fotos as $slot => $file) {
                if (!$file || !$file->isValid()) continue;

                $path = $file->store('inventaris', 'public');
                $slotInt = (int) $slot;

                $ada = $inventaris->fotos->firstWhere('urutan', $slotInt);
                if ($ada) {
                    if (Storage::disk('public')->exists($ada->foto)) {
                        Storage::disk('public')->delete($ada->foto);
                    }
                    $ada->update(['foto' => $path, 'urutan' => $slotInt]);
                } else {
                    InventarisFoto::create([
                        'inventaris_id' => $inventaris->id,
                        'foto' => $path,
                        'urutan' => $slotInt,
                    ]);
                }
            }
        }

        // 3. Kompak urutan agar "Foto 1" selalu foto pertama
        $inventaris->load('fotos');
        $inventaris->fotos->each(function ($f, $i) {
            $f->update(['urutan' => $i]);
        });

        // 4. Perbarui thumbnail dari foto pertama (atau null jika kosong)
        $first = $inventaris->fotos->first();
        $inventaris->update(['foto' => $first?->foto]);
    }
}
