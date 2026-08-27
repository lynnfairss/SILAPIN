<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

$phpWord = new PhpWord();

$section = $phpWord->addSection([
    'pageSizeW' => 11906,
    'pageSizeH' => 16838,
    'marginTop' => 1135,
    'marginRight' => 1800,
    'marginBottom' => 1440,
    'marginLeft' => 1276,
]);

$fontB15 = ['name' => 'Arial', 'size' => 15, 'bold' => true];
$fontB16 = ['name' => 'Arial', 'size' => 16, 'bold' => true];
$fontB18 = ['name' => 'Arial', 'size' => 18, 'bold' => true];
$fontSmall = ['name' => 'Arial', 'size' => 10];
$fontTNR = ['name' => 'Times New Roman', 'size' => 12];
$center = ['alignment' => 'center'];
$right = ['alignment' => 'right'];

$section->addParagraph('PEMERINTAH KABUPATEN PONOROGO', $fontB15 + $center);
$section->addParagraph('DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK', $fontB16 + $center);
$section->addParagraph('Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418', $fontSmall + $center);
$section->addParagraph('Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id', $fontSmall + $center);
$section->addParagraph('P O N O R O G O', $fontB18 + $center);

$section->addParagraph('');
$section->addParagraph('{{TANGGAL_SURAT}}', $fontTNR + $right);
$section->addParagraph('Hal        : Permohonan Peminjaman {{NAMA_BARANG}}', $fontTNR);
$section->addParagraph('');
$section->addParagraph('Kepada', $fontTNR);
$section->addParagraph('Yth. Kepala Dinas Komunikasi Informasi dan Statistik', $fontTNR);
$section->addParagraph('di tempat', $fontTNR);
$section->addParagraph('');
$section->addParagraph('Dengan Hormat,', $fontTNR);
$section->add_paragraph('');

$p = $section->addParagraph($fontTNR);
$p->addText('Saya yang bertanda tangan di bawah ini :', $fontTNR);

$section->addParagraph('');
$section->addParagraph("\tNama            : {{NAMA}}", $fontTNR);
$section->addParagraph("\tNRP/NIK         : {{NIK}}", $fontTNR);
$section->addParagraph("\tPangkat/Jabatan : {{JABATAN}}", $fontTNR);
$section->addParagraph("\tNo. Telepon/HP  : {{TELEPON}}", $fontTNR);
$section->add_paragraph('');

$p2 = $section->addParagraph($fontTNR);
$p2->addText("\tbermaksud meminjam alat:", $fontTNR);
$section->addParagraph('');

$tableStyle = [
    'borderSize' => 4,
    'borderColor' => '000000',
];
$phpWord->addTableStyle('ItemTable', $tableStyle);

$table = $section->addTable('ItemTable');
$table->addRow();
$cellNo = $table->addCell(500);
$cellNo->addText('No', ['bold' => true, 'name' => 'Arial', 'size' => 10]);
$cellNama = $table->addCell(3500);
$cellNama->addText('Nama Alat', ['bold' => true, 'name' => 'Arial', 'size' => 10]);
$cellJml = $table->addCell(1000);
$cellJml->addText('Jumlah', ['bold' => true, 'name' => 'Arial', 'size' => 10]);
$cellKet = $table->addCell(2500);
$cellKet->addText('Keterangan', ['bold' => true, 'name' => 'Arial', 'size' => 10]);

$table->addRow();
$table->addCell(500)->addText('1.');
$table->addCell(3500)->addText('{{NAMA_BARANG}}');
$table->addCell(1000)->addText('{{JUMLAH}}');
$table->addCell(2500)->addText('{{KETERANGAN}}');

$section->addParagraph('');
$section->addParagraph('untuk keperluan {{KEPERLUAN}}.', $fontTNR);
$section->addParagraph('');
$section->addParagraph('Rencananya akan dilaksanakan pada:', $fontTNR);
$section->addParagraph("\tHari    : {{HARI}}", $fontTNR);
$section->addParagraph("\tTanggal : {{TANGGAL_PINJAM}}", $fontTNR);
$section->addParagraph("\tTempat  : {{TEMPAT}}", $fontTNR);
$section->add_paragraph('');

$section->addParagraph('Demikian surat permohonan peminjaman ini saya buat dan saya menyatakan akan bertanggung jawab sepenuhnya jika terjadi kerusakan atau kehilangan atas alat di atas selama saya pinjam. Atas perhatian dan bantuannya saya ucapkan terima kasih.', $fontTNR);
$section->addParagraph('');
$section->add_paragraph('');

$sigTable = $section->addTable();
$sigTable->addRow();
$sigTable->addCell(4500)->addText('Yang menyerahkan,', ['alignment' => 'center']);
$sigTable->addCell(4500)->addText('Yang menerima,', ['alignment' => 'center']);

for ($i = 0; $i < 5; $i++) {
    $sigTable->addRow();
    $sigTable->addCell(4500)->addText('');
    $sigTable->addCell(4500)->addText('');
}

$sigTable->addRow();
$sigTable->addCell(4500)->addText('{{NAMA_PENYERAH}}', ['alignment' => 'center', 'bold' => true]);
$sigTable->addCell(4500)->addText('{{NAMA}}', ['alignment' => 'center', 'bold' => true]);

$sigTable->addRow();
$sigTable->addCell(4500)->addText('NIP. {{NIP_PENYERAH}}', ['alignment' => 'center']);
$sigTable->addCell(4500)->addText('NRP/NIK. {{NIK}}', ['alignment' => 'center']);

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save(__DIR__ . '/surat_peminjaman.docx');

echo "Template berhasil dibuat!" . PHP_EOL;
echo "Path: " . __DIR__ . '/surat_peminjaman.docx' . PHP_EOL;
