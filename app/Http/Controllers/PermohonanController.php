<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Instansi;
use App\Models\Inventaris;
use App\Models\Jenis;
use App\Models\Kategori;
use App\Models\DetailPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\Shared\Converter;

class PermohonanController extends Controller
{
    public function form()
    {
        $instansi = Instansi::all();
        $kategori = Kategori::all();
        $inventaris = Inventaris::with('kategori', 'jenis', 'fotos')->where('stok', '>', 0)->get();

        $jenisList = Jenis::orderBy('nama_jenis')->pluck('nama_jenis');

        return view('peminjam.index', compact('instansi', 'kategori', 'inventaris', 'jenisList'));
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
            'instansi_id'      => 'nullable|string|max:150',
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
            $instansiId = null;
            $namaInstansiLain = null;

            $instansiVal = $request->input('instansi_id');
            if (!empty($instansiVal)) {
                if (is_numeric($instansiVal)) {
                    $instansiId = (int) $instansiVal;
                } else {
                    $namaBaru = trim($instansiVal);
                    $existing = \App\Models\Instansi::whereRaw('LOWER(nama_instansi) = ?', [strtolower($namaBaru)])->first();
                    if ($existing) {
                        $instansiId = $existing->id;
                    } else {
                        $instansiId = \App\Models\Instansi::create(['nama_instansi' => $namaBaru])->id;
                    }
                }
            }

            $permohonan = Permohonan::create([
                'nomor_permohonan'   => $nomor,
                'instansi_id'        => $instansiId,
                'nama_instansi_lain' => $namaInstansiLain,
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

    public function downloadDocx(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');

        $sc = \App\Http\Controllers\Admin\SuratController::getContent($permohonan);

        // --- Resolve all values ---
        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
        $halText = $sc['hal'] ?: 'Permohonan Peminjaman ' . ($halItems ?: 'Barang Inventaris');
        $dateText = 'Ponorogo, ' . $permohonan->created_at->format('d F Y');

        $vNama = $sc['nama_peminjam'] ?: $permohonan->nama_peminjam ?? '-';
        $vNrp  = $sc['nik']           ?: $permohonan->nik ?? '';
        $vJab  = $sc['jabatan']       ?: ($permohonan->jabatan ?? '-');
        $vInst = $sc['instansi']      ?: ($permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-');

        $datePinjam = \Carbon\Carbon::parse($permohonan->tanggal_pinjam);
        $hariNames = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariNames[$datePinjam->format('l')] ?? $datePinjam->format('l');

        $ttdKiriNama  = $sc['ttd_kiri_nama']   ?: $permohonan->nama_peminjam ?? '-';
        $ttdKiriNrp   = $sc['ttd_kiri_nrp']    ?: $permohonan->nik ?? '';
        $ttdKiriJab   = $sc['ttd_kiri_jabatan'] ?: $permohonan->jabatan ?? '';
        $ttdKananNama = $sc['ttd_kanan_nama']  ?: $permohonan->nama_peminjam ?? '-';
        $ttdKananNrp  = $sc['ttd_kanan_nrp']   ?: $permohonan->nik ?? '';
        $ttdKananJab  = $sc['ttd_kanan_jabatan']?: $permohonan->jabatan ?? '';

        $isiText = '';
        if (!empty($sc['isi'])) {
            $isiText = str_replace("\n", ' ', $sc['isi']);
        }

        $penutupText = str_replace("\n", ' ', $sc['penutup']);
        $terimaKasihText = str_replace("\n", ' ', $sc['terima_kasih']);

        // --- Load template ---
        $templatePath = public_path('templates/template_peminjaman.docx');
        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'Template surat tidak ditemukan.'], 500);
        }

        $template = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // Helper: escape XML entities to prevent broken DOCX (& < > etc.)
        $xv = fn(string $val) => htmlspecialchars($val, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // --- Logo handling — hardcode fallback ---
        $logoKiriPath = null;
        $candidates = [
            public_path('images/surat/logo-kiri.jpg'),
            public_path('images/logo-kominfo.png'),
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $logoKiriPath = $path;
                break;
            }
        }
        if ($logoKiriPath) {
            $template->setImageValue('LOGO', $logoKiriPath);
        }

        // --- Simple placeholder replacements (escape XML entities) ---
        $template->setValue('hal', $xv($halText));
        $template->setValue('tanggal', $xv($dateText));
        $template->setValue('kepada_yth', $xv($sc['kepada_yth']));
        $template->setValue('kepada_kab', $xv($sc['kepada_kab']));
        $template->setValue('kepada_tempat', $xv($sc['kepada_tempat']));
        $template->setValue('pembuka', $xv($sc['pembuka']));
        $template->setValue('saya_yang', $xv($sc['saya_yang']));
        $template->setValue('nama_peminjam', $xv($vNama));
        $template->setValue('nrp', $xv($vNrp));
        $template->setValue('pangkat', $xv($vJab));
        $template->setValue('telepon', $xv($permohonan->telepon));
        $template->setValue('bermaksud', $xv($sc['bermaksud']));
        $template->setValue('keperluan', $xv($permohonan->keperluan));
        $template->setValue('isi', $xv($isiText));
        $template->setValue('rencana', $xv($sc['rencana']));
        $template->setValue('hari_label', $xv($sc['hari_label']));
        $template->setValue('hari', $xv($hari));
        $template->setValue('tanggal_label', $xv($sc['tanggal_label']));
        $template->setValue('tanggal_pinjam', $xv($datePinjam->format('d F Y')));
        $template->setValue('tempat_label', $xv($sc['tempat_label']));
        $template->setValue('instansi', $xv($vInst));
        $template->setValue('penutup', $xv($penutupText));
        $template->setValue('terima_kasih', $xv($terimaKasihText));
        $template->setValue('ttd_kiri_label', $xv($sc['ttd_kiri_label']));
        $template->setValue('ttd_kiri_nama', $xv($ttdKiriNama));
        $template->setValue('ttd_kiri_nrp', $xv($ttdKiriNrp));
        $template->setValue('ttd_kiri_jabatan', $xv($ttdKiriJab));
        $template->setValue('ttd_kanan_label', $xv($sc['ttd_kanan_label']));
        $template->setValue('ttd_kanan_nama', $xv($ttdKananNama));
        $template->setValue('ttd_kanan_nrp', $xv($ttdKananNrp));
        $template->setValue('ttd_kanan_jabatan', $xv($ttdKananJab));

        // --- Item table: cloneRow + indexed setValue (PhpWord uses #N notation) ---
        $items = $permohonan->detailPermohonan;
        $itemCount = $items->count();
        if ($itemCount > 0) {
            $template->cloneRow('item_no', $itemCount);
            foreach ($items as $i => $detail) {
                $idx = $i + 1;
                $template->setValue('item_no#' . $idx, ($i + 1) . '.');
                $template->setValue('item_nama#' . $idx, $xv($detail->inventaris?->nama_barang ?? '-'));
                $template->setValue('item_jumlah#' . $idx, (string) $detail->jumlah);
                $template->setValue('item_keterangan#' . $idx, $xv($detail->inventaris?->kondisi ?? '-'));
            }
        } else {
            $template->cloneRow('item_no', 1);
            $template->setValue('item_no#1', '1.');
            $template->setValue('item_nama#1', '-');
            $template->setValue('item_jumlah#1', '1');
            $template->setValue('item_keterangan#1', '-');
        }

        // --- Save & download ---
        $filename = 'Surat_Peminjaman_' . preg_replace('/[^a-zA-Z0-9]/', '_', $permohonan->nomor_permohonan) . '.docx';
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'silapin_' . uniqid() . '.docx';
        $template->saveAs($tempFile);

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
