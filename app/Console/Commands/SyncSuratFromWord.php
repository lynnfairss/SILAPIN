<?php

namespace App\Console\Commands;

use App\Models\Permohonan;
use App\Services\DocxParserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SyncSuratFromWord extends Command
{
    protected $signature = 'surat:sync-from-word';
    protected $description = 'Scan OneDrive folder for edited DOCX files and sync changes back to database, then auto-generate PDF';

    private DocxParserService $parser;

    public function __construct(DocxParserService $parser)
    {
        parent::__construct();
        $this->parser = $parser;
    }

    public function handle(): int
    {
        $folderPath = config('app.onedrive_surat_path');

        if (!File::isDirectory($folderPath)) {
            $this->error("OneDrive folder not found: {$folderPath}");
            return 1;
        }

        $files = glob($folderPath . '/*.docx');
        if (empty($files)) {
            $this->info('No DOCX files found in OneDrive folder.');
            return 0;
        }

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $filePath) {
            $filename = basename($filePath, '.docx');

            $permohonan = Permohonan::where('nomor_permohonan', $filename)->first();
            if (!$permohonan) {
                $this->line("  SKIP: {$filename} - no matching permohonan");
                $skipped++;
                continue;
            }

            $lastModified = filemtime($filePath);
            $lastSync = $permohonan->last_sync_at?->timestamp ?? 0;

            if ($lastModified <= $lastSync) {
                $skipped++;
                continue;
            }

            $this->line("  SYNC: {$filename} ({$permohonan->nomor_permohonan})");

            try {
                $parsed = $this->parser->parse($filePath);

                if (empty($parsed)) {
                    $this->warn("    WARNING: No content extracted from DOCX");
                    $failed++;
                    continue;
                }

                $existing = $permohonan->surat_content ?? [];
                $merged = array_merge($existing, array_filter($parsed, fn($v) => $v !== ''));

                $permohonan->update([
                    'surat_content' => $merged,
                    'last_sync_at' => now(),
                    'word_path' => $filePath,
                ]);

                $changedFields = array_keys(array_filter($parsed, fn($v) => $v !== ''));
                $this->info("    OK: Updated " . count($changedFields) . " fields");

                // Auto-generate PDF
                $this->generatePdf($permohonan);
                $synced++;

            } catch (\Exception $e) {
                $this->error("    ERROR: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Sync complete: {$synced} synced, {$skipped} skipped, {$failed} failed");

        Cache::put('surat:last_sync_result', [
            'timestamp' => now()->toDateTimeString(),
            'synced' => $synced,
            'skipped' => $skipped,
            'failed' => $failed,
        ], 60 * 24);

        return 0;
    }

    private function generatePdf(Permohonan $permohonan): void
    {
        try {
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

            $pdfDir = storage_path('app/surat-pdf');
            if (!File::isDirectory($pdfDir)) {
                File::makeDirectory($pdfDir, 0755, true);
            }

            $pdfPath = $pdfDir . '/' . $permohonan->nomor_permohonan . '.pdf';
            $pdf->save($pdfPath);

            $permohonan->update(['pdf_path' => $pdfPath]);

            $this->info("    PDF: Generated → {$permohonan->nomor_permohonan}.pdf");
        } catch (\Exception $e) {
            $this->warn("    PDF FAILED: " . $e->getMessage());
        }
    }
}
