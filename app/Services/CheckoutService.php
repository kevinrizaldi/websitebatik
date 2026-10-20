<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pengiriman;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CheckoutService
{
    public function __construct(
        protected MidtransService $midtransService,
        protected CartService $cartService
    ) {}

    /**
     * Prepare cart items for the checkout page.
     * Selects all items if no item is currently selected.
     *
     * @return Collection<int, CartItem>
     */
    public function prepareCheckoutItems(): Collection
    {
        $this->cartService->consolidateGuestCart();

        $cartItems = CartItem::with('produk')
            ->forCurrentVisitor()
            ->where('selected', true)
            ->latest()
            ->get();

        if ($cartItems->isEmpty()) {
            $allCartItems = CartItem::with('produk')
                ->forCurrentVisitor()
                ->latest()
                ->get();

            if ($allCartItems->isEmpty()) {
                return collect();
            }

            CartItem::forCurrentVisitor()->update(['selected' => true]);

            return $allCartItems;
        }

        return $cartItems;
    }

    /**
     * Validate that each item has sufficient stock for its size.
     *
     * @param  Collection<int, CartItem>  $cartItems
     * @return array<int, string> List of unavailable item messages
     */
    public function validateItemsStock(Collection $cartItems): array
    {
        $unavailable = [];

        foreach ($cartItems as $item) {
            if ($item->produk) {
                $stock = $item->produk->getStokForUkuran($item->ukuran);
                if ($stock < $item->qty) {
                    $sizeLabel = $item->ukuran ? " (Ukuran {$item->ukuran})" : '';
                    $unavailable[] = $item->produk->nama.$sizeLabel." (sisa {$stock})";
                }
            }
        }

        return $unavailable;
    }

    /**
     * Calculate subtotal, voucher discount, shipping, and grand total.
     *
     * @param  Collection<int, CartItem>  $cartItems
     * @return array{
     *     subtotal: float,
     *     discount: float,
     *     shipping: float,
     *     grand_total: float
     * }
     */
    public function calculateOrderTotals(Collection $cartItems, ?string $voucher = null, ?float $shippingCost = null): array
    {
        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $price = $item->produk ? (float) $item->produk->harga : 0.0;
            $subtotal += ($price * $item->qty);
        }

        $discount = 0.0;
        $voucherInput = strtoupper((string) $voucher);
        if (in_array($voucherInput, ['SIRKULAR50K', 'LESTARI50K', 'LESTARICAPSULE'], true) && $subtotal >= 200000) {
            $discount = 50000.0;
        }

        $shipping = $shippingCost !== null
            ? (float) $shippingCost
            : (($subtotal >= 955000) ? 0.0 : 20000.0);

        $grandTotal = max(0.0, $subtotal - $discount + $shipping);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Automatically synchronize default address if user doesn't have one saved yet.
     *
     * @param  array<string, mixed>  $validated
     */
    public function syncDefaultAddress(User $user, array $validated, ?string $city = null, ?string $province = null, ?string $postalCode = null): void
    {
        if ($user->alamats()->count() === 0 && ! empty($validated['address'])) {
            try {
                $user->alamats()->create([
                    'label_alamat' => 'Alamat Utama',
                    'penerima' => $validated['customer_name'],
                    'no_telepon' => $validated['phone'],
                    'alamat_lengkap' => $validated['address'],
                    'kota' => $city ?: 'Indonesia',
                    'provinsi' => $province ?: 'Indonesia',
                    'kode_pos' => $postalCode ?: '',
                    'is_utama' => true,
                ]);
            } catch (Throwable $e) {
                // Jangan batalkan checkout jika sinkronisasi alamat gagal
            }
        }
    }

    /**
     * Create the order, items, deduct inventory per size, and clean cart within a transaction.
     *
     * @param  array<string, mixed>  $validated
     * @param  Collection<int, CartItem>  $cartItems
     */
    public function createOrder(User $user, array $validated, Collection $cartItems, float $grandTotal, float $shipping): Order
    {
        $orderCode = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(4));
        $shippingOption = $validated['shipping_option'] ?? 'JNE Reguler';

        return DB::transaction(function () use ($user, $validated, $cartItems, $grandTotal, $orderCode, $shippingOption, $shipping) {
            $notesText = ! empty($validated['notes']) ? ' (Catatan: '.$validated['notes'].')' : '';
            $fullAddress = $validated['address'].$notesText.' [Kurir: '.$shippingOption.']';

            $order = Order::create([
                'user_id' => $user->id,
                'code' => $orderCode,
                'customer_name' => $validated['customer_name'],
                'phone' => $validated['phone'],
                'address' => $fullAddress,
                'total_price' => $grandTotal,
                'shipping_cost' => $shipping,
                'payment_method' => $validated['payment_method'] ?? 'Midtrans Gateway',
                'status' => 'Menunggu Pembayaran',
                'payment_status' => Order::PAYMENT_PENDING,
            ]);

            foreach ($cartItems as $item) {
                if ($item->produk) {
                    $itemPrice = (float) $item->produk->harga;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'produk_id' => $item->produk->id,
                        'produk_name' => $item->produk->nama.($item->ukuran ? ' ('.$item->ukuran.')' : ''),
                        'price' => $itemPrice,
                        'quantity' => $item->qty,
                        'subtotal' => $itemPrice * $item->qty,
                    ]);

                    if ($item->produk->stok >= $item->qty) {
                        $item->produk->decrementStokForUkuran($item->ukuran, $item->qty);
                    }
                }

                $item->delete();
            }

            Pengiriman::create([
                'order_id' => $order->id,
                'ekspedisi' => $shippingOption,
                'status_pengiriman' => 'Menunggu Pengiriman',
            ]);

            return $order;
        });
    }

    /**
     * Safely request a Midtrans Snap transaction token for the order.
     */
    public function createSnapToken(Order $order): ?string
    {
        try {
            $snapData = $this->midtransService->createSnapTransaction($order);

            return $snapData['token'] ?? null;
        } catch (Throwable $e) {
            Log::warning('Midtrans Snap token gagal dibuat saat checkout', [
                'order_code' => $order->code,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
