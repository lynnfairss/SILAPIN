<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\Instansi;
use Illuminate\Http\Request;
use App\Models\Inventaris;
use App\Models\DetailPermohonan;
use Illuminate\Support\Facades\DB;

class PermohonanController extends Controller
{
    public function index()
    {
        $permohonan = Permohonan::with('instansi')->get();

        return view('admin.permohonan.index', compact('permohonan'));
    }

    public function create()
    {
    $instansi = Instansi::all();
    $inventaris = Inventaris::where('stok', '>', 0)->get();

    return view(
        'admin.permohonan.create',
        compact('instansi', 'inventaris')
    );
    }

    public function store(Request $request)
    {
        $request->validate([
            'instansi_id'      => 'required',
            'nama_peminjam'    => 'required',
            'nik'              => 'required',
            'jabatan'          => 'nullable',
            'telepon'          => 'required',
            'tanggal_pinjam'   => 'required|date',
            'tanggal_kembali'  => 'required|date',
            'keperluan'        => 'required',
        ]);

        Permohonan::create([
            'instansi_id'      => $request->instansi_id,
            'nama_peminjam'    => $request->nama_peminjam,
            'nik'              => $request->nik,
            'jabatan'          => $request->jabatan,
            'telepon'          => $request->telepon,
            'tanggal_pinjam'   => $request->tanggal_pinjam,
            'tanggal_kembali'  => $request->tanggal_kembali,
            'keperluan'        => $request->keperluan,
            'status'           => 'Menunggu',
        ]);

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil ditambahkan.');
    }

    public function edit(Permohonan $permohonan)
    {
        $instansi = Instansi::all();

        return view('admin.permohonan.edit', compact('permohonan','instansi'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'instansi_id'      => 'required',
            'nama_peminjam'    => 'required',
            'nik'              => 'required',
            'jabatan'          => 'nullable',
            'telepon'          => 'required',
            'tanggal_pinjam'   => 'required|date',
            'tanggal_kembali'  => 'required|date',
            'keperluan'        => 'required',
        ]);

        $permohonan->update([
            'instansi_id'      => $request->instansi_id,
            'nama_peminjam'    => $request->nama_peminjam,
            'nik'              => $request->nik,
            'jabatan'          => $request->jabatan,
            'telepon'          => $request->telepon,
            'tanggal_pinjam'   => $request->tanggal_pinjam,
            'tanggal_kembali'  => $request->tanggal_kembali,
            'keperluan'        => $request->keperluan,
        ]);

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil diubah.');
    }

    public function destroy(Permohonan $permohonan)
    {
        $permohonan->delete();

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil dihapus.');
    }
}