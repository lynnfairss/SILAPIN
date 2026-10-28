<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\SuratTemplate;
use App\Services\DocxParserService;
use App\Services\SuratPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratController extends Controller
{
    /**
     * Placeholder minimal yang harus ada di template tahunan. Tanpa ini surat
     * tidak bisa diisi dengan benar (mis. cloneRow item akan gagal).
     */
    private const PLACEHOLDER_WAJIB = [
        'nomor',
        'tanggal',
        'nama_peminjam',
        'nrp',
        'item_no',
        'item_nama',
        'item_jumlah',
        'item_keterangan',
        'QR',
        'ttd_kiri_nama',
        'ttd_kanan_nama',
    ];

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

    /**
     * Tentukan isi keenam kolom tanda tangan surat.
     *
     * Kolom kiri berlabel "Yang menyerahkan" diisi nama admin/superadmin yang
     * menyetujui permohonan; kolom kanan "Yang menerima" diisi nama peminjam.
     *
     * Urutan untuk kolom kiri:
     *   1. nilai yang sudah tersimpan di surat_content (hasil sync dari Word)
     *      -- isian manual itu yang menang;
     *   2. nama akun penyetuju beserta NIK-nya; jabatan dikosongkan karena
     *      tabel users tidak menyimpannya;
     *   3. belum ada penyetuju (permohonan belum disetujui) -- kembali ke data
     *      peminjam supaya surat lama tetap sama seperti sebelumnya.
     */
    public static function resolveTtd(Permohonan $permohonan, array $sc): array
    {
        $namaPeminjam = $permohonan->nama_peminjam ?: '-';
        $penyetuju = $permohonan->penyetuju();

        if (filled($sc['ttd_kiri_nama'])) {
            $kiri = [
                'nama'    => $sc['ttd_kiri_nama'],
                'nrp'     => $sc['ttd_kiri_nrp'] ?: '',
                'jabatan' => $sc['ttd_kiri_jabatan'] ?: '',
            ];
        } elseif ($penyetuju?->name) {
            $kiri = [
                'nama'    => $penyetuju->name,
                'nrp'     => $sc['ttd_kiri_nrp'] ?: ($penyetuju->nik ?: ''),
                'jabatan' => $sc['ttd_kiri_jabatan'] ?: '',
            ];
        } else {
            $kiri = [
                'nama'    => $namaPeminjam,
                'nrp'     => $sc['ttd_kiri_nrp'] ?: ($permohonan->nik ?: ''),
                'jabatan' => $sc['ttd_kiri_jabatan'] ?: ($permohonan->jabatan ?: ''),
            ];
        }

        return [
            'kiri_nama'     => $kiri['nama'],
            'kiri_nrp'      => $kiri['nrp'],
            'kiri_jabatan'  => $kiri['jabatan'],
            'kanan_nama'    => $sc['ttd_kanan_nama'] ?: $namaPeminjam,
            'kanan_nrp'     => $sc['ttd_kanan_nrp'] ?: ($permohonan->nik ?: ''),
            'kanan_jabatan' => $sc['ttd_kanan_jabatan'] ?: ($permohonan->jabatan ?: ''),
        ];
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
     * Halaman Upload Surat: unggah DOCX hasil edit dan kelola template tahunan.
     */
    public function upload()
    {
        $permohonan = Permohonan::with('instansi')->latest()->get();
        $templates = SuratTemplate::with('uploader')
            ->whereNotNull('tahun')
            ->orderByDesc('tahun')
            ->get();

        $terpilih = (int) request('permohonan', 0);

        return view('admin.surat.upload', compact('permohonan', 'templates', 'terpilih'));
    }

    /**
     * Terima DOCX surat yang sudah diedit di Word, baca isinya, lalu simpan.
     */
    public function uploadSurat(
        Request $request,
        DocxParserService $parser,
        SuratPdfService $pdf
    ) {
        $request->validate([
            'permohonan_id' => ['required', 'exists:permohonans,id'],
            'file'          => ['required', 'file', 'mimes:docx', 'max:10240'],
        ], [], [
            'permohonan_id' => 'surat',
            'file'          => 'berkas DOCX',
        ]);

        $berkas = $request->file('file');

        try {
            $parsed = $parser->parse($berkas->getRealPath());
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'file' => 'Berkas DOCX tidak bisa dibaca: ' . $e->getMessage(),
            ]);
        }

        if (empty($parsed)) {
            return back()->withInput()->withErrors([
                'file' => 'Isi surat tidak terbaca dari DOCX. Pastikan berkas dibuat dari template surat aplikasi.',
            ]);
        }

        $permohonan = Permohonan::findOrFail($request->integer('permohonan_id'));

        $relative = $berkas->storeAs(
            'surat-docx',
            $permohonan->nomor_permohonan . '.docx',
            'local'
        );

        $merged = array_merge(
            $permohonan->surat_content ?? [],
            array_filter($parsed, fn ($nilai) => $nilai !== '')
        );

        $permohonan->update([
            'surat_content' => $merged,
            'last_sync_at'  => now(),
            'word_path'     => Storage::disk('local')->path($relative),
        ]);

        $pdf->buat($permohonan);

        return back()->with('success', 'Surat ' . $permohonan->nomor_permohonan . ' berhasil diunggah. Isi surat sudah diperbarui dan PDF dibuat ulang.');
    }

    /**
     * Simpan template DOCX untuk satu tahun (menggantikan template tahun itu
     * bila sudah ada).
     */
    public function uploadTemplate(Request $request)
    {
        $data = $request->validate([
            'tahun'         => ['required', 'integer', 'between:2000,2100'],
            'template_file' => ['required', 'file', 'mimes:docx', 'max:10240'],
        ], [], [
            'tahun'         => 'tahun template',
            'template_file' => 'berkas template',
        ]);

        $berkas = $request->file('template_file');

        try {
            $variabel = (new TemplateProcessor($berkas->getRealPath()))->getVariables();
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'template_file' => 'Berkas template tidak bisa dibaca: ' . $e->getMessage(),
            ]);
        }

        $kurang = array_values(array_diff(self::PLACEHOLDER_WAJIB, $variabel));
        if ($kurang) {
            return back()->withInput()->withErrors([
                'template_file' => 'Template tidak valid. Placeholder berikut tidak ditemukan: '
                    . implode(', ', $kurang) . '.',
            ]);
        }

        $relative = $berkas->storeAs(
            'surat-templates',
            'template_peminjaman_' . $data['tahun'] . '.docx',
            'local'
        );

        SuratTemplate::updateOrCreate(
            ['tahun' => (int) $data['tahun']],
            [
                'nama_file'   => $berkas->getClientOriginalName(),
                'path'        => $relative,
                'uploaded_by' => auth()->id(),
            ]
        );

        return back()->with('success', 'Template surat tahun ' . $data['tahun'] . ' berhasil disimpan.');
    }

    public function destroyTemplate(SuratTemplate $template)
    {
        if (filled($template->path)) {
            Storage::disk('local')->delete($template->path);
        }

        $tahun = $template->tahun;
        $template->delete();

        return back()->with('success', 'Template surat tahun ' . $tahun . ' dihapus. Surat tahun itu akan memakai template default.');
    }

    /**
     * Unduh DOCX surat yang terakhir diunggah admin.
     */
    public function berkas(Permohonan $permohonan)
    {
        $path = $permohonan->word_path;

        abort_unless($path && is_file($path), 404);

        $nama = 'Surat_' . preg_replace('/[^A-Za-z0-9]/', '_', $permohonan->nomor_permohonan) . '.docx';

        return response()->download($path, $nama);
    }
}
