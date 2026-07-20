<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use Illuminate\Http\Request;

class InstansiController extends Controller
{
    /**
     * Menampilkan daftar instansi
     */
    public function index()
    {
        $instansi = Instansi::latest()->paginate(10);

        return view('admin.instansi.index', compact('instansi'));
    }

    /**
     * Menampilkan form tambah instansi
     */
    public function create()
    {
        return view('admin.instansi.create');
    }

    /**
     * Menyimpan data instansi
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20',
        ]);

        Instansi::create([
            'nama_instansi' => $request->nama_instansi,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit
     */
    public function edit(Instansi $instansi)
    {
        return view('admin.instansi.edit', compact('instansi'));
    }

    /**
     * Update data
     */
    public function update(Request $request, Instansi $instansi)
    {
        $request->validate([
            'nama_instansi' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20',
        ]);

        $instansi->update([
            'nama_instansi' => $request->nama_instansi,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil diperbarui.');
    }

    /**
     * Menghapus data
     */
    public function destroy(Instansi $instansi)
    {
        $instansi->delete();

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil dihapus.');
    }
}