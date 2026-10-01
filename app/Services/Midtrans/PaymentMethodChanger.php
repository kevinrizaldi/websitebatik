<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Atomically handles changing the payment method on an order.
 *
 * PHASE A: a single DB::transaction that locks the order and the active payment
 * attempt, calls getTransactionStatus (while holding the lock — accepted trade-off),
 * and either cancels/replaces the old attempt or marks the order paid. This
 * transaction is committed before any call to getOrCreateSnapToken.
 *
 * PHASE B (outside Phase A's transaction): requests a new Snap token. If this
 * fails, the old attempt stays `replaced` (Phase A is already committed).
 *
 * NOTE: lockForUpdate() is a no-op on SQLite (the test database), so real
 * concurrency is not proven by the tests. Integration tests against MySQL/PostgreSQL
 * would be required to verify lock ordering.
 */
class PaymentMethodChanger
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly PaymentAttemptProcessor $processor,
    ) {}

    /**
     * Change (or confirm the current) payment method for an order.
     *
     * @return array{outcome: 'same_token'|'changed'|'paid', token: ?string}
     *
     * @throws \InvalidArgumentException when the order is not eligible.
     * @throws \Exception on Midtrans API failure.
     */
    public function change(Order $order): array
    {
        // PHASE A — DB transaction; NO getOrCreateSnapToken calls inside.
        $phaseAResult = DB::transaction(function () use ($order): array {
            // 1. Lock the order first (canonical lock order).
            /** @var Order $lockedOrder */
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Payable check
            if (
                $lockedOrder->status !== Order::STATUS_UNPAID
                || $lockedOrder->payment_status !== Order::PAYMENT_PENDING
            ) {
                throw new \InvalidArgumentException(
                    "Order #{$lockedOrder->id} is not eligible for change-method."
                );
            }

            if ($lockedOrder->isPastDeadline()) {
                throw new \InvalidArgumentException(
                    "Order #{$lockedOrder->id} has passed its payment deadline."
                );
            }

            // 2. Find the latest pending attempt, locked AFTER the order lock.
            /** @var Payment|null $active */
            $active = Payment::where('order_id', $lockedOrder->id)
                ->where('status', Payment::STATUS_PENDING)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $active) {
                // No active attempt — signal caller to run Phase B.
                return ['_phase' => 'B'];
            }

            // 3. Get the current Midtrans status while holding the lock.
            // NOTE: HTTP calls while holding the order row lock are an accepted trade-off
            // to avoid a window between releasing the lock and re-acquiring it.
            // An exception propagates to the controller, which answers 502.
            $statusResponse = $this->midtransService->getTransactionStatus($active->midtrans_order_id);

            if ($statusResponse === null) {
                // No transaction at Midtrans yet.
                if ($active->isReusable()) {
                    return ['_phase' => 'same_token', 'token' => $active->snap_token];
                }

                // Token expired locally — mark expired and move to Phase B.
                $active->status = Payment::STATUS_EXPIRED;
                $active->save();

                return ['_phase' => 'B'];
            }

            $classified = $this->midtransService->classifyTransactionStatus($statusResponse);

            if ($classified === 'paid') {
                // Already paid — apply and return paid outcome.
                $this->processor->apply($active, $statusResponse);

                return ['_phase' => 'paid'];
            }

            if ($classified === 'dead') {
                // Expired or denied — apply local status, then create a new attempt (Phase B).
                $this->processor->apply($active, $statusResponse);

                return ['_phase' => 'B'];
            }

            if ($classified === 'pending') {
                // Cancel the Midtrans transaction.
                try {
                    $this->midtransService->cancelTransaction($active->midtrans_order_id);
                } catch (\Exception $e) {
                    if ((int) $e->getCode() === 412) {
                        // Re-fetch status once to handle the race condition.
                        $retryStatus = $this->midtransService->getTransactionStatus($active->midtrans_order_id);
                        if ($retryStatus !== null) {
                            $retryClassified = $this->midtransService->classifyTransactionStatus($retryStatus);

                            if ($retryClassified === 'paid') {
                                $this->processor->apply($active, $retryStatus);

                                return ['_phase' => 'paid'];
                            }

                            if ($retryClassified === 'dead') {
                                $this->processor->apply($active, $retryStatus);

                                return ['_phase' => 'B'];
                            }
                        }

                        // Not paid and not dead — rethrow.
                        throw $e;
                    }

                    // Any other exception: log and rethrow; nothing changes.
                    Log::warning('PaymentMethodChanger: Midtrans cancel failed', [
                        'order_id' => $lockedOrder->id,
                        'payment_id' => $active->id,
                    ]);
                    throw $e;
                }

                // Cancel succeeded — mark old attempt replaced.
                $active->status = Payment::STATUS_REPLACED;
                $active->save();

                return ['_phase' => 'B'];
            }

            // 'unknown' status
            throw new \InvalidArgumentException(
                "Unexpected Midtrans status for order #{$lockedOrder->id}."
            );
        });

        // PHASE B — outside Phase A's transaction.
        if (($phaseAResult['_phase'] ?? '') === 'same_token') {
            return ['outcome' => 'same_token', 'token' => $phaseAResult['token']];
        }

        if (($phaseAResult['_phase'] ?? '') === 'paid') {
            return ['outcome' => 'paid', 'token' => null];
        }

        // Phase B: create a new Snap token (no DB transaction here — getOrCreateSnapToken has its own).
        $token = $this->midtransService->getOrCreateSnapToken($order);

        return ['outcome' => 'changed', 'token' => $token];
    }
}
