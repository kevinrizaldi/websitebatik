<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireOverdueOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel orders whose payment_deadline has passed and payment_status is still pending (PAY-17, PAY-19).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Find overdue unpaid orders in chunks to limit memory usage.
        Order::query()
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('status', Order::STATUS_UNPAID)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->chunkById(50, function ($orders) {
                foreach ($orders as $order) {
                    $this->cancelOrder($order);
                }
            });

        return Command::SUCCESS;
    }

    /**
     * Cancel a single overdue order inside a transaction with row locks.
     */
    private function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            /** @var Order|null $locked */
            $locked = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            // Re-check conditions after acquiring lock.
            if (
                $locked->payment_status !== Order::PAYMENT_PENDING
                || $locked->status !== Order::STATUS_UNPAID
                || $locked->payment_deadline === null
                || $locked->payment_deadline->isFuture()
            ) {
                return;
            }

            // Expire all pending payment attempts (no Cancel API call – PAY-19).
            Payment::where('order_id', $locked->id)
                ->where('status', Payment::STATUS_PENDING)
                ->get()
                ->each(function (Payment $payment) use ($locked) {
                    $payment->status = Payment::STATUS_EXPIRED;
                    $payment->save();

                    Log::info('orders:expire-overdue: expired payment attempt', [
                        'order_id' => $locked->id,
                        'payment_id' => $payment->id,
                    ]);
                });

            // Cancel the order.
            $locked->status = Order::STATUS_CANCELLED;
            $locked->payment_status = Order::PAYMENT_EXPIRED;
            $locked->save();

            Log::info('orders:expire-overdue: cancelled overdue order', [
                'order_id' => $locked->id,
            ]);
        });
    }
}
