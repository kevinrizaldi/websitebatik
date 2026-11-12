<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'size' => ['nullable', 'string', 'max:50'],
            'variant' => ['nullable', 'string', 'max:100'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $qty = $validated['quantity'] ?? 1;
        $size = $validated['size'] ?? null;
        $variant = $validated['variant'] ?? null;

        $existing = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('size', $size)
            ->where('variant', $variant)
            ->first();

        if ($existing) {
            $existing->quantity = min(99, $existing->quantity + $qty);
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
            'selected' => ['sometimes', 'boolean'],
        ]);

        $cartItem->fill($validated);
        $cartItem->save();
        $cartItem->load('product');

        return response()->json([
            'message' => 'Keranjang diperbarui.',
            'data' => $this->formatItem($cartItem),
        ]);
    }

    /**
     * Remove a cart item.
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        if ((int) $cartItem->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item dihapus dari keranjang.',
        ]);
    }

    private function formatItem(CartItem $item): array
    {
        $product = $item->product;

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $product?->name ?: $product?->nama ?: 'Produk',
            'size' => $item->size,
            'variant' => $item->variant,
            'quantity' => (int) $item->quantity,
            'selected' => (bool) $item->selected,
            'unit_price' => (float) ($product?->price ?? $product?->harga ?? 0),
            'image_url' => MediaUrl::fromProduct($product),
        ];
    }
}
