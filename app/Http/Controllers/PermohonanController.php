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

        $instansiTipe = $instansi->mapWithKeys(fn($item) => [$item->id => $item->effective_tipe_identitas]);

        return view('peminjam.index', compact('instansi', 'kategori', 'inventaris', 'instansiTipe', 'jenisList'));
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

        $phpWord = new PhpWord();

        $section = $phpWord->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginTop' => 1440,
            'marginRight' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
        ]);

        $right = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT];
        $justify = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH];
        $center = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $singleLine = ['spacing' => ['after' => 0, 'line' => 240]];

        $fontTNR = ['name' => 'Times New Roman', 'size' => 12];

        $noBorderTable = [
            'borderTop' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
            'borderBottom' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
            'borderLeft' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
            'borderRight' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
        ];
        $noBorderCell = [
            'borderTop' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
            'borderBottom' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
            'borderLeft' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
            'borderRight' => ['val' => 'none', 'sz' => 0, 'color' => 'FFFFFF'],
        ];

        $logoPath = public_path('images/logo-kominfo.png');
        $template = \App\Models\SuratTemplate::find(1);
        $logoKiriPath = ($template && $template->logo_kiri && file_exists(public_path($template->logo_kiri)))
            ? public_path($template->logo_kiri) : $logoPath;
        $logoKananPath = ($template && $template->logo_kanan && file_exists(public_path($template->logo_kanan)))
            ? public_path($template->logo_kanan) : null;

        $hasLogoKanan = $logoKananPath !== null;
        $textCellWidth = $hasLogoKanan ? 6800 : 9000;

        $headerTable = $section->addTable([
            'width' => 10000,
            'layout' => 'fixed',
        ]);
        $headerTable->addRow(1700);
        $logoCell = $headerTable->addCell(1600, [
            'borderBottom' => ['val' => 'double', 'sz' => 12, 'color' => '000000'],
            'valign' => 'center',
        ]);
        $logoCell->addImage($logoKiriPath, [
            'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(2.1),
            'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(2.1),
        ]);

        $textCell = $headerTable->addCell($textCellWidth, [
            'borderBottom' => ['val' => 'double', 'sz' => 12, 'color' => '000000'],
            'valign' => 'center',
        ]);
        $textCell->addText('PEMERINTAH KABUPATEN PONOROGO', [
            'name' => 'Arial', 'size' => 13, 'bold' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spacing' => ['after' => 0, 'line' => 260],
        ]);
        $textCell->addText('DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK', [
            'name' => 'Arial', 'size' => 13, 'bold' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spacing' => ['after' => 0, 'line' => 260],
        ]);
        $textCell->addText('Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418', [
            'name' => 'Arial', 'size' => 10,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spacing' => ['after' => 0, 'line' => 240],
        ]);
        $textCell->addText('Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id', [
            'name' => 'Arial', 'size' => 10, 'italic' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spacing' => ['after' => 0, 'line' => 240],
        ]);
        $textCell->addText('P O N O R O G O', [
            'name' => 'Arial', 'size' => 14, 'bold' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spacing' => ['after' => 0, 'line' => 260],
        ]);

        if ($hasLogoKanan) {
            $logoKananCell = $headerTable->addCell(1600, [
                'borderBottom' => ['val' => 'double', 'sz' => 12, 'color' => '000000'],
                'valign' => 'center',
            ]);
            $logoKananCell->addImage($logoKananPath, [
                'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(2.1),
                'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(2.1),
            ]);
        }

        $section->addText('', null, ['spacing' => ['after' => 120]]);

        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
        $halText = $sc['hal'] ?: 'Permohonan Peminjaman ' . ($halItems ?: 'Barang Inventaris');
        $dateText = 'Ponorogo, ' . $permohonan->created_at->format('d F Y');

        $infoTable = $section->addTable(array_merge(['width' => 10000, 'layout' => 'fixed'], $noBorderTable));
        $infoTable->addRow();
        $infoTable->addCell(6500, $noBorderCell)->addText('Hal        : ' . $halText, $fontTNR + $singleLine);
        $infoTable->addCell(3500, $noBorderCell)->addText($dateText, $fontTNR + $right + $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        $section->addText('Kepada', $fontTNR, $singleLine);
        $section->addText($sc['kepada_yth'], $fontTNR, $singleLine);
        $section->addText($sc['kepada_kab'], $fontTNR, $singleLine);
        $section->addText($sc['kepada_tempat'], $fontTNR, $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);
        $section->addText($sc['pembuka'], $fontTNR, $singleLine);

        $section->addText($sc['saya_yang'], $fontTNR, $singleLine);

        $vNama = $sc['nama_peminjam'] ?: $permohonan->nama_peminjam ?? '-';
        $vNrp  = $sc['nik']           ?: $permohonan->nik ?? '';
        $vJab  = $sc['jabatan']       ?: ($permohonan->jabatan ?? '-');
        $vInst = $sc['instansi']      ?: ($permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-');

        $identitasTable = $section->addTable(array_merge(['width' => 7000, 'layout' => 'fixed'], $noBorderTable));
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $noBorderCell)->addText('Nama', $fontTNR + $singleLine);
        $identitasTable->addCell(300, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(4300, $noBorderCell)->addText($vNama, $fontTNR + $singleLine);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $noBorderCell)->addText('NRP', $fontTNR + $singleLine);
        $identitasTable->addCell(300, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(4300, $noBorderCell)->addText($vNrp, $fontTNR + $singleLine);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $noBorderCell)->addText('Pangkat', $fontTNR + $singleLine);
        $identitasTable->addCell(300, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(4300, $noBorderCell)->addText($vJab, $fontTNR + $singleLine);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $noBorderCell)->addText('No. Telepon/HP', $fontTNR + $singleLine);
        $identitasTable->addCell(300, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(4300, $noBorderCell)->addText($permohonan->telepon, $fontTNR + $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);
        $section->addText($sc['bermaksud'], $fontTNR, $singleLine);

        $phpWord->addTableStyle('ItemTable', [
            'borderSize' => 4,
            'borderColor' => '000000',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 60,
            'cellMarginRight' => 60,
        ]);
        $itemTable = $section->addTable('ItemTable');

        $itemTable->addRow();
        $itemTable->addCell(532, ['shading' => ['fill' => 'D9D9D9']])->addText('No', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $itemTable->addCell(3689, ['shading' => ['fill' => 'D9D9D9']])->addText('Nama alat', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $itemTable->addCell(992, ['shading' => ['fill' => 'D9D9D9']])->addText('Jumlah', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $itemTable->addCell(3083, ['shading' => ['fill' => 'D9D9D9']])->addText('Keterangan', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        foreach ($permohonan->detailPermohonan as $i => $detail) {
            $itemTable->addRow();
            $itemTable->addCell(532)->addText((string) ($i + 1), ['name' => 'Arial', 'size' => 10, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $itemTable->addCell(3689)->addText($detail->inventaris?->nama_barang ?? '-', ['name' => 'Arial', 'size' => 10]);
            $numCell = $itemTable->addCell(992);
            $numCell->addText((string) $detail->jumlah, [
                'name' => 'Arial', 'size' => 10,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            $ketCell = $itemTable->addCell(3083);
            $ketText = $detail->inventaris?->kondisi ?? '-';
            $ketCell->addText($ketText, [
                'name' => 'Arial', 'size' => 10,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
        }

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        $pKeperluan = $section->addTextRun($justify + ['indentation' => ['firstLine' => 480]] + $singleLine);
        $pKeperluan->addText($sc['untuk_keperluan'] . ' ', $fontTNR);
        $pKeperluan->addText($permohonan->keperluan, $fontTNR + ['bold' => true]);
        $pKeperluan->addText('.', $fontTNR);

        if (!empty($sc['isi'])) {
            $section->addText('', null, ['spacing' => ['after' => 80]]);
            foreach (explode("\n", $sc['isi']) as $line) {
                $section->addText($line, $fontTNR, $justify + ['indentation' => ['firstLine' => 480]] + $singleLine);
            }
        }

        $section->addText($sc['rencana'], $fontTNR, $justify + ['indentation' => ['firstLine' => 480]] + $singleLine);

        $datePinjam = \Carbon\Carbon::parse($permohonan->tanggal_pinjam);
        $hariNames = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariNames[$datePinjam->format('l')] ?? $datePinjam->format('l');

        $jadwalTable = $section->addTable(array_merge(['width' => 8000, 'layout' => 'fixed'], $noBorderTable));
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $noBorderCell);
        $jadwalTable->addCell(1400, $noBorderCell)->addText($sc['hari_label'], $fontTNR + $singleLine);
        $jadwalTable->addCell(300, $noBorderCell);
        $jadwalTable->addCell(5500, $noBorderCell)->addText(':  ' . $hari, $fontTNR + $singleLine);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $noBorderCell);
        $jadwalTable->addCell(1400, $noBorderCell)->addText($sc['tanggal_label'], $fontTNR + $singleLine);
        $jadwalTable->addCell(300, $noBorderCell);
        $jadwalTable->addCell(5500, $noBorderCell)->addText(':  ' . $datePinjam->format('d F Y'), $fontTNR + $singleLine);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $noBorderCell);
        $jadwalTable->addCell(1400, $noBorderCell)->addText($sc['tempat_label'], $fontTNR + $singleLine);
        $jadwalTable->addCell(300, $noBorderCell);
        $jadwalTable->addCell(5500, $noBorderCell)->addText(':  ' . $vInst, $fontTNR + $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        $pDemikian = $section->addTextRun($justify + ['indentation' => ['firstLine' => 480]] + $singleLine);
        foreach (explode("\n", $sc['penutup']) as $line) {
            $pDemikian->addText($line . ' ', $fontTNR);
        }
        $pDemikian->addText(' ', $fontTNR);
        foreach (explode("\n", $sc['terima_kasih']) as $line) {
            $pDemikian->addText($line . ' ', $fontTNR);
        }

        $ttdKiriNama = $sc['ttd_kiri_nama']  ?: $permohonan->nama_peminjam ?? '-';
        $ttdKiriNrp  = $sc['ttd_kiri_nrp']   ?: $permohonan->nik ?? '';
        $ttdKiriJab  = $sc['ttd_kiri_jabatan']?: $permohonan->jabatan ?? '';
        $ttdKananNama = $sc['ttd_kanan_nama'] ?: $permohonan->nama_peminjam ?? '-';
        $ttdKananNrp  = $sc['ttd_kanan_nrp']  ?: $permohonan->nik ?? '';
        $ttdKananJab  = $sc['ttd_kanan_jabatan']?: $permohonan->jabatan ?? '';

        $ttdTable = $section->addTable(array_merge(['width' => 10000, 'layout' => 'fixed'], $noBorderTable));
        $ttdTable->addRow();
        $ttdTable->addCell(5000, $noBorderCell)->addText($sc['ttd_kiri_label'], $fontTNR + $center + $singleLine);
        $ttdTable->addCell(5000, $noBorderCell)->addText($sc['ttd_kanan_label'], $fontTNR + $center + $singleLine);

        $ttdTable->addRow();
        $leftCell = $ttdTable->addCell(5000, $noBorderCell);
        for ($i = 0; $i < 6; $i++) {
            $leftCell->addTextBreak();
        }
        $leftCell->addText($ttdKiriNama, ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]]);
        $leftCell->addText('NRP. ' . $ttdKiriNrp, ['name' => 'Times New Roman', 'size' => 12, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]]);
        if ($ttdKiriJab) {
            $leftCell->addText($ttdKiriJab, ['name' => 'Times New Roman', 'size' => 12, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]]);
        }

        $rightCell = $ttdTable->addCell(5000, $noBorderCell);
        for ($i = 0; $i < 6; $i++) {
            $rightCell->addTextBreak();
        }
        $rightCell->addText($ttdKananNama, ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]]);
        $rightCell->addText('NRP. ' . $ttdKananNrp, ['name' => 'Times New Roman', 'size' => 12, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]]);
        if ($ttdKananJab) {
            $rightCell->addText($ttdKananJab, ['name' => 'Times New Roman', 'size' => 12, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]]);
        }

        $filename = 'Surat_Peminjaman_' . preg_replace('/[^a-zA-Z0-9]/', '_', $permohonan->nomor_permohonan) . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'silapin_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
