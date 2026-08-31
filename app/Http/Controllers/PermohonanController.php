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

        $phpWord = new PhpWord();

        $section = $phpWord->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginTop' => 1135,
            'marginRight' => 1800,
            'marginBottom' => 1440,
            'marginLeft' => 1276,
        ]);

        $right = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT];
        $justify = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH];
        $spacing = ['spacing' => ['after' => 160]];

        $fontTNR = ['name' => 'Times New Roman', 'size' => 12];
        $fontArial = ['name' => 'Arial', 'size' => 12];
        $transparent = ['borderSize' => 0, 'color' => 'FFFFFF'];

        $logoPath = public_path('images/logo-kominfo.png');

        $headerTable = $section->addTable([
            'width' => 10490,
            'layout' => 'fixed',
        ]);
        $headerTable->addRow(2073);
        $logoCell = $headerTable->addCell(1646, [
            'borderBottom' => ['val' => 'thickThinMediumGap', 'sz' => 12, 'color' => '000000'],
        ]);
        $logoCell->addImage($logoPath, [
            'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(2.07),
            'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(2.81),
        ]);

        $textCell = $headerTable->addCell(8844, [
            'borderBottom' => ['val' => 'thickThinMediumGap', 'sz' => 12, 'color' => '000000'],
        ]);
        $textCell->addText('PEMERINTAH KABUPATEN PONOROGO', [
            'name' => 'Arial', 'size' => 15, 'bold' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]);
        $textCell->addText('DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK', [
            'name' => 'Arial', 'size' => 16, 'bold' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]);
        $textCell->addText('Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418', [
            'name' => 'Arial', 'size' => 12,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]);
        $textCell->addText('Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id', [
            'name' => 'Arial', 'size' => 12, 'italic' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]);
        $textCell->addText('P O N O R O G O', [
            'name' => 'Arial', 'size' => 18, 'bold' => true,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
        ]);

        $section->addText('', null, $spacing);
        $section->addText('', null, $spacing);

        $section->addText('Ponorogo, ' . $permohonan->created_at->format('d F Y'), $fontTNR, $right + $spacing);

        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
        $section->addText('Hal        : Permohonan Peminjaman ' . ($halItems ?: 'Barang Inventaris'), $fontTNR, $spacing);

        $section->addText('', null, $spacing);

        $section->addText('Kepada', $fontTNR, $spacing);
        $section->addText('Yth. Kepala Dinas Komunikasi Informasi dan Statistik.', $fontTNR, $spacing);
        $section->addText('Kabupaten Ponorogo', $fontTNR, $spacing);
        $section->addText('di tempat', $fontTNR, $justify + $spacing);

        $section->addText('');
        $section->addText('Dengan Hormat,', $fontTNR, $spacing);

        $section->addText('Saya yang bertanda tangan di bawah ini :', $fontTNR, [
            'indentation' => ['left' => 360],
        ] + $spacing);

        $identitasTable = $section->addTable(['width' => 0, 'layout' => 'fixed']);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $transparent)->addText('Nama', $fontTNR);
        $identitasTable->addCell(400, $transparent)->addText(':', $fontTNR);
        $identitasTable->addCell(6400, $transparent)->addText($permohonan->nama_peminjam, $fontTNR);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $transparent)->addText('NRP', $fontTNR);
        $identitasTable->addCell(400, $transparent)->addText(':', $fontTNR);
        $identitasTable->addCell(6400, $transparent)->addText($permohonan->nik, $fontTNR);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $transparent)->addText('Pangkat', $fontTNR);
        $identitasTable->addCell(400, $transparent)->addText(':', $fontTNR);
        $identitasTable->addCell(6400, $transparent)->addText($permohonan->jabatan ?? '-', $fontTNR);
        $identitasTable->addRow();
        $identitasTable->addCell(2400, $transparent)->addText('No. Telepon/HP', $fontTNR);
        $identitasTable->addCell(400, $transparent)->addText(':', $fontTNR);
        $identitasTable->addCell(6400, $transparent)->addText($permohonan->telepon, $fontTNR);

        $section->addText('bermaksud meminjam alat:', $fontTNR, [
            'indentation' => ['left' => 720],
        ] + $spacing);

        $section->addText('', null, $spacing);

        $phpWord->addTableStyle('ItemTable', [
            'borderSize' => 4,
            'borderColor' => '000000',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
        ]);
        $itemTable = $section->addTable('ItemTable');

        $itemTable->addRow();
        $itemTable->addCell(532, ['shading' => ['fill' => 'D9D9D9']])->addText('No', ['bold' => true, 'name' => 'Arial', 'size' => 10]);
        $itemTable->addCell(3689, ['shading' => ['fill' => 'D9D9D9']])->addText('Nama alat', ['bold' => true, 'name' => 'Arial', 'size' => 10]);
        $itemTable->addCell(992, ['shading' => ['fill' => 'D9D9D9']])->addText('Jumlah', ['bold' => true, 'name' => 'Arial', 'size' => 10]);
        $itemTable->addCell(3083, ['shading' => ['fill' => 'D9D9D9']])->addText('Keterangan', ['bold' => true, 'name' => 'Arial', 'size' => 10]);

        foreach ($permohonan->detailPermohonan as $i => $detail) {
            $itemTable->addRow();
            $itemTable->addCell(532)->addText((string) ($i + 1), ['name' => 'Arial', 'size' => 10]);
            $itemTable->addCell(3689)->addText($detail->inventaris?->nama_barang ?? '-', ['name' => 'Arial', 'size' => 10]);
            $numCell = $itemTable->addCell(992);
            $numCell->addText((string) $detail->jumlah, [
                'name' => 'Arial', 'size' => 10,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            $ketCell = $itemTable->addCell(3083);
            $ketText = '';
            if ($detail->jumlah > 1) {
                $ketText = 'Kondisi Baik';
            }
            $ketCell->addText($ketText, [
                'name' => 'Arial', 'size' => 10,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
        }

        $section->addText('', null, $spacing);
        $pKeperluan = $section->addTextRun($spacing);
        $pKeperluan->addText('untuk keperluan ', $fontTNR);
        $pKeperluan->addText($permohonan->keperluan, $fontTNR + ['bold' => true]);
        $pKeperluan->addText('.', $fontTNR);

        $section->addText('', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER] + $spacing);

        $pRencana = $section->addTextRun($justify + ['indentation' => ['firstLine' => 480]] + $spacing);
        $pRencana->addText('Rencananya akan dilaksanakan pada : ', $fontTNR);

        $datePinjam = \Carbon\Carbon::parse($permohonan->tanggal_pinjam);
        $hariNames = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariNames[$datePinjam->format('l')] ?? $datePinjam->format('l');

        $jadwalTable = $section->addTable(['width' => 0, 'layout' => 'fixed']);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $transparent);
        $jadwalTable->addCell(1200, $transparent)->addText('Hari', $fontTNR);
        $jadwalTable->addCell(360, $transparent);
        $jadwalTable->addCell(7000, $transparent)->addText(':  ' . $hari, $fontTNR);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $transparent);
        $jadwalTable->addCell(1200, $transparent)->addText('Tanggal', $fontTNR);
        $jadwalTable->addCell(360, $transparent);
        $jadwalTable->addCell(7000, $transparent)->addText(':  ' . $datePinjam->format('d F Y'), $fontTNR);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $transparent);
        $jadwalTable->addCell(1200, $transparent)->addText('Tempat', $fontTNR);
        $jadwalTable->addCell(360, $transparent);
        $jadwalTable->addCell(7000, $transparent)->addText(':  ' . ($permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-'), $fontTNR);

        $section->addText('', null, $spacing);

        $pDemikian = $section->addTextRun($justify + ['indentation' => ['firstLine' => 480]] + $spacing);
        $pDemikian->addText('Demikian surat permohonan peminjaman ini saya buat dan saya menyatakan akan bertanggung jawab sepenuhnya jika terjadi kerusakan atau kehilangan atas alat di atas selama saya pinjam.', $fontTNR);
        $pDemikian->addText('  Atas perhatian dan bantuannya saya ucapkan terima kasih.', $fontTNR);

        $section->addText('', null, ['spacing' => ['before' => 240]]);

        $ttdTable = $section->addTable(['width' => 100]);

        $ttdTable->addRow();
        $ttdTable->addCell(4416)->addText('Yang menyerahkan,', [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'name' => 'Times New Roman', 'size' => 12,
        ]);
        $ttdTable->addCell(4414)->addText('Yang menerima,', [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'name' => 'Times New Roman', 'size' => 12,
        ]);

        $ttdTable->addRow();
        $leftCell = $ttdTable->addCell(4416);
        for ($i = 0; $i < 5; $i++) {
            $leftCell->addTextBreak();
        }
        $leftCell->addText($permohonan->nama_peminjam ?? '-', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true]);
        $leftCell->addText('NRP. ' . $permohonan->nik, ['name' => 'Times New Roman', 'size' => 12, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $rightCell = $ttdTable->addCell(4414);
        for ($i = 0; $i < 5; $i++) {
            $rightCell->addTextBreak();
        }
        $rightCell->addText($permohonan->nama_peminjam ?? '-', ['name' => 'Times New Roman', 'size' => 12, 'bold' => true]);
        $rightCell->addText('NRP. ' . $permohonan->nik, ['name' => 'Times New Roman', 'size' => 12, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $filename = 'Surat_Peminjaman_' . preg_replace('/[^a-zA-Z0-9]/', '_', $permohonan->nomor_permohonan) . '.docx';

        $tempFile = tempnam(sys_get_temp_dir(), 'silapin_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
