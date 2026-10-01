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
    /** Maximum accepted QR image body size in bytes (1 MB). */
    private const MAX_BODY_BYTES = 1_048_576;

    /**
     * Stream the official Midtrans QR image for an active pending payment attempt.
     *
     * - Verifies order ownership and that the attempt is still pending and not expired.
     * - Resolves the QR URL from payment_details.actions (set by webhook or sync).
     * - Proxies the image from Midtrans without storing it permanently (PAY-22).
     * - Does NOT follow redirects; treats 3xx as failure.
     * - Accepts only image/png or image/jpeg content types.
     * - Rejects bodies larger than 1 MB.
     * - Adds X-Content-Type-Options: nosniff.
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
            // withoutRedirecting() ensures 3xx responses are returned as-is (not followed).
            $imageResponse = Http::withoutRedirecting()->timeout(10)->get($qrUrl);
        } catch (\Exception $e) {
            Log::warning('PaymentQr: failed to fetch QR image from Midtrans', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);

            abort(502, 'Gagal mengambil gambar QR dari Midtrans.');
        }

        // Treat any non-2xx response (including 3xx redirects) as failure.
        if (! $imageResponse->successful()) {
            abort(502, 'Gagal mengambil gambar QR dari Midtrans.');
        }

        $contentType = $imageResponse->header('Content-Type') ?? '';
        // Strip any parameters (e.g. "; charset=utf-8") for comparison.
        $mimeType = strtolower(trim(explode(';', $contentType)[0]));

        // Only allow image/png or image/jpeg.
        if (! in_array($mimeType, ['image/png', 'image/jpeg'], true)) {
            abort(502, 'Respons dari Midtrans bukan gambar yang diizinkan.');
        }

        // Reject oversized bodies: check Content-Length header first (fast path),
        // then check the real body size.
        $contentLength = $imageResponse->header('Content-Length');
        if ($contentLength !== null && (int) $contentLength > self::MAX_BODY_BYTES) {
            abort(502, 'Respons dari Midtrans terlalu besar.');
        }

        $body = $imageResponse->body();
        if (strlen($body) > self::MAX_BODY_BYTES) {
            abort(502, 'Respons dari Midtrans terlalu besar.');
        }

        return response($body, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="qr-payment-'.$payment->midtrans_order_id.'.png"',
            'Cache-Control' => 'no-store, no-cache',
            'X-Content-Type-Options' => 'nosniff',
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
