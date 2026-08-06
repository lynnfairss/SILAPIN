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
        $totalItemDipinjam = (clone $query)->get()->sum(fn ($p) => $p->detailPermohonan->sum('jumlah'));

        $statusCounts = [];
        foreach ($statusList as $st) {
            $statusCounts[$st] = (clone $query)->where('status', $st)->count();
        }

        // ---------- Tren permohonan (per hari atau per bulan) ----------
        $per = $request->input('per', 'auto');
        if ($per === 'auto') {
            $rangeDays = null;
            if ($dari && $sampai) {
                $rangeDays = $dari->diffInDays($sampai) + 1;
            }
            $per = ($rangeDays !== null && $rangeDays <= 31) ? 'hari' : 'bulan';
        }

        if ($per === 'hari') {
            $trenQuery = (clone $query)
                ->select(DB::raw("DATE_FORMAT(tanggal_pinjam, '%Y-%m-%d') as label"), DB::raw('count(*) as total'))
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('total', 'label');

            $labels = $trenQuery->keys()->map(fn ($l) => Carbon::parse($l)->translatedFormat('d M'))->toArray();
            $values = $trenQuery->values()->toArray();
            $chartLabel = 'Permohonan per Hari';
        } else {
            $trenQuery = (clone $query)
                ->select(DB::raw("DATE_FORMAT(tanggal_pinjam, '%Y-%m') as label"), DB::raw('count(*) as total'))
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('total', 'label');

            $labels = $trenQuery->keys()->map(fn ($l) => Carbon::createFromFormat('Y-m', $l)->translatedFormat('M Y'))->toArray();
            $values = $trenQuery->values()->toArray();
            $chartLabel = 'Permohonan per Bulan';
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

        // ---------- Inventaris per kategori ----------
        $perKategori = Inventaris::with('kategori')
            ->get()
            ->groupBy(fn ($i) => $i->kategori?->nama ?? 'Tanpa Kategori')
            ->map->count()
            ->sortDesc()
            ->take(8);
        $kategoriLabels = $perKategori->keys()->toArray();
        $kategoriValues = $perKategori->values()->toArray();

        // ---------- Top 5 item paling sering dipinjam ----------
        $topItem = DetailPermohonan::with('inventaris')
            ->select('inventaris_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('inventaris_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
        $topLabels = $topItem->map(fn ($d) => $d->inventaris?->nama_barang ?? 'Item #'.$d->inventaris_id)->toArray();
        $topValues = $topItem->pluck('total')->toArray();

        // ---------- Recap tabel (per bulan atau per hari) ----------
        if ($per === 'hari') {
            $recap = (clone $query)
                ->select(
                    DB::raw("DATE_FORMAT(tanggal_pinjam, '%Y-%m-%d') as periode"),
                    'status',
                    DB::raw('count(*) as jml'),
                    DB::raw('count(distinct id) as total_row')
                )
                ->groupBy('periode', 'status')
                ->get()
                ->groupBy('periode');
            $recapColumns = ['Tanggal'];
        } else {
            $recap = (clone $query)
                ->select(
                    DB::raw("DATE_FORMAT(tanggal_pinjam, '%Y-%m') as periode"),
                    'status',
                    DB::raw('count(*) as jml')
                )
                ->groupBy('periode', 'status')
                ->get()
                ->groupBy('periode');
            $recapColumns = ['Bulan'];
        }

        $recap = $recap->sortKeys();
        $recapRows = [];
        $recapTotals = array_fill_keys($statusList, 0);
        $recapGrandTotal = 0;

        foreach ($recap as $periode => $group) {
            $row = [
                'periode' => $per === 'hari'
                    ? Carbon::parse($periode)->translatedFormat('d M Y')
                    : Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y'),
                'status' => [],
            ];
            foreach ($group as $g) {
                $row['status'][$g->status] = $g->jml;
                $recapTotals[$g->status] += $g->jml;
                $recapGrandTotal += $g->jml;
            }
            foreach ($statusList as $st) {
                $row['status'][$st] = $row['status'][$st] ?? 0;
            }
            $recapRows[] = $row;
        }

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

        return view('admin.dashboard', compact(
            'presets', 'filters', 'preset',
            'totalInventaris', 'totalKategori', 'totalInstansi',
            'totalPermohonan', 'totalItemDipinjam', 'statusCounts',
            'labels', 'values', 'chartLabel', 'per',
            'statusLabels', 'statusValues',
            'kategoriLabels', 'kategoriValues',
            'topLabels', 'topValues',
            'recapRows', 'recapTotals', 'recapGrandTotal', 'recapColumns', 'statusList',
            'permohonan', 'instansiList', 'tahunList'
        ));
    }
}
