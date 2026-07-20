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
     * Menampilkan form tambah kategori
     */
    public function create()
    {
        return view('admin.kategori.create');
    }

    /**
     * Menyimpan data kategori
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100',
            'keterangan'    => 'nullable',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit
     */
    public function edit(Kategori $kategori)
    {
        return view('admin.kategori.edit', compact('kategori'));
    }

    /**
     * Mengupdate data kategori
     */
    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100',
            'keterangan'    => 'nullable',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil diperbarui.');
    }

    /**
     * Menghapus data kategori
     */
    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil dihapus.');
    }
}