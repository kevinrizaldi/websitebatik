<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $decimalToRupiah = function (string $decimal): int {
            if (str_contains($decimal, '.')) {
                [$integer, $fraction] = explode('.', $decimal, 2);
                if (ltrim($fraction, '0') !== '') {
                    throw new InvalidArgumentException("Non-zero fractional rupiah: {$decimal}");
                }

                return (int) $integer;
            }

            return (int) $decimal;
        };

        DB::table('orders')
            ->whereNotNull('midtrans_order_id')
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($decimalToRupiah) {
                foreach ($orders as $order) {
                    $exists = DB::table('payments')
                        ->where('midtrans_order_id', $order->midtrans_order_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $items = DB::table('order_items')
                        ->where('order_id', $order->id)
                        ->get();

                    if ($items->isEmpty()) {
                        Log::warning('copy_midtrans_data_from_orders_to_payments: order has no items', [
                            'order_id' => $order->id,
                        ]);

                        continue;
                    }

                    try {
                        $grossAmount = 0;
                        foreach ($items as $item) {
                            $price = $decimalToRupiah((string) $item->price);
                            $grossAmount += $price * (int) $item->quantity;
                        }
                        $shipping = $decimalToRupiah((string) ($order->shipping_cost ?? '0'));
                        $grossAmount += $shipping;
                    } catch (InvalidArgumentException $e) {
                        Log::warning('copy_midtrans_data_from_orders_to_payments: order has fractional money value', [
                            'order_id' => $order->id,
                        ]);

                        continue;
                    }

                    $status = $order->payment_status ?: 'pending';

                    DB::table('payments')->insert([
                        'order_id' => $order->id,
                        'midtrans_order_id' => $order->midtrans_order_id,
                        'snap_token' => $order->snap_token,
                        'status' => $status,
                        'payment_type' => $order->payment_type,
                        'transaction_id' => $order->transaction_id,
                        'payment_details' => null,
                        'gross_amount' => $grossAmount,
                        'expires_at' => null,
                        'paid_at' => $order->paid_at,
                        'raw_notification' => $order->raw_notification,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     * Documented no-op: dropping the payments table in create_payments_table down() handles rollback.
     */
    public function down(): void
    {
        // No-op by design. Data migration is reversed when payments table is dropped.
    }
};
