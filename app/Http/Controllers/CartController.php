<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Produk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the shopping cart page with database items.
     */
    public function index(): View
    {
        $this->consolidateGuestCart();

        $cartItems = CartItem::with('produk')
            ->forCurrentVisitor()
            ->latest()
            ->get();

        $produksRekomendasi = Produk::where('status', '!=', 'Habis')
            ->latest()
            ->take(4)
            ->get();

        return view('keranjang', compact('cartItems', 'produksRekomendasi'));
    }

    /**
     * Add a product to the cart in database.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'qty' => 'nullable|integer|min:1',
            'ukuran' => 'nullable|string|max:50',
            'varian' => 'nullable|string|max:255',
        ]);

        $produk = Produk::findOrFail($validated['produk_id']);
        $qty = $validated['qty'] ?? 1;
        $ukuran = $validated['ukuran'] ?? null;
        $varian = $validated['varian'] ?? null;

        $userId = Auth::id();
        $sessionId = session()->getId();

        // Check if existing item exists with same product & size
        $existing = CartItem::where('produk_id', $produk->id)
            ->where('ukuran', $ukuran)
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId)->orWhere('session_id', $sessionId);
                } else {
                    $q->where('session_id', $sessionId);
                }
            })
            ->first();

        $item = null;
        if ($existing) {
            $newQty = $existing->qty + $qty;
            if ($produk->stok > 0 && $newQty > $produk->stok) {
                $newQty = $produk->stok;
            }
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
                'qty' => min($qty, max(1, $produk->stok)),
                'selected' => true,
            ]);
        }

        $cartCount = CartItem::forCurrentVisitor()->sum('qty');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$produk->nama} berhasil ditambahkan ke keranjang!",
                'cartCount' => $cartCount,
                'item' => [
                    'id' => $item->id,
                    'nama' => $produk->nama,
                    'qty' => $item->qty,
                ],
            ]);
        }

        return redirect()->route('keranjang.index')->with('success', "{$produk->nama} berhasil ditambahkan ke keranjang!");
    }

    /**
     * Update cart item quantity or selection status.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeAccess($cartItem);

        $validated = $request->validate([
            'qty' => 'nullable|integer|min:1',
            'selected' => 'nullable|boolean',
        ]);

        if (isset($validated['qty'])) {
            $maxStock = $cartItem->produk ? $cartItem->produk->stok : 999;
            $qty = min($validated['qty'], max(1, $maxStock));
            $cartItem->qty = $qty;
        }

        if (isset($validated['selected'])) {
            $cartItem->selected = (bool) $validated['selected'];
        }

        $cartItem->save();

        $cartCount = CartItem::forCurrentVisitor()->sum('qty');

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui',
            'qty' => $cartItem->qty,
            'selected' => $cartItem->selected,
            'cartCount' => $cartCount,
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $this->authorizeAccess($cartItem);

        $cartItem->delete();
        $cartCount = CartItem::forCurrentVisitor()->sum('qty');

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus dari keranjang',
                'cartCount' => $cartCount,
            ]);
        }

        return redirect()->route('keranjang.index')->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Get the count of total items in cart.
     */
    public function count(): JsonResponse
    {
        $cartCount = CartItem::forCurrentVisitor()->sum('qty');

        return response()->json(['count' => $cartCount]);
    }

    /**
     * Display the checkout page with selected items from database cart.
     */
    public function checkoutPage(): View|RedirectResponse
    {
        $this->consolidateGuestCart();

        $cartItems = CartItem::with('produk')
            ->forCurrentVisitor()
            ->where('selected', true)
            ->latest()
            ->get();

        // If no selected items, get all cart items or redirect to cart if totally empty
        if ($cartItems->isEmpty()) {
            $allCartItems = CartItem::with('produk')
                ->forCurrentVisitor()
                ->latest()
                ->get();

            if ($allCartItems->isEmpty()) {
                return redirect()->route('keranjang.index')->with('error', 'Keranjang belanja Anda masih kosong.');
            }

            // Mark all as selected if none were selected
            CartItem::forCurrentVisitor()->update(['selected' => true]);
            $cartItems = $allCartItems;
        }

        $user = Auth::user();

        return view('checkout', compact('cartItems', 'user'));
    }

    /**
     * Checkout selected items from cart into orders table.
     */
    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'payment_method' => 'nullable|string|max:100',
            'shipping_option' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $cartItems = CartItem::with('produk')
            ->forCurrentVisitor()
            ->where('selected', true)
            ->get();

        if ($cartItems->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pilih minimal satu produk untuk checkout.'], 422);
            }

            return redirect()->route('keranjang.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $price = $item->produk ? (float) $item->produk->harga : 0;
            $totalPrice += ($price * $item->qty);
        }

        // Apply voucher discount if applicable (e.g. SIRKULAR50K or LESTARI50K: min 200k, hemat 50k)
        $discount = 0;
        $voucherInput = strtoupper((string) $request->input('voucher'));
        if (in_array($voucherInput, ['SIRKULAR50K', 'LESTARI50K', 'LESTARICAPSULE']) && $totalPrice >= 200000) {
            $discount = 50000;
        }

        $shipping = isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : (($totalPrice >= 955000) ? 0 : 20000);
        $serviceFee = 1000;
        $grandTotal = max(0, $totalPrice - $discount + $shipping + $serviceFee);

        $orderCode = 'ORD-'.date('Ymd').'-'.strtoupper(Str::random(4));
        $shippingOption = $validated['shipping_option'] ?? 'JNE Reguler';

        $order = DB::transaction(function () use ($validated, $cartItems, $grandTotal, $orderCode, $shippingOption, $shipping) {
            $notesText = ! empty($validated['notes']) ? ' (Catatan: '.$validated['notes'].')' : '';
            $fullAddress = $validated['address'].$notesText.' [Kurir: '.$shippingOption.']';

            $order = Order::create([
                'user_id' => Auth::id(),
                'code' => $orderCode,
                'customer_name' => $validated['customer_name'],
                'phone' => $validated['phone'],
                'address' => $fullAddress,
                'total_price' => $grandTotal,
                'shipping_cost' => $shipping,
                'payment_method' => $validated['payment_method'] ?? 'Transfer Bank Manual (BCA)',
                'status' => Order::STATUS_UNPAID,
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

                    // Deduct stock if sufficient
                    if ($item->produk->stok >= $item->qty) {
                        $item->produk->decrement('stok', $item->qty);
                    }
                }

                $item->delete();
            }

            return $order;
        });

        // Redirect ke halaman pay Midtrans jika bukan transfer manual & user login
        $paymentMethod = $validated['payment_method'] ?? '';
        $isMidtransMethod = Auth::check() && ! str_contains(strtolower($paymentMethod), 'transfer bank manual');

        if ($isMidtransMethod) {
            $payUrl = route('orders.pay', $order->id);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.',
                    'order_code' => $order->code,
                    'order_id' => $order->id,
                    'redirect_url' => $payUrl,
                ]);
            }

            return redirect($payUrl)->with('success', "Pesanan #{$order->code} berhasil dibuat! Silakan selesaikan pembayaran.");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'order_code' => $order->code,
                'redirect_url' => route('pesanan.index'),
            ]);
        }

        return redirect()->route('pesanan.index')->with('success', "Pesanan #{$order->code} berhasil dibuat!");
    }

    /**
     * Consolidate guest cart items into authenticated user when logged in.
     */
    protected function consolidateGuestCart(): void
    {
        if (Auth::check()) {
            $sessionId = session()->getId();
            CartItem::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update(['user_id' => Auth::id()]);
        }
    }

    /**
     * Ensure current user/session owns the cart item.
     */
    protected function authorizeAccess(CartItem $cartItem): void
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $allowed = false;
        if ($userId && $cartItem->user_id === $userId) {
            $allowed = true;
        } elseif ($cartItem->session_id === $sessionId) {
            $allowed = true;
        }

        if (! $allowed) {
            abort(403, 'Unauthorized cart action.');
        }
    }
}
