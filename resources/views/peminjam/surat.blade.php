<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan - {{ $permohonan->nomor_permohonan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; padding: 20mm 25mm; color: #000; font-size: 12pt; line-height: 1.4; }
        p { margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-table td { border-bottom: 4px double #000; vertical-align: top; padding: 0; }
        .header-table .logo-cell { width: 2.1cm; padding: 0 6px 8px 0; }
        .header-table .logo-cell img { width: 2.07cm; height: auto; display: block; }
        .header-table .logo-kanan-cell { width: 2.1cm; padding: 0 0 8px 6px; }
        .header-table .logo-kanan-cell img { width: 2.07cm; height: auto; display: block; margin-left: auto; }
        .header-table .text-cell { padding: 0 0 8px 12px; }
        .header-table .text-cell p { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .header-table .text-cell .title-small { font-size: 15px; font-weight: bold; text-align: center; line-height: 1.4; }
        .header-table .text-cell .title-medium { font-size: 16px; font-weight: bold; text-align: center; line-height: 1.5; margin-top: 1px; }
        .header-table .text-cell .info { font-size: 12px; text-align: center; line-height: 1.2; }
        .header-table .text-cell .info-italic { font-size: 12px; font-style: italic; text-align: center; line-height: 1.2; }
        .header-table .text-cell .kota { font-size: 18px; font-weight: bold; text-align: center; line-height: 1.4; margin-top: 3px; }
        .info-line { width: 100%; overflow: hidden; margin-top: 12px; margin-bottom: 8px; }
        .info-line .hal-text { float: left; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        .info-line .date-text { float: right; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        .identitas td { padding: 1px 0; }
        .item-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 4px 8px; font-size: 12px; }
        .item-table th { background-color: #D9D9D9; font-weight: bold; font-family: Arial, sans-serif; font-size: 10px; }
        .item-table td { font-family: Arial, sans-serif; font-size: 10px; }
        .item-table .text-center { text-align: center; }
        .jadwal-table td { padding: 1px 0; }
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .ttd-table td { vertical-align: top; padding: 0 10px; text-align: center; }
        .keperluan-bold { font-weight: bold; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; font-family: Arial, sans-serif; }
        .btn-danger { background-color: #dc3545; color: #fff; border: none; }
        .btn-success { background-color: #28a745; color: #fff; border: none; }
        .btn-primary { background-color: #0d6efd; color: #fff; border: none; cursor: pointer; }
        .btn-secondary { background-color: #6c757d; color: #fff; border: none; }
        .no-print { text-align: right; margin-bottom: 12px; }
        @page { margin: 0; size: A4; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 15mm 20mm; font-size: 12pt; }
            p { margin: 0 !important; padding: 0 !important; }
            .ttd-table { margin-top: 25px !important; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('peminjam.download-surat.docx', $permohonan) }}" class="btn btn-success">Download .docx</a>
        <button class="btn btn-primary" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('peminjam.cek-status', ['nomor' => $permohonan->nomor_permohonan]) }}" class="btn btn-secondary">Kembali</a>
    </div>

    @php
        $template = \App\Models\SuratTemplate::find(1);
        $logoKiriPath = $template->logo_kiri ?? null;
        $logoKananPath = $template->logo_kanan ?? null;
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoKiriPath && file_exists(public_path($logoKiriPath)))
                    <img src="{{ asset($logoKiriPath) }}" alt="Logo Kiri">
                @else
                    <img src="{{ asset('images/logo-kominfo.png') }}" alt="Logo Kominfo">
                @endif
            </td>
            <td class="text-cell">
                <p class="title-small">PEMERINTAH KABUPATEN PONOROGO</p>
                <p class="title-medium">DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK</p>
                <p class="info">Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418</p>
                <p class="info-italic">Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id</p>
                <p class="kota">P O N O R O G O</p>
            </td>
            @if($logoKananPath && file_exists(public_path($logoKananPath)))
            <td class="logo-kanan-cell">
                <img src="{{ asset($logoKananPath) }}" alt="Logo Kanan">
            </td>
            @endif
        </tr>
    </table>

    @php
        $sc = \App\Http\Controllers\Admin\SuratController::getContent($permohonan);
        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
        $halText = $sc['hal'] ?: 'Permohonan Peminjaman ' . ($halItems ?: 'Barang Inventaris');

        $vNama  = $sc['nama_peminjam'] ?: $permohonan->nama_peminjam ?? '-';
        $vNrp   = $sc['nik']           ?: $permohonan->nik ?? '';
        $vJab   = $sc['jabatan']       ?: $permohonan->jabatan ?? '';
        $vInst  = $sc['instansi']      ?: ($permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-');

        $ttdKiriNama = $sc['ttd_kiri_nama']  ?: $permohonan->nama_peminjam ?? '-';
        $ttdKiriNrp  = $sc['ttd_kiri_nrp']   ?: $permohonan->nik ?? '';
        $ttdKiriJab  = $sc['ttd_kiri_jabatan']?: $permohonan->jabatan ?? '';
        $ttdKananNama = $sc['ttd_kanan_nama'] ?: $permohonan->nama_peminjam ?? '-';
        $ttdKananNrp  = $sc['ttd_kanan_nrp']  ?: $permohonan->nik ?? '';
        $ttdKananJab  = $sc['ttd_kanan_jabatan']?: $permohonan->jabatan ?? '';
    @endphp

    <div class="info-line">
        <span class="hal-text">Hal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $halText }}</span>
        <span class="date-text">Ponorogo, {{ $permohonan->created_at->format('d F Y') }}</span>
    </div>

    <p>Kepada</p>
    <p>{{ $sc['kepada_yth'] }}</p>
    <p>{{ $sc['kepada_kab'] }}</p>
    <p style="text-align: justify;">{{ $sc['kepada_tempat'] }}</p>

    <p>{{ $sc['pembuka'] }}</p>

    <p style="padding-left: 20px;">{{ $sc['saya_yang'] }}</p>

    <table class="identitas" style="margin-left: 35px;">
        <tr><td style="width:140px">Nama</td><td style="width:40px">:</td><td>{{ $vNama }}</td></tr>
        <tr><td>NRP</td><td>:</td><td>{{ $vNrp }}</td></tr>
        <tr><td>Pangkat</td><td>:</td><td>{{ $vJab ?: '-' }}</td></tr>
        <tr><td>No. Telepon/HP</td><td>:</td><td>{{ $permohonan->telepon }}</td></tr>
    </table>

    <p style="padding-left: 40px;">{{ $sc['bermaksud'] }}</p>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>Nama alat</th>
                <th style="width:60px">Jumlah</th>
                <th style="width:120px">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permohonan->detailPermohonan as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detail->inventaris->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ $detail->jumlah }}</td>
                <td class="text-center">{{ $detail->inventaris->kondisi ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="text-indent: 25px; text-align: justify;">{{ $sc['untuk_keperluan'] }} <span class="keperluan-bold">{{ $permohonan->keperluan }}</span>.</p>

    @if(!empty($sc['isi']))
    <p style="text-indent: 25px; text-align: justify; margin-top: 6px;">{!! nl2br(e($sc['isi'])) !!}</p>
    @endif

    <p style="text-indent: 25px; text-align: justify; margin-top: 6px;">{{ $sc['rencana'] }}</p>

    @php
        $datePinjam = \Carbon\Carbon::parse($permohonan->tanggal_pinjam);
        $hariNames = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $hari = $hariNames[$datePinjam->format('l')] ?? $datePinjam->format('l');
    @endphp

    <table style="margin-left: 35px; border: none;">
        <tr><td style="width:25px">&nbsp;</td><td style="width:70px">{{ $sc['hari_label'] }}</td><td style="width:20px">&nbsp;</td><td>:&nbsp;&nbsp;{{ $hari }}</td></tr>
        <tr><td>&nbsp;</td><td>{{ $sc['tanggal_label'] }}</td><td>&nbsp;</td><td>:&nbsp;&nbsp;{{ $datePinjam->format('d F Y') }}</td></tr>
        <tr><td>&nbsp;</td><td>{{ $sc['tempat_label'] }}</td><td>&nbsp;</td><td>:&nbsp;&nbsp;{{ $vInst }}</td></tr>
    </table>

    <p style="text-indent: 25px; text-align: justify; margin-top: 6px;">{!! nl2br(e($sc['penutup'])) !!} {!! nl2br(e($sc['terima_kasih'])) !!}</p>

    <table class="ttd-table">
        <tr>
            <td style="width:50%">
                {{ $sc['ttd_kiri_label'] }}<br><br><br><br><br><br>
                <strong>{{ $ttdKiriNama }}</strong><br>
                NRP. {{ $ttdKiriNrp }}
                @if($ttdKiriJab)<br>{{ $ttdKiriJab }}@endif
            </td>
            <td style="width:50%">
                {{ $sc['ttd_kanan_label'] }}<br><br><br><br><br><br>
                <strong>{{ $ttdKananNama }}</strong><br>
                NRP. {{ $ttdKananNrp }}
                @if($ttdKananJab)<br>{{ $ttdKananJab }}@endif
            </td>
        </tr>
    </table>

</body>
</html>
