<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\Inventaris;
use App\Models\Kategori;
use App\Models\Instansi;
use App\Models\DetailPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---------- Preset cepat ----------
        $presets = [
            'hari_ini'      => ['Hari Ini', now()->startOfDay(), now()->endOfDay()],
            '7_hari'        => ['7 Hari Terakhir', now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'bulan_ini'     => ['Bulan Ini', now()->startOfMonth(), now()->endOfMonth()],
            '1_bulan'       => ['1 Bulan Terakhir', now()->subMonth()->startOfDay(), now()->endOfDay()],
            '3_bulan'       => ['3 Bulan Terakhir', now()->subMonths(2)->startOfMonth(), now()->endOfDay()],
            '1_tahun'       => ['1 Tahun Terakhir', now()->subYear()->startOfDay(), now()->endOfDay()],
            'semua'         => ['Semua Data', null, null],
        ];

        // ---------- Tangkap filter ----------
        $filters = [
            'dari'       => $request->input('dari'),
            'sampai'     => $request->input('sampai'),
            'bulan'      => $request->input('bulan'),
            'tahun'      => $request->input('tahun'),
            'status'     => $request->input('status'),
            'instansi_id' => $request->input('instansi_id'),
        ];

        $preset = $request->input('preset');
        if ($preset && isset($presets[$preset])) {
            $filters['dari'] = $presets[$preset][1]?->format('Y-m-d');
            $filters['sampai'] = $presets[$preset][2]?->format('Y-m-d');
        }

        $dari = $filters['dari'] && $filters['dari'] !== 'null'
            ? Carbon::parse($filters['dari'])->startOfDay()
            : null;
        $sampai = $filters['sampai'] && $filters['sampai'] !== 'null'
            ? Carbon::parse($filters['sampai'])->endOfDay()
            : null;

        // ---------- Query permohonan sesuai filter ----------
        $query = Permohonan::query()
            ->when($dari, fn ($q) => $q->whereDate('tanggal_pinjam', '>=', $dari->toDateString()))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal_pinjam', '<=', $sampai->toDateString()))
            ->when($filters['bulan'], fn ($q, $m) => $q->whereMonth('tanggal_pinjam', $m))
            ->when($filters['tahun'], fn ($q, $t) => $q->whereYear('tanggal_pinjam', $t))
            ->when($filters['status'], fn ($q, $s) => $q->where('status', $s))
            ->when($filters['instansi_id'], fn ($q, $i) => $q->where('instansi_id', $i));

        $statusList = ['Menunggu', 'Disetujui', 'Ditolak', 'Dipinjam', 'Dikembalikan'];

        // ---------- Statistik ----------
        $totalInventaris  = Inventaris::count();
        $totalKategori    = Kategori::count();
        $totalInstansi    = Instansi::count();
        $totalPermohonan  = (clone $query)->count();

        // Agregat di database: tidak perlu memuat semua permohonan lalu
        // menjumlahkan detailnya di PHP.
        $totalItemDipinjam = (int) (clone $query)
            ->join('detail_permohonans', 'detail_permohonans.permohonan_id', '=', 'permohonans.id')
            ->sum('detail_permohonans.jumlah');

        // KPI tambahan: permohonan yang diajukan pada bulan berjalan.
        $permohonanBulanIni = Permohonan::whereYear('tanggal_pinjam', now()->year)
            ->whereMonth('tanggal_pinjam', now()->month)
            ->count();

        // Satu query GROUP BY untuk semua status, bukan lima COUNT terpisah.
        $statusCounts = array_fill_keys($statusList, 0);
        (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->each(function ($total, $status) use (&$statusCounts) {
                $statusCounts[$status] = (int) $total;
            });

        // ---------- Tren permohonan (per hari atau per bulan) ----------
        $per = $request->input('per', 'auto');
        if ($per === 'auto') {
            $rangeDays = null;
            if ($dari && $sampai) {
                $rangeDays = $dari->diffInDays($sampai) + 1;
            }
            $per = ($rangeDays !== null && $rangeDays <= 31) ? 'hari' : 'bulan';
        }

        $trenDateFormat = $per === 'hari' ? '%Y-%m-%d' : '%Y-%m';
        $chartLabel = $per === 'hari' ? 'Permohonan per Hari' : 'Permohonan per Bulan';

        $trenPermohonan = (clone $query)
            ->select(DB::raw("DATE_FORMAT(tanggal_pinjam, '{$trenDateFormat}') as label"), DB::raw('count(*) as total'))
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label')
            ->all();

        // Seri kedua chart area: total item yang dipinjam pada periode yang sama.
        $trenItem = (clone $query)
            ->join('detail_permohonans', 'detail_permohonans.permohonan_id', '=', 'permohonans.id')
            ->select(
                DB::raw("DATE_FORMAT(tanggal_pinjam, '{$trenDateFormat}') as label"),
                DB::raw('COALESCE(SUM(detail_permohonans.jumlah), 0) as total')
            )
            ->groupBy('label')
            ->pluck('total', 'label')
            ->all();

        // Isi periode kosong dengan nol agar grafik tetap berupa garis tren,
        // bukan satu titik ketika data hanya terkumpul di satu periode.
        if ($per === 'hari') {
            $keys = array_keys($trenPermohonan);
            sort($keys);
            $dataStart = $keys ? Carbon::parse($keys[0])->startOfDay() : now()->startOfDay();
            $dataEnd = $keys ? Carbon::parse(end($keys))->startOfDay() : now()->startOfDay();

            $start = $dari ? $dari->copy()->startOfDay() : $dataStart->copy();
            $end = $sampai ? $sampai->copy()->startOfDay() : $dataEnd->copy();
            if ($start->greaterThan($end)) {
                $start = $end->copy();
            }
            $tanpaFilterTanggal = !$dari && empty($filters['bulan']) && empty($filters['tahun']);
            if ($tanpaFilterTanggal && $start->diffInDays($end) + 1 < 7) {
                $start = $end->copy()->subDays(6)->startOfDay();
            }

            $labels = [];
            $values = [];
            $trenItemValues = [];
            for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addDay()) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $values[] = (int) ($trenPermohonan[$key] ?? 0);
                $trenItemValues[] = (int) ($trenItem[$key] ?? 0);
            }
        } else {
            $keys = array_keys($trenPermohonan);
            sort($keys);
            $dataStart = $keys ? Carbon::createFromFormat('Y-m', $keys[0])->startOfMonth() : now()->startOfMonth();
            $dataEnd = $keys ? Carbon::createFromFormat('Y-m', end($keys))->startOfMonth() : now()->startOfMonth();

            $start = $dari ? $dari->copy()->startOfMonth() : $dataStart->copy();
            $end = $sampai ? $sampai->copy()->startOfMonth() : $dataEnd->copy();

            // Filter tahun tanpa rentang tanggal: tampilkan satu tahun penuh.
            if (!$dari && !empty($filters['tahun'])) {
                $start = Carbon::create((int) $filters['tahun'], 1, 1)->startOfMonth();
            }
            if (!$sampai && !empty($filters['tahun'])) {
                $end = Carbon::create((int) $filters['tahun'], 12, 1)->startOfMonth();
                if ($end->greaterThan(now())) {
                    $end = now()->startOfMonth();
                }
            }

            if ($start->greaterThan($end)) {
                $start = $end->copy();
            }
            $tanpaFilterTanggal = !$dari && empty($filters['bulan']) && empty($filters['tahun']);
            if ($tanpaFilterTanggal && $start->diffInMonths($end) + 1 < 6) {
                $start = $end->copy()->subMonths(5)->startOfMonth();
            }

            $labels = [];
            $values = [];
            $trenItemValues = [];
            for ($cursor = $start->copy(); $cursor->lessThanOrEqualTo($end); $cursor->addMonth()) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $values[] = (int) ($trenPermohonan[$key] ?? 0);
                $trenItemValues[] = (int) ($trenItem[$key] ?? 0);
            }
        }

        // ---------- Distribusi status (doughnut) ----------
        $statusLabels = [];
        $statusValues = [];
        foreach ($statusList as $st) {
            if (($statusCounts[$st] ?? 0) > 0) {
                $statusLabels[] = $st;
                $statusValues[] = $statusCounts[$st];
            }
        }

        // ---------- Top 5 item paling sering dipinjam ----------
        $topItem = DetailPermohonan::with('inventaris')
            ->select('inventaris_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('inventaris_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
        $topLabels = $topItem->map(fn ($d) => $d->inventaris?->nama_barang ?? 'Item #'.$d->inventaris_id)->toArray();
        $topValues = $topItem->pluck('total')->toArray();

        // ---------- Grand total (untuk summary cards) ----------
        $recapGrandTotal = array_sum($statusCounts);

        // ---------- Permohonan dalam rentang ----------
        $permohonan = (clone $query)
            ->with(['instansi', 'detailPermohonan.inventaris'])
            ->orderByDesc('tanggal_pinjam')
            ->latest()
            ->take(20)
            ->get();

        // ---------- Data dropdown ----------
        $instansiList = Instansi::orderBy('nama_instansi')->get();
        $tahunList = Permohonan::selectRaw('YEAR(tanggal_pinjam) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        // ---------- Rekap peminjaman per instansi (tanpa status Menunggu) ----------
        $allPermohonan = (clone $query)
            ->where('status', '!=', 'Menunggu')
            ->with(['instansi', 'detailPermohonan.inventaris'])
            ->get();

        $recapInstansi = $allPermohonan
            ->groupBy(fn ($p) => $p->instansi_id ?? 0)
            ->map(function ($group) {
                $first = $group->first();
                $totalBarang = $group->sum(fn ($p) => $p->detailPermohonan->sum('jumlah'));

                $namaPeminjamList = $group
                    ->pluck('nama_peminjam')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $barangCounts = [];
                foreach ($group as $p) {
                    foreach ($p->detailPermohonan as $d) {
                        $nama = $d->inventaris?->nama_barang ?? 'Barang #' . $d->inventaris_id;
                        $barangCounts[$nama] = ($barangCounts[$nama] ?? 0) + $d->jumlah;
                    }
                }
                arsort($barangCounts);
                $topBarang = !empty($barangCounts)
                    ? array_key_first($barangCounts) . ' (' . $barangCounts[array_key_first($barangCounts)] . 'x)'
                    : '-';

                return [
                    'instansi_id'      => $first->instansi_id,
                    'nama_instansi'    => $first->instansi?->nama_instansi ?? $first->nama_instansi_lain ?? 'Tanpa Instansi',
                    'total_permohonan' => $group->count(),
                    'total_barang'     => $totalBarang,
                    'top_barang'       => $topBarang,
                    'nama_peminjam_list' => $namaPeminjamList,
                ];
            })
            ->sortByDesc('total_permohonan')
            ->values();

        return view('admin.dashboard', compact(
            'presets', 'filters', 'preset',
            'totalInventaris', 'totalKategori', 'totalInstansi',
            'totalPermohonan', 'totalItemDipinjam', 'permohonanBulanIni', 'statusCounts',
            'labels', 'values', 'trenItemValues', 'chartLabel', 'per',
            'statusLabels', 'statusValues',
            'topLabels', 'topValues',
            'recapGrandTotal', 'statusList',
            'permohonan', 'instansiList', 'tahunList',
            'recapInstansi'
        ));
    }

    public function instansiDetail(Request $request, $instansiId)
    {
        $instansi = Instansi::findOrFail($instansiId);

        // Semua permohonan dari instansi ini
        $permohonan = Permohonan::where('instansi_id', $instansiId)
            ->with('detailPermohonan.inventaris')
            ->orderByDesc('tanggal_pinjam')
            ->get();

        // Hitung barang per inventaris, urut dari paling sering
        $barangList = DetailPermohonan::whereIn('permohonan_id', $permohonan->pluck('id'))
            ->with('inventaris')
            ->select('inventaris_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('inventaris_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($d) => [
                'id'    => $d->inventaris_id,
                'nama'  => $d->inventaris?->nama_barang ?? 'Barang #' . $d->inventaris_id,
                'kode'  => $d->inventaris?->kode_barang ?? '-',
                'total' => (int) $d->total,
            ]);

        // Ranking barang: nama => total (untuk sort detail)
        $rankBarang = $barangList->pluck('total', 'nama')->toArray();

        // Detail semua peminjaman, sort barang paling sering di atas
        $detailList = [];
        foreach ($permohonan as $p) {
            foreach ($p->detailPermohonan as $d) {
                $namaBarang = $d->inventaris?->nama_barang ?? 'Barang #' . $d->inventaris_id;
                $detailList[] = [
                    'barang'          => $namaBarang,
                    'kode'            => $d->inventaris?->kode_barang ?? '-',
                    'jumlah'          => $d->jumlah,
                    'peminjam'        => $p->nama_peminjam,
                    'tanggal_pinjam'  => $p->tanggal_pinjam,
                    'tanggal_kembali' => $p->tanggal_kembali,
                    'status'          => $p->status,
                    'nomor'           => $p->nomor_permohonan,
                    '_rank'           => $rankBarang[$namaBarang] ?? 0,
                ];
            }
        }
        usort($detailList, fn ($a, $b) => $b['_rank'] <=> $a['_rank']);

        // Statistik ringkas
        $totalPermohonan = $permohonan->count();
        $totalBarang     = $permohonan->sum(fn ($p) => $p->detailPermohonan->sum('jumlah'));
        $peminjamUniq    = $permohonan->pluck('nama_peminjam')->unique()->count();

        return view('admin.dashboard.instansi-detail', compact(
            'instansi', 'permohonan', 'barangList', 'detailList',
            'totalPermohonan', 'totalBarang', 'peminjamUniq'
        ));
    }
}
