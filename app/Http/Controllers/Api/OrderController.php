<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use App\Services\OrderLifecycleService;
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
        $status = $request->query('status');

        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'shipment', 'reviews'])
            ->when(is_string($status) && $status !== '', fn ($query) => $query->where('status', $status))
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
            // Flutter sends customer_name; the plan canonical body uses recipient_name.
            'customer_name' => ['required_without:recipient_name', 'nullable', 'string', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            // Flutter sends shipping_option; the plan uses courier.
            'shipping_option' => ['nullable', 'string', 'max:100'],
            'courier' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'voucher' => ['nullable', 'string', 'max:50'],
            // Flutter sends notes; the plan uses note.
            'notes' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'cart_item_ids' => ['nullable', 'array'],
            'cart_item_ids.*' => ['integer'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.size' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:99'],
        ]);

        // Normalize plan aliases onto the web CheckoutService field names.
        $validated['customer_name'] = $validated['customer_name'] ?? $validated['recipient_name'] ?? null;
        unset($validated['recipient_name']);
        $validated['shipping_option'] = $validated['shipping_option'] ?? $validated['courier'] ?? null;
        unset($validated['courier']);
        $validated['notes'] = $validated['notes'] ?? $validated['note'] ?? null;
        unset($validated['note']);

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

        $checkoutService->syncDefaultAddress(
            $user,
            $validated,
            $validated['city'] ?? null,
            $validated['province'] ?? null,
            $validated['postal_code'] ?? null,
        );
        $snapToken = $checkoutService->createSnapToken($order);

        $order->load(['items.product', 'shipment', 'reviews', 'pembayaran']);
        $snapRedirectUrl = $this->resolveSnapRedirectUrl($order);

        // Plan-canonical payload: data.order + data.snap_token + data.snap_redirect_url.
        // Flat order keys are kept inside data because checkout_screen.dart reads
        // res['data'] as the order map directly (dual-shape).
        $orderPayload = $this->formatOrder($order);

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'data' => $orderPayload + [
                'order' => $orderPayload,
                'snap_token' => $snapToken,
                'snap_redirect_url' => $snapRedirectUrl,
            ],
            'totals' => $totals,
            'snap_token' => $snapToken,
        ], 201);
    }

    /**
     * Payment/snap info for an order (plan: GET /api/orders/{id}/payment).
     *
     * Mirrors the web pembayaran page: refresh the Midtrans status first,
     * then make sure a snap token exists for payable orders.
     */
    public function payment(Request $request, Order $order, MidtransService $midtrans): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        try {
            $midtrans->refreshFromMidtrans($order);
        } catch (\Throwable) {
            // Gateway unreachable — fall back to the stored payment record.
        }

        $order->refresh();

        $payable = in_array($order->status, ['Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi'], true);

        if (empty($order->pembayaran?->snap_token) && $payable) {
            try {
                $midtrans->createSnapTransaction($order);
            } catch (\Throwable) {
                // Ignore — respond with whatever is stored.
            }
            $order->refresh();
        }

        $snapToken = $order->pembayaran?->snap_token ?? null;
        $snapRedirectUrl = $order->pembayaran?->snap_redirect_url ?? null;

        if (empty($snapRedirectUrl) && ! empty($snapToken)) {
            $snapRedirectUrl = $this->deriveRedirectFromToken($order, (string) $snapToken);
        }

        return response()->json([
            'data' => [
                'payment_status' => $order->payment_status,
                'snap_token' => $snapToken,
                'snap_redirect_url' => $snapRedirectUrl,
            ],
        ]);
    }

    /**
     * Customer confirms received goods (order → Selesai).
     */
    public function confirm(Request $request, Order $order, OrderLifecycleService $lifecycle): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        if (! $lifecycle->confirmReceived($order)) {
            return response()->json([
                'message' => 'Pesanan tidak dapat dikonfirmasi pada status saat ini.',
            ], 422);
        }

        $order->load(['items.product', 'shipment', 'reviews']);

        return response()->json([
            'message' => 'Pesanan telah dikonfirmasi diterima.',
            'data' => $this->formatOrder($order),
        ]);
    }

    /**
     * Customer cancels an unpaid order directly, or submits a cancellation
     * request for a paid-but-not-yet-shipped order (admin must approve).
     */
    public function cancel(Request $request, Order $order, OrderLifecycleService $lifecycle): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        // Belum dibayar: batalkan langsung oleh customer (stok dikembalikan).
        if ($lifecycle->cancelUnpaid($order)) {
            $order->load(['items.product', 'shipment', 'reviews']);

            return response()->json([
                'message' => 'Pesanan berhasil dibatalkan.',
                'data' => $this->formatOrder($order),
            ]);
        }

        // Sudah diajukan sebelumnya: tunggu keputusan admin.
        if ($order->cancel_requested_at !== null) {
            return response()->json([
                'message' => 'Permintaan pembatalan sudah diajukan. Menunggu persetujuan admin.',
            ], 422);
        }

        // Sudah dibayar, belum dikirim: ajukan pembatalan ke admin.
        if ($lifecycle->requestCancellation($order)) {
            $order->load(['items.product', 'shipment', 'reviews']);

            return response()->json([
                'message' => 'Permintaan pembatalan berhasil diajukan. Menunggu persetujuan admin.',
                'data' => $this->formatOrder($order),
            ]);
        }

        return response()->json([
            'message' => 'Pesanan tidak dapat dibatalkan pada status ini.',
        ], 422);
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

        // Plan gate: reviews are only allowed on Selesai orders
        // (same rule as UlasanController::store).
        if ($order->status !== 'Selesai') {
            return response()->json([
                'message' => 'Ulasan hanya dapat diberikan setelah Anda menyelesaikan pembelian produk ini (pesanan berstatus Selesai).',
            ], 422);
        }

        if (! $order->items->contains(fn ($item): bool => (int) ($item->product_id ?? $item->produk_id) === (int) $productId)) {
            return response()->json(['message' => 'Produk tersebut tidak ada dalam pesanan ini.'], 422);
        }

        // One review per order × product (plan: belum pernah review).
        $alreadyReviewed = Review::query()
            ->where('order_id', $order->id)
            ->where('product_id', $productId)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'message' => 'Anda sudah memberi ulasan untuk produk ini pada pesanan #'.$order->code.'.',
            ], 422);
        }

        $review = Review::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'user_id' => $request->user()->id,
            'customer_name' => $request->user()->name,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'Disetujui',
        ]);

        return response()->json([
            'message' => 'Ulasan berhasil disimpan.',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ],
        ], 201);
    }

    /**
     * Resolve the Snap redirect URL for an order, deriving it from the
     * token when Midtrans never returned one (mock/production fallback).
     */
    private function resolveSnapRedirectUrl(Order $order): ?string
    {
        $redirectUrl = $order->pembayaran?->snap_redirect_url;

        if (! empty($redirectUrl)) {
            return $redirectUrl;
        }

        $snapToken = $order->pembayaran?->snap_token;

        return $snapToken ? $this->deriveRedirectFromToken($order, (string) $snapToken) : null;
    }

    /**
     * Build the Snap redirect URL from a bare token:
     * mock tokens finish on the sandbox simulation route, real tokens on
     * the standard Snap transactions/{token}/redirect endpoint.
     */
    private function deriveRedirectFromToken(Order $order, string $snapToken): string
    {
        if (str_starts_with($snapToken, 'MOCK_SNAP_')) {
            return route('midtrans.finish', ['order_id' => $order->code, 'mock' => 1]);
        }

        $base = config('midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        return $base.'/snap/v1/transactions/'.$snapToken.'/redirect';
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
            'cancel_requested' => $order->cancel_requested_at !== null,
            'created_at' => $order->created_at?->toIso8601String(),
            'payment_deadline' => $order->payment_deadline?->toIso8601String(),
            'items' => $items,
            // Plan-canonical payment block: {payment: {status}} on order detail.
            'payment' => [
                'status' => $order->payment_status,
            ],
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
