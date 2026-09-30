<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentSyncController extends Controller
{
    public function __construct(private readonly MidtransService $midtransService) {}

    /**
     * Sync the status and payment instructions from Midtrans GET-status API
     * for the authenticated user's active payment attempt.
     *
     * POST /payment/sync
     */
    public function sync(Request $request): JsonResponse
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

        /** @var Payment|null $payment */
        $payment = $order->activePayment();

        if (! $payment) {
            return response()->json(['message' => 'Tidak ada percobaan pembayaran aktif.'], 404);
        }

        try {
            $statusResponse = $this->midtransService->getTransactionStatus($payment->midtrans_order_id);
        } catch (\Exception $e) {
            Log::warning('PaymentSync: Midtrans GET-status failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);

            return response()->json([
                'message' => 'Gagal sinkronisasi dengan Midtrans. Silakan coba lagi nanti.',
                'payment_details' => $payment->payment_details,
            ], 502);
        }

        // null means no transaction created at Midtrans yet (buyer never chose method).
        if ($statusResponse === null) {
            return response()->json([
                'message' => 'Transaksi belum dibuat di Midtrans.',
                'payment_details' => $payment->payment_details,
            ]);
        }

        $this->midtransService->syncPaymentAttempt($payment, $statusResponse);
        $payment->refresh();

        return response()->json([
            'message' => 'Sinkronisasi berhasil.',
            'payment_details' => $payment->payment_details,
            'payment_type' => $payment->payment_type,
            'expires_at' => $payment->expires_at?->toIso8601String(),
        ]);
    }
}
