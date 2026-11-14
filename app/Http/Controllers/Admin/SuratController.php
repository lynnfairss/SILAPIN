<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\SuratTemplate;
use App\Services\DocxParserService;
use App\Services\SuratPdfService;
use App\Services\SuratTemplateScanner;
use App\Services\TransisiStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class SuratController extends Controller
{
    /**
     * Placeholder minimal yang harus ada di template tahunan. Tanpa ini surat
     * tidak bisa diisi dengan benar (mis. cloneRow item akan gagal).
     */
    public const PLACEHOLDER_WAJIB = [
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
     *   2. nama akun penyetuju beserta NIP-nya (kolom users.nip); jabatan
     *      dikosongkan karena akun admin tidak menyimpannya;
     *   3. belum ada penyetuju (permohonan belum disetujui) -- kembali ke data
     *      peminjam supaya surat lama tetap sama seperti sebelumnya.
     */
    public static function resolveTtd(Permohonan $permohonan, array $sc): array
    {
        $namaPeminjam = $permohonan->nama_peminjam ?: '-';
        $penyetuju = $permohonan->penyetuju();

        // Nilai lama dari Word bisa masih berlabel NRP./NIK./NIP.; buang
        // prefix-nya karena view dan template sudah menambahkan label NIP.
        $nrpTersimpan = preg_replace(
            '/^(?:NRP|NIK|NIP)\.\s*/i',
            '',
            (string) ($sc['ttd_kiri_nrp'] ?? '')
        );

        if (filled($sc['ttd_kiri_nama'])) {
            $kiri = [
                'nama'    => $sc['ttd_kiri_nama'],
                'nrp'     => $nrpTersimpan ?: '',
                'jabatan' => $sc['ttd_kiri_jabatan'] ?: '',
            ];
        } elseif ($penyetuju?->name) {
            $kiri = [
                'nama'    => $penyetuju->name,
                'nrp'     => $nrpTersimpan ?: ($penyetuju->nip ?: ''),
                'jabatan' => $sc['ttd_kiri_jabatan'] ?: '',
            ];
        } else {
            $kiri = [
                'nama'    => $namaPeminjam,
                'nrp'     => $nrpTersimpan ?: ($permohonan->nik ?: ''),
                'jabatan' => $sc['ttd_kiri_jabatan'] ?: ($permohonan->jabatan ?: ''),
            ];
        }

        $nrpKananTersimpan = preg_replace(
            '/^(?:NRP|NIK|NIP)\.\s*/i',
            '',
            (string) ($sc['ttd_kanan_nrp'] ?? '')
        );

        return [
            'kiri_nama'     => $kiri['nama'],
            'kiri_nrp'      => $kiri['nrp'],
            'kiri_jabatan'  => $kiri['jabatan'],
            'kanan_nama'    => $sc['ttd_kanan_nama'] ?: $namaPeminjam,
            'kanan_nrp'     => $nrpKananTersimpan ?: ($permohonan->nik ?: ''),
            'kanan_jabatan' => $sc['ttd_kanan_jabatan'] ?: ($permohonan->jabatan ?: ''),
        ];
    }

    public function index()
    {
        $permohonan = Permohonan::with('instansi', 'suratTemplate')
            ->whereIn('status', TransisiStatus::SURAT_BISA_DILIHAT)
            ->latest()
            ->get();

        return view('admin.surat.index', compact('permohonan'));
    }

    public function preview(Permohonan $permohonan)
    {
        abort_unless($permohonan->bolehLihatSurat(), 403, $permohonan->pesanSuratTidakTersedia());

        $permohonan->load('detailPermohonan.inventaris', 'instansi');
        return view('peminjam.surat', compact('permohonan'));
    }

    /**
     * Halaman Upload Surat: unggah DOCX hasil edit dan kelola template tahunan.
     */
    public function upload()
    {
        $permohonan = Permohonan::with('instansi', 'suratTemplate')
            ->whereIn('status', TransisiStatus::SURAT_BISA_DILIHAT)
            ->latest()
            ->get();

        $templates = SuratTemplate::withTrashed()
            ->with('uploader')
            ->whereNotNull('tahun')
            ->orderByDesc('tahun')
            ->orderByDesc('versi')
            ->get();

        $terpilih = (int) request('permohonan', 0);
        $suratTerpilih = $terpilih ? $permohonan->firstWhere('id', $terpilih) : null;

        $placeholderWajib = self::PLACEHOLDER_WAJIB;

        return view('admin.surat.upload', compact(
            'permohonan', 'templates', 'terpilih', 'suratTerpilih', 'placeholderWajib'
        ));
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

        $permohonan = Permohonan::findOrFail($request->integer('permohonan_id'));

        if (! $permohonan->bolehLihatSurat()) {
            return back()->withInput()->withErrors([
                'permohonan_id' => $permohonan->pesanSuratTidakTersedia(),
            ]);
        }

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

        // Nama berkas diturunkan dari nomor surat, tetapi karakter selain
        // huruf/angka/titik/dash diganti supaya tidak membentuk sub-folder
        // (mis. nomor lama yang memuat "/").
        $namaBerkas = preg_replace('/[^A-Za-z0-9._-]/', '_', $permohonan->nomor_permohonan) . '.docx';

        $relative = $berkas->storeAs('surat-docx', $namaBerkas, 'local');

        if (! $relative) {
            return back()->withInput()->withErrors([
                'file' => 'Berkas DOCX gagal disimpan di server. Periksa izin folder storage lalu coba lagi.',
            ]);
        }

        $merged = array_merge(
            $permohonan->surat_content ?? [],
            array_filter($parsed, fn ($nilai) => $nilai !== '')
        );

        $permohonan->update([
            'surat_content' => $merged,
            'last_sync_at'  => now(),
            'word_path'     => Storage::disk('local')->path($relative),
        ]);

        // PDF hanya pelengkap: kalau gagal, DOCX tetap tersimpan. Jangan
        // mengklaim PDF berhasil dibuat saat layanan mengembalikan null.
        if ($pdf->buat($permohonan) === null) {
            return back()->with('error', 'DOCX surat ' . $permohonan->nomor_permohonan
                . ' berhasil diperbarui, tetapi PDF gagal dibuat. PDF lama sudah dihapus agar tidak '
                . 'menampilkan isi yang keliru; coba unggah ulang atau unduh DOCX untuk memeriksa.');
        }

        return back()->with('success', 'Surat ' . $permohonan->nomor_permohonan . ' berhasil diunggah. Isi surat sudah diperbarui dan PDF dibuat ulang.');
    }

    /**
     * Simpan template DOCX sebagai versi baru untuk satu tahun.
     *
     * Versi baru otomatis aktif. Versi lama tidak dihapus dan tetap bisa
     * diaktifkan kembali kapan saja.
     */
    public function uploadTemplate(Request $request)
    {
        $data = $request->validate([
            'tahun'         => ['required', 'integer', 'between:2000,2100'],
            'template_file' => ['required', 'file', 'mimes:docx', 'max:10240'],
            'catatan'       => ['nullable', 'string', 'max:255'],
        ], [], [
            'tahun'         => 'tahun template',
            'template_file' => 'berkas template',
            'catatan'       => 'catatan',
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

        $hash = hash_file('sha256', $berkas->getRealPath());
        $duplikat = SuratTemplate::withTrashed()->where('hash', $hash)->first();
        if ($duplikat) {
            return back()->withInput()->withErrors([
                'template_file' => 'Berkas ini identik dengan template tahun '
                    . $duplikat->tahun . ' versi ' . $duplikat->versi . '.',
            ]);
        }

        $tahun = (int) $data['tahun'];
        $versi = SuratTemplate::nextVersi($tahun);

        // Nama acak supaya file versi lama tidak tertimpa.
        $relative = $berkas->storeAs(
            'surat-templates',
            (string) Str::uuid() . '.docx',
            'local'
        );

        // Hanya satu template aktif per tahun.
        SuratTemplate::where('tahun', $tahun)->update(['is_active' => false]);

        SuratTemplate::create([
            'tahun'       => $tahun,
            'versi'       => $versi,
            'is_active'   => true,
            'nama_file'   => $berkas->getClientOriginalName(),
            'path'        => $relative,
            'hash'        => $hash,
            'ukuran'      => $berkas->getSize(),
            'catatan'     => $data['catatan'] ?? null,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Template surat tahun ' . $tahun . ' versi ' . $versi
            . ' berhasil disimpan dan diaktifkan. Surat yang dibuat setelah ini memakainya; '
            . 'surat lama dan versi lama tetap tersimpan.');
    }

    /**
     * Aktifkan kembali template versi lama untuk tahunnya.
     */
    public function activateTemplate(SuratTemplate $template)
    {
        SuratTemplate::where('tahun', $template->tahun)->update(['is_active' => false]);
        $template->update(['is_active' => true]);

        return back()->with('success', 'Template tahun ' . $template->tahun
            . ' versi ' . $template->versi . ' diaktifkan untuk surat yang dibuat setelah ini. '
            . 'Surat lama tetap memakai versinya sendiri.');
    }

    /**
     * Matikan template tanpa mengarsipkannya.
     *
     * Surat yang sudah dibuat tidak berubah karena versinya tercatat pada
     * masing-masing surat. Yang terpengaruh hanya surat yang dibuat setelahnya.
     */
    public function deactivateTemplate(SuratTemplate $template)
    {
        if (! $template->is_active) {
            return back()->with('error', 'Template tahun ' . $template->tahun
                . ' versi ' . $template->versi . ' sudah tidak aktif.');
        }

        $template->update(['is_active' => false]);

        $lain = SuratTemplate::aktif()->where('tahun', $template->tahun)->exists();

        return back()->with('success', 'Template tahun ' . $template->tahun
            . ' versi ' . $template->versi . ' dinonaktifkan. Surat yang sudah ada tetap memakai versinya; '
            . ($lain
                ? 'surat baru tahun itu memakai versi aktif lainnya.'
                : 'surat baru tahun ' . $template->tahun
                    . ' memakai versi terbaru tahun itu yang masih tersimpan.'));
    }

    /**
     * Arsipkan template. File fisik tidak dihapus, jadi surat tahun itu masih
     * bisa dicetak ulang memakai versi tersebut.
     */
    public function destroyTemplate(SuratTemplate $template)
    {
        $tahun = $template->tahun;
        $versi = $template->versi;

        $template->update(['is_active' => false]);
        $template->delete();

        return back()->with('success', 'Template tahun ' . $tahun . ' versi ' . $versi
            . ' diarsipkan. File tetap tersimpan dan bisa dipulihkan.');
    }

    /**
     * Pulihkan template yang diarsipkan.
     */
    public function restoreTemplate(int $template)
    {
        $data = SuratTemplate::withTrashed()->findOrFail($template);
        $data->restore();

        if (! SuratTemplate::aktif()->where('tahun', $data->tahun)->exists()) {
            $data->update(['is_active' => true]);
        }

        return back()->with('success', 'Template tahun ' . $data->tahun
            . ' versi ' . $data->versi . ' dipulihkan.');
    }

    /**
     * Hapus permanen template yang sudah diarsipkan: baris database beserta
     * berkas DOCX-nya. Ditolak selama masih ada surat yang mencatat versinya,
     * supaya menghapus template tidak mengubah surat-surat lama.
     */
    public function destroyPermanent(int $template)
    {
        $data = SuratTemplate::onlyTrashed()->findOrFail($template);

        $pemakai = Permohonan::where('surat_template_id', $data->id)->count();

        if ($pemakai > 0) {
            return back()->with('error', 'Template tahun ' . $data->tahun . ' versi ' . $data->versi
                . ' masih tercatat pada ' . $pemakai . ' surat. Biarkan tetap diarsipkan; '
                . 'hapus permanen hanya bila surat-surat itu memang tidak ada.');
        }

        $berkas = $data->path;
        $data->forceDelete();

        if ($berkas) {
            Storage::disk('local')->delete($berkas);
        }

        return back()->with('success', 'Template tahun ' . $data->tahun . ' versi ' . $data->versi
            . ' dihapus permanen beserta berkasnya. Berkas aslinya di folder public/templates '
            . 'tidak ikut terhapus, jadi jangan pindai ulang bila tidak ingin versi itu kembali.');
    }

    /**
     * Pindai folder template lama dan daftarkan ke database.
     */
    public function scanTemplates(SuratTemplateScanner $scanner)
    {
        $hasil = $scanner->scan(auth()->id());

        return back()->with('success', 'Pemindaian selesai: ' . $hasil['baru']
            . ' template baru, ' . $hasil['dilewati'] . ' dilewati (duplikat).');
    }

    /**
     * Pilih template tertentu untuk satu surat.
     */
    public function assignTemplate(Request $request, Permohonan $permohonan)
    {
        if (! $permohonan->bolehLihatSurat()) {
            return back()->with('error', $permohonan->pesanSuratTidakTersedia());
        }

        $data = $request->validate([
            'surat_template_id' => ['nullable', 'integer', 'exists:surat_templates,id'],
        ], [], [
            'surat_template_id' => 'template surat',
        ]);

        $permohonan->update([
            'surat_template_id' => $data['surat_template_id'] ?: null,
        ]);

        $pesan = $permohonan->surat_template_id
            ? 'Template surat ' . $permohonan->nomor_permohonan . ' berhasil diubah.'
            : 'Template surat ' . $permohonan->nomor_permohonan . ' dikembalikan ke otomatis.';

        return back()->with('success', $pesan);
    }

    /**
     * Unduh DOCX surat yang terakhir diunggah admin.
     */
    public function berkas(Permohonan $permohonan)
    {
        abort_unless($permohonan->bolehLihatSurat(), 403, $permohonan->pesanSuratTidakTersedia());

        $path = $permohonan->word_path;

        abort_unless($path && is_file($path), 404);

        $nama = 'Surat_' . preg_replace('/[^A-Za-z0-9]/', '_', $permohonan->nomor_permohonan) . '.docx';

        return response()->download($path, $nama);
    }
}
