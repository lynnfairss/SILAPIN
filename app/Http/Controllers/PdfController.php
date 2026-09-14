<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function download(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');

        $html = view('peminjam.surat', ['permohonan' => $permohonan, 'forPdf' => true])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('dpi', 150);
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('defaultFont', 'serif');
        $pdf->setOption('margin_left', 15);
        $pdf->setOption('margin_right', 15);
        $pdf->setOption('margin_top', 15);
        $pdf->setOption('margin_bottom', 15);

        $filename = 'Surat-' . $permohonan->nomor_permohonan . '.pdf';

        return $pdf->download($filename);
    }
}
