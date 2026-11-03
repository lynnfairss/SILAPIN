<?php

namespace App\Console\Commands;

use App\Services\SuratTemplateScanner;
use Illuminate\Console\Command;

class ScanSuratTemplates extends Command
{
    protected $signature = 'surat:scan-templates';

    protected $description = 'Daftarkan berkas template surat yang sudah ada ke database tanpa menghapus file lama';

    public function handle(SuratTemplateScanner $scanner): int
    {
        $hasil = $scanner->scan();

        $this->info('Pemindaian template surat selesai.');
        $this->line('Berkas ditemukan : ' . $hasil['berkas']);
        $this->line('Template baru    : ' . $hasil['baru']);
        $this->line('Dilewati (duplikat): ' . $hasil['dilewati']);

        return self::SUCCESS;
    }
}
