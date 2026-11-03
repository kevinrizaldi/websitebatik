<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Seed store profile & shipping origin settings.
     */
    public function run(): void
    {
        StoreSetting::updateOrCreate(
            ['id' => 1],
            [
                'nama_toko' => 'Hamzah Style Official',
                'kota_asal' => 'Kota Surakarta (Solo)',
                'provinsi_asal' => 'Jawa Tengah',
                'kode_pos_asal' => '57141',
                'alamat_asal' => 'Jl. Slamet Riyadi No. 120, Laweyan, Surakarta, Jawa Tengah',
                'no_telepon_toko' => '0812-3456-7890',
                'email_toko' => 'official@hamzahstyle.com',
                'deskripsi_toko' => 'Produsen Busana Batik Nusantara & Kerajinan Upcycled Olahan Kain Sisa Berkualitas Tinggi.',
            ]
        );
    }
}
