<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;

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
        $global = SuratTemplate::find(1)?->template ?? [];
        $saved = $permohonan->surat_content ?? [];
        return array_merge($defaults, $global, $saved);
    }

    public function index()
    {
        $permohonan = Permohonan::with('instansi')->latest()->get();
        return view('admin.surat.index', compact('permohonan'));
    }

    public function edit(Permohonan $permohonan)
    {
        $content = self::getContent($permohonan);
        $global = SuratTemplate::find(1)?->template ?? [];
        $perSurat = $permohonan->surat_content ?? [];
        return view('admin.surat.edit', compact('permohonan', 'content', 'global', 'perSurat'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        $fields = array_keys(self::defaultContent());
        $validated = $request->only($fields);

        $permohonan->update([
            'surat_content' => $validated,
        ]);

        return redirect()->route('surat.edit', $permohonan->id)
            ->with('success', 'Surat berhasil diperbarui.');
    }

    public function preview(Permohonan $permohonan)
    {
        $permohonan->load('detailPermohonan.inventaris', 'instansi');
        return view('peminjam.surat', compact('permohonan'));
    }
}
