<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventaris;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $kategori = Kategori::query()
            ->withCount('inventaris')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_kategori', 'like', "%{$q}%")
                        ->orWhere('keterangan', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
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
