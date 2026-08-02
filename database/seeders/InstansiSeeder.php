<?php

namespace Database\Seeders;

use App\Models\Instansi;
use Illuminate\Database\Seeder;

class InstansiSeeder extends Seeder
{
    public function run(): void
    {
        $instansi = [
            ['nama_instansi' => 'Dinas Pendidikan',      'alamat' => 'Jl. Pendidikan No. 10',      'telepon' => '021-5551234', 'tipe_identitas' => 'NIP'],
            ['nama_instansi' => 'Dinas Kesehatan',       'alamat' => 'Jl. Kesehatan No. 25',       'telepon' => '021-5555678', 'tipe_identitas' => 'NIP'],
            ['nama_instansi' => 'Dinas PU',              'alamat' => 'Jl. Teknik No. 5',           'telepon' => '021-5559012', 'tipe_identitas' => 'NIP'],
            ['nama_instansi' => 'Sekretariat Daerah',    'alamat' => 'Jl. Pemda No. 1',            'telepon' => '021-5553456', 'tipe_identitas' => 'NIP'],
        ];

        foreach ($instansi as $data) {
            Instansi::create($data);
        }
    }
}
