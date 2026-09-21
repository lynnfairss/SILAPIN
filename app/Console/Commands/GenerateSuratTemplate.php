<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class GenerateSuratTemplate extends Command
{
    protected $signature = 'generate:surat-template';
    protected $description = 'Generate the DOCX surat template with placeholders';

    public function handle()
    {
        $phpWord = new PhpWord();

        $section = $phpWord->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginTop' => 1440,
            'marginRight' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
        ]);

        $fontTNR = ['name' => 'Times New Roman', 'size' => 12];
        $singleLine = ['spacing' => ['after' => 120, 'line' => 300]];
        $right = ['alignment' => Jc::RIGHT];
        $justify = ['alignment' => Jc::BOTH];
        $center = ['alignment' => Jc::CENTER];

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

        // === HEADER ===
        $headerTable = $section->addTable(['width' => 10000, 'layout' => 'fixed']);
        $headerTable->addRow(1700);

        $logoCell = $headerTable->addCell(1600, [
            'borderBottom' => ['val' => 'double', 'sz' => 12, 'color' => '000000'],
            'valign' => 'center',
        ]);
        // Logo placeholder - will be replaced by code via setImageValue
        $logoCell->addText('${LOGO}', ['name' => 'Arial', 'size' => 8, 'color' => '999999']);

        $textCellWidth = 6800;
        $textCell = $headerTable->addCell($textCellWidth, [
            'borderBottom' => ['val' => 'double', 'sz' => 12, 'color' => '000000'],
            'valign' => 'center',
        ]);
        $textCell->addText('PEMERINTAH KABUPATEN PONOROGO', [
            'name' => 'Arial', 'size' => 13, 'bold' => true,
            'alignment' => Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 260],
        ]);
        $textCell->addText('DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK', [
            'name' => 'Arial', 'size' => 13, 'bold' => true,
            'alignment' => Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 260],
        ]);
        $textCell->addText('Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418', [
            'name' => 'Arial', 'size' => 10,
            'alignment' => Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240],
        ]);
        $textCell->addText('Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id', [
            'name' => 'Arial', 'size' => 10, 'italic' => true,
            'alignment' => Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240],
        ]);
        $textCell->addText('P O N O R O G O', [
            'name' => 'Arial', 'size' => 14, 'bold' => true,
            'alignment' => Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 260],
        ]);

        $section->addText('', null, ['spacing' => ['after' => 160]]);

        // === HAL / TANGGAL ===
        $infoTable = $section->addTable(array_merge(['width' => 10000, 'layout' => 'fixed'], $noBorderTable));
        $infoTable->addRow();
        $infoTable->addCell(6500, $noBorderCell)->addText('Hal        : ${hal}', $fontTNR + $singleLine);
        $infoTable->addCell(3500, $noBorderCell)->addText('${tanggal}', $fontTNR + $right + $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === KEPADA ===
        $section->addText('Kepada', $fontTNR, $singleLine);
        $section->addText('${kepada_yth}', $fontTNR, $singleLine);
        $section->addText('${kepada_kab}', $fontTNR, $singleLine);
        $section->addText('${kepada_tempat}', $fontTNR, $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 120]]);

        // === PEMBUKA ===
        $section->addText('${pembuka}', $fontTNR, $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === SAYA YANG BERTANDA TANGAN ===
        $section->addText('${saya_yang}', $fontTNR, $singleLine + ['indentation' => ['left' => 480]]);

        // === IDENTITAS TABLE ===
        $identitasTable = $section->addTable(array_merge(['width' => 8000, 'layout' => 'fixed'], $noBorderTable));
        $identitasTable->addRow();
        $identitasTable->addCell(2000, $noBorderCell + ['indentation' => ['left' => 480]])->addText('Nama', $fontTNR + $singleLine);
        $identitasTable->addCell(400, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(5600, $noBorderCell)->addText('${nama_peminjam}', $fontTNR + $singleLine);
        $identitasTable->addRow();
        $identitasTable->addCell(2000, $noBorderCell + ['indentation' => ['left' => 480]])->addText('NRP', $fontTNR + $singleLine);
        $identitasTable->addCell(400, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(5600, $noBorderCell)->addText('${nrp}', $fontTNR + $singleLine);
        $identitasTable->addRow();
        $identitasTable->addCell(2000, $noBorderCell + ['indentation' => ['left' => 480]])->addText('Pangkat', $fontTNR + $singleLine);
        $identitasTable->addCell(400, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(5600, $noBorderCell)->addText('${pangkat}', $fontTNR + $singleLine);
        $identitasTable->addRow();
        $identitasTable->addCell(2000, $noBorderCell + ['indentation' => ['left' => 480]])->addText('No. Telepon/HP', $fontTNR + $singleLine);
        $identitasTable->addCell(400, $noBorderCell)->addText(':', $fontTNR + $singleLine);
        $identitasTable->addCell(5600, $noBorderCell)->addText('${telepon}', $fontTNR + $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === BERMAKSUD ===
        $section->addText('${bermaksud}', $fontTNR, $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === ITEM TABLE ===
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
        $itemTable->addCell(532, ['shading' => ['fill' => 'D9D9D9']])->addText('No', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);
        $itemTable->addCell(3689, ['shading' => ['fill' => 'D9D9D9']])->addText('Nama alat', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);
        $itemTable->addCell(992, ['shading' => ['fill' => 'D9D9D9']])->addText('Jumlah', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);
        $itemTable->addCell(3083, ['shading' => ['fill' => 'D9D9D9']])->addText('Keterangan', ['bold' => true, 'name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);

        // Single item row with placeholders (will be cloned per item)
        $itemTable->addRow();
        $itemTable->addCell(532)->addText('${item_no}', ['name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);
        $itemTable->addCell(3689)->addText('${item_nama}', ['name' => 'Arial', 'size' => 10]);
        $itemTable->addCell(992)->addText('${item_jumlah}', ['name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);
        $itemTable->addCell(3083)->addText('${item_keterangan}', ['name' => 'Arial', 'size' => 10, 'alignment' => Jc::CENTER]);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === UNTUK KEPERLUAN ===
        $pKeperluan = $section->addTextRun($justify + ['indentation' => ['firstLine' => 480]] + $singleLine);
        $pKeperluan->addText('untuk keperluan ', $fontTNR);
        $pKeperluan->addText('${keperluan}', $fontTNR + ['bold' => true]);
        $pKeperluan->addText('.', $fontTNR);

        // === ISI ===
        $section->addText('', null, ['spacing' => ['after' => 80]]);
        $section->addText('${isi}', $fontTNR, $justify + ['indentation' => ['firstLine' => 480]] + $singleLine);

        // === RENCANA ===
        $section->addText('${rencana}', $fontTNR, $justify + ['indentation' => ['firstLine' => 480]] + $singleLine);

        // === JADWAL TABLE ===
        $jadwalTable = $section->addTable(array_merge(['width' => 8000, 'layout' => 'fixed'], $noBorderTable));
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $noBorderCell);
        $jadwalTable->addCell(1400, $noBorderCell)->addText('${hari_label}', $fontTNR + $singleLine);
        $jadwalTable->addCell(300, $noBorderCell);
        $jadwalTable->addCell(5500, $noBorderCell)->addText(':  ${hari}', $fontTNR + $singleLine);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $noBorderCell);
        $jadwalTable->addCell(1400, $noBorderCell)->addText('${tanggal_label}', $fontTNR + $singleLine);
        $jadwalTable->addCell(300, $noBorderCell);
        $jadwalTable->addCell(5500, $noBorderCell)->addText(':  ${tanggal_pinjam}', $fontTNR + $singleLine);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720, $noBorderCell);
        $jadwalTable->addCell(1400, $noBorderCell)->addText('${tempat_label}', $fontTNR + $singleLine);
        $jadwalTable->addCell(300, $noBorderCell);
        $jadwalTable->addCell(5500, $noBorderCell)->addText(':  ${instansi}', $fontTNR + $singleLine);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === PENUTUP + TERIMA KASIH ===
        $pDemikian = $section->addTextRun($justify + $singleLine);
        $pDemikian->addText('${penutup} ', $fontTNR);
        $pDemikian->addText('${terima_kasih}', $fontTNR);

        $section->addText('', null, ['spacing' => ['after' => 80]]);

        // === TTD TABLE (4-column) ===
        $ttdTable = $section->addTable(array_merge(['width' => 10000, 'layout' => 'fixed'], $noBorderTable));
        $ttdTable->addRow();
        $ttdTable->addCell(500, $noBorderCell);
        $ttdTable->addCell(4500, $noBorderCell)->addText('${ttd_kiri_label}', $fontTNR + $singleLine);
        $ttdTable->addCell(500, $noBorderCell);
        $ttdTable->addCell(4500, $noBorderCell)->addText('${ttd_kanan_label}', $fontTNR + $singleLine);

        $ttdTable->addRow();
        $ttdTable->addCell(500, $noBorderCell);
        $leftCell = $ttdTable->addCell(4500, $noBorderCell);
        for ($i = 0; $i < 6; $i++) {
            $leftCell->addTextBreak();
        }
        $centerStyle = ['name' => 'Times New Roman', 'size' => 12, 'alignment' => Jc::CENTER, 'spacing' => ['after' => 0, 'line' => 240]];
        $leftCell->addText('${ttd_kiri_nama}', $centerStyle + ['bold' => true]);
        $leftCell->addText('NRP. ${ttd_kiri_nrp}', $centerStyle);
        $leftCell->addText('${ttd_kiri_jabatan}', $centerStyle);

        $ttdTable->addCell(500, $noBorderCell);
        $rightCell = $ttdTable->addCell(4500, $noBorderCell);
        for ($i = 0; $i < 6; $i++) {
            $rightCell->addTextBreak();
        }
        $rightCell->addText('${ttd_kanan_nama}', $centerStyle + ['bold' => true]);
        $rightCell->addText('NRP. ${ttd_kanan_nrp}', $centerStyle);
        $rightCell->addText('${ttd_kanan_jabatan}', $centerStyle);

        $outputPath = public_path('templates/template_peminjaman.docx');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);

        $this->info("Template generated at: {$outputPath}");
        return 0;
    }
}
