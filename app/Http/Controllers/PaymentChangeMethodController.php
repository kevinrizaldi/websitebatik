<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentChangeMethodController extends Controller
{
    public function __construct(private readonly MidtransService $midtransService) {}

    /**
     * Cancel the active payment attempt (if buyer already chose a method) and
     * create a new one with a fresh midtrans_order_id + Snap token.
     *
     * If the buyer has NOT yet chosen a method (no transaction at Midtrans),
     * the existing token is returned unchanged.
     *
     * POST /payment/change-method
     */
    public function change(Request $request): JsonResponse
    {
        $validated = $request->validate(['order_id' => ['required', 'integer']]);

        /** @var Order|null $order */
        $order = Order::find($validated['order_id']);

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pesanan ini.'], 403);
        }

        try {
            ['snap_token' => $snapToken, 'client_key' => $clientKey] = DB::transaction(
                function () use ($order): array {
                    return $this->processChangeMethod($order);
                }
            );
        } catch (\InvalidArgumentException $e) {
            Log::warning('PaymentChangeMethod: order not eligible', [
                'order_id' => $order->id,
            ]);

            return response()->json(['message' => 'Pesanan ini tidak dapat diubah metode pembayarannya saat ini.'], 422);
        } catch (\Exception $e) {
            Log::error('PaymentChangeMethod: unexpected error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Gagal mengganti metode pembayaran. Silakan coba lagi nanti.'], 502);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => $clientKey,
        ]);
    }

    /**
     * Core logic inside a DB transaction with strict lock order (Order then Payment — PAY-05).
     *
     * @return array{snap_token: string, client_key: string}
     *
     * @throws \InvalidArgumentException when the order is not eligible.
     * @throws \Exception on Midtrans Cancel API or Snap API failure.
     */
    private function processChangeMethod(Order $order): array
    {
        /** @var Order $lockedOrder */
        $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

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

        /** @var Payment|null $active */
        $active = Payment::where('order_id', $lockedOrder->id)
            ->where('status', Payment::STATUS_PENDING)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        if (! $active) {
            // No active attempt — create the first one.
            $snapToken = $this->midtransService->getOrCreateSnapToken($lockedOrder);

            return ['snap_token' => $snapToken, 'client_key' => config('midtrans.client_key')];
        }

        // Determine whether the buyer has already chosen a payment method at Midtrans.
        // payment_type being set means they have interacted; use GET-status as fallback.
        $hasChosenMethod = $this->buyerAlreadyChoseMethod($active);

        if (! $hasChosenMethod) {
            // Buyer never chose a method; the existing token is still valid — reuse it.
            return ['snap_token' => $active->snap_token, 'client_key' => config('midtrans.client_key')];
        }

        // Buyer already chose a method; cancel the old attempt at Midtrans first.
        $this->cancelAtMidtrans($active, $lockedOrder);

        // Create a new attempt.
        $newSnapToken = $this->midtransService->getOrCreateSnapToken($lockedOrder);

        return ['snap_token' => $newSnapToken, 'client_key' => config('midtrans.client_key')];
    }

    /**
     * Determine whether the buyer has already chosen a payment method.
     *
     * Heuristic: payment_type is set in our DB OR Midtrans GET-status returns a
     * non-404 response (which means a transaction was created at their side).
     */
    private function buyerAlreadyChoseMethod(Payment $payment): bool
    {
        if (! empty($payment->payment_type)) {
            return true;
        }

        try {
            $status = $this->midtransService->getTransactionStatus($payment->midtrans_order_id);

            return $status !== null;
        } catch (\Exception) {
            // Cannot reach Midtrans; assume method was NOT chosen to avoid blocking buyer.
            return false;
        }
    }

    /**
     * Call the Midtrans Cancel API on the given attempt and mark it `replaced`.
     *
     * If the Cancel API reports the transaction was already settled, the order
     * is marked paid (PAY-15 race condition) and an exception is thrown to
     * abort the outer transaction and inform the caller.
     *
     * @throws \Exception if Cancel API fails or the transaction was already settled.
     */
    private function cancelAtMidtrans(Payment $active, Order $lockedOrder): void
    {
        try {
            $this->midtransService->cancelTransaction($active->midtrans_order_id);
        } catch (\Exception $e) {
            // 412 = transaction cannot be cancelled (might already be settled).
            if ($e->getCode() === 412) {
                // Check actual status — it might be settled while we were working.
                $statusResponse = null;
                try {
                    $statusResponse = $this->midtransService->getTransactionStatus($active->midtrans_order_id);
                } catch (\Exception) {
                }

                if (
                    $statusResponse !== null
                    && isset($statusResponse['transaction_status'])
                    && in_array($statusResponse['transaction_status'], ['settlement', 'capture'], true)
                ) {
                    // The old attempt was actually paid — apply it and abort.
                    Log::warning('PaymentChangeMethod: cancel failed because already settled, marking paid', [
                        'order_id' => $lockedOrder->id,
                        'payment_id' => $active->id,
                    ]);

                    $active->status = Payment::STATUS_PAID;
                    $active->paid_at = now();
                    $active->save();

                    $lockedOrder->payment_status = Order::PAYMENT_PAID;
                    $lockedOrder->paid_at = now();
                    $lockedOrder->status = Order::STATUS_PAID;
                    $lockedOrder->save();

                    throw new \RuntimeException(
                        "Order #{$lockedOrder->id} was already settled; no new attempt created.",
                        409
                    );
                }
            }

            // Other failure: leave the old attempt in place and propagate.
            Log::warning('PaymentChangeMethod: Midtrans cancel failed', [
                'order_id' => $lockedOrder->id,
                'payment_id' => $active->id,
            ]);
            throw $e;
        }

        // Cancel succeeded — mark old attempt as replaced.
        $active->status = Payment::STATUS_REPLACED;
        $active->save();

        Log::info('PaymentChangeMethod: old attempt replaced', [
            'order_id' => $lockedOrder->id,
            'payment_id' => $active->id,
        ]);
    }
}
