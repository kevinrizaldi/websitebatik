<?php

namespace App\Services\Midtrans;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;
use Midtrans\Transaction;

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
     * Expiry hours for payment attempts (minimum 1 hour).
     */
    private function expiryHours(): int
    {
        return max(1, (int) config('midtrans.expiry_hours'));
    }

    /**
     * Convert a decimal money string to integer rupiah.
     *
     * Rules:
     * - Split on ".". If the fractional part is not all zeros, throw.
     * - Return the integer part as int.
     *
     * @throws InvalidArgumentException
     */
    public function toRupiah(string $decimal): int
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
     * Build the item_details array for an order (single source of truth for gross amount).
     *
     * Includes one row per order item plus an optional SHIPPING row.
     * The sum of (price * quantity) across all rows equals the gross amount.
     *
     * @return array{item_details: list<array{id: string, price: int, quantity: int, name: string}>, gross_amount: int}
     *
     * @throws InvalidArgumentException if the order has no items or a money value has non-zero fractional rupiah.
     */
    public function buildItemDetails(Order $order): array
    {
        $items = $order->items()->get();

        if ($items->isEmpty()) {
            throw new InvalidArgumentException(
                "Order #{$order->id} has no items and cannot be submitted to Midtrans."
            );
        }

        $itemDetails = [];
        $grossAmount = 0;

        foreach ($items as $item) {
            $priceRupiah = $this->toRupiah((string) $item->price);
            $lineTotal = $priceRupiah * (int) $item->quantity;
            $grossAmount += $lineTotal;

            $itemDetails[] = [
                'id' => $item->produk_id ?? 'ITEM-'.$item->id,
                'price' => $priceRupiah,
                'quantity' => (int) $item->quantity,
                'name' => mb_substr((string) $item->produk_name, 0, 50),
            ];
        }

        $shippingRupiah = $this->toRupiah((string) $order->shipping_cost);
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
            'item_details' => $itemDetails,
            'gross_amount' => $grossAmount,
        ];
    }

    /**
     * Build the Snap parameter array for an order.
     *
     * The $expiresAt value is used for BOTH Payment.expires_at and the Snap request,
     * ensuring the Midtrans-side expiry never exceeds the order deadline.
     *
     * @throws InvalidArgumentException if the order has no items.
     */
    public function buildSnapParams(Order $order, string $midtransOrderId, Carbon $expiresAt): array
    {
        ['item_details' => $itemDetails, 'gross_amount' => $grossAmount] = $this->buildItemDetails($order);

        $user = $order->user;
        $orderId = $midtransOrderId;

        // Convert remaining seconds to minutes, minimum 1.
        $minutesLeft = max(1, (int) ceil($expiresAt->diffInSeconds(now(), false) * -1 / 60));

        return [
            'transaction_details' => [
                'order_id' => $orderId,
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
                'unit' => 'minutes',
                'duration' => $minutesLeft,
            ],
            'callbacks' => [
                'finish' => route('payment.finish'),
            ],
        ];
    }

    /**
     * Retrieve an existing reusable snap token or generate a new payment attempt.
     *
     * @throws InvalidArgumentException if the order is not payable or past its deadline.
     */
    public function getOrCreateSnapToken(Order $order): string
    {
        return DB::transaction(function () use ($order): string {
            /** @var Order $locked */
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (
                $locked->status !== Order::STATUS_UNPAID
                || $locked->payment_status !== Order::PAYMENT_PENDING
            ) {
                throw new InvalidArgumentException(
                    "Order #{$locked->id} is not payable (status={$locked->status}, payment_status={$locked->payment_status})."
                );
            }

            if ($locked->isPastDeadline()) {
                throw new InvalidArgumentException(
                    "Order #{$locked->id} has passed its payment deadline."
                );
            }

            /** @var Payment|null $reusable */
            $reusable = Payment::where('order_id', $locked->id)
                ->where('status', Payment::STATUS_PENDING)
                ->whereNotNull('snap_token')
                ->where('snap_token', '!=', '')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->first();

            if ($reusable) {
                return $reusable->snap_token;
            }

            ['gross_amount' => $gross] = $this->buildItemDetails($locked);

            // Set the order's payment_deadline on the first attempt (PAY-17).
            $deadlineHours = max(1, (int) config('midtrans.payment_deadline_hours', 24));
            if ($locked->payment_deadline === null) {
                $locked->payment_deadline = now()->addHours($deadlineHours);
                $locked->save();
            }

            // Compute ONE expiry value: min(now + expiryHours, payment_deadline).
            // This is used for BOTH Payment.expires_at and the Midtrans Snap request,
            // so the Midtrans-side expiry can never exceed the order deadline (PAY-19, commit 3a).
            $attemptExpiry = now()->addHours($this->expiryHours());
            if ($locked->payment_deadline->lt($attemptExpiry)) {
                $attemptExpiry = $locked->payment_deadline->copy();
            }

            $midtransOrderId = mb_substr((string) $locked->code, 0, 30).'-'.time().'-'.Str::lower(Str::random(4));

            $params = $this->buildSnapParams($locked, $midtransOrderId, $attemptExpiry);
            $snapToken = $this->requestSnapToken($params);

            Payment::create([
                'order_id' => $locked->id,
                'midtrans_order_id' => $midtransOrderId,
                'snap_token' => $snapToken,
                'status' => Payment::STATUS_PENDING,
                'gross_amount' => $gross,
                'expires_at' => $attemptExpiry,
            ]);

            return $snapToken;
        });
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
     * The ONLY method that calls the real Midtrans Cancel API.
     * Kept as a separate public method so tests can mock it.
     *
     * @throws \Exception on Midtrans API failure.
     */
    public function cancelTransaction(string $midtransOrderId): void
    {
        $this->configure();

        Transaction::cancel($midtransOrderId);
    }

    /**
     * Extract whitelisted payment details from a notification payload.
     * Drops all sensitive and non-whitelisted data.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function extractPaymentDetails(array $payload): array
    {
        $details = [];

        if (isset($payload['payment_type']) && is_string($payload['payment_type']) && $payload['payment_type'] !== '') {
            $details['payment_type'] = $payload['payment_type'];
        }

        if (isset($payload['va_numbers']) && is_array($payload['va_numbers'])) {
            $vaList = [];
            foreach ($payload['va_numbers'] as $va) {
                if (is_array($va) && isset($va['bank'], $va['va_number'])
                    && is_string($va['bank']) && is_string($va['va_number'])) {
                    $vaList[] = [
                        'bank' => $va['bank'],
                        'va_number' => $va['va_number'],
                    ];
                }
            }
            if (! empty($vaList)) {
                $details['va_numbers'] = $vaList;
            }
        }

        foreach (['permata_va_number', 'bill_key', 'biller_code', 'payment_code', 'store', 'qr_string', 'expiry_time'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key]) && $payload[$key] !== '') {
                $details[$key] = $payload[$key];
            }
        }

        if (isset($payload['actions']) && is_array($payload['actions'])) {
            $actionList = [];
            foreach ($payload['actions'] as $action) {
                if (is_array($action) && isset($action['name'], $action['method'], $action['url'])
                    && is_string($action['name']) && is_string($action['method']) && is_string($action['url'])) {
                    $parsed = parse_url($action['url']);
                    $scheme = strtolower($parsed['scheme'] ?? '');
                    $host = strtolower($parsed['host'] ?? '');

                    if ($scheme === 'https' && ($host === 'midtrans.com' || str_ends_with($host, '.midtrans.com'))) {
                        $actionList[] = [
                            'name' => $action['name'],
                            'method' => $action['method'],
                            'url' => $action['url'],
                        ];
                    }
                }
            }
            if (! empty($actionList)) {
                $details['actions'] = $actionList;
            }
        }

        return $details;
    }

    /**
     * Parse Midtrans expiry_time (Asia/Jakarta / WIB) to application timezone.
     */
    public function parseExpiryTime(?string $value): ?Carbon
    {
        if (empty($value) || ! is_string($value)) {
            return null;
        }

        try {
            return Carbon::parse($value, 'Asia/Jakarta')->setTimezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Verify a Midtrans webhook signature.
     *
     * Formula: SHA-512(order_id + status_code + gross_amount + server_key)
     * compared with hash_equals to prevent timing attacks.
     *
     * All four fields must be non-empty strings; an integer 0 or boolean false
     * must not be treated as present (avoids empty() pitfall).
     */
    public function verifySignature(array $payload): bool
    {
        $required = ['order_id', 'status_code', 'gross_amount', 'signature_key'];
        foreach ($required as $field) {
            if (! isset($payload[$field]) || ! is_string($payload[$field]) || $payload[$field] === '') {
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
                default => Order::PAYMENT_PAID,   // "accept" or null
            },
            'settlement' => Order::PAYMENT_PAID,
            'pending' => Order::PAYMENT_PENDING,
            'deny' => Order::PAYMENT_FAILED,
            'cancel' => Order::PAYMENT_CANCELLED,
            'expire' => Order::PAYMENT_EXPIRED,
            default => null,
        };
    }

    /**
     * Classify a Midtrans GET-status response into a coarse category used by
     * PaymentMethodChanger and PaymentSyncController.
     *
     * settlement           -> 'paid'
     * capture/accept|null  -> 'paid'
     * capture/challenge    -> 'pending'
     * pending, authorize   -> 'pending'
     * deny, cancel, expire, failure -> 'dead'
     * anything else        -> 'unknown'
     *
     * @param  array<string, mixed>  $payload
     */
    public function classifyTransactionStatus(array $payload): string
    {
        $status = (string) ($payload['transaction_status'] ?? '');
        $fraud = isset($payload['fraud_status']) && is_string($payload['fraud_status'])
            ? $payload['fraud_status']
            : null;

        return match ($status) {
            'settlement' => 'paid',
            'capture' => match ($fraud) {
                'challenge' => 'pending',
                default => 'paid',   // 'accept' or null
            },
            'pending', 'authorize' => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'dead',
            default => 'unknown',
        };
    }

    /**
     * Deep-convert an SDK response (which may be a stdClass or contain nested
     * stdClass objects) to a plain PHP array.
     *
     * json_decode(json_encode()) recursively converts all stdClass objects to
     * associative arrays, so extractPaymentDetails() can safely use is_array().
     * Returns [] if the result is not an array (e.g. the input was a scalar).
     *
     * @return array<string, mixed>
     */
    public function normalizeSdkResponse(mixed $response): array
    {
        $decoded = json_decode(json_encode($response), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * The ONLY method that calls the real Midtrans GET-status API.
     * Returns the raw response array, or null when Midtrans returns 404 (no transaction yet).
     * Kept as a separate public method so tests can mock it.
     *
     * @return array<string, mixed>|null
     *
     * @throws \Exception on non-404 API failure.
     */
    public function getTransactionStatus(string $midtransOrderId): ?array
    {
        $this->configure();

        try {
            $response = Transaction::status($midtransOrderId);

            return $this->normalizeSdkResponse($response);
        } catch (\Exception $e) {
            if ((int) $e->getCode() === 404) {
                return null; // transaction not yet created at Midtrans
            }
            throw $e;
        }
    }

    /**
     * Apply a Midtrans GET-status response to a Payment attempt.
     *
     * - Updates payment_type, transaction_id, payment_details, expires_at.
     * - Only updates when the attempt is still pending.
     *
     * @param  array<string, mixed>  $statusResponse
     */
    public function syncPaymentAttempt(Payment $payment, array $statusResponse): void
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return;
        }

        if (isset($statusResponse['payment_type']) && is_string($statusResponse['payment_type'])) {
            $payment->payment_type = $statusResponse['payment_type'];
        }

        if (isset($statusResponse['transaction_id']) && is_string($statusResponse['transaction_id'])) {
            $payment->transaction_id = $statusResponse['transaction_id'];
        }

        $extracted = $this->extractPaymentDetails($statusResponse);
        if (! empty($extracted)) {
            $payment->payment_details = array_merge($payment->payment_details ?? [], $extracted);
        }

        if (isset($statusResponse['expiry_time']) && is_string($statusResponse['expiry_time'])) {
            $parsed = $this->parseExpiryTime($statusResponse['expiry_time']);
            if ($parsed !== null) {
                $payment->expires_at = $parsed;
            }
        }

        $payment->save();
    }
}
