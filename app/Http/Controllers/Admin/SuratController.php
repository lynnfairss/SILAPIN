<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Services\DocxParserService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratController extends Controller
{
    public static function defaultContent(): array
    {
        return [
            'hal'                 => '',
            'kepada_yth'          => 'Yth. Kepala Dinas Komunikasi Informasi dan Statistik.',
            'kepada_kab'          => 'Kabupaten Ponorogo',
            'kepada_tempat'       => 'di tempat',
            'pembuka'             => 'Dengan Hormat,',
            'saya_yang'           => 'Saya yang bertanda tangan di bawah ini :',
            'nama_peminjam'       => '',
            'nik'                 => '',
            'jabatan'             => '',
            'instansi'            => '',
            'bermaksud'           => 'bermaksud meminjam alat:',
            'untuk_keperluan'     => 'untuk keperluan',
            'isi'                 => '',
            'rencana'             => 'Rencananya akan dilaksanakan pada :',
            'hari_label'          => 'Hari',
            'tanggal_label'       => 'Tanggal',
            'tempat_label'        => 'Tempat',
            'penutup'             => 'Demikian surat permohonan peminjaman ini saya buat dan saya menyatakan akan bertanggung jawab sepenuhnya jika terjadi kerusakan atau kehilangan atas alat di atas selama saya pinjam.',
            'terima_kasih'        => 'Atas perhatian dan bantuannya saya ucapkan terima kasih.',
            'ttd_kiri_label'      => 'Yang menyerahkan,',
            'ttd_kiri_nama'       => '',
            'ttd_kiri_nrp'        => '',
            'ttd_kiri_jabatan'    => '',
            'ttd_kanan_label'     => 'Yang menerima,',
            'ttd_kanan_nama'      => '',
            'ttd_kanan_nrp'       => '',
            'ttd_kanan_jabatan'   => '',
        ];
    }

    public static function getContent(Permohonan $permohonan): array
    {
        $defaults = self::defaultContent();
        $saved = $permohonan->surat_content ?? [];
        return array_merge($defaults, $saved);
    }

    public function index()
    {
        $permohonan = Permohonan::with('instansi')->latest()->get();
        return view('admin.surat.index', compact('permohonan'));
    }

    public function preview(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');
        return view('peminjam.surat', compact('permohonan'));
    }

    /**
     * Generate DOCX and save to OneDrive folder for pimpinan to edit in Word.
     */
    public function generateWord(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');
        $sc = self::getContent($permohonan);

        $folderPath = config('app.onedrive_surat_path');
        if (!File::isDirectory($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $templatePath = public_path('templates/template_peminjaman.docx');
        if (!file_exists($templatePath)) {
            return back()->with('error', 'Template surat tidak ditemukan.');
        }

        $template = new TemplateProcessor($templatePath);

        // Resolve values (same as downloadDocx in PermohonanController)
        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
        $halText = $sc['hal'] ?: 'Permohonan Peminjaman ' . ($halItems ?: 'Barang Inventaris');
        $dateText = 'Ponorogo, ' . $permohonan->created_at->format('d F Y');
        $vNama = $sc['nama_peminjam'] ?: $permohonan->nama_peminjam ?? '-';
        $vNrp = $sc['nik'] ?: $permohonan->nik ?? '';
        $vJab = $sc['jabatan'] ?: ($permohonan->jabatan ?? '-');
        $vInst = $sc['instansi'] ?: ($permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-');

        $datePinjam = \Carbon\Carbon::parse($permohonan->tanggal_pinjam);
        $hariNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        $hari = $hariNames[$datePinjam->format('l')] ?? $datePinjam->format('l');

        $xv = fn(string $val) => htmlspecialchars($val, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // Logo — hardcode fallback
        $logoPath = null;
        $candidates = [
            public_path('images/surat/logo-kiri.jpg'),
            public_path('images/logo-kominfo.png'),
        ];
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                $logoPath = $path;
                break;
            }
        }
        if ($logoPath) {
            $template->setImageValue('LOGO', $logoPath);
        }

        $template->setValue('hal', $xv($halText));
        $template->setValue('tanggal', $xv($dateText));
        $template->setValue('kepada_yth', $xv($sc['kepada_yth']));
        $template->setValue('kepada_kab', $xv($sc['kepada_kab']));
        $template->setValue('kepada_tempat', $xv($sc['kepada_tempat']));
        $template->setValue('pembuka', $xv($sc['pembuka']));
        $template->setValue('saya_yang', $xv($sc['saya_yang']));
        $template->setValue('nama_peminjam', $xv($vNama));
        $template->setValue('nrp', $xv($vNrp));
        $template->setValue('pangkat', $xv($vJab));
        $template->setValue('telepon', $xv($permohonan->telepon));
        $template->setValue('bermaksud', $xv($sc['bermaksud']));
        $template->setValue('keperluan', $xv($permohonan->keperluan));
        $template->setValue('isi', $xv(str_replace("\n", ' ', $sc['isi'])));
        $template->setValue('rencana', $xv($sc['rencana']));
        $template->setValue('hari_label', $xv($sc['hari_label']));
        $template->setValue('hari', $xv($hari));
        $template->setValue('tanggal_label', $xv($sc['tanggal_label']));
        $template->setValue('tanggal_pinjam', $xv($datePinjam->format('d F Y')));
        $template->setValue('tempat_label', $xv($sc['tempat_label']));
        $template->setValue('instansi', $xv($vInst));
        $template->setValue('penutup', $xv(str_replace("\n", ' ', $sc['penutup'])));
        $template->setValue('terima_kasih', $xv(str_replace("\n", ' ', $sc['terima_kasih'])));
        $template->setValue('ttd_kiri_label', $xv($sc['ttd_kiri_label']));
        $template->setValue('ttd_kiri_nama', $xv($sc['ttd_kiri_nama'] ?: $permohonan->nama_peminjam ?? '-'));
        $template->setValue('ttd_kiri_nrp', $xv($sc['ttd_kiri_nrp'] ?: $permohonan->nik ?? ''));
        $template->setValue('ttd_kiri_jabatan', $xv($sc['ttd_kiri_jabatan'] ?: $permohonan->jabatan ?? ''));
        $template->setValue('ttd_kanan_label', $xv($sc['ttd_kanan_label']));
        $template->setValue('ttd_kanan_nama', $xv($sc['ttd_kanan_nama'] ?: $permohonan->nama_peminjam ?? '-'));
        $template->setValue('ttd_kanan_nrp', $xv($sc['ttd_kanan_nrp'] ?: $permohonan->nik ?? ''));
        $template->setValue('ttd_kanan_jabatan', $xv($sc['ttd_kanan_jabatan'] ?: $permohonan->jabatan ?? ''));

        // Items
        $items = $permohonan->detailPermohonan;
        $template->cloneRow('item_no', $items->count() ?: 1);
        if ($items->count() > 0) {
            foreach ($items as $i => $detail) {
                $idx = $i + 1;
                $template->setValue('item_no#' . $idx, ($i + 1) . '.');
                $template->setValue('item_nama#' . $idx, $xv($detail->inventaris?->nama_barang ?? '-'));
                $template->setValue('item_jumlah#' . $idx, (string) $detail->jumlah);
                $template->setValue('item_keterangan#' . $idx, $xv($detail->inventaris?->kondisi ?? '-'));
            }
        } else {
            $template->setValue('item_no#1', '1.');
            $template->setValue('item_nama#1', '-');
            $template->setValue('item_jumlah#1', '1');
            $template->setValue('item_keterangan#1', '-');
        }

        $outputPath = $folderPath . '/' . $permohonan->nomor_permohonan . '.docx';
        $template->saveAs($outputPath);

        $permohonan->update(['word_path' => $outputPath]);

        return back()->with('success', "DOCX berhasil dikirim ke OneDrive: {$permohonan->nomor_permohonan}.docx");
    }

    /**
     * Manually trigger sync from OneDrive DOCX files.
     */
    public function syncNow()
    {
        $result = Artisan::call('surat:sync-from-word');
        $output = Artisan::output();

        return back()->with('success', "Sync selesai.\n{$output}");
    }

    /**
     * Show sync status page.
     */
    public function syncStatus()
    {
        $permohonan = Permohonan::with('instansi')
            ->where(function ($q) {
                $q->whereNotNull('word_path')
                  ->orWhereNotNull('last_sync_at');
            })
            ->latest('last_sync_at')
            ->get();

        $lastResult = Cache::get('surat:last_sync_result');

        $folderPath = config('app.onedrive_surat_path');
        $folderExists = File::isDirectory($folderPath);
        $fileCount = $folderExists ? count(glob($folderPath . '/*.docx')) : 0;

        return view('admin.surat.sync-status', compact('permohonan', 'lastResult', 'folderPath', 'folderExists', 'fileCount'));
    }
}
