<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\CheckoutService;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderController extends Controller
{
    /**
     * List orders belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'shipment', 'reviews'])
            ->latest()
            ->get()
            ->map(fn (Order $order): array => $this->formatOrder($order))
            ->values();

        return response()->json([
            'data' => $orders,
        ]);
    }

    /**
     * Show a single order owned by the authenticated user.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $order->load(['items.product', 'shipment', 'reviews']);

        return response()->json([
            'data' => $this->formatOrder($order),
        ]);
    }

    /**
     * Create an order from either direct items (Buy Now) or selected cart items.
     */
    public function checkout(Request $request, CheckoutService $checkoutService): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'shipping_option' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'voucher' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'cart_item_ids' => ['nullable', 'array'],
            'cart_item_ids.*' => ['integer'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.size' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:99'],
        ]);

        $user = $request->user();

        if (! empty($validated['items'])) {
            $productIds = collect($validated['items'])->pluck('product_id')->unique()->all();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $cartItems = new Collection;
            foreach ($validated['items'] as $entry) {
                $product = $products->get($entry['product_id']);
                if (! $product) {
                    return response()->json(['message' => 'Produk tidak ditemukan.'], 422);
                }
                $item = new CartItem([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'size' => $entry['size'] ?? null,
                    'quantity' => (int) ($entry['quantity'] ?? 1),
                    'selected' => true,
                ]);
                $item->setRelation('product', $product);
                $cartItems->push($item);
            }
        } else {
            $cartQuery = CartItem::query()
                ->with('product')
                ->where('user_id', $user->id);

            if (! empty($validated['cart_item_ids'])) {
                $cartQuery->whereIn('id', $validated['cart_item_ids']);
            } else {
                $cartQuery->where('selected', true);
            }

            $cartItems = $cartQuery->get();
        }

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Keranjang kosong atau belum ada item yang dipilih.',
            ], 422);
        }

        $totals = $checkoutService->calculateOrderTotals(
            $cartItems,
            $validated['voucher'] ?? null,
            isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : null,
            $user,
        );

        try {
            $order = $checkoutService->createOrder(
                $user,
                $validated,
                $cartItems,
                $totals['grand_total'],
                $totals['shipping'],
                $totals['voucher'],
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        $checkoutService->syncDefaultAddress($user, $validated);
        $snapToken = $checkoutService->createSnapToken($order);

        $order->load(['items.product', 'shipment', 'reviews']);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $this->formatOrder($order),
            'totals' => $totals,
            'snap_token' => $snapToken,
        ], 201);
    }

    /**
     * Submit a review for an order owned by the authenticated user.
     */
    public function submitReview(Request $request, Order $order): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $order->load('items');
        $productId = $validated['product_id'] ?? $order->items->first()?->product_id;

        if (! $productId) {
            return response()->json(['message' => 'Produk pesanan tidak ditemukan.'], 422);
        }

        $review = Review::updateOrCreate(
            [
                'order_id' => $order->id,
                'product_id' => $productId,
                'user_id' => $request->user()->id,
            ],
            [
                'customer_name' => $request->user()->name,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'Disetujui',
            ]
        );

        return response()->json([
            'message' => 'Ulasan berhasil disimpan.',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ],
        ], 201);
    }

    private function formatOrder(Order $order): array
    {
        $items = $order->items->map(function ($item): array {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id ?? $item->produk_id,
                'name' => $item->product_name ?: $item->produk_name ?: 'Produk Batik',
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) $item->subtotal,
                'image_url' => MediaUrl::fromProduct($item->product),
            ];
        })->values()->all();

        $statusIndex = $this->resolveStatusIndex((string) $order->status);

        return [
            'id' => $order->id,
            'code' => $order->code,
            'customer_name' => $order->customer_name,
            'phone' => $order->phone,
            'address' => $order->address,
            'status' => $order->status,
            'status_index' => $statusIndex,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'shipping_cost' => (float) ($order->shipping_cost ?? 0),
            'total_price' => (float) ($order->total_price ?? 0),
            'courier' => $order->shipment?->courier ?? $order->shipment?->ekspedisi ?? 'JNE Reguler',
            'tracking_number' => $order->tracking_number ?: $order->shipment?->tracking_number,
            'has_review' => $order->reviews->isNotEmpty(),
            'created_at' => $order->created_at?->toIso8601String(),
            'payment_deadline' => $order->payment_deadline?->toIso8601String(),
            'items' => $items,
        ];
    }

    private function resolveStatusIndex(string $status): int
    {
        $lower = strtolower($status);
        if (str_contains($lower, 'selesai') || str_contains($lower, 'diterima')) {
            return 4;
        }
        if (str_contains($lower, 'kirim')) {
            return 3;
        }
        if (str_contains($lower, 'proses') || str_contains($lower, 'sudah dibayar') || str_contains($lower, 'konfirmasi')) {
            return 2;
        }

        return 1;
    }
}

