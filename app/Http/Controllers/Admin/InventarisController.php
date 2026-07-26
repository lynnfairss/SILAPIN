<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventarisController extends Controller
{
    public function index()
    {
        $inventaris = Inventaris::with('kategori')
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('inventaris', 'public');
        }

        Inventaris::create([
            'kategori_id' => $request->kategori_id,
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'stok' => $request->stok,
            'kondisi' => $request->kondisi,
            'deskripsi' => $request->deskripsi,
            'foto' => $foto,
        ]);

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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'kategori_id' => $request->kategori_id,
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'stok' => $request->stok,
            'kondisi' => $request->kondisi,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('foto')) {
            if ($inventari->foto && Storage::disk('public')->exists($inventari->foto)) {
                Storage::disk('public')->delete($inventari->foto);
            }
            $data['foto'] = $request->file('foto')->store('inventaris', 'public');
        }

        $inventari->update($data);

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    public function destroy(Inventaris $inventari)
    {
        if ($inventari->foto && Storage::disk('public')->exists($inventari->foto)) {
            Storage::disk('public')->delete($inventari->foto);
        }

        $inventari->delete();

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil dihapus.');
    }
}
