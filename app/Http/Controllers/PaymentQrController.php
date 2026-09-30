<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentQrController extends Controller
{
    /**
     * Stream the official Midtrans QR image for an active pending payment attempt.
     *
     * - Verifies order ownership and that the attempt is still pending and not expired.
     * - Resolves the QR URL from payment_details.actions (set by webhook or sync).
     * - Proxies the image from Midtrans without storing it permanently (PAY-22).
     *
     * GET /orders/{order}/payment/qr
     */
    public function download(Request $request, Order $order): Response
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses ke pesanan ini.');
        }

        /** @var Payment|null $payment */
        $payment = $order->activePayment();

        if (! $payment || ! $payment->isReusable()) {
            abort(404, 'Tidak ada percobaan pembayaran aktif atau sudah kedaluwarsa.');
        }

        $qrUrl = $this->resolveQrUrl($payment);

        if ($qrUrl === null) {
            abort(404, 'URL gambar QR tidak tersedia untuk percobaan pembayaran ini.');
        }

        try {
            $imageResponse = Http::timeout(10)->get($qrUrl);
        } catch (\Exception $e) {
            Log::warning('PaymentQr: failed to fetch QR image from Midtrans', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);

            abort(502, 'Gagal mengambil gambar QR dari Midtrans.');
        }

        if (! $imageResponse->successful()) {
            abort(502, 'Gagal mengambil gambar QR dari Midtrans.');
        }

        $contentType = $imageResponse->header('Content-Type') ?? 'image/png';
        // Only allow image content types
        if (! str_starts_with($contentType, 'image/')) {
            abort(502, 'Respons dari Midtrans bukan gambar.');
        }

        return response($imageResponse->body(), 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="qr-payment-'.$payment->midtrans_order_id.'.png"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * Extract the official QR image URL from payment_details.actions.
     *
     * Midtrans puts the QR image URL in the `actions` array with name "generate-qr-code".
     * URL is validated to be HTTPS and on midtrans.com.
     */
    private function resolveQrUrl(Payment $payment): ?string
    {
        $details = $payment->payment_details;

        if (! is_array($details)) {
            return null;
        }

        $actions = $details['actions'] ?? [];

        if (! is_array($actions)) {
            return null;
        }

        foreach ($actions as $action) {
            if (
                is_array($action)
                && isset($action['name'], $action['url'])
                && is_string($action['name'])
                && is_string($action['url'])
                && str_contains(strtolower($action['name']), 'qr')
            ) {
                $parsed = parse_url($action['url']);
                $scheme = strtolower($parsed['scheme'] ?? '');
                $host = strtolower($parsed['host'] ?? '');

                if (
                    $scheme === 'https'
                    && ($host === 'midtrans.com' || str_ends_with($host, '.midtrans.com'))
                ) {
                    return $action['url'];
                }
            }
        }

        return null;
    }
}
