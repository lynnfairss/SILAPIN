<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jenis;
use Illuminate\Http\Request;

class JenisController extends Controller
{
    public function index()
    {
        $jenis = Jenis::latest()->paginate(10);

        return view('admin.jenis.index', compact('jenis'));
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
