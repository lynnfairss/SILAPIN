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

        $fTNR       = ['name' => 'Times New Roman', 'size' => 12];
        $fArial     = ['name' => 'Arial', 'size' => 10];
        $fArial9    = ['name' => 'Arial', 'size' => 9];
        $fArialNum    = ['name' => 'Arial', 'size' => 5.5];

        $para = ['lineHeight' => 1.5, 'spaceAfter' => 0];
        $pRight   = $para + ['alignment' => Jc::RIGHT];
        $pJustify = $para + ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 480]];
        $pFirst   = $para + ['indentation' => ['firstLine' => 480]];
        $pKop     = ['lineHeight' => 1.25, 'spaceAfter' => 0, 'alignment' => Jc::CENTER];
        $pList    = ['lineHeight' => 1.5, 'spaceAfter' => 0];
        $pListC   = $pList + ['alignment' => Jc::CENTER];
        $pTtd     = ['lineHeight' => 1.2, 'spaceAfter' => 0, 'alignment' => Jc::CENTER];
        $pTtdLabel = ['lineHeight' => 1.5, 'spaceAfter' => 0, 'alignment' => Jc::CENTER];
        $pQr      = ['lineHeight' => 1.2, 'spaceAfter' => 0, 'alignment' => Jc::RIGHT];
        // Nomor di bawah QR: kotak = lebar QR (1191 twips dari 3159) → teks persis di bawah barcode.
        $pNomor   = ['lineHeight' => 1.2, 'spaceAfter' => 0, 'alignment' => Jc::CENTER,
                     'indentation' => ['left' => 3159 - 1191]];

        $gapFont = fn (int $pt) => ['name' => 'Times New Roman', 'size' => $pt];
        $gapPara = ['lineHeight' => 1, 'spaceAfter' => 0];

        $kopBorder = [
            'borderBottomStyle' => 'double',
            'borderBottomSize' => 12,
            'borderBottomColor' => '000000',
        ];

        // Lebar konten A4 dgn margin 1440 twips = 11906 - 2880 = 9026 twips.
        // Tiga sel simetris (16% / 68% / 16%) supaya pusat teks kop = pusat halaman.
        $headerTable = $section->addTable(['width' => 9026, 'layout' => 'fixed']);
        $headerTable->addRow(1700);

        $logoCell = $headerTable->addCell(1219, $kopBorder + ['valign' => 'center']);
        $logoCell->addText('${LOGO}', ['name' => 'Arial', 'size' => 8, 'color' => '999999'], $pKop);

        $textCell = $headerTable->addCell(6588, $kopBorder + ['valign' => 'center']);
        $textCell->addText('PEMERINTAH KABUPATEN PONOROGO', ['name' => 'Arial', 'size' => 12, 'bold' => true], $pKop);
        $textCell->addText('DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK', ['name' => 'Arial', 'size' => 12, 'bold' => true], $pKop);
        $textCell->addText('Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418', ['name' => 'Arial', 'size' => 10], $pKop);
        $textCell->addText('Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id', ['name' => 'Arial', 'size' => 10, 'italic' => true], $pKop);
        $textCell->addText('P O N O R O G O', ['name' => 'Arial', 'size' => 14, 'bold' => true], $pKop);

        $headerTable->addCell(1219, $kopBorder + ['valign' => 'center']);

        $section->addText('', $gapFont(8), $gapPara);

        $infoTable = $section->addTable(['width' => 9026, 'layout' => 'fixed']);
        $infoTable->addRow();
        $infoTable->addCell(5867)->addText('Hal        : ${hal}', $fTNR, $pList);
        $infoRight = $infoTable->addCell(3159);
        $infoRight->addText('${tanggal}', $fTNR, $pRight);
        $infoRight->addText('${QR}', $fArial9, $pQr);
        $infoRight->addText('${nomor}', $fArialNum, $pNomor);

        $section->addText('', $gapFont(4), $gapPara);

        $section->addText('Kepada', $fTNR, $pList);
        $section->addText('${kepada_yth}', $fTNR, $pList);
        $section->addText('${kepada_kab}', $fTNR, $pList);
        $section->addText('${kepada_tempat}', $fTNR, $pList);

        $section->addText('', $gapFont(6), $gapPara);

        $section->addText('${pembuka}', $fTNR, $pList);

        $section->addText('', $gapFont(4), $gapPara);

        $section->addText('${saya_yang}', $fTNR, $pFirst);

        $identitasTable = $section->addTable(['width' => 8000, 'layout' => 'fixed']);
        $pIdentitas = $pList + ['indentation' => ['left' => 480]];
        $identitasTable->addRow();
        $identitasTable->addCell(2000)->addText('Nama', $fTNR, $pIdentitas);
        $identitasTable->addCell(400)->addText(':', $fTNR, $pIdentitas);
        $identitasTable->addCell(5600)->addText('${nama_peminjam}', $fTNR, $pIdentitas);
        $identitasTable->addRow();
        $identitasTable->addCell(2000)->addText('NRP', $fTNR, $pIdentitas);
        $identitasTable->addCell(400)->addText(':', $fTNR, $pIdentitas);
        $identitasTable->addCell(5600)->addText('${nrp}', $fTNR, $pIdentitas);
        $identitasTable->addRow();
        $identitasTable->addCell(2000)->addText('Pangkat', $fTNR, $pIdentitas);
        $identitasTable->addCell(400)->addText(':', $fTNR, $pIdentitas);
        $identitasTable->addCell(5600)->addText('${pangkat}', $fTNR, $pIdentitas);
        $identitasTable->addRow();
        $identitasTable->addCell(2000)->addText('No. Telepon/HP', $fTNR, $pIdentitas);
        $identitasTable->addCell(400)->addText(':', $fTNR, $pIdentitas);
        $identitasTable->addCell(5600)->addText('${telepon}', $fTNR, $pIdentitas);

        $section->addText('', $gapFont(4), $gapPara);

        $section->addText('${bermaksud}', $fTNR, $pList);

        $section->addText('', $gapFont(4), $gapPara);

        $phpWord->addTableStyle('ItemTable', [
            'borderSize' => 8,
            'borderColor' => '000000',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 60,
            'cellMarginRight' => 60,
        ]);
        $itemTable = $section->addTable('ItemTable');

        $itemTable->addRow();
        $itemTable->addCell(532, ['shading' => ['fill' => 'D9D9D9']])->addText('No', $fArial + ['bold' => true], $pListC);
        $itemTable->addCell(3689, ['shading' => ['fill' => 'D9D9D9']])->addText('Nama alat', $fArial + ['bold' => true], $pListC);
        $itemTable->addCell(992, ['shading' => ['fill' => 'D9D9D9']])->addText('Jumlah', $fArial + ['bold' => true], $pListC);
        $itemTable->addCell(3083, ['shading' => ['fill' => 'D9D9D9']])->addText('Keterangan', $fArial + ['bold' => true], $pListC);

        $itemTable->addRow();
        $itemTable->addCell(532)->addText('${item_no}', $fArial, $pListC);
        $itemTable->addCell(3689)->addText('${item_nama}', $fArial, $pList);
        $itemTable->addCell(992)->addText('${item_jumlah}', $fArial, $pListC);
        $itemTable->addCell(3083)->addText('${item_keterangan}', $fArial, $pListC);

        $section->addText('', $gapFont(4), $gapPara);

        $pKeperluan = $section->addTextRun($pJustify);
        $pKeperluan->addText('untuk keperluan ', $fTNR);
        $pKeperluan->addText('${keperluan}', $fTNR + ['bold' => true]);
        $pKeperluan->addText('.', $fTNR);

        $section->addText('', $gapFont(2), $gapPara);

        $section->addText('${isi}', $fTNR, $pJustify);

        $section->addText('${rencana}', $fTNR, $pJustify);

        $jadwalTable = $section->addTable(['width' => 8000, 'layout' => 'fixed']);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720);
        $jadwalTable->addCell(1400)->addText('${hari_label}', $fTNR, $pList);
        $jadwalTable->addCell(300);
        $jadwalTable->addCell(5500)->addText(':  ${hari}', $fTNR, $pList);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720);
        $jadwalTable->addCell(1400)->addText('${tanggal_label}', $fTNR, $pList);
        $jadwalTable->addCell(300);
        $jadwalTable->addCell(5500)->addText(':  ${tanggal_pinjam}', $fTNR, $pList);
        $jadwalTable->addRow();
        $jadwalTable->addCell(720);
        $jadwalTable->addCell(1400)->addText('${tempat_label}', $fTNR, $pList);
        $jadwalTable->addCell(300);
        $jadwalTable->addCell(5500)->addText(':  ${instansi}', $fTNR, $pList);

        $section->addText('', $gapFont(4), $gapPara);

        $pDemikian = $section->addTextRun($pJustify);
        $pDemikian->addText('${penutup} ', $fTNR);
        $pDemikian->addText('${terima_kasih}', $fTNR);

        $section->addText('', $gapFont(2), $gapPara);

        // Dua kolom simetris (50/50) supaya center kolom = 25% dan 75% halaman.
        $ttdTable = $section->addTable(['width' => 9026, 'layout' => 'fixed']);
        $ttdTable->addRow();
        $ttdTable->addCell(4513)->addText('${ttd_kiri_label}', $fTNR, $pTtdLabel);
        $ttdTable->addCell(4513)->addText('${ttd_kanan_label}', $fTNR, $pTtdLabel);

        $ttdTable->addRow();
        $leftCell = $ttdTable->addCell(4513);
        for ($i = 0; $i < 6; $i++) {
            $leftCell->addTextBreak();
        }
        $leftCell->addText('${ttd_kiri_nama}', $fTNR + ['bold' => true], $pTtd);
        $leftCell->addText('NRP. ${ttd_kiri_nrp}', $fTNR, $pTtd);
        $leftCell->addText('${ttd_kiri_jabatan}', $fTNR, $pTtd);

        $rightCell = $ttdTable->addCell(4513);
        for ($i = 0; $i < 6; $i++) {
            $rightCell->addTextBreak();
        }
        $rightCell->addText('${ttd_kanan_nama}', $fTNR + ['bold' => true], $pTtd);
        $rightCell->addText('NRP. ${ttd_kanan_nrp}', $fTNR, $pTtd);
        $rightCell->addText('${ttd_kanan_jabatan}', $fTNR, $pTtd);

        $outputPath = public_path('templates/template_peminjaman.docx');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);

        $this->info("Template generated at: {$outputPath}");
        return 0;
    }
}
