<?php

namespace Database\Seeders;

use App\Models\Inventaris;
use App\Models\Jenis;
use Illuminate\Database\Seeder;

class InventarisSeeder extends Seeder
{
    public function run(): void
    {
        $inventaris = [
            ['kategori_id' => 1, 'kode_barang' => 'ELK-001', 'nama_barang' => 'Laptop ASUS VivoBook 14',  'jenis' => 'Laptop', 'stok' => 10, 'kondisi' => 'Baik', 'deskripsi' => 'Laptop 14 inch, Intel Core i5, RAM 8GB'],
            ['kategori_id' => 1, 'kode_barang' => 'ELK-002', 'nama_barang' => 'Proyektor Epson EB-X51',   'jenis' => 'Proyektor', 'stok' => 5,  'kondisi' => 'Baik', 'deskripsi' => 'Proyektor 3800 lumens, XGA'],
            ['kategori_id' => 1, 'kode_barang' => 'ELK-003', 'nama_barang' => 'Printer Canon PIXMA G2020','jenis' => 'Printer', 'stok' => 3,  'kondisi' => 'Baik', 'deskripsi' => 'Printer ink tank, print scan copy'],
            ['kategori_id' => 1, 'kode_barang' => 'ELK-004', 'nama_barang' => 'Monitor LG 24 inch',       'jenis' => 'Monitor', 'stok' => 8,  'kondisi' => 'Baik', 'deskripsi' => 'Monitor LED IPS FHD'],
            ['kategori_id' => 2, 'kode_barang' => 'FUR-001', 'nama_barang' => 'Meja Kerja Lipat',         'jenis' => 'Meja', 'stok' => 15, 'kondisi' => 'Baik', 'deskripsi' => 'Meja lipat portable, tinggi adjustable'],
            ['kategori_id' => 2, 'kode_barang' => 'FUR-002', 'nama_barang' => 'Kursi Lipat',              'jenis' => 'Kursi', 'stok' => 20, 'kondisi' => 'Baik', 'deskripsi' => 'Kursi lipat besi, ringan dan kuat'],
            ['kategori_id' => 3, 'kode_barang' => 'ATK-001', 'nama_barang' => 'Set Alat Tulis',           'jenis' => 'Alat Tulis', 'stok' => 50, 'kondisi' => 'Baik', 'deskripsi' => 'Pensil, pulpen, penghapus, penggaris'],
            ['kategori_id' => 4, 'kode_barang' => 'OLA-001', 'nama_barang' => 'Bola Voli Mikasa',         'jenis' => 'Bola', 'stok' => 10, 'kondisi' => 'Baik', 'deskripsi' => 'Bola voli resmi, ukuran standar'],
        ];

        foreach ($inventaris as $data) {
            $jenis = Jenis::firstOrCreate(['nama_jenis' => $data['jenis']]);
            unset($data['jenis']);
            $data['jenis_id'] = $jenis->id;

            Inventaris::updateOrCreate(
                ['kode_barang' => $data['kode_barang']],
                $data
            );
        }
    }
}
