<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Pembayaran;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    protected string $serverKey;

    protected string $clientKey;

    protected bool $isProduction;

    protected string $snapUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key');
        $this->clientKey = (string) config('midtrans.client_key');
        $this->isProduction = (bool) config('midtrans.is_production');
        $this->snapUrl = (string) config('midtrans.snap_url');
    }

    /**
     * Create Snap Token for an Order.
     *
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order): array
    {
        $hasRealKey = ! empty($this->serverKey)
            && ! str_contains($this->serverKey, 'demo-key')
            && ! str_contains($this->serverKey, 'YOUR_KEY');

        // Reuse stored token, except MOCK fallback tokens which are regenerated
        // once real API credentials are configured.
        if ($order->pembayaran && ! empty($order->pembayaran->snap_token) && ! empty($order->pembayaran->snap_redirect_url)) {
            $isMock = str_starts_with((string) $order->pembayaran->snap_token, 'MOCK_SNAP_');

            if (! $isMock || ! $hasRealKey) {
                return [
                    'token' => $order->pembayaran->snap_token,
                    'redirect_url' => $order->pembayaran->snap_redirect_url,
                ];
            }
        }

        $grossAmount = (int) round((float) $order->total_price);
        $user = $order->user;

        // Build items array
        $items = [];
        $runningSum = 0;
        foreach ($order->items as $item) {
            $itemPrice = (int) round((float) $item->price);
            $itemQty = (int) $item->quantity;
            $items[] = [
                'id' => 'PRD-'.$item->produk_id,
                'price' => $itemPrice,
                'quantity' => $itemQty,
                'name' => Str::limit($item->produk_name, 45, '...'),
            ];
            $runningSum += ($itemPrice * $itemQty);
        }

        // Remaining difference (shipping or service fee)
        $diff = $grossAmount - $runningSum;
        if ($diff > 0) {
            $items[] = [
                'id' => 'FEES',
                'price' => $diff,
                'quantity' => 1,
                'name' => 'Ongkos Kirim & Layanan',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->code,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $user?->email ?? 'pelanggan@hamzahstyle.com',
                'phone' => $order->phone,
                'shipping_address' => [
                    'first_name' => $order->customer_name,
                    'phone' => $order->phone,
                    'address' => $order->address,
                ],
            ],
            'callbacks' => [
                'finish' => route('midtrans.finish', ['order_id' => $order->code]),
            ],
        ];

        // Attempt actual API call if server key is configured
        $token = null;
        $redirectUrl = null;

        if (! empty($this->serverKey) && ! str_contains($this->serverKey, 'demo-key') && ! str_contains($this->serverKey, 'YOUR_KEY')) {
            try {
                $response = Http::withBasicAuth($this->serverKey, '')
                    ->withHeaders(['Content-Type' => 'application/json', 'Accept' => 'application/json'])
                    ->timeout(10)
                    ->post($this->snapUrl, $params);

                if ($response->successful()) {
                    $json = $response->json();
                    $token = $json['token'] ?? null;
                    $redirectUrl = $json['redirect_url'] ?? null;
                } else {
                    Log::warning('Midtrans Snap request non-200: '.$response->body());
                }
            } catch (Exception $e) {
                Log::error('Midtrans Snap API exception: '.$e->getMessage());
            }
        }

        // Fallback mock token for sandbox simulation/testing if remote Midtrans is offline or demo key is used
        if (! $token) {
            $token = 'MOCK_SNAP_'.md5($order->code.time());
            $redirectUrl = route('midtrans.finish', ['order_id' => $order->code, 'mock' => 1]);
        }

        // Store or update Pembayaran record
        Pembayaran::updateOrCreate(
            ['order_id' => $order->id],
            [
                'metode_pembayaran' => $order->payment_method ?? 'Midtrans Gateway',
                'status_pembayaran' => $order->status === 'Sudah Dibayar' ? 'Berhasil' : 'Menunggu Pembayaran',
                'jumlah_bayar' => $order->total_price,
                'snap_token' => $token,
                'snap_redirect_url' => $redirectUrl,
            ]
        );

        return [
            'token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Process webhook notification from Midtrans.
     *
     * @param  array<string, mixed>  $notification
     */
    public function handleNotification(array $notification): ?Order
    {
        $orderId = $notification['order_id'] ?? null;
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';
        $signatureKey = $notification['signature_key'] ?? '';
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? '';
        $paymentType = $notification['payment_type'] ?? 'midtrans';

        if (! $orderId) {
            return null;
        }

        $order = Order::where('code', $orderId)->first();
        if (! $order) {
            return null;
        }

        // Verify signature if remote server key is valid
        if (! empty($this->serverKey) && ! str_contains($this->serverKey, 'demo-key')) {
            $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
            if ($signatureKey && $signatureKey !== $expectedSignature) {
                Log::warning("Midtrans signature mismatch for order {$orderId}");

                return null;
            }
        }

        // Determine new order and payment statuses based on Midtrans spec
        $orderStatus = $order->status;
        $paymentStatus = 'Menunggu Pembayaran';

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                $orderStatus = 'Menunggu Pembayaran';
                $paymentStatus = 'Menunggu Pembayaran';
            } elseif ($fraudStatus === 'accept') {
                $orderStatus = 'Diproses';
                $paymentStatus = 'Berhasil';
            }
        } elseif ($transactionStatus === 'settlement') {
            $orderStatus = 'Diproses';
            $paymentStatus = 'Berhasil';
        } elseif ($transactionStatus === 'pending') {
            $orderStatus = 'Menunggu Pembayaran';
            $paymentStatus = 'Menunggu Pembayaran';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $orderStatus = 'Dibatalkan';
            $paymentStatus = 'Dibatalkan';
        }

        // Update Order
        $order->update([
            'status' => $orderStatus,
            'payment_method' => 'Midtrans ('.strtoupper($paymentType).')',
        ]);

        // Update or create Pembayaran record
        Pembayaran::updateOrCreate(
            ['order_id' => $order->id],
            [
                'metode_pembayaran' => 'Midtrans ('.strtoupper($paymentType).')',
                'status_pembayaran' => $paymentStatus,
                'jumlah_bayar' => (float) $grossAmount ?: $order->total_price,
                'tanggal_bayar' => $paymentStatus === 'Berhasil' ? now() : null,
                'transaction_id' => $notification['transaction_id'] ?? null,
                'payment_response' => $notification,
            ]
        );

        // Ensure Pengiriman record is initialized
        $order->getOrInitPengiriman();

        return $order;
    }
}
