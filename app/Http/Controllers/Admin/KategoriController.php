<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Menampilkan daftar kategori
     */
    public function index()
    {
        $kategori = Kategori::latest()->paginate(10);

        return view('admin.kategori.index', compact('kategori'));
    }

    /**
     * Menyimpan kategori baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100',
            'keterangan'    => 'nullable|max:255',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil ditambahkan.');
    }

    /**
     * Update kategori
     */
    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100',
            'keterangan'    => 'nullable|max:255',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori
     */
    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil dihapus.');
    }
}