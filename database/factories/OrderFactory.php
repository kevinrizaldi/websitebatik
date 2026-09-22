<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'code' => 'ORD-' . strtoupper(fake()->bothify('??###??')),
            'customer_name' => fake()->name(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'total_price' => 0, // diupdate via Seeder/Item
            'payment_method' => fake()->randomElement(['Transfer Bank BNI', 'Transfer Bank BCA', ]),
            'payment_proof' => null,
            'status' => fake()->randomElement(['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai']),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
