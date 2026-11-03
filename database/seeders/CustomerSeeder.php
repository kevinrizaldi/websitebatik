<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Seed customer users and their shipping addresses.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'customer@webstorebatik.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081234567890',
                'address' => [
                    'label' => 'Rumah Utama',
                    'label_alamat' => 'Rumah Utama',
                    'recipient_name' => 'Budi Santoso',
                    'penerima' => 'Budi Santoso',
                    'phone' => '081234567890',
                    'no_telepon' => '081234567890',
                    'full_address' => 'Jl. Senopati No. 42, RT 02 / RW 03, Kel. Senayan, Kec. Kebayoran Baru',
                    'alamat_lengkap' => 'Jl. Senopati No. 42, RT 02 / RW 03, Kel. Senayan, Kec. Kebayoran Baru',
                    'city' => 'Jakarta Selatan',
                    'kota' => 'Jakarta Selatan',
                    'province' => 'DKI Jakarta',
                    'provinsi' => 'DKI Jakarta',
                    'postal_code' => '12190',
                    'kode_pos' => '12190',
                    'is_primary' => true,
                    'is_utama' => true,
                ],
            ],
            [
                'name' => 'Al anshr',
                'email' => 'alanshorrr43@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081932697354',
                'address' => [
                    'label' => 'Rumah',
                    'label_alamat' => 'Rumah',
                    'recipient_name' => 'Al anshr',
                    'penerima' => 'Al anshr',
                    'phone' => '081932697354',
                    'no_telepon' => '081932697354',
                    'full_address' => 'Jl. Sumatra No. 14, Sumbersari',
                    'alamat_lengkap' => 'Jl. Sumatra No. 14, Sumbersari',
                    'city' => 'Jember',
                    'kota' => 'Jember',
                    'province' => 'Jawa Timur',
                    'provinsi' => 'Jawa Timur',
                    'postal_code' => '68121',
                    'kode_pos' => '68121',
                    'is_primary' => true,
                    'is_utama' => true,
                ],
            ],
            [
                'name' => 'Dewi Laksmi',
                'email' => 'dewi.laksmi@email.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081388772210',
                'address' => [
                    'label' => 'Rumah',
                    'label_alamat' => 'Rumah',
                    'recipient_name' => 'Dewi Laksmi',
                    'penerima' => 'Dewi Laksmi',
                    'phone' => '081388772210',
                    'no_telepon' => '081388772210',
                    'full_address' => 'Jl. Pemuda No. 78, Embong Kaliasin',
                    'alamat_lengkap' => 'Jl. Pemuda No. 78, Embong Kaliasin',
                    'city' => 'Surabaya',
                    'kota' => 'Surabaya',
                    'province' => 'Jawa Timur',
                    'provinsi' => 'Jawa Timur',
                    'postal_code' => '60271',
                    'kode_pos' => '60271',
                    'is_primary' => true,
                    'is_utama' => true,
                ],
            ],
            [
                'name' => 'Hendrik Pratama',
                'email' => 'hendrik.pratama@email.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '085611223344',
                'address' => [
                    'label' => 'Kantor',
                    'label_alamat' => 'Kantor',
                    'recipient_name' => 'Hendrik Pratama',
                    'penerima' => 'Hendrik Pratama',
                    'phone' => '085611223344',
                    'no_telepon' => '085611223344',
                    'full_address' => 'Jl. Diponegoro No. 101, Citarum',
                    'alamat_lengkap' => 'Jl. Diponegoro No. 101, Citarum',
                    'city' => 'Bandung',
                    'kota' => 'Bandung',
                    'province' => 'Jawa Barat',
                    'provinsi' => 'Jawa Barat',
                    'postal_code' => '40115',
                    'kode_pos' => '40115',
                    'is_primary' => true,
                    'is_utama' => true,
                ],
            ],
            [
                'name' => 'Ratna Kusuma',
                'email' => 'ratna.k@email.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081198765432',
                'address' => [
                    'label' => 'Galeri',
                    'label_alamat' => 'Galeri',
                    'recipient_name' => 'Ratna Kusuma',
                    'penerima' => 'Ratna Kusuma',
                    'phone' => '081198765432',
                    'no_telepon' => '081198765432',
                    'full_address' => 'Jl. Kaliurang Km 9, Sinduharjo, Ngaglik',
                    'alamat_lengkap' => 'Jl. Kaliurang Km 9, Sinduharjo, Ngaglik',
                    'city' => 'Sleman',
                    'kota' => 'Sleman',
                    'province' => 'D.I. Yogyakarta',
                    'provinsi' => 'D.I. Yogyakarta',
                    'postal_code' => '55581',
                    'kode_pos' => '55581',
                    'is_primary' => true,
                    'is_utama' => true,
                ],
            ],
            [
                'name' => 'Anindya Putri',
                'email' => 'anindya.p@email.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '082177889900',
                'address' => [
                    'label' => 'Rumah',
                    'label_alamat' => 'Rumah',
                    'recipient_name' => 'Anindya Putri',
                    'penerima' => 'Anindya Putri',
                    'phone' => '082177889900',
                    'no_telepon' => '082177889900',
                    'full_address' => 'Jl. Pandanaran No. 12, Mugassari',
                    'alamat_lengkap' => 'Jl. Pandanaran No. 12, Mugassari',
                    'city' => 'Semarang',
                    'kota' => 'Semarang',
                    'province' => 'Jawa Tengah',
                    'provinsi' => 'Jawa Tengah',
                    'postal_code' => '50249',
                    'kode_pos' => '50249',
                    'is_primary' => true,
                    'is_utama' => true,
                ],
            ],
            [
                'name' => 'Bayu Wicaksono',
                'email' => 'bayu.w@email.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '081298765432',
                'address' => null,
            ],
        ];

        foreach ($customers as $c) {
            $addressData = $c['address'];
            unset($c['address']);

            $user = User::updateOrCreate(
                ['email' => $c['email']],
                array_merge($c, ['email_verified_at' => now()])
            );

            if ($addressData !== null) {
                Address::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'label' => $addressData['label'],
                    ],
                    array_merge($addressData, ['user_id' => $user->id])
                );
            }
        }
    }
}
