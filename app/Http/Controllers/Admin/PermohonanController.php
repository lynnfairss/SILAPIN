<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\PermohonanStatusLog;
use App\Models\Instansi;
use Illuminate\Http\Request;
use App\Models\Inventaris;
use App\Models\DetailPermohonan;
use Illuminate\Support\Facades\DB;

class PermohonanController extends Controller
{
    public function index()
    {
        $permohonan = Permohonan::with('instansi', 'detailPermohonan.inventaris')->get();

        return view('admin.permohonan.index', compact('permohonan'));
    }

    public function show(Permohonan $permohonan)
    {
        $permohonan->load('instansi', 'detailPermohonan.inventaris', 'statusLogs.user');

        return view('admin.permohonan.show', compact('permohonan'));
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
            'inventaris'       => 'required|array|min:1',
            'inventaris.*'     => 'exists:inventaris,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ]);

        foreach ($request->inventaris as $inventarisId) {
            $item = Inventaris::find($inventarisId);
            $qty = $request->jumlah[$inventarisId] ?? 1;
            if (!$item || (int) $qty > (int) $item->stok) {
                return back()->withErrors([
                    'jumlah.' . $inventarisId => "Jumlah melebihi stok tersedia ({$item->nama_barang} = {$item->stok}).",
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $permohonan = Permohonan::create([
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

            foreach ($request->inventaris as $inventarisId) {
                DetailPermohonan::create([
                    'permohonan_id'  => $permohonan->id,
                    'inventaris_id'  => $inventarisId,
                    'jumlah'         => $request->jumlah[$inventarisId] ?? 1,
                ]);
            }

            $permohonan->statusLogs()->create([
                'status_lama' => null,
                'status_baru' => 'Menunggu',
                'catatan'     => 'Permohonan dibuat oleh admin.',
                'user_id'     => auth()->id(),
            ]);
        });

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil ditambahkan.');
    }

    public function edit(Permohonan $permohonan)
    {
        $instansi = Instansi::all();
        $inventaris = Inventaris::with('kategori')->where('stok', '>', 0)->get();
        $itemSelected = $permohonan->detailPermohonan->pluck('jumlah', 'inventaris_id');

        return view('admin.permohonan.edit', compact('permohonan', 'instansi', 'inventaris', 'itemSelected'));
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
            'inventaris'       => 'required|array|min:1',
            'inventaris.*'     => 'exists:inventaris,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ]);

        foreach ($request->inventaris as $inventarisId) {
            $item = Inventaris::find($inventarisId);
            $qty = $request->jumlah[$inventarisId] ?? 1;
            if (!$item || (int) $qty > (int) $item->stok) {
                return back()->withErrors([
                    'jumlah.' . $inventarisId => "Jumlah melebihi stok tersedia ({$item->nama_barang} = {$item->stok}).",
                ])->withInput();
            }
        }

        $statusLama = $permohonan->status;

        DB::transaction(function () use ($request, $permohonan, $statusLama) {
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

            $permohonan->detailPermohonan()->delete();
            foreach ($request->inventaris as $inventarisId) {
                DetailPermohonan::create([
                    'permohonan_id'  => $permohonan->id,
                    'inventaris_id'  => $inventarisId,
                    'jumlah'         => $request->jumlah[$inventarisId] ?? 1,
                ]);
            }

            PermohonanStatusLog::create([
                'permohonan_id' => $permohonan->id,
                'status_lama'   => $statusLama,
                'status_baru'   => $statusLama,
                'catatan'       => 'Surat / permohonan diperbarui oleh admin.',
                'user_id'       => auth()->id(),
            ]);
        });

        return redirect()->route('permohonan.index')
            ->with('success', 'Surat / permohonan berhasil diubah.');
    }

    public function updateStatus(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'catatan_admin' => $request->status === 'Ditolak' ? 'required|string' : 'nullable|string',
        ]);

        $statusLama = $permohonan->status;

        DB::transaction(function () use ($request, $permohonan, $statusLama) {
            $permohonan->update([
                'status' => $request->status,
                'catatan_admin' => $request->catatan_admin,
            ]);

            PermohonanStatusLog::create([
                'permohonan_id' => $permohonan->id,
                'status_lama'   => $statusLama,
                'status_baru'   => $request->status,
                'catatan'       => $request->catatan_admin,
                'user_id'       => auth()->id(),
            ]);
        });

        $pesan = $request->status === 'Disetujui'
            ? 'Permohonan berhasil disetujui.'
            : 'Permohonan berhasil ditolak.';

        return redirect()->route('permohonan.index')
            ->with('success', $pesan);
    }

    public function destroy(Permohonan $permohonan)
    {
        $permohonan->delete();

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil dihapus.');
    }
}