<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\PermohonanStatusLog;
use App\Models\Instansi;
use App\Services\NomorPermohonan;
use App\Services\StokTidakCukup;
use App\Services\StatusPermohonan;
use App\Services\TransisiStatus;
use App\Services\TransisiStatusTidakValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Inventaris;
use App\Models\DetailPermohonan;

class PermohonanController extends Controller
{
    public function index()
    {
        $permohonan = Permohonan::with('instansi', 'detailPermohonan.inventaris')->get();
        $pageTitle = 'Data Permohonan';
        $activeStatus = 'Semua';

        return view('admin.permohonan.index', compact('permohonan', 'pageTitle', 'activeStatus'));
    }

    public function byStatus(string $status)
    {
        $allowed = [
            'Menunggu'      => 'Permohonan Menunggu',
            'Disetujui'     => 'Permohonan Disetujui',
            'Ditolak'       => 'Permohonan Ditolak',
            'Dikembalikan'  => 'Permohonan Dikembalikan',
        ];

        if (!isset($allowed[$status])) {
            abort(404);
        }

        $query = Permohonan::with('instansi', 'detailPermohonan.inventaris')
            ->orderByDesc('tanggal_pinjam')
            ->latest();

        if ($status === 'Disetujui') {
            $query->whereIn('status', ['Disetujui', 'Dipinjam']);
        } else {
            $query->where('status', $status);
        }

        $permohonan = $query->get();
        $pageTitle = $allowed[$status];
        $activeStatus = $status;

        return view('admin.permohonan.index', compact('permohonan', 'pageTitle', 'activeStatus'));
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
        $validated = $request->validate($this->rulesPermohonan());

        if ($galat = $this->periksaStok($validated)) {
            return back()->withErrors($galat)->withInput();
        }

        DB::transaction(function () use ($validated) {
            $permohonan = Permohonan::create([
                'nomor_permohonan' => NomorPermohonan::generate(),
                'instansi_id'      => $validated['instansi_id'],
                'nama_peminjam'    => $validated['nama_peminjam'],
                'nik'              => $validated['nik'],
                'jabatan'          => $validated['jabatan'] ?? null,
                'telepon'          => $validated['telepon'],
                'tanggal_pinjam'   => $validated['tanggal_pinjam'],
                'tanggal_kembali'  => $validated['tanggal_kembali'],
                'keperluan'        => $validated['keperluan'],
                'status'           => TransisiStatus::MENUNGGU,
            ]);

            $this->simpanDetail($permohonan, $validated);

            $permohonan->statusLogs()->create([
                'status_lama' => null,
                'status_baru' => TransisiStatus::MENUNGGU,
                'catatan'     => 'Permohonan dibuat oleh admin.',
                'user_id'     => auth()->id(),
            ]);
        });

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil ditambahkan.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function rulesPermohonan(): array
    {
        return [
            'instansi_id'      => ['required', 'integer', 'exists:instansis,id'],
            'nama_peminjam'    => ['required', 'string', 'max:150'],
            'nik'              => ['required', 'string', 'max:20'],
            'jabatan'          => ['nullable', 'string', 'max:100'],
            'telepon'          => ['required', 'string', 'max:20'],
            'tanggal_pinjam'   => ['required', 'date'],
            'tanggal_kembali'  => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan'        => ['required', 'string', 'max:2000'],
            'inventaris'       => ['required', 'array', 'min:1', 'max:50'],
            'inventaris.*'     => ['integer', 'exists:inventaris,id', 'distinct'],
            'jumlah'           => ['required', 'array'],
            'jumlah.*'         => ['integer', 'min:1'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, string>  pesan galat, atau array kosong bila aman
     */
    private function periksaStok(array $validated): array
    {
        $galat = [];

        foreach ($validated['inventaris'] as $inventarisId) {
            $jumlah = (int) ($validated['jumlah'][$inventarisId] ?? 1);
            $barang = Inventaris::find($inventarisId);

            if (! $barang) {
                $galat['inventaris'] = 'Barang yang dipilih tidak ditemukan.';

                continue;
            }

            if ($jumlah > (int) $barang->stok) {
                $galat['jumlah.'.$inventarisId] = sprintf(
                    'Jumlah melebihi stok tersedia (%s = %d).',
                    $barang->nama_barang,
                    (int) $barang->stok,
                );
            }
        }

        return $galat;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function simpanDetail(Permohonan $permohonan, array $validated): void
    {
        foreach ($validated['inventaris'] as $inventarisId) {
            DetailPermohonan::create([
                'permohonan_id' => $permohonan->id,
                'inventaris_id' => $inventarisId,
                'jumlah'        => (int) ($validated['jumlah'][$inventarisId] ?? 1),
            ]);
        }
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
        $validated = $request->validate($this->rulesPermohonan());

        if (in_array($permohonan->status, TransisiStatus::SEDANG_DIPAKAI, true)) {
            return back()->with('error', 'Permohonan yang barangnya sudah keluar tidak dapat diedit. Gunakan menu Pengembalian.');
        }

        if ($galat = $this->periksaStok($validated)) {
            return back()->withErrors($galat)->withInput();
        }

        DB::transaction(function () use ($validated, $permohonan) {
            $permohonan->update([
                'instansi_id'      => $validated['instansi_id'],
                'nama_peminjam'    => $validated['nama_peminjam'],
                'nik'              => $validated['nik'],
                'jabatan'          => $validated['jabatan'] ?? null,
                'telepon'          => $validated['telepon'],
                'tanggal_pinjam'   => $validated['tanggal_pinjam'],
                'tanggal_kembali'  => $validated['tanggal_kembali'],
                'keperluan'        => $validated['keperluan'],
            ]);

            $permohonan->detailPermohonan()->delete();

            $this->simpanDetail($permohonan, $validated);

            PermohonanStatusLog::create([
                'permohonan_id' => $permohonan->id,
                'status_lama'   => $permohonan->status,
                'status_baru'   => $permohonan->status,
                'catatan'       => 'Surat / permohonan diperbarui oleh admin.',
                'user_id'       => auth()->id(),
            ]);
        });

        return redirect()->route('permohonan.index')
            ->with('success', 'Surat / permohonan berhasil diubah.');
    }

    public function updateStatus(Request $request, Permohonan $permohonan, StatusPermohonan $statusPermohonan)
    {
        $request->validate([
            'status' => ['required', Rule::in(TransisiStatus::SEMUA)],
            'catatan_admin' => [
                Rule::requiredIf($request->input('status') === TransisiStatus::DITOLAK),
                'nullable', 'string', 'max:1000',
            ],
        ]);

        $statusBaru = $request->string('status')->toString();

        try {
            $statusPermohonan->ubah(
                $permohonan,
                $statusBaru,
                ['catatan_admin' => $request->input('catatan_admin')],
                $request->input('catatan_admin'),
            );
        } catch (TransisiStatusTidakValid $e) {
            return redirect()->route('permohonan.index')->with('error', $e->getMessage());
        } catch (StokTidakCukup $e) {
            return redirect()->route('permohonan.index')->with('error', $e->getMessage());
        }

        $pesan = match ($statusBaru) {
            TransisiStatus::DISETUJUI => 'Permohonan berhasil disetujui. Stok barang telah dikurangi.',
            TransisiStatus::DITOLAK => 'Permohonan berhasil ditolak.',
            TransisiStatus::DIKEMBALIKAN => 'Barang berhasil dikembalikan dan stok telah bertambah.',
            default => 'Status permohonan diperbarui.',
        };

        return redirect()->route('permohonan.index')->with('success', $pesan);
    }

    public function cekNomor(Request $request)
    {
        $request->validate([
            'nomor_permohonan' => ['required', 'string', 'max:50'],
        ]);

        $permohonan = Permohonan::with('detailPermohonan.inventaris')
            ->where('nomor_permohonan', $request->string('nomor_permohonan')->toString())
            ->first();

        if (!$permohonan) {
            return response()->json(['exists' => false]);
        }

        $barang = $permohonan->detailPermohonan->map(function ($d) {
            return ($d->inventaris->nama_barang ?? 'Barang #' . $d->inventaris_id) . ' (' . $d->jumlah . ')';
        })->implode(', ');

        return response()->json([
            'exists'         => true,
            'nama_peminjam'  => $permohonan->nama_peminjam,
            'status'         => $permohonan->status,
            'barang'         => $barang ?: '-',
            'total_jumlah'   => $permohonan->detailPermohonan->sum('jumlah'),
        ]);
    }

    public function destroy(Permohonan $permohonan)
    {
        if (in_array($permohonan->status, TransisiStatus::SEDANG_DIPAKAI, true)) {
            return redirect()->route('permohonan.index')
                ->with('error', 'Barang pada permohonan ini masih dipinjam. Proses pengembalian terlebih dahulu.');
        }

        DB::transaction(function () use ($permohonan) {
            // Tidak ada stok yang perlu dilepas: status Disetujui/Dipinjam sudah
            // ditolak di atas, dan yang sudah Dikembalikan stoknya sudah kembali
            // ke inventaris saat proses pengembalian.
            $berkas = array_values(array_filter([
                $permohonan->foto_ktp,
                $permohonan->surat_tugas,
                $permohonan->bukti_pengembalian,
            ]));

            if ($berkas !== []) {
                Storage::disk('public')->delete($berkas);
            }

            $permohonan->delete();
        });

        return redirect()->route('permohonan.index')
            ->with('success', 'Permohonan berhasil dihapus.');
    }
}