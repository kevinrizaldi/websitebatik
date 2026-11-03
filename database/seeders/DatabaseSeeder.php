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
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            StoreSettingSeeder::class,
            CustomerSeeder::class,
            ProdukSeeder::class,
            OrderSeeder::class,
            UlasanSeeder::class,
        ]);
    }
}
