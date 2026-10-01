<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Midtrans\PaymentMethodChanger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentChangeMethodController extends Controller
{
    public function __construct(private readonly PaymentMethodChanger $changer) {}

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
            $result = $this->changer->change($order);
        } catch (\InvalidArgumentException $e) {
            Log::warning('PaymentChangeMethod: order not eligible', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Pesanan ini tidak dapat diubah metode pembayarannya saat ini.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('PaymentChangeMethod: unexpected error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal mengganti metode pembayaran. Silakan coba lagi nanti.',
            ], 502);
        }

        if ($result['outcome'] === 'paid') {
            return response()->json([
                'message' => 'Pesanan ini sudah dibayar.',
            ], 409);
        }

        // 'same_token' or 'changed'
        return response()->json([
            'snap_token' => $result['token'],
            'client_key' => config('midtrans.client_key'),
        ]);
    }
}
