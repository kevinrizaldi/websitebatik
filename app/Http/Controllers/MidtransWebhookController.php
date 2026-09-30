<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(private readonly MidtransService $midtransService) {}

    /**
     * Handle a Midtrans payment notification webhook.
     *
     * POST /midtrans/notification
     */
    public function handle(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        // -- 1. Validate required fields (strings, non-empty) -------------------
        $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'];
        foreach ($requiredFields as $field) {
            if (! isset($payload[$field]) || ! is_string($payload[$field]) || $payload[$field] === '') {
                return response()->json(['message' => "Missing or invalid field: {$field}"], 400);
            }
        }

        // Validate optional fraud_status type before any DB work
        $rawFraudStatus = $payload['fraud_status'] ?? null;
        if ($rawFraudStatus !== null && ! is_string($rawFraudStatus)) {
            return response()->json(['message' => 'Invalid field: fraud_status'], 400);
        }

        // -- 2. Verify signature before any DB work ----------------------------
        if (! $this->midtransService->verifySignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', [
                'order_id' => $payload['order_id'] ?? 'unknown',
            ]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        // -- 3. Lookup payment attempt by midtrans_order_id (unlocked read) ----
        $payment = Payment::where('midtrans_order_id', $payload['order_id'])->first();
        if (! $payment) {
            return response()->json(['message' => 'Payment attempt not found.'], 404);
        }

        // -- 4. DB transaction with strict lock order: Order, then Payment -----
        return DB::transaction(function () use ($payment, $payload, $rawFraudStatus): JsonResponse {
            /** @var Order|null $order */
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->first();
            /** @var Payment|null $lockedPayment */
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $order || ! $lockedPayment) {
                return response()->json(['message' => 'Record not found.'], 404);
            }

            // Early return if attempt is already paid
            if ($lockedPayment->status === Payment::STATUS_PAID) {
                return response()->json(['message' => 'OK (already paid)'], 200);
            }

            // Compare gross_amount with the attempt's gross_amount
            $attemptGross = $this->midtransService->toRupiah((string) $lockedPayment->gross_amount);
            $payloadAmount = (int) $payload['gross_amount'];

            if ($attemptGross !== $payloadAmount) {
                Log::warning('Midtrans webhook: gross_amount mismatch with payment attempt', [
                    'order_id' => $order->id,
                    'payment_id' => $lockedPayment->id,
                ]);

                return response()->json(['message' => 'Gross amount mismatch.'], 400);
            }

            // Map incoming transaction status
            $incoming = $this->midtransService->mapPaymentStatus(
                $payload['transaction_status'],
                $rawFraudStatus
            );

            if ($incoming === null) {
                return response()->json(['message' => 'OK (ignored)'], 200);
            }

            // -- 5. Apply state transitions ------------------------------------
            if ($incoming === Payment::STATUS_PAID) {
                // Verify server gross amount matches the attempt gross amount
                try {
                    ['gross_amount' => $serverGross] = $this->midtransService->buildItemDetails($order);
                } catch (\InvalidArgumentException $e) {
                    Log::warning('Midtrans webhook: could not compute order gross amount', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);

                    return response()->json(['message' => 'Cannot compute order amount.'], 400);
                }

                if ($serverGross !== $attemptGross) {
                    Log::warning('Midtrans webhook: server gross amount mismatch with attempt gross', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);

                    return response()->json(['message' => 'Server amount mismatch.'], 400);
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

            return response()->json(['message' => 'OK'], 200);
        });
    }
}
