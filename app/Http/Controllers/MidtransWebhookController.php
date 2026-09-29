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

        // ── 1. Validate required fields ───────────────────────────────────────
        $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'];
        foreach ($requiredFields as $field) {
            if (empty($payload[$field])) {
                return response()->json(['message' => "Missing field: {$field}"], 400);
            }
        }

        // ── 2. Verify signature before any DB work ────────────────────────────
        if (! $this->midtransService->verifySignature($payload)) {
            Log::warning('Midtrans webhook: invalid signature', [
                'order_id' => $payload['order_id'] ?? 'unknown',
            ]);

            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        // ── 3. DB transaction with row lock ───────────────────────────────────
        return DB::transaction(function () use ($payload): JsonResponse {
            /** @var Order|null $order */
            $order = Order::where('midtrans_order_id', $payload['order_id'])
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
            }

            // ── 4. Idempotency: early return if already final ─────────────────
            if ($order->isPaymentFinal()) {
                return response()->json(['message' => 'OK (already final).'], 200);
            }

            // ── 5. Gross amount integrity check ───────────────────────────────
            $serverGrossAmount = $this->computeGrossAmount($order);
            $payloadAmount = (int) $payload['gross_amount'];

            if ($serverGrossAmount !== $payloadAmount) {
                Log::warning('Midtrans webhook: gross_amount mismatch', [
                    'order_id' => $order->id,
                    'server_amount' => $serverGrossAmount,
                    'payload_amount' => $payloadAmount,
                ]);

                return response()->json(['message' => 'Gross amount mismatch.'], 400);
            }

            // ── 6. Map status ─────────────────────────────────────────────────
            $newPaymentStatus = $this->midtransService->mapPaymentStatus(
                $payload['transaction_status'],
                $payload['fraud_status'] ?? null
            );

            if ($newPaymentStatus === null) {
                // Unknown / no-op transaction status — acknowledge silently.
                return response()->json(['message' => 'OK (ignored).'], 200);
            }

            // ── 7. Update payment fields ───────────────────────────────────────
            $order->payment_status = $newPaymentStatus;
            $order->payment_type = $payload['payment_type'] ?? $order->payment_type;
            $order->transaction_id = $payload['transaction_id'] ?? $order->transaction_id;
            $order->raw_notification = $payload;

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
                // Only cancel the order if it hasn't progressed past 'Belum Dibayar'.
                if ($order->status === Order::STATUS_UNPAID) {
                    $order->status = Order::STATUS_CANCELLED;
                }
            }

            $order->save();

            return response()->json(['message' => 'OK'], 200);
        });
    }

    /**
     * Compute the server-side gross amount (integer rupiah) for an order.
     * Must mirror buildSnapParams logic exactly.
     */
    private function computeGrossAmount(Order $order): int
    {
        $items = $order->items()->get();
        $grossAmount = 0;

        foreach ($items as $item) {
            $priceStr = (string) $item->price;
            if (str_contains($priceStr, '.')) {
                [, $fraction] = explode('.', $priceStr, 2);
                if (ltrim($fraction, '0') !== '') {
                    // Non-zero fraction — this is a data integrity issue.
                    Log::error('Midtrans webhook: non-zero fractional price', ['item_id' => $item->id]);

                    return -1; // Force mismatch so we return 400.
                }
                $priceInt = (int) explode('.', $priceStr)[0];
            } else {
                $priceInt = (int) $priceStr;
            }

            $grossAmount += $priceInt * (int) $item->quantity;
        }

        $shippingStr = (string) $order->shipping_cost;
        if (str_contains($shippingStr, '.')) {
            [, $fraction] = explode('.', $shippingStr, 2);
            $shippingInt = ltrim($fraction, '0') === '' ? (int) explode('.', $shippingStr)[0] : 0;
        } else {
            $shippingInt = (int) $shippingStr;
        }

        $grossAmount += $shippingInt;

        return $grossAmount;
    }
}
