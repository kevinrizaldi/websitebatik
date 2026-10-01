<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies a Midtrans status payload to a Payment attempt.
 *
 * All DB transition logic that was in MidtransWebhookController lives here so
 * that PaymentSyncController and PaymentMethodChanger can reuse it without
 * duplicating the state-machine.
 */
class PaymentAttemptProcessor
{
    public function __construct(private readonly MidtransService $midtransService) {}

    /**
     * Apply a Midtrans payload (webhook or GET-status) to the given Payment attempt.
     *
     * Runs inside its own DB::transaction with the canonical lock order:
     * Order (lockForUpdate) first, then Payment (lockForUpdate).
     *
     * @param  array<string, mixed>  $payload
     * @return array{code: int, message: string}
     */
    public function apply(Payment $payment, array $payload): array
    {
        return DB::transaction(function () use ($payment, $payload): array {
            /** @var Order|null $order */
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->first();
            /** @var Payment|null $lockedPayment */
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $order || ! $lockedPayment) {
                return ['code' => 404, 'message' => 'Record not found.'];
            }

            // Early return if attempt is already paid
            if ($lockedPayment->status === Payment::STATUS_PAID) {
                return ['code' => 200, 'message' => 'OK (already paid)'];
            }

            // Compare gross_amount with the attempt's gross_amount
            try {
                $attemptGross = $this->midtransService->toRupiah((string) $lockedPayment->gross_amount);
            } catch (\InvalidArgumentException $e) {
                Log::warning('PaymentAttemptProcessor: could not parse attempt gross amount', [
                    'order_id' => $order->id,
                    'payment_id' => $lockedPayment->id,
                ]);

                return ['code' => 400, 'message' => 'Cannot parse attempt amount.'];
            }

            $payloadAmount = (int) ($payload['gross_amount'] ?? 0);

            if ($attemptGross !== $payloadAmount) {
                Log::warning('Midtrans webhook: gross_amount mismatch with payment attempt', [
                    'order_id' => $order->id,
                    'payment_id' => $lockedPayment->id,
                ]);

                return ['code' => 400, 'message' => 'Gross amount mismatch.'];
            }

            // Map incoming transaction status
            $rawFraudStatus = $payload['fraud_status'] ?? null;
            $incoming = $this->midtransService->mapPaymentStatus(
                (string) ($payload['transaction_status'] ?? ''),
                is_string($rawFraudStatus) ? $rawFraudStatus : null
            );

            if ($incoming === null) {
                return ['code' => 200, 'message' => 'OK (ignored)'];
            }

            // -- Apply state transitions ------------------------------------------
            if ($incoming === Payment::STATUS_PAID) {
                // Verify server gross amount matches the attempt gross amount
                try {
                    ['gross_amount' => $serverGross] = $this->midtransService->buildItemDetails($order);
                } catch (\InvalidArgumentException $e) {
                    Log::warning('Midtrans webhook: could not compute order gross amount', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);

                    return ['code' => 400, 'message' => 'Cannot compute order amount.'];
                }

                if ($serverGross !== $attemptGross) {
                    Log::warning('Midtrans webhook: server gross amount mismatch with attempt gross', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);

                    return ['code' => 400, 'message' => 'Server amount mismatch.'];
                }

                // Update attempt
                $lockedPayment->status = Payment::STATUS_PAID;
                $lockedPayment->paid_at = now();
                if (isset($payload['payment_type']) && is_string($payload['payment_type'])) {
                    $lockedPayment->payment_type = $payload['payment_type'];
                }
                if (isset($payload['transaction_id']) && is_string($payload['transaction_id'])) {
                    $lockedPayment->transaction_id = $payload['transaction_id'];
                }
                $lockedPayment->raw_notification = array_diff_key($payload, ['signature_key' => true]);
                $extracted = $this->midtransService->extractPaymentDetails($payload);
                $lockedPayment->payment_details = array_merge($lockedPayment->payment_details ?? [], $extracted);
                $lockedPayment->save();

                // Order handling
                if ($order->status === Order::STATUS_CANCELLED) {
                    Log::warning('Midtrans: payment received for cancelled order', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);
                } elseif ($order->payment_status === Order::PAYMENT_PAID) {
                    Log::warning('Midtrans: duplicate payment for paid order', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);
                } else {
                    $order->payment_status = Order::PAYMENT_PAID;
                    $order->paid_at = now();

                    if (
                        $order->status === Order::STATUS_UNPAID
                        || $order->status === Order::STATUS_WAITING
                    ) {
                        $order->status = Order::STATUS_PAID;
                    }

                    $order->save();

                    // Cancel other pending attempts of this order locally
                    $otherPendingPayments = Payment::where('order_id', $order->id)
                        ->where('id', '!=', $lockedPayment->id)
                        ->where('status', Payment::STATUS_PENDING)
                        ->get();

                    foreach ($otherPendingPayments as $other) {
                        $other->status = Payment::STATUS_CANCELLED;
                        $other->save();
                        Log::warning('Midtrans: cancelled pending payment attempt locally', [
                            'order_id' => $order->id,
                            'payment_id' => $other->id,
                        ]);
                    }
                }
            } elseif ($incoming === Payment::STATUS_PENDING) {
                if (in_array($lockedPayment->status, [Payment::STATUS_PENDING, Payment::STATUS_FAILED], true)) {
                    $lockedPayment->status = Payment::STATUS_PENDING;
                    if (isset($payload['payment_type']) && is_string($payload['payment_type'])) {
                        $lockedPayment->payment_type = $payload['payment_type'];
                    }
                    if (isset($payload['transaction_id']) && is_string($payload['transaction_id'])) {
                        $lockedPayment->transaction_id = $payload['transaction_id'];
                    }
                    $lockedPayment->payment_details = $this->midtransService->extractPaymentDetails($payload);
                    if (isset($payload['expiry_time']) && is_string($payload['expiry_time'])) {
                        $parsedExpiry = $this->midtransService->parseExpiryTime($payload['expiry_time']);
                        if ($parsedExpiry !== null) {
                            $lockedPayment->expires_at = $parsedExpiry;
                        }
                    }
                    $lockedPayment->raw_notification = array_diff_key($payload, ['signature_key' => true]);
                    $lockedPayment->save();
                }
            } elseif ($incoming === Payment::STATUS_FAILED) {
                if ($lockedPayment->status === Payment::STATUS_PENDING) {
                    $lockedPayment->status = Payment::STATUS_FAILED;
                    if (isset($payload['payment_type']) && is_string($payload['payment_type'])) {
                        $lockedPayment->payment_type = $payload['payment_type'];
                    }
                    if (isset($payload['transaction_id']) && is_string($payload['transaction_id'])) {
                        $lockedPayment->transaction_id = $payload['transaction_id'];
                    }
                    $lockedPayment->raw_notification = array_diff_key($payload, ['signature_key' => true]);
                    $lockedPayment->save();
                }
            } elseif (in_array($incoming, [Payment::STATUS_CANCELLED, Payment::STATUS_EXPIRED], true)) {
                if (in_array($lockedPayment->status, [Payment::STATUS_PENDING, Payment::STATUS_FAILED], true)) {
                    $lockedPayment->status = $incoming;
                    if (isset($payload['payment_type']) && is_string($payload['payment_type'])) {
                        $lockedPayment->payment_type = $payload['payment_type'];
                    }
                    if (isset($payload['transaction_id']) && is_string($payload['transaction_id'])) {
                        $lockedPayment->transaction_id = $payload['transaction_id'];
                    }
                    $lockedPayment->raw_notification = array_diff_key($payload, ['signature_key' => true]);
                    $lockedPayment->save();
                }
            }

            return ['code' => 200, 'message' => 'OK'];
        });
    }
}
