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
    protected $description = 'Cancel orders whose payment_deadline has passed and payment_status is still pending (PAY-17, PAY-19). Also cancels orders that never had an attempt (null deadline) and are older than the configured deadline hours.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deadlineHours = max(1, (int) config('midtrans.payment_deadline_hours', 24));

        // 1. Cancel orders with an explicit payment_deadline that has passed.
        Order::query()
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('status', Order::STATUS_UNPAID)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->chunkById(50, function ($orders) {
                foreach ($orders as $order) {
                    $this->cancelOrder($order, requireNonNullDeadline: true);
                }
            });

        // 2. Also cancel orders that never got an attempt (payment_deadline IS NULL)
        //    and whose created_at is older than the configured deadline hours.
        //    Without this, orders that never reach getOrCreateSnapToken stay open forever.
        Order::query()
            ->where('payment_status', Order::PAYMENT_PENDING)
            ->where('status', Order::STATUS_UNPAID)
            ->whereNull('payment_deadline')
            ->where('created_at', '<', now()->subHours($deadlineHours))
            ->chunkById(50, function ($orders) use ($deadlineHours) {
                foreach ($orders as $order) {
                    $this->cancelOrder($order, requireNonNullDeadline: false, deadlineHours: $deadlineHours);
                }
            });

        return Command::SUCCESS;
    }

    /**
     * Cancel a single overdue order inside a transaction with row locks.
     *
     * @param  bool  $requireNonNullDeadline  True for the deadline-passed path; false for the null-deadline path.
     * @param  int  $deadlineHours  Only used when $requireNonNullDeadline is false.
     */
    private function cancelOrder(Order $order, bool $requireNonNullDeadline, int $deadlineHours = 24): void
    {
        DB::transaction(function () use ($order, $requireNonNullDeadline, $deadlineHours): void {
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
            ) {
                return;
            }

            if ($requireNonNullDeadline) {
                // Deadline-passed path: must have a non-null, past deadline.
                if ($locked->payment_deadline === null || $locked->payment_deadline->isFuture()) {
                    return;
                }
            } else {
                // Null-deadline path: deadline must still be null and created_at old enough.
                if ($locked->payment_deadline !== null) {
                    return;
                }
                if ($locked->created_at->gt(now()->subHours($deadlineHours))) {
                    return;
                }
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
