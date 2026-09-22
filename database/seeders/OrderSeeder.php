<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::factory()->count(10)->create()->each(function ($order) {
            $itemsCount = rand(1, 3);
            $total = 0;

            for ($i = 0; $i < $itemsCount; $i++) {
                $item = OrderItem::factory()->create([
                    'order_id' => $order->id,
                ]);
                $total += $item->subtotal;
            }

            $order->update(['total_price' => $total]);
        });
    }
}
