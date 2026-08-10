<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Kategori;
use App\Models\DetailPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function form()
    {
        $instansi = Instansi::all();
        $kategori = Kategori::all();
        $inventaris = Inventaris::with('kategori', 'fotos')->where('stok', '>', 0)->get();

        $instansiTipe = $instansi->mapWithKeys(fn($item) => [$item->id => $item->effective_tipe_identitas]);

        return view('peminjam.index', compact('instansi', 'kategori', 'inventaris', 'instansiTipe'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peminjam'    => 'required|string|max:150',
            'nik'              => 'required|string|max:30',
            'jabatan'          => 'nullable|string|max:100',
            'telepon'          => 'required|string|max:15|regex:/^[0-9]+$/',
            'alamat'           => 'nullable|string|max:255',
            'tempat_tanggal_lahir' => 'nullable|string|max:120',
            'instansi_id'      => 'nullable',
            'nama_instansi_lain' => 'nullable|string|max:100',
            'tanggal_pinjam'   => 'required|date',
            'tanggal_kembali'  => 'required|date|after_or_equal:tanggal_pinjam',
            'keperluan'        => 'required|string',
            'foto_ktp'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'surat_tugas'      => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'inventaris'       => 'required|array|min:1',
            'inventaris.*'     => 'exists:inventaris,id',
            'jumlah'           => 'required|array',
            'jumlah.*'         => 'integer|min:1',
        ]);

        $fotoKtp = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoKtp = $request->file('foto_ktp')->store('foto-ktp', 'public');
        }

        $suratTugas = null;
        if ($request->hasFile('surat_tugas')) {
            $suratTugas = $request->file('surat_tugas')->store('surat-tugas', 'public');
        }

        $nomor = 'SP-' . strtoupper(date('dmy')) . '-' . strtoupper(substr(uniqid(), -6));

        DB::transaction(function () use ($request, $nomor, $fotoKtp, $suratTugas) {
            $permohonan = Permohonan::create([
                'nomor_permohonan'   => $nomor,
                'instansi_id'        => is_numeric($request->instansi_id) ? $request->instansi_id : null,
                'nama_instansi_lain' => $request->instansi_id === 'lainnya' ? $request->nama_instansi_lain : null,
                'nama_peminjam'      => $request->nama_peminjam,
                'nik'                => $request->nik,
                'jabatan'            => $request->jabatan,
                'telepon'            => $request->telepon,
                'alamat'             => $request->alamat,
                'tempat_tanggal_lahir' => $request->tempat_tanggal_lahir,
                'tanggal_pinjam'     => $request->tanggal_pinjam,
                'tanggal_kembali'    => $request->tanggal_kembali,
                'keperluan'          => $request->keperluan,
                'status'             => 'Menunggu',
                'foto_ktp'           => $fotoKtp,
                'surat_tugas'        => $suratTugas,
            ]);

            foreach ($request->inventaris as $key => $inventarisId) {
                $jml = $request->jumlah[$inventarisId] ?? 1;
                DetailPermohonan::create([
                    'permohonan_id'  => $permohonan->id,
                    'inventaris_id'  => $inventarisId,
                    'jumlah'         => $jml,
                ]);
            }

            \App\Models\PermohonanStatusLog::create([
                'permohonan_id' => $permohonan->id,
                'status_lama'   => null,
                'status_baru'   => 'Menunggu',
                'catatan'       => 'Permohonan diajukan oleh peminjam.',
                'user_id'       => null,
            ]);
        });

        $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
            ->where('nomor_permohonan', $nomor)
            ->first();

        return redirect()->route('peminjam.cek-status', ['nomor' => $nomor])
            ->with('success', 'Permohonan berhasil dikirim!');
    }

    public function cekStatus(Request $request)
    {
        if ($request->expectsJson()) {
            $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
                ->where('nomor_permohonan', $request->nomor)
                ->first();

            if (!$permohonan) {
                return response()->json([
                    'error' => 'Permohonan dengan nomor tersebut tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'nomor'           => $permohonan->nomor_permohonan,
                'nama'            => $permohonan->nama_peminjam,
                'instansi'        => $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-',
                'status'          => $permohonan->status,
                'tanggal_pinjam'  => $permohonan->tanggal_pinjam,
                'tanggal_kembali' => $permohonan->tanggal_kembali,
                'catatan'         => $permohonan->catatan_admin,
                'created_at'      => $permohonan->created_at?->format('d M Y H:i'),
                'barang'          => $permohonan->detailPermohonan->map(fn ($d) => [
                    'nama'   => $d->inventaris?->nama_barang ?? 'Barang #'.$d->inventaris_id,
                    'kode'   => $d->inventaris?->kode_barang ?? '',
                    'jumlah' => $d->jumlah,
                ]),
            ]);
        }

        $permohonan = null;

        if ($request->filled('nomor')) {
            $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
                ->where('nomor_permohonan', $request->nomor)
                ->first();

            if (!$permohonan) {
                return redirect()->route('peminjam.cek-status')
                    ->with('error', 'Permohonan dengan nomor "' . $request->nomor . '" tidak ditemukan.');
            }
        }

        return view('peminjam.cek-status', compact('permohonan'));
    }

    public function downloadSurat(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');

        return view('peminjam.surat', compact('permohonan'));
    }
}
