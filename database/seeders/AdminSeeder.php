<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Membuat akun admin default.
     * Jalankan dengan: php artisan db:seed --class=AdminSeeder
     *
     * Aman dijalankan berkali-kali — tidak akan duplikasi jika email sudah ada.
     */
    public function run(): void
    {
        $email = 'admin@webstorebatik.com';

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => 'Administrator',
                'password'          => Hash::make('admin12345'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info("✓ Akun admin baru dibuat: {$email}");
        } else {
            $this->command->info("✓ Akun admin sudah ada, data diperbarui: {$email}");
        }

        $this->command->warn('  Password: admin12345  — segera ganti setelah login!');
    }
}
