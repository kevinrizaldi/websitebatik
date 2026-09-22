<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $statuses = ['Belum Dibayar', 'Sudah Dibayar', 'Diproses', 'Dikirim', 'Selesai', 'Batal'];
        $payments = ['Transfer Bank BCA', 'Transfer Bank Mandiri', 'Transfer Bank BRI', 'QRIS', 'COD (Bayar di Tempat)', 'GoPay'];

        return [
            'user_id' => User::first()?->id,
            'code' => 'ORD-' . date('Ymd') . '-' . fake()->unique()->numberBetween(100, 999),
            'customer_name' => fake('id_ID')->name(),
            'address' => fake('id_ID')->address(),
            'phone' => '08' . fake()->numerify('##########'),
            'total_price' => fake()->randomElement([250000, 380000, 450000, 650000, 890000, 1250000]),
            'payment_method' => fake()->randomElement($payments),
            'payment_proof' => null,
            'status' => fake()->randomElement($statuses),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
