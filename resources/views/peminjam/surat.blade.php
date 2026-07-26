<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan - {{ $permohonan->nomor_permohonan }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; padding: 40px; color: #000; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h3 { margin-bottom: 0; font-weight: bold; }
        .header h5 { margin-top: 5px; }
        hr { border-top: 2px solid #000; }
        .ttd { margin-top: 60px; }
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="text-end no-print mb-3">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Cetak / Simpan PDF</button>
        <a href="{{ route('peminjam.cek-status', ['nomor' => $permohonan->nomor_permohonan]) }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="header">
        <h3>SURAT PERMOHONAN PEMINJAMAN INVENTARIS</h3>
        <h5>SILAPIN - Sistem Informasi Peminjaman Inventaris</h5>
        <hr>
    </div>

    <table class="table table-bordered">
        <tr>
            <th style="width:180px">Nomor Permohonan</th>
            <td>{{ $permohonan->nomor_permohonan }}</td>
        </tr>
        <tr>
            <th>Tanggal Pengajuan</th>
            <td>{{ $permohonan->created_at->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $permohonan->status }}</td>
        </tr>
    </table>

    <h5 class="mt-4">A. Data Pemohon</h5>
    <table class="table table-bordered">
        <tr><th style="width:180px">Nama Lengkap</th><td>{{ $permohonan->nama_peminjam }}</td></tr>
        <tr><th>NIK</th><td>{{ $permohonan->nik }}</td></tr>
        <tr><th>Jabatan</th><td>{{ $permohonan->jabatan ?? '-' }}</td></tr>
        <tr><th>Telepon</th><td>{{ $permohonan->telepon }}</td></tr>
        <tr><th>Instansi</th><td>{{ $permohonan->instansi?->nama_instansi ?? $permohonan->nama_instansi_lain ?? '-' }}</td></tr>
    </table>

    <h5>B. Jadwal Peminjaman</h5>
    <table class="table table-bordered">
        <tr><th style="width:180px">Tanggal Pinjam</th><td>{{ $permohonan->tanggal_pinjam }}</td></tr>
        <tr><th>Tanggal Kembali</th><td>{{ $permohonan->tanggal_kembali }}</td></tr>
        <tr><th>Keperluan</th><td>{{ $permohonan->keperluan }}</td></tr>
    </table>

    <h5>C. Barang yang Dipinjam</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permohonan->detailPermohonan as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detail->inventaris->nama_barang ?? '-' }}</td>
                <td><code>{{ $detail->inventaris->kode_barang ?? '-' }}</code></td>
                <td class="text-center">{{ $detail->jumlah }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="mt-4">Demikian surat permohonan ini dibuat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.</p>

    <div class="row ttd">
        <div class="col-4 offset-8 text-center">
            <p>{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
            <br><br><br>
            <p class="fw-bold">({{ $permohonan->nama_peminjam }})</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
