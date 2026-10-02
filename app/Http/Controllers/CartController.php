<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pengiriman;
use App\Models\Produk;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CartController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

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

        $ukuran = $validated['ukuran'] ?? null;
        $varian = $validated['varian'] ?? null;
        $availableStock = $produk->getStokForUkuran($ukuran);

        // Produk habis atau ukuran terkait habis tidak boleh masuk keranjang.
        if ($availableStock <= 0) {
            $sizeNotice = $ukuran ? " untuk ukuran {$ukuran}" : '';
            $message = "Stok {$produk->nama}{$sizeNotice} sedang habis.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $qty = min($validated['qty'] ?? 1, $availableStock);

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
            $maxStock = $cartItem->produk ? (int) $cartItem->produk->stok : 0;

            if ($maxStock <= 0) {
                $cartItem->delete();
                $cartCount = CartItem::forCurrentVisitor()->sum('qty');

                return response()->json([
                    'success' => false,
                    'removed' => true,
                    'message' => 'Produk sudah habis dan dihapus dari keranjang.',
                    'qty' => 0,
                    'selected' => false,
                    'cartCount' => $cartCount,
                ], 422);
            }

            $cartItem->qty = min($validated['qty'], $maxStock);
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
     * PRD FR-02: Must be logged in to proceed to checkout.
     */
    public function checkoutPage(): View|RedirectResponse
    {
        if (! Auth::check()) {
            session()->put('url.intended', route('checkout.index'));

            return redirect()->route('login')->with('info', 'Silakan masuk atau daftar akun terlebih dahulu untuk melanjutkan checkout.');
        }

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
        $alamats = $user->alamats()->latest()->get();

        return view('checkout', compact('cartItems', 'user', 'alamats'));
    }

    /**
     * Checkout selected items from cart into orders table.
     */
    public function checkout(Request $request): JsonResponse|RedirectResponse
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

        if ($request->has('notes') && trim((string) $request->input('notes')) === '') {
            $request->merge(['notes' => null]);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.()\-]+$/u'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\- ]{10,20}$/'],
            'address' => ['required', 'string', 'max:1000', 'regex:/^[\pL0-9\s.,\/\-()\[\]]+$/u'],
            'payment_method' => 'nullable|string|max:100',
            'shipping_option' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => ['nullable', 'string', 'max:500', 'regex:/^[\pL0-9\s.,\'\-()\/!?]+$/u'],
        ], [
            'customer_name.required' => 'Nama penerima wajib diisi.',
            'customer_name.regex' => 'Nama penerima hanya boleh berupa huruf, spasi, titik, atau tanda petik.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp / HP harus berupa nomor telepon yang valid.',
            'address.required' => 'Alamat pengiriman wajib diisi.',
            'address.regex' => 'Alamat pengiriman tidak boleh mengandung simbol khusus yang tidak valid.',
            'notes.regex' => 'Catatan pesanan mengandung simbol yang tidak diperbolehkan.',
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

        // Tolak item yang stoknya tidak mencukupi (termasuk spesifik ukuran).
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

        if (! empty($unavailable)) {
            $message = 'Stok tidak mencukupi untuk: '.implode(', ', $unavailable).'. Silakan sesuaikan jumlah di keranjang.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('keranjang.index')->with('error', $message);
        }

        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $price = $item->produk ? (float) $item->produk->harga : 0;
            $totalPrice += ($price * $item->qty);
        }

        // Apply voucher discount if applicable
        $discount = 0;
        $voucherInput = strtoupper((string) $request->input('voucher'));
        if (in_array($voucherInput, ['SIRKULAR50K', 'LESTARI50K', 'LESTARICAPSULE']) && $totalPrice >= 200000) {
            $discount = 50000;
        }

        $shipping = isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : (($totalPrice >= 955000) ? 0 : 20000);
        $grandTotal = max(0, $totalPrice - $discount + $shipping);

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

                    // Deduct stock if sufficient
                    if ($item->produk->stok >= $item->qty) {
                        $item->produk->decrementStokForUkuran($item->ukuran, $item->qty);
                    }
                }

                $item->delete();
            }

            // Create initial Pengiriman record
            Pengiriman::create([
                'order_id' => $order->id,
                'ekspedisi' => $shippingOption,
                'status_pengiriman' => 'Menunggu Pengiriman',
            ]);

            return $order;
        });

        // Buat Snap token agar popup bawaan Midtrans bisa langsung terbuka.
        // Gagal membuat token tidak menggagalkan pesanan: customer tetap
        // bisa membayar lewat halaman pembayaran.
        $snapToken = null;
        try {
            $snapData = $this->midtransService->createSnapTransaction($order);
            $snapToken = $snapData['token'] ?? null;
        } catch (Throwable $e) {
            Log::warning('Midtrans Snap token gagal dibuat saat checkout', [
                'order_code' => $order->code,
                'error' => $e->getMessage(),
            ]);
        }

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
