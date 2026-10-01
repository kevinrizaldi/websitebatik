<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Pemanggilan seeder HARUS di dalam method run() ini
        $this->call([
            AdminSeeder::class,
            ProdukSeeder::class,
            CategoryAndSettingSeeder::class,
        ]);
    }
}
