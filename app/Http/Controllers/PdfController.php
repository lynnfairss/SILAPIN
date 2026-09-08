<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function download(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');

        $html = view('peminjam.surat', compact('permohonan'))->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('dpi', 150);
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('defaultFont', 'serif');

        return $pdf->download('Surat-' . $permohonan->nomor_permohonan . '.pdf');
    }
}
