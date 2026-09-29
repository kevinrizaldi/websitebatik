<?php

namespace App\Http\Controllers;

use App\Models\Order;
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

        // -- 2. Verify signature before any DB work ----------------------------
        if (! $this->midtransService->verifySignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', [
                'order_id' => $payload['order_id'] ?? 'unknown',
            ]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        // -- 3. DB transaction with row lock -----------------------------------
        return DB::transaction(function () use ($payload): JsonResponse {
            /** @var Order|null $order */
            $order = Order::where('midtrans_order_id', $payload['order_id'])
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
            }

            // -- 4. Idempotency: early return if already final -----------------
            if ($order->isPaymentFinal()) {
                return response()->json(['message' => 'OK (already final).'], 200);
            }

            // -- 5. Gross amount integrity check (single source of truth) ------
            try {
                ['gross_amount' => $serverGrossAmount] = $this->midtransService->buildItemDetails($order);
            } catch (\InvalidArgumentException $e) {
                Log::error('Midtrans webhook: could not compute gross amount', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json(['message' => 'Internal error computing order amount.'], 500);
            }

            $payloadAmount = (int) $payload['gross_amount'];

            if ($serverGrossAmount !== $payloadAmount) {
                Log::warning('Midtrans webhook: gross_amount mismatch', [
                    'order_id' => $order->id,
                    'server_amount' => $serverGrossAmount,
                    'payload_amount' => $payloadAmount,
                ]);

                return response()->json(['message' => 'Gross amount mismatch.'], 400);
            }

            // -- 6. Map status -------------------------------------------------
            $newPaymentStatus = $this->midtransService->mapPaymentStatus(
                $payload['transaction_status'],
                $payload['fraud_status'] ?? null
            );

            if ($newPaymentStatus === null) {
                // Unknown / no-op transaction status — acknowledge silently.
                return response()->json(['message' => 'OK (ignored).'], 200);
            }

            // -- 7. Update payment fields (FIX 4: exclude signature_key) -------
            $order->payment_status = $newPaymentStatus;
            $order->payment_type = $payload['payment_type'] ?? $order->payment_type;
            $order->transaction_id = $payload['transaction_id'] ?? $order->transaction_id;
            $order->raw_notification = array_diff_key($payload, ['signature_key' => true]);

            if ($newPaymentStatus === Order::PAYMENT_PAID) {
                $order->paid_at = now();

                // Advance order status only if not already beyond waiting-for-payment.
                if (
                    $order->status === Order::STATUS_UNPAID
                    || $order->status === Order::STATUS_WAITING
                ) {
                    $order->status = Order::STATUS_PAID;
                }
                // If status is Diproses / Dikirim / Selesai etc., do NOT downgrade.
            } elseif (in_array($newPaymentStatus, [Order::PAYMENT_FAILED, Order::PAYMENT_CANCELLED, Order::PAYMENT_EXPIRED], true)) {
                // Only cancel the order if it has not progressed past "Belum Dibayar".
                if ($order->status === Order::STATUS_UNPAID) {
                    $order->status = Order::STATUS_CANCELLED;
                }
            }

            $order->save();

            return response()->json(['message' => 'OK'], 200);
        });
    }
}
