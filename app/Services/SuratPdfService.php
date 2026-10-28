<?php

namespace App\Services;

use App\Models\Permohonan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Membuat PDF surat dari tampilan HTML aplikasi lalu menyimpannya di disk
 * "local" (storage/app/private/surat-pdf).
 */
class SuratPdfService
{
    public function buat(Permohonan $permohonan): ?string
    {
        try {
            $permohonan->loadMissing('detailPermohonan.inventaris', 'instansi');

            $html = view('peminjam.surat', [
                'permohonan' => $permohonan,
                'forPdf'     => true,
            ])->render();

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

            $relative = 'surat-pdf/' . $permohonan->nomor_permohonan . '.pdf';
            Storage::disk('local')->put($relative, $pdf->output());

            $absolute = Storage::disk('local')->path($relative);
            $permohonan->update(['pdf_path' => $absolute]);

            return $absolute;
        } catch (\Throwable $e) {
            // PDF hanya pelengkap. Kegagalan membuat PDF tidak boleh
            // membatalkan surat DOCX yang sudah diunggah.
            Log::warning('Gagal membuat PDF surat ' . $permohonan->nomor_permohonan . ': ' . $e->getMessage());

            return null;
        }
    }
}
