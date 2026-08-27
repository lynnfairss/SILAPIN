<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan - {{ $permohonan->nomor_permohonan }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; padding: 40px; color: #000; max-width: 210mm; margin: 0 auto; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-table td { border-bottom: 2.5px solid #000; vertical-align: top; padding: 0; }
        .header-table .logo-cell { width: 2.1cm; padding: 0 6px 0 0; }
        .header-table .logo-cell img { width: 2.07cm; height: auto; display: block; }
        .header-table .text-cell { padding: 0 0 4px 12px; }
        .header-table .text-cell p { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .header-table .text-cell .title-small { font-size: 15px; font-weight: bold; text-align: center; line-height: 1.4; }
        .header-table .text-cell .title-medium { font-size: 16px; font-weight: bold; text-align: center; line-height: 1.5; margin-top: 1px; }
        .header-table .text-cell .info { font-size: 12px; text-align: center; line-height: 1.2; }
        .header-table .text-cell .info-italic { font-size: 12px; font-style: italic; text-align: center; line-height: 1.2; }
        .header-table .text-cell .kota { font-size: 18px; font-weight: bold; text-align: center; line-height: 1.4; margin-top: 3px; }
        .date-line { text-align: right; margin-top: 20px; }
        .hal-line { margin-top: 10px; }
        .identitas td { padding: 1px 0; }
        .item-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 4px 8px; font-size: 12px; }
        .item-table th { background-color: #D9D9D9; font-weight: bold; font-family: Arial, sans-serif; font-size: 10px; }
        .item-table td { font-family: Arial, sans-serif; font-size: 10px; }
        .item-table .text-center { text-align: center; }
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .ttd-table td { vertical-align: top; padding: 0 10px; text-align: center; }
        .keperluan-bold { font-weight: bold; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="text-end no-print mb-3">
        <a href="{{ route('peminjam.download-surat.docx', $permohonan) }}" class="btn btn-success"><i class="fas fa-file-word me-1"></i>Download .docx</a>
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Cetak / Simpan PDF</button>
        <a href="{{ route('peminjam.cek-status', ['nomor' => $permohonan->nomor_permohonan]) }}" class="btn btn-secondary">Kembali</a>
    </div>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ asset('images/logo-kominfo.png') }}" alt="Logo Kominfo">
            </td>
            <td class="text-cell">
                <p class="title-small">PEMERINTAH KABUPATEN PONOROGO</p>
                <p class="title-medium">DINAS KOMUNIKASI INFORMATIKA DAN STATISTIK</p>
                <p class="info">Jl. Ir. Juanda Nomor 198 Telp. (0352) 3592999 Kode Pos 63418</p>
                <p class="info-italic">Website: https://kominfo.ponorogo.go.id, Email: kominfo@ponorogo.go.id</p>
                <p class="kota">P O N O R O G O</p>
            </td>
        </tr>
    </table>

    <div class="date-line">
        <p>Ponorogo, {{ $permohonan->created_at->format('d F Y') }}</p>
    </div>

    @php
        $halItems = $permohonan->detailPermohonan->pluck('inventaris.nama_barang')->filter()->implode(', ');
    @endphp
    <p class="hal-line">Hal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Permohonan Peminjaman {{ $halItems ?: 'Barang Inventaris' }}</p>

    <p>Kepada</p>
    <p>Yth. Kepala Dinas Komunikasi Informasi dan Statistik.</p>
    <p>Kabupaten Ponorogo</p>
    <p style="text-align: justify;">di tempat</p>

    <p>Dengan Hormat,</p>

    <p style="padding-left: 20px;">Saya yang bertanda tangan di bawah ini :</p>

    <table class="identitas" style="margin-left: 35px;">
        <tr><td style="width:140px">Nama</td><td style="width:40px">:</td><td>{{ $permohonan->nama_peminjam }}</td></tr>
        <tr><td>NRP</td><td>:</td><td>{{ $permohonan->nik }}</td></tr>
        <tr><td>Pangkat</td><td>:</td><td>{{ $permohonan->jabatan ?? '-' }}</td></tr>
        <tr><td>No. Telepon/HP</td><td>:</td><td>{{ $permohonan->telepon }}</td></tr>
    </table>

    <p style="padding-left: 40px;">bermaksud meminjam alat:</p>

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
                <td class="text-center">{{ $detail->jumlah > 1 ? 'Kondisi Baik' : '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:15px">untuk keperluan <span class="keperluan-bold">{{ $permohonan->keperluan }}</span>.</p>

    <p style="text-align: center; font-weight: bold;">&nbsp;</p>

    <p style="text-indent: 25px; text-align: justify;">Rencananya akan dilaksanakan pada : </p>

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
        <tr><td style="width:25px">&nbsp;</td><td style="width:70px">Hari</td><td style="width:20px">&nbsp;</td><td>:&nbsp;&nbsp;{{ $hari }}</td></tr>
        <tr><td>&nbsp;</td><td>Tanggal</td><td>&nbsp;</td><td>:&nbsp;&nbsp;{{ $datePinjam->format('d F Y') }}</td></tr>
        <tr><td>&nbsp;</td><td>Tempat</td><td>&nbsp;</td><td>:&nbsp;&nbsp;{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-' }}</td></tr>
    </table>

    <p style="text-indent: 25px; text-align: justify; margin-top: 12px;">Demikian surat permohonan peminjaman ini saya buat dan saya menyatakan akan bertanggung jawab sepenuhnya jika terjadi kerusakan atau kehilangan atas alat di atas selama saya pinjam.&nbsp;&nbsp;Atas perhatian dan bantuannya saya ucapkan terima kasih.</p>

    <table class="ttd-table">
        <tr>
            <td style="width:50%">
                Yang menyerahkan,<br><br><br><br><br><br>
                <strong>{{ $permohonan->nama_peminjam ?? '-' }}</strong><br>
                NRP. {{ $permohonan->nik }}
            </td>
            <td style="width:50%">
                Yang menerima,<br><br><br><br><br><br>
                <strong>{{ $permohonan->nama_peminjam ?? '-' }}</strong><br>
                NRP. {{ $permohonan->nik }}
            </td>
        </tr>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
