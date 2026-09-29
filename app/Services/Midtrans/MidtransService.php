<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use InvalidArgumentException;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MidtransService
{
    /**
     * Configure the Midtrans SDK from application config.
     */
    public function configure(): void
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Convert a decimal money string to integer rupiah.
     *
     * Rules:
     * - Split on '.'. If the fractional part is not all zeros, throw.
     * - Return the integer part as int.
     *
     * @throws InvalidArgumentException
     */
    private function decimalToRupiah(string $decimal): int
    {
        if (str_contains($decimal, '.')) {
            [$integer, $fraction] = explode('.', $decimal, 2);
            if (ltrim($fraction, '0') !== '') {
                throw new InvalidArgumentException(
                    "Money value '{$decimal}' has non-zero fractional rupiah."
                );
            }

            return (int) $integer;
        }

        return (int) $decimal;
    }

    /**
     * Build the Snap parameter array for an order.
     *
     * @throws InvalidArgumentException if the order has no items.
     */
    public function buildSnapParams(Order $order): array
    {
        $items = $order->items()->get();

        if ($items->isEmpty()) {
            throw new InvalidArgumentException(
                "Order #{$order->id} has no items and cannot be submitted to Midtrans."
            );
        }

        $user = $order->user;

        $itemDetails = [];
        $grossAmount = 0;

        foreach ($items as $item) {
            $priceRupiah = $this->decimalToRupiah((string) $item->price);
            $lineTotal = $priceRupiah * (int) $item->quantity;
            $grossAmount += $lineTotal;

            $itemDetails[] = [
                'id' => $item->produk_id ?? 'ITEM-'.$item->id,
                'price' => $priceRupiah,
                'quantity' => (int) $item->quantity,
                'name' => mb_substr((string) $item->produk_name, 0, 50),
            ];
        }

        $shippingRupiah = $this->decimalToRupiah((string) $order->shipping_cost);
        if ($shippingRupiah > 0) {
            $grossAmount += $shippingRupiah;
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => $shippingRupiah,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        return [
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email' => $user?->email ?? '',
                'phone' => (string) $order->phone,
                'shipping_address' => [
                    'first_name' => $order->customer_name,
                    'phone' => (string) $order->phone,
                    'address' => mb_substr((string) $order->address, 0, 200),
                ],
            ],
            'expiry' => [
                'unit' => 'hours',
                'duration' => config('midtrans.expiry_hours'),
            ],
            'callbacks' => [
                'finish' => route('payment.finish'),
            ],
        ];
    }

    /**
     * Retrieve an existing snap token or generate a new one.
     *
     * A payable order must have:
     *   - status === STATUS_UNPAID ('Belum Dibayar')
     *   - payment_status === PAYMENT_PENDING ('pending')
     *
     * @throws InvalidArgumentException if the order is not payable.
     */
    public function getOrCreateSnapToken(Order $order): string
    {
        if (
            $order->status !== Order::STATUS_UNPAID
            || $order->payment_status !== Order::PAYMENT_PENDING
        ) {
            throw new InvalidArgumentException(
                "Order #{$order->id} is not payable (status={$order->status}, payment_status={$order->payment_status})."
            );
        }

        // Return existing token without a new Midtrans API call.
        if ($order->snap_token && $order->payment_status === Order::PAYMENT_PENDING) {
            return $order->snap_token;
        }

        // Generate a unique midtrans_order_id (max 50 chars).
        $midtransOrderId = mb_substr($order->code.'-'.time(), 0, 50);
        $order->midtrans_order_id = $midtransOrderId;
        $order->save();

        $params = $this->buildSnapParams($order);
        $snapToken = $this->requestSnapToken($params);

        $order->snap_token = $snapToken;
        $order->save();

        return $snapToken;
    }

    /**
     * The ONLY method that calls the real Midtrans Snap API.
     * Kept as a separate public method so tests can mock it.
     *
     * @throws \Exception on Midtrans API failure.
     */
    public function requestSnapToken(array $params): string
    {
        $this->configure();

        return Snap::getSnapToken($params);
    }

    /**
     * Verify a Midtrans webhook signature.
     *
     * Formula: SHA-512(order_id + status_code + gross_amount + server_key)
     * compared with hash_equals to prevent timing attacks.
     */
    public function verifySignature(array $payload): bool
    {
        $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                return false;
            }
        }

        $serverKey = config('midtrans.server_key');
        $expected = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey
        );

        return hash_equals($expected, $payload['signature_key']);
    }

    /**
     * Map a Midtrans transaction_status + fraud_status to our internal payment_status.
     *
     * Returns null when the notification should be silently acknowledged with no state change.
     */
    public function mapPaymentStatus(string $transactionStatus, ?string $fraudStatus): ?string
    {
        return match ($transactionStatus) {
            'capture' => match ($fraudStatus) {
                'challenge' => Order::PAYMENT_PENDING,
                default => Order::PAYMENT_PAID,   // 'accept' or null
            },
            'settlement' => Order::PAYMENT_PAID,
            'pending' => Order::PAYMENT_PENDING,
            'deny' => Order::PAYMENT_FAILED,
            'cancel' => Order::PAYMENT_CANCELLED,
            'expire' => Order::PAYMENT_EXPIRED,
            default => null,
        };
    }
}
