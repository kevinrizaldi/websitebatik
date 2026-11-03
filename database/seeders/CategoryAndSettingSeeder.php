<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoryAndSettingSeeder extends Seeder
{
    /**
     * Seed categories and store settings.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            StoreSettingSeeder::class,
        ]);
    }
}
