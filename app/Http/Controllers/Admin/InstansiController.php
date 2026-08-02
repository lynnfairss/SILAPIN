<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
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
            'nama_instansi'   => 'required|max:100',
            'alamat'          => 'nullable',
            'telepon'         => 'nullable|max:20|regex:/^[0-9]+$/',
            'tipe_identitas'  => 'required|in:NIK,NRP,NIP,NDP/NRP',
        ]);

        Instansi::create([
            'nama_instansi'   => $request->nama_instansi,
            'alamat'          => $request->alamat,
            'telepon'         => $request->telepon,
            'tipe_identitas'  => $request->tipe_identitas,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Instansi $instansi)
    {
        $request->validate([
            'nama_instansi'   => 'required|max:100',
            'alamat'          => 'nullable',
            'telepon'         => 'nullable|max:20|regex:/^[0-9]+$/',
            'tipe_identitas'  => 'required|in:NIK,NRP,NIP,NDP/NRP',
        ]);

        $instansi->update([
            'nama_instansi'   => $request->nama_instansi,
            'alamat'          => $request->alamat,
            'telepon'         => $request->telepon,
            'tipe_identitas'  => $request->tipe_identitas,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        $instansi->delete();

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil dihapus.');
    }
}
