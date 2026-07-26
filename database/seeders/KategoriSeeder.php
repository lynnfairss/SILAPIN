<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'Elektronik',   'keterangan' => 'Barang elektronik seperti laptop, proyektor, dll'],
            ['nama_kategori' => 'Furniture',    'keterangan' => 'Meja, kursi, lemari, dan sejenisnya'],
            ['nama_kategori' => 'ATK',          'keterangan' => 'Alat Tulis Kantor'],
            ['nama_kategori' => 'Olahraga',     'keterangan' => 'Peralatan olahraga'],
        ];

        foreach ($kategori as $data) {
            Kategori::create($data);
        }
    }
}
