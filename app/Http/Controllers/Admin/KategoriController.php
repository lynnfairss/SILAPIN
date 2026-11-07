<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\Kategori;
use App\Services\CsvExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KategoriController extends Controller
{
    private const PER_PAGE = 8;

    /**
     * Sumber query bersama untuk daftar (paginated) dan ekspor CSV (semua
     * baris). Filter dan urutan di sini adalah satu-satunya definisi "hasil
     * pencarian", jadi ekspor tidak pernah menyimpang dari yang tampil.
     */
    private function daftarQuery(Request $request): Builder
    {
        $q = trim((string) $request->query('q', ''));

        return Kategori::query()
            ->withCount('inventaris')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_kategori', 'like', "%{$q}%")
                        ->orWhere('keterangan', 'like', "%{$q}%");
                });
            })
            ->latest();
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $kategori = $this->daftarQuery($request)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $terpopuler = Kategori::withCount('inventaris')
            ->orderByDesc('inventaris_count')
            ->first();

        $stats = [
            'total' => Kategori::count(),
            'terpakai' => Kategori::has('inventaris')->count(),
            'total_item' => Inventaris::whereNotNull('kategori_id')->count(),
            'terpopuler' => $terpopuler?->nama_kategori ?? '-',
        ];

        return view('admin.kategori.index', compact('kategori', 'stats', 'q'));
    }

    /**
     * Unduh seluruh kategori yang cocok filter, bukan hanya halaman aktif.
     */
    public function export(Request $request): Response
    {
        return CsvExport::stream(
            $this->daftarQuery($request)->get(),
            ['Kode', 'Nama Kategori', 'Keterangan', 'Total Item'],
            function (Kategori $item) {
                return [
                    'KAT-' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT),
                    $item->nama_kategori,
                    $item->keterangan,
                    $item->inventaris_count ?? 0,
                ];
            },
            'data-kategori.csv'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100',
            'keterangan'    => 'nullable|max:255',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama_kategori' => 'required|max:100',
            'keterangan'    => 'nullable|max:255',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'keterangan'    => $request->keterangan,
        ]);

        return redirect()->route('kategori.index')
            ->with('success', 'Data kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        try {
            $kategori->delete();
            return redirect()->route('kategori.index')
                ->with('success', 'Data kategori berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('kategori.index')
                ->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh data inventaris.');
        }
    }
}
