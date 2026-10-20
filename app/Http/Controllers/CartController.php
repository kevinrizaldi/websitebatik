<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService
    ) {}

    /**
     * Display the shopping cart page with database items.
     */
    public function index(): View
    {
        $this->consolidateGuestCart();

        return view('keranjang', [
            'cartItems' => $this->cartService->getVisitorCartItems(),
            'produksRekomendasi' => $this->cartService->getRecommendedProducts(4),
        ]);
    }

    /**
     * Add a product to the cart in database.
     */
    public function store(AddToCartRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->cartService->addToCart(
            productId: (int) $request->validated('produk_id'),
            requestedQty: (int) ($request->validated('qty') ?? 1),
            ukuran: $request->validated('ukuran'),
            varian: $request->validated('varian')
        );

        if (! $result['success']) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }

            return redirect()->back()->with('error', $result['message']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('keranjang.index')->with('success', $result['message']);
    }

    /**
     * Update cart item quantity or selection status.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $result = $this->cartService->updateCartItem(
            cartItem: $cartItem,
            qty: $request->has('qty') ? (int) $request->validated('qty') : null,
            selected: $request->has('selected') ? (bool) $request->validated('selected') : null
        );

        $status = $result['success'] ? 200 : 422;

        return response()->json($result, $status);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $cartCount = $this->cartService->deleteCartItem($cartItem);

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
        return response()->json(['count' => $this->cartService->getCartCount()]);
    }

    /**
     * Display the checkout page with selected items from database cart.
     */
    public function checkoutPage(): View|RedirectResponse
    {
        if (! Auth::check()) {
            session()->put('url.intended', route('checkout.index'));

            return redirect()->route('login')->with('info', 'Silakan masuk atau daftar akun terlebih dahulu untuk melanjutkan checkout.');
        }

        $cartItems = $this->checkoutService->prepareCheckoutItems();

        if ($cartItems->isEmpty()) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        $user = Auth::user();

        return view('checkout', [
            'cartItems' => $cartItems,
            'user' => $user,
            'alamats' => $user->alamats()->latest()->get(),
        ]);
    }

    /**
     * Checkout selected items from cart into orders table.
     */
    public function checkout(CheckoutRequest $request): JsonResponse|RedirectResponse
    {
        if (! Auth::check()) {
            session()->put('url.intended', route('checkout.index'));

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu untuk menyelesaikan transaksi.',
                    'redirect_url' => route('login'),
                ], 401);
            }

            return redirect()->route('login')->with('info', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        $validated = $request->validated();

        $this->checkoutService->syncDefaultAddress(
            user: $user,
            validated: $validated,
            city: $request->input('city'),
            province: $request->input('province'),
            postalCode: $request->input('postal_code')
        );

        $cartItems = $this->cartService->getVisitorSelectedCartItems();

        if ($cartItems->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pilih minimal satu produk untuk checkout.'], 422);
            }

            return redirect()->route('keranjang.index')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $unavailable = $this->checkoutService->validateItemsStock($cartItems);

        if (! empty($unavailable)) {
            $message = 'Stok tidak mencukupi untuk: '.implode(', ', $unavailable).'. Silakan sesuaikan jumlah di keranjang.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('keranjang.index')->with('error', $message);
        }

        $totals = $this->checkoutService->calculateOrderTotals(
            cartItems: $cartItems,
            voucher: $request->input('voucher'),
            shippingCost: isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : null
        );

        $order = $this->checkoutService->createOrder(
            user: $user,
            validated: $validated,
            cartItems: $cartItems,
            grandTotal: $totals['grand_total'],
            shipping: $totals['shipping']
        );

        $snapToken = $this->checkoutService->createSnapToken($order);
        $payUrl = route('pembayaran', ['id' => $order->code]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.',
                'order_code' => $order->code,
                'order_id' => $order->id,
                'snap_token' => $snapToken,
                'client_key' => config('midtrans.client_key'),
                'redirect_url' => $payUrl,
            ]);
        }

        return redirect($payUrl)->with('success', "Pesanan #{$order->code} berhasil dibuat! Silakan selesaikan pembayaran.");
    }

    /**
     * Consolidate guest cart items into authenticated user when logged in.
     */
    protected function consolidateGuestCart(): void
    {
        $this->cartService->consolidateGuestCart();
    }

    /**
     * Ensure current user/session owns the cart item.
     */
    protected function authorizeAccess(CartItem $cartItem): void
    {
        $this->cartService->authorizeAccess($cartItem);
    }
}
