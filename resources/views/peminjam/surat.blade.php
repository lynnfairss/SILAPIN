<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan - {{ $permohonan->nomor_permohonan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; padding: 25mm; color: #000; font-size: 12pt; line-height: 1.5; }
        @page { margin: 0; size: A4; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 25mm; }
        }
    </style>
</head>
<body>
    @php
        $forPdf = $forPdf ?? false;

        $kiriFile = file_exists(public_path('images/surat/logo-kiri.jpg')) ? 'images/surat/logo-kiri.jpg' : 'images/logo-kominfo.png';
        $kiriFull = public_path($kiriFile);
        if ($forPdf && file_exists($kiriFull)) {
            $ext = strtolower(pathinfo($kiriFull, PATHINFO_EXTENSION));
            $kiriMime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
            $imgLogoKiri = 'data:' . $kiriMime . ';base64,' . base64_encode(file_get_contents($kiriFull));
        } else {
            $imgLogoKiri = asset($kiriFile);
        }

        $imgLogoKanan = null;

        $sc = \App\Http\Controllers\Admin\SuratController::getContent($permohonan);
        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
        $halText = $sc['hal'] ?: 'Permohonan Peminjaman ' . ($halItems ?: 'Barang Inventaris');
        $dateText = 'Ponorogo, ' . $permohonan->created_at->format('d F Y');

        $vNama  = $sc['nama_peminjam'] ?: $permohonan->nama_peminjam ?? '-';
        $vNrp   = $sc['nik']           ?: $permohonan->nik ?? '';
        $vJab   = $sc['jabatan']       ?: $permohonan->jabatan ?? '-';
        $vInst  = $sc['instansi']      ?: ($permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-');

        $ttdKiriNama = $sc['ttd_kiri_nama']  ?: $permohonan->nama_peminjam ?? '-';
        $ttdKiriNrp  = $sc['ttd_kiri_nrp']   ?: $permohonan->nik ?? '';
        $ttdKiriJab  = $sc['ttd_kiri_jabatan']?: $permohonan->jabatan ?? '';
        $ttdKananNama = $sc['ttd_kanan_nama'] ?: $permohonan->nama_peminjam ?? '-';
        $ttdKananNrp  = $sc['ttd_kanan_nrp']  ?: $permohonan->nik ?? '';
        $ttdKananJab  = $sc['ttd_kanan_jabatan']?: $permohonan->jabatan ?? '';

        $datePinjam = \Carbon\Carbon::parse($permohonan->tanggal_pinjam);
        $hariNames = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariNames[$datePinjam->format('l')] ?? $datePinjam->format('l');
    @endphp

    @if(!$forPdf)
    <div class="no-print" style="text-align: right; margin-bottom: 8px;">
        <a href="{{ route('peminjam.download-surat.docx', $permohonan) }}" style="display:inline-block;padding:6px 12px;border-radius:4px;text-decoration:none;font-size:12px;font-family:Arial,sans-serif;background:#28a745;color:#fff;">Download .docx</a>
        <button style="display:inline-block;padding:6px 12px;border-radius:4px;font-size:12px;font-family:Arial,sans-serif;background:#0d6efd;color:#fff;border:none;cursor:pointer;" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('peminjam.cek-status', ['nomor' => $permohonan->nomor_permohonan]) }}" style="display:inline-block;padding:6px 12px;border-radius:4px;text-decoration:none;font-size:12px;font-family:Arial,sans-serif;background:#6c757d;color:#fff;">Kembali</a>
    </div>
    @endif

    {{-- HEADER TABLE: logo kiri + text center + logo kanan, double border bawah --}}
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:16%; border-bottom:6px double #000; vertical-align:center; padding:0;">
                <img src="{{ $imgLogoKiri }}" style="width:2.1cm; height:2.1cm; display:block;">
            </td>
            <td style="width:68%; border-bottom:6px double #000; vertical-align:center; padding:0 0 10px 14px;">
                <p style="margin:0; padding:0; font-family:Arial,sans-serif; font-size:13pt; font-weight:bold; text-align:center; line-height:1.3;">PEMERINTAH KABUPATEN PONOROGO</p>
                <p style="margin:0; padding:0; font-family:Arial,sans-serif; font-size:13pt; font-weight:bold; text-align:center; line-height:1.3;">DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK</p>
                <p style="margin:0; padding:0; font-family:Arial,sans-serif; font-size:10pt; text-align:center; line-height:1.2;">Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418</p>
                <p style="margin:0; padding:0; font-family:Arial,sans-serif; font-size:10pt; font-style:italic; text-align:center; line-height:1.2;">Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id</p>
                <p style="margin:0; padding:0; font-family:Arial,sans-serif; font-size:14pt; font-weight:bold; text-align:center; line-height:1.3; margin-top:4px;">P O N O R O G O</p>
            </td>
            @if($imgLogoKanan)
            <td style="width:16%; border-bottom:6px double #000; vertical-align:center; padding:0;">
                <img src="{{ $imgLogoKanan }}" style="width:2.1cm; height:2.1cm; display:block; margin-left:auto;">
            </td>
            @endif
        </tr>
    </table>

    {{-- SPACER: 8pt (after=160) --}}
    <p style="margin:0; padding:0; height:8pt;">&nbsp;</p>

    {{-- HAL/TANGGAL TABLE: double border bawah --}}
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:65%; padding:0;">
                <p style="margin:0; padding:0; font-size:12pt;">Hal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $halText }}</p>
            </td>
            <td style="width:35%; padding:0; text-align:right;">
                <p style="margin:0; padding:0; font-size:12pt;">{{ $dateText }}</p>
            </td>
        </tr>
    </table>

    {{-- SPACER: 4pt (after=80) --}}
    <p style="margin:0; padding:0; height:4pt;">&nbsp;</p>

    {{-- KEPADA --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">Kepada</p>
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['kepada_yth'] }}</p>
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['kepada_kab'] }}</p>
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['kepada_tempat'] }}</p>

    {{-- SPACER: 6pt (after=120) --}}
    <p style="margin:0; padding:0; height:6pt;">&nbsp;</p>

    {{-- PEMBUKA --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['pembuka'] }}</p>

    {{-- SPACER: 4pt (after=80) --}}
    <p style="margin:0; padding:0; height:4pt;">&nbsp;</p>

    {{-- SAYA YANG BERTANDA TANGAN --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5; text-indent:24pt;">{{ $sc['saya_yang'] }}</p>

    {{-- IDENTITAS TABLE --}}
    <table style="width:80%; border-collapse:collapse; margin-left:24pt;">
        <tr>
            <td style="width:100pt; padding:0; font-size:12pt; line-height:1.5;">Nama</td>
            <td style="width:20pt; padding:0; font-size:12pt; line-height:1.5;">:</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">{{ $vNama }}</td>
        </tr>
        <tr>
            <td style="padding:0; font-size:12pt; line-height:1.5;">NRP</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">:</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">{{ $vNrp }}</td>
        </tr>
        <tr>
            <td style="padding:0; font-size:12pt; line-height:1.5;">Pangkat</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">:</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">{{ $vJab }}</td>
        </tr>
        <tr>
            <td style="padding:0; font-size:12pt; line-height:1.5;">No. Telepon/HP</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">:</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">{{ $permohonan->telepon }}</td>
        </tr>
    </table>

    {{-- SPACER: 4pt (after=80) --}}
    <p style="margin:0; padding:0; height:4pt;">&nbsp;</p>

    {{-- BERMAKSUD --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['bermaksud'] }}</p>

    {{-- SPACER: 4pt (after=80) --}}
    <p style="margin:0; padding:0; height:4pt;">&nbsp;</p>

    {{-- ITEM TABLE --}}
    <table style="width:100%; border-collapse:collapse; border:1.5px solid #000;">
        <thead>
            <tr>
                <th style="width:40px; border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; font-weight:bold; background:#D9D9D9; text-align:center;">No</th>
                <th style="border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; font-weight:bold; background:#D9D9D9; text-align:center;">Nama alat</th>
                <th style="width:60px; border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; font-weight:bold; background:#D9D9D9; text-align:center;">Jumlah</th>
                <th style="width:120px; border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; font-weight:bold; background:#D9D9D9; text-align:center;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permohonan->detailPermohonan as $i => $detail)
            <tr>
                <td style="border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; text-align:center;">{{ $i + 1 }}.</td>
                <td style="border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif;">{{ $detail->inventaris->nama_barang ?? '-' }}</td>
                <td style="border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; text-align:center;">{{ $detail->jumlah }}</td>
                <td style="border:1.5px solid #000; padding:0 6pt; font-size:10pt; font-family:Arial,sans-serif; text-align:center;">{{ $detail->inventaris->kondisi ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SPACER: 4pt (after=80) --}}
    <p style="margin:0; padding:0; height:4pt;">&nbsp;</p>

    {{-- UNTUK KEPERLUAN --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5; text-indent:24pt; text-align:justify;">{{ $sc['untuk_keperluan'] }} <strong>{{ $permohonan->keperluan }}</strong>.</p>

    {{-- ISI --}}
    @if(!empty($sc['isi']))
        @foreach(explode("\n", $sc['isi']) as $isiLine)
            @if(trim($isiLine) !== '')
            <p style="margin:0; padding:0; font-size:12pt; line-height:1.5; text-indent:24pt; text-align:justify;">{{ $isiLine }}</p>
            @endif
        @endforeach
    @endif

    {{-- RENCANA --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5; text-indent:24pt; text-align:justify;">{{ $sc['rencana'] }}</p>

    {{-- JADWAL TABLE --}}
    <table style="width:80%; border-collapse:collapse;">
        <tr>
            <td style="width:36pt; padding:0; font-size:12pt; line-height:1.5;">&nbsp;</td>
            <td style="width:70pt; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['hari_label'] }}</td>
            <td style="width:15pt; padding:0; font-size:12pt; line-height:1.5;">&nbsp;</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">:&nbsp;&nbsp;{{ $hari }}</td>
        </tr>
        <tr>
            <td style="padding:0; font-size:12pt; line-height:1.5;">&nbsp;</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">{{ $sc['tanggal_label'] }}</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">&nbsp;</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">:&nbsp;&nbsp;{{ $datePinjam->format('d F Y') }}</td>
        </tr>
        <tr>
            <td style="padding:0; font-size:12pt; line-height:1.5;">&nbsp;</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">{{ $sc['tempat_label'] }}</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">&nbsp;</td>
            <td style="padding:0; font-size:12pt; line-height:1.5;">:&nbsp;&nbsp;{{ $vInst }}</td>
        </tr>
    </table>

    {{-- SPACER: 4pt (after=80) --}}
    <p style="margin:0; padding:0; height:4pt;">&nbsp;</p>

    {{-- PENUTUP + TERIMA KASIH --}}
    <p style="margin:0; padding:0; font-size:12pt; line-height:1.5; text-align:justify;">{{ str_replace("\n", " ", $sc['penutup']) }} {{ str_replace("\n", " ", $sc['terima_kasih']) }}</p>

    {{-- TTD TABLE: 4-column --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="5%" style="padding:0;">&nbsp;</td>
            <td width="45%" valign="top" style="padding:0;"><p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['ttd_kiri_label'] }}</p></td>
            <td width="5%" style="padding:0;">&nbsp;</td>
            <td width="45%" valign="top" style="padding:0;"><p style="margin:0; padding:0; font-size:12pt; line-height:1.5;">{{ $sc['ttd_kanan_label'] }}</p></td>
        </tr>
        <tr>
            <td colspan="4" height="72" style="padding:0;">&nbsp;</td>
        </tr>
        <tr>
            <td width="5%" style="padding:0;">&nbsp;</td>
            <td width="45%" valign="top" align="center" style="padding:0;">
                <p style="margin:0; padding:0; font-size:12pt; line-height:1.2;"><strong>{{ $ttdKiriNama }}</strong></p>
                <p style="margin:0; padding:0; font-size:12pt; line-height:1.2;">NRP. {{ $ttdKiriNrp }}</p>
                @if($ttdKiriJab)<p style="margin:0; padding:0; font-size:12pt; line-height:1.2;">{{ $ttdKiriJab }}</p>@endif
            </td>
            <td width="5%" style="padding:0;">&nbsp;</td>
            <td width="45%" valign="top" align="center" style="padding:0;">
                <p style="margin:0; padding:0; font-size:12pt; line-height:1.2;"><strong>{{ $ttdKananNama }}</strong></p>
                <p style="margin:0; padding:0; font-size:12pt; line-height:1.2;">NRP. {{ $ttdKananNrp }}</p>
                @if($ttdKananJab)<p style="margin:0; padding:0; font-size:12pt; line-height:1.2;">{{ $ttdKananJab }}</p>@endif
            </td>
        </tr>
    </table>

</body>
</html>
