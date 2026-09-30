<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSnapTokenRequest;
use App\Models\Order;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(private readonly MidtransService $midtransService) {}

    /**
     * Return a Snap token for the authenticated user's order.
     *
     * POST /payment/token
     */
    public function token(CreateSnapTokenRequest $request): JsonResponse
    {
        $order = Order::find($request->validated('order_id'));

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pesanan ini.'], 403);
        }

        try {
            $snapToken = $this->midtransService->getOrCreateSnapToken($order);
        } catch (InvalidArgumentException $e) {
            Log::warning('Midtrans token: order not payable', [
                'order_id' => $order->id,
                'reason' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Pesanan ini tidak dapat dibayar saat ini.',
            ], 422);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap token request failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Gagal menghubungi gateway pembayaran. Silakan coba lagi nanti.',
            ], 502);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'client_key' => config('midtrans.client_key'),
        ]);
    }

    /**
     * Payment finish callback page (placeholder — real page in next step).
     *
     * GET /payment/finish
     * Does NOT change any order status.
     */
    public function finish(Request $request): Response
    {
        return response('Pembayaran selesai. Halaman ini akan dikembangkan lebih lanjut.');
    }
}
