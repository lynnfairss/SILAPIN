<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\Permohonan;
use App\Services\CsvExport;
use App\Services\TransisiStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstansiController extends Controller
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

        return Instansi::query()
            ->withCount('permohonan')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_instansi', 'like', "%{$q}%")
                        ->orWhere('alamat', 'like', "%{$q}%")
                        ->orWhere('telepon', 'like', "%{$q}%");
                });
            })
            ->latest();
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $instansi = $this->daftarQuery($request)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $stats = [
            'total' => Instansi::count(),
            'kontak' => Instansi::whereNotNull('telepon')->where('telepon', '!=', '')->count(),
            'peminjaman' => Permohonan::whereNotNull('instansi_id')->count(),
        ];

        return view('admin.instansi.index', compact('instansi', 'stats', 'q'));
    }

    /**
     * Unduh seluruh instansi yang cocok filter, bukan hanya halaman aktif.
     */
    public function export(Request $request): Response
    {
        return CsvExport::stream(
            $this->daftarQuery($request)->get(),
            ['Kode', 'Nama Instansi', 'Alamat', 'Telepon', 'Peminjaman', 'Terdaftar'],
            function (Instansi $item) {
                return [
                    'INS-' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT),
                    $item->nama_instansi,
                    $item->alamat,
                    $item->telepon,
                    $item->permohonan_count ?? 0,
                    $item->created_at?->format('d M Y'),
                ];
            },
            'data-instansi.csv'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_instansi' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20|regex:/^[0-9]+$/',
        ]);

        Instansi::create([
            'nama_instansi' => $request->nama_instansi,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Instansi $instansi)
    {
        $request->validate([
            'nama_instansi' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20|regex:/^[0-9]+$/',
        ]);

        $instansi->update([
            'nama_instansi' => $request->nama_instansi,
            'alamat' => $request->alamat,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        $dipakai = $instansi->permohonan()
            ->whereIn('status', TransisiStatus::SEDANG_DIPAKAI)
            ->count();

        if ($dipakai > 0) {
            return redirect()->route('instansi.index')
                ->with('error', "Instansi masih dipakai oleh {$dipakai} peminjaman yang sedang berjalan. Selesaikan atau pindahkan permohonan tersebut lebih dulu.");
        }

        // instansi_id sekarang ON DELETE SET NULL, jadi berkas lama tetap
        // utuh dan hanya kehilangan label instansi.
        $instansi->delete();

        return redirect()->route('instansi.index')
            ->with('success', 'Data instansi berhasil dihapus. Riwayat permohonan terkait tetap tersimpan tanpa nama instansi.');
    }
}
