<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailPermohonan;
use App\Models\Inventaris;
use App\Models\InventarisFoto;
use App\Models\Jenis;
use App\Models\Kategori;
use App\Services\CsvExport;
use App\Services\TransisiStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InventarisController extends Controller
{
    /**
     * Harus sama persis dengan enum di migration create_inventaris_table.
     * Nilai lain akan ditolak database sebagai QueryException (500), bukan
     * sebagai pesan validasi.
     */
    private const KONDISI = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

    private const PER_PAGE = 8;

    /**
     * Sumber query bersama untuk daftar (paginated) dan ekspor CSV (semua
     * baris). Filter dan urutan di sini adalah satu-satunya definisi "hasil
     * pencarian", jadi ekspor tidak pernah menyimpang dari yang tampil.
     */
    private function daftarQuery(Request $request): Builder
    {
        $query = Inventaris::with('kategori', 'jenis', 'fotos')
            ->withSum(['detailPermohonan as dipinjam' => function ($query) {
                $query->whereHas('permohonan', function ($permohonan) {
                    $permohonan->whereIn('status', TransisiStatus::SEDANG_DIPAKAI);
                });
            }], 'jumlah');

        if ($request->filled('jenis')) {
            $query->where('jenis_id', $request->jenis);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_barang', 'like', "%{$q}%")
                    ->orWhere('kode_barang', 'like', "%{$q}%")
                    ->orWhere('deskripsi', 'like', "%{$q}%");
            });
        }

        return $query->latest();
    }

    public function index(Request $request)
    {
        $inventaris = $this->daftarQuery($request)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $q = trim((string) $request->query('q', ''));

        $jenisList = Jenis::orderBy('nama_jenis')->get();
        $kategoriList = Kategori::orderBy('nama_kategori')->get();

        $jenisCount = Inventaris::select('jenis_id')
            ->whereNotNull('jenis_id')
            ->selectRaw('count(*) as total')
            ->groupBy('jenis_id')
            ->pluck('total', 'jenis_id');

        $kategoriCount = Inventaris::select('kategori_id')
            ->whereNotNull('kategori_id')
            ->selectRaw('count(*) as total')
            ->groupBy('kategori_id')
            ->pluck('total', 'kategori_id');

        $dipinjam = (int) DetailPermohonan::whereHas('permohonan', function ($query) {
            $query->whereIn('status', TransisiStatus::SEDANG_DIPAKAI);
        })->sum('jumlah');

        $tersedia = (int) Inventaris::sum('stok');

        $stats = [
            'total_barang' => Inventaris::count(),
            'kondisi_baik' => Inventaris::where('kondisi', 'Baik')->count(),
            'tersedia' => $tersedia,
            'dipinjam' => $dipinjam,
            'total_unit' => $tersedia + $dipinjam,
        ];

        $kategori = Kategori::all();

        return view('admin.inventaris.index', compact(
            'inventaris',
            'kategori',
            'kategoriList',
            'jenisList',
            'jenisCount',
            'kategoriCount',
            'stats',
            'q'
        ));
    }

    /**
     * Unduh seluruh barang yang cocok filter, bukan hanya halaman aktif.
     */
    public function export(Request $request): Response
    {
        return CsvExport::stream(
            $this->daftarQuery($request)->get(),
            ['Kode', 'Nama Barang', 'Kategori', 'Jenis', 'Stok Tersedia', 'Dipinjam', 'Total', 'Kondisi', 'Deskripsi'],
            function (Inventaris $item) {
                return [
                    $item->kode_barang,
                    $item->nama_barang,
                    $item->kategori?->nama_kategori,
                    $item->jenis?->nama_jenis,
                    $item->stok,
                    (int) ($item->dipinjam ?? 0),
                    (int) $item->stok + (int) ($item->dipinjam ?? 0),
                    $item->kondisi,
                    $item->deskripsi,
                ];
            },
            'data-inventaris.csv'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'nullable|integer|exists:kategoris,id',
            'kode_barang' => 'required|string|max:30|unique:inventaris,kode_barang',
            'nama_barang' => 'required|string|max:150',
            'jenis_id' => 'required|integer|exists:jensis,id',
            'stok' => 'required|integer|min:0',
            'kondisi' => ['required', Rule::in(self::KONDISI)],
            'deskripsi' => 'nullable|string|max:2000',
            'foto' => 'nullable|array|max:5',
            'foto.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $inventaris = Inventaris::create([
            'kategori_id' => $request->kategori_id ?: null,
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'jenis_id' => $request->jenis_id ?: null,
            'stok' => $request->stok,
            'kondisi' => $request->kondisi,
            'deskripsi' => $request->deskripsi,
        ]);

        $this->simpanFotos($request, $inventaris);

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil ditambahkan.');
    }

    public function update(Request $request, Inventaris $inventari)
    {
        $request->validate([
            'kategori_id' => 'nullable|integer|exists:kategoris,id',
            'kode_barang' => 'required|string|max:30|unique:inventaris,kode_barang,' . $inventari->id,
            'nama_barang' => 'required|string|max:150',
            'jenis_id' => 'required|integer|exists:jensis,id',
            'stok' => 'required|integer|min:0',
            'kondisi' => ['required', Rule::in(self::KONDISI)],
            'deskripsi' => 'nullable|string|max:2000',
            'foto' => 'nullable|array|max:5',
            'foto.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'hapus_foto' => 'nullable|string',
        ]);

        $inventari->update([
            'kategori_id' => $request->kategori_id,
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'jenis_id' => $request->jenis_id ?: null,
            'stok' => $request->stok,
            'kondisi' => $request->kondisi,
            'deskripsi' => $request->deskripsi,
        ]);

        $this->simpanFotos($request, $inventari);

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil diperbarui.');
    }

    public function destroy(Inventaris $inventari)
    {
        $dipakai = $inventari->detailPermohonan()
            ->whereHas('permohonan', fn ($q) => $q->whereIn('status', TransisiStatus::SEDANG_DIPAKAI))
            ->count();

        if ($dipakai > 0) {
            return redirect()
                ->route('inventaris.index')
                ->with('error', "Barang masih dipinjam pada {$dipakai} peminjaman yang sedang berjalan. Selesaikan pengembaliannya terlebih dahulu.");
        }

        DB::transaction(function () use ($inventari) {
            foreach ($inventari->fotos as $foto) {
                if (Storage::disk('public')->exists($foto->foto)) {
                    Storage::disk('public')->delete($foto->foto);
                }
            }

            // detail_permohonans.inventaris_id kini RESTRICT, jadi penghapusan
            // gagal di level database bila ada riwayat. Yang tersisa di sini
            // hanya berkas tanpa riwayat.
            $inventari->delete();
        });

        return redirect()
            ->route('inventaris.index')
            ->with('success', 'Data inventaris berhasil dihapus.');
    }

    public function destroyFoto(InventarisFoto $foto)
    {
        if (Storage::disk('public')->exists($foto->foto)) {
            Storage::disk('public')->delete($foto->foto);
        }
        $foto->delete();

        return response()->json(['success' => true]);
    }

    private function simpanFotos(Request $request, Inventaris $inventaris)
    {
        // 1. Hapus foto yang ditandai lewat tombol ✕ (daftar id dipisah koma)
        $hapusIds = [];
        if ($request->filled('hapus_foto')) {
            $hapusIds = array_values(array_filter(array_map('intval', explode(',', $request->hapus_foto))));
        }

        foreach ($inventaris->fotos as $foto) {
            if (in_array($foto->id, $hapusIds)) {
                if (Storage::disk('public')->exists($foto->foto)) {
                    Storage::disk('public')->delete($foto->foto);
                }
                $foto->delete();
            }
        }

        // 2. Upload / ganti foto per slot (urutan 0-4)
        if ($request->hasFile('foto')) {
            $fotos = $request->file('foto');
            $inventaris->load('fotos');

            foreach ($fotos as $slot => $file) {
                if (!$file || !$file->isValid()) continue;

                $path = $file->store('inventaris', 'public');
                $slotInt = (int) $slot;

                $ada = $inventaris->fotos->firstWhere('urutan', $slotInt);
                if ($ada) {
                    if (Storage::disk('public')->exists($ada->foto)) {
                        Storage::disk('public')->delete($ada->foto);
                    }
                    $ada->update(['foto' => $path, 'urutan' => $slotInt]);
                } else {
                    InventarisFoto::create([
                        'inventaris_id' => $inventaris->id,
                        'foto' => $path,
                        'urutan' => $slotInt,
                    ]);
                }
            }
        }

        // 3. Kompak urutan agar "Foto 1" selalu foto pertama
        $inventaris->load('fotos');
        $inventaris->fotos->each(function ($f, $i) {
            $f->update(['urutan' => $i]);
        });

        // 4. Perbarui thumbnail dari foto pertama (atau null jika kosong)
        $first = $inventaris->fotos->first();
        $inventaris->update(['foto' => $first?->foto]);
    }
}
