<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InstansiSeeder::class,
            KategoriSeeder::class,
            InventarisSeeder::class,
            SuperAdminSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
