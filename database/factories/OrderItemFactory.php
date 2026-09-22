<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $produk = Produk::inRandomOrder()->first();
        $price = $produk ? $produk->harga : fake()->numberBetween(100000, 500000);
        $qty = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'produk_id' => $produk?->id,
            'produk_name' => $produk ? $produk->nama : 'Batik Tulis Elegance',
            'price' => $price,
            'quantity' => $qty,
            'subtotal' => $price * $qty,
        ];
    }
}
