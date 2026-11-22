<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{
    /**
     * List the authenticated user's cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $items = CartItem::query()
            ->with('product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (CartItem $item): array => $this->formatItem($item))
            ->values();

        $subtotal = $items
            ->where('selected', true)
            ->sum(fn (array $item): float => $item['unit_price'] * $item['quantity']);

        return response()->json([
            'data' => $items,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * Add a product to the authenticated user's cart.
     *
     * Accepts both the Flutter payload (size/variant/quantity) and the
     * plan-canonical payload (ukuran/qty/variant).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'size' => ['nullable', 'string', 'max:50'],
            'ukuran' => ['nullable', 'string', 'max:50'],
            'variant' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = (int) ($validated['quantity'] ?? $validated['qty'] ?? 1);
        $size = $validated['size'] ?? $validated['ukuran'] ?? null;
        $variant = $validated['variant'] ?? null;

        $existing = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('size', $size)
            ->where('variant', $variant)
            ->first();

        // Same stock rule as web CartService::addToCart / CheckoutService:
        // requested quantity must not exceed available stock for the size.
        $availableStock = $product->getStokForUkuran($size);
        $requestedTotal = $qty + ($existing ? (int) $existing->quantity : 0);

        if ($availableStock <= 0 || $requestedTotal > $availableStock) {
            $sizeNotice = $size ? " (Ukuran {$size})" : '';

            return response()->json([
                'message' => "Stok {$product->name}{$sizeNotice} tidak mencukupi (tersedia: {$availableStock}).",
            ], 422);
        }

        if ($existing) {
            $existing->quantity = $requestedTotal;
            $existing->save();
            $cartItem = $existing;
        } else {
            $cartItem = CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'size' => $size,
                'variant' => $variant,
                'quantity' => $qty,
                'selected' => true,
            ]);
        }

        $cartItem->load('product');

        return response()->json([
            'message' => 'Produk ditambahkan ke keranjang.',
            'data' => $this->formatItem($cartItem),
        ], 201);
    }

    /**
     * Update quantity or selection state of a cart item.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ((int) $cartItem->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'qty' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'selected' => ['sometimes', 'boolean'],
        ]);

        if (! isset($validated['quantity']) && isset($validated['qty'])) {
            $validated['quantity'] = $validated['qty'];
        }
        unset($validated['qty']);

        $cartItem->fill($validated);
        $cartItem->save();
        $cartItem->load('product');

        return response()->json([
            'message' => 'Keranjang diperbarui.',
            'data' => $this->formatItem($cartItem),
        ]);
    }

    /**
     * Remove a cart item. Plan contract: 204 empty (Flutter ignores the body).
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse|Response
    {
        if ((int) $cartItem->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $cartItem->delete();

        return response()->noContent();
    }

    private function formatItem(CartItem $item): array
    {
        $product = $item->product;
        $unitPrice = (float) ($product?->price ?? $product?->harga ?? 0);
        $quantity = (int) $item->quantity;

        $base = [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $product?->name ?: $product?->nama ?: 'Produk',
            'size' => $item->size,
            'variant' => $item->variant,
            'quantity' => $quantity,
            'selected' => (bool) $item->selected,
            'unit_price' => $unitPrice,
            'image_url' => MediaUrl::fromProduct($product),
        ];

        // Plan-canonical keys (dual-shape: coexist with the Flutter keys above).
        return $base + [
            'product' => [
                'id' => $product?->id,
                'name' => $product?->name ?: $product?->nama ?: 'Produk',
                'price' => $unitPrice,
                'image' => MediaUrl::fromProduct($product),
            ],
            'ukuran' => $item->ukuran,
            'qty' => $quantity,
            'subtotal' => $unitPrice * $quantity,
        ];
    }
}
