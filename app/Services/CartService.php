<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Consolidate guest cart items into authenticated user when logged in.
     */
    public function consolidateGuestCart(): void
    {
        if (Auth::check()) {
            $sessionId = session()->getId();
            CartItem::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update(['user_id' => Auth::id()]);
        }
    }

    /**
     * Get all cart items for the current visitor.
     *
     * @return Collection<int, CartItem>
     */
    public function getVisitorCartItems(): Collection
    {
        return CartItem::with('produk')
            ->forCurrentVisitor()
            ->latest()
            ->get();
    }

    /**
     * Get selected cart items for the current visitor.
     *
     * @return Collection<int, CartItem>
     */
    public function getVisitorSelectedCartItems(): Collection
    {
        return CartItem::with('produk')
            ->forCurrentVisitor()
            ->where('selected', true)
            ->latest()
            ->get();
    }

    /**
     * Get recommended products for the cart view.
     *
     * @return Collection<int, Produk>
     */
    public function getRecommendedProducts(int $limit = 4): Collection
    {
        return Produk::where('status', '!=', 'Habis')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get the count of total items in cart for the current visitor.
     */
    public function getCartCount(): int
    {
        return (int) CartItem::forCurrentVisitor()->sum('qty');
    }

    /**
     * Ensure current visitor owns the specified cart item.
     */
    public function authorizeAccess(CartItem $cartItem): void
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $allowed = ($userId && $cartItem->user_id === $userId) || ($cartItem->session_id === $sessionId);

        if (! $allowed) {
            abort(403, 'Unauthorized cart action.');
        }
    }

    /**
     * Add a product to the cart with size and variant options.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     cartCount?: int,
     *     item?: array{id: int, nama: string, qty: int}
     * }
     */
    public function addToCart(int $productId, int $requestedQty = 1, ?string $ukuran = null, ?string $varian = null): array
    {
        $produk = Produk::findOrFail($productId);
        $availableStock = $produk->getStokForUkuran($ukuran);

        if ($availableStock <= 0) {
            $sizeNotice = $ukuran ? " untuk ukuran {$ukuran}" : '';

            return [
                'success' => false,
                'message' => "Stok {$produk->nama}{$sizeNotice} sedang habis.",
            ];
        }

        $qty = min($requestedQty, $availableStock);
        $userId = Auth::id();
        $sessionId = session()->getId();

        $existing = CartItem::where('produk_id', $produk->id)
            ->where('ukuran', $ukuran)
            ->where(function ($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId)->orWhere('session_id', $sessionId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->first();

        if ($existing) {
            $newQty = min($existing->qty + $qty, $availableStock);
            $existing->update([
                'qty' => $newQty,
                'selected' => true,
            ]);
            $item = $existing;
        } else {
            $item = CartItem::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'produk_id' => $produk->id,
                'ukuran' => $ukuran,
                'varian' => $varian ?: ($ukuran ? 'Ukuran: '.$ukuran : null),
                'qty' => $qty,
                'selected' => true,
            ]);
        }

        return [
            'success' => true,
            'message' => "{$produk->nama} berhasil ditambahkan ke keranjang!",
            'cartCount' => $this->getCartCount(),
            'item' => [
                'id' => $item->id,
                'nama' => $produk->nama,
                'qty' => $item->qty,
            ],
        ];
    }

    /**
     * Update quantity or selected status of a cart item.
     *
     * @return array{
     *     success: bool,
     *     removed?: bool,
     *     message: string,
     *     qty: int,
     *     selected: bool,
     *     cartCount: int
     * }
     */
    public function updateCartItem(CartItem $cartItem, ?int $qty = null, ?bool $selected = null): array
    {
        $this->authorizeAccess($cartItem);

        if ($qty !== null) {
            $maxStock = $cartItem->produk ? (int) $cartItem->produk->stok : 0;

            if ($maxStock <= 0) {
                $cartItem->delete();

                return [
                    'success' => false,
                    'removed' => true,
                    'message' => 'Produk sudah habis dan dihapus dari keranjang.',
                    'qty' => 0,
                    'selected' => false,
                    'cartCount' => $this->getCartCount(),
                ];
            }

            $cartItem->qty = min($qty, $maxStock);
        }

        if ($selected !== null) {
            $cartItem->selected = $selected;
        }

        $cartItem->save();

        return [
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'qty' => $cartItem->qty,
            'selected' => $cartItem->selected,
            'cartCount' => $this->getCartCount(),
        ];
    }

    /**
     * Remove an item from the cart.
     */
    public function deleteCartItem(CartItem $cartItem): int
    {
        $this->authorizeAccess($cartItem);
        $cartItem->delete();

        return $this->getCartCount();
    }
}
