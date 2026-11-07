<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\PermohonanStatusLog;
use App\Models\Instansi;
use App\Services\CsvExport;
use App\Services\StokTidakCukup;
use App\Services\StatusPermohonan;
use App\Services\TransisiStatus;
use App\Services\TransisiStatusTidakValid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Inventaris;
use App\Models\DetailPermohonan;

class PermohonanController extends Controller
{
    /**
     * Tab pada halaman daftar. 'Dipinjam' sengaja tidak punya tab sendiri:
     * ia menyatu dengan tab 'Disetujui', persis seperti sebelum migrasi tampilan.
     */
    private const STATUS_TAB = [
        'Menunggu'     => 'Permohonan Menunggu',
        'Disetujui'    => 'Permohonan Disetujui',
        'Ditolak'      => 'Permohonan Ditolak',
        'Dikembalikan' => 'Permohonan Dikembalikan',
    ];

    private const PER_PAGE = 8;

    public function index(Request $request)
    {
        return $this->daftarPermohonan($request, 'Semua', 'Data Permohonan');
    }

    public function byStatus(Request $request, string $status)
    {
        if (!isset(self::STATUS_TAB[$status])) {
            abort(404);
        }

        return $this->daftarPermohonan($request, $status, self::STATUS_TAB[$status]);
    }

    /**
     * Satu view (admin.permohonan.index) melayani halaman "Semua" dan halaman per status.
     */
    private function daftarPermohonan(Request $request, string $activeStatus, string $pageTitle)
    {
        $permohonan = $this->daftarQuery($request, $activeStatus)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $q = trim((string) $request->query('q', ''));

        $tabCounts = $this->hitungPerStatus();

        $stats = [
            'total' => $tabCounts['Semua'],
            'menunggu' => $tabCounts['Menunggu'],
            'aktif' => $tabCounts['Disetujui'],
            'tolak' => $tabCounts['Ditolak'],
        ];

        return view('admin.permohonan.index', compact(
            'permohonan', 'pageTitle', 'activeStatus', 'q', 'tabCounts', 'stats'
        ));
    }

    /**
     * Sumber query bersama untuk daftar (paginated) dan ekspor CSV (semua
     * baris), sehingga ekspor selalu mengikuti tab dan pencarian yang tampil.
     */
    private function daftarQuery(Request $request, string $activeStatus): Builder
    {
        $q = trim((string) $request->query('q', ''));

        return Permohonan::with('instansi', 'detailPermohonan.inventaris')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nomor_permohonan', 'like', "%{$q}%")
                        ->orWhere('nama_peminjam', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('telepon', 'like', "%{$q}%")
                        ->orWhere('keperluan', 'like', "%{$q}%")
                        ->orWhere('nama_instansi_lain', 'like', "%{$q}%")
                        ->orWhereHas('instansi', function ($instansi) use ($q) {
                            $instansi->where('nama_instansi', 'like', "%{$q}%");
                        });
                });
            })
            ->when($activeStatus !== 'Semua', function ($query) use ($activeStatus) {
                if ($activeStatus === TransisiStatus::DISETUJUI) {
                    $query->whereIn('status', TransisiStatus::SEDANG_DIPAKAI);
                } else {
                    $query->where('status', $activeStatus);
                }
            })
            ->orderByDesc('tanggal_pinjam')
            ->latest();
    }

    /**
     * Unduh seluruh permohonan pada tab aktif, bukan hanya halaman aktif.
     */
    public function export(Request $request): Response
    {
        $status = (string) $request->query('status', 'Semua');

        if ($status !== 'Semua' && !isset(self::STATUS_TAB[$status])) {
            abort(404);
        }

        return CsvExport::stream(
            $this->daftarQuery($request, $status)->get(),
            ['Nomor Permohonan', 'Instansi', 'Nama', 'NIK', 'Tanggal Pinjam', 'Tanggal Kembali', 'Status', 'Barang'],
            function (Permohonan $item) {
                return [
                    $item->nomor_permohonan,
                    $item->instansi?->nama_instansi ?? $item->nama_instansi_lain ?? '-',
                    $item->nama_peminjam,
                    $item->nik,
                    $item->tanggal_pinjam,
                    $item->tanggal_kembali,
                    $item->status,
                    $item->detailPermohonan->pluck('inventaris.nama_barang')->implode(', '),
                ];
            },
            'data-permohonan-' . strtolower($status) . '.csv'
        );
    }

    /**
     * Satu query GROUP BY untuk seluruh badge tab dan kartu statistik,
     * bukan lima COUNT terpisah.
     */
    private function hitungPerStatus(): array
    {
        $perStatus = Permohonan::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $ambil = fn (string $status) => (int) ($perStatus[$status] ?? 0);

        return [
            'Semua' => array_sum(array_map('intval', $perStatus)),
            'Menunggu' => $ambil(TransisiStatus::MENUNGGU),
            'Disetujui' => $ambil(TransisiStatus::DISETUJUI) + $ambil(TransisiStatus::DIPINJAM),
            'Ditolak' => $ambil(TransisiStatus::DITOLAK),
            'Dikembalikan' => $ambil(TransisiStatus::DIKEMBALIKAN),
        ];
    }

    public function show(Permohonan $permohonan)
    {
        $permohonan->load('instansi', 'detailPermohonan.inventaris', 'statusLogs.user');

        return view('admin.permohonan.show', compact('permohonan'));
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

    /**
     * Data permohonan untuk halaman pengembalian setelah QR discan.
     *
     * Dipakai juga oleh petugas untuk memverifikasi identitas peminjam:
     * karena itu data pribadi dan lampiran (foto KTP / surat tugas) ikut
     * dikirim. Route ini hanya untuk Admin/Super Admin.
     */
    public function cekNomor(Request $request)
    {
        $request->validate([
            'nomor_permohonan' => ['required', 'string', 'max:50'],
        ]);

        $permohonan = Permohonan::with('detailPermohonan.inventaris', 'instansi')
            ->where('nomor_permohonan', $request->string('nomor_permohonan')->toString())
            ->first();

        if (!$permohonan) {
            return response()->json(['exists' => false]);
        }

        $items = $permohonan->detailPermohonan->map(function ($d) {
            return [
                'nama'   => $d->inventaris->nama_barang ?? 'Barang #' . $d->inventaris_id,
                'kode'   => $d->inventaris->kode_barang ?? '-',
                'jumlah' => (int) $d->jumlah,
            ];
        })->values();

        $barang = $items
            ->map(fn ($i) => $i['nama'] . ' (' . $i['jumlah'] . ')')
            ->implode(', ');

        return response()->json([
            'exists'         => true,

            // Identitas peminjam
            'nama_peminjam'  => $permohonan->nama_peminjam,
            'nik'            => $permohonan->nik,
            'telepon'        => $permohonan->telepon,
            'alamat'         => $permohonan->alamat,
            'instansi'       => $permohonan->instansi?->nama_instansi
                ?? $permohonan->nama_instansi_lain
                ?? '-',
            'tanggal_pinjam' => $this->formatTanggal($permohonan->tanggal_pinjam),
            'tanggal_kembali' => $this->formatTanggal($permohonan->tanggal_kembali),
            'keperluan'      => $permohonan->keperluan,
            'status'         => $permohonan->status,

            // Barang yang dipinjam
            'items'          => $items,
            'barang'         => $barang ?: '-', // dipertahankan untuk kompatibilitas
            'total_jumlah'   => (int) $permohonan->detailPermohonan->sum('jumlah'),

            // Lampiran
            'foto_ktp'       => $this->infoLampiran($permohonan->foto_ktp),
            'surat_tugas'    => $this->infoLampiran($permohonan->surat_tugas),
        ]);
    }

    private function formatTanggal($tanggal): ?string
    {
        if (blank($tanggal)) {
            return null;
        }

        return \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('d M Y');
    }

    /**
     * @return array{url: string, nama: string, is_image: bool, is_pdf: bool}|null
     */
    private function infoLampiran(?string $path): ?array
    {
        if (blank($path)) {
            return null;
        }

        $ekstensi = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return [
            'url'      => asset('storage/' . $path),
            'nama'     => basename($path),
            'is_image' => in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true),
            'is_pdf'   => $ekstensi === 'pdf',
        ];
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