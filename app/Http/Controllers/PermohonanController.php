<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\DetailPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function form()
    {
        $instansi = Instansi::all();
        $inventaris = Inventaris::with('kategori')->where('stok', '>', 0)->get();

        return view('peminjam.index', compact('instansi', 'inventaris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_peminjam'    => 'required|string|max:150',
            'nik'              => 'required|string|max:20',
            'jabatan'          => 'nullable|string|max:100',
            'telepon'          => 'required|string|max:20',
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
        });

        $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
            ->where('nomor_permohonan', $nomor)
            ->first();

        return redirect()->route('peminjam.cek-status', ['nomor' => $nomor])
            ->with('success', 'Permohonan berhasil dikirim!');
    }

    public function cekStatus(Request $request)
    {
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
