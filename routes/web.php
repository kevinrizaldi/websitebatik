<?php

use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UlasanController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderPayController;
use App\Http\Controllers\PaymentChangeMethodController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentQrController;
use App\Http\Controllers\PaymentSyncController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Models\Order;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $produks = collect();
    try {
        if (Schema::hasTable('produks')) {
            $produks = Produk::latest()->take(12)->get();
        }
    } catch (Throwable $e) {
        $produks = collect();
    }

    return view('welcome', compact('produks'));
})->name('home');

Route::get('/koleksi', function () {
    $produks = collect();
    try {
        if (Schema::hasTable('produks')) {
            $produks = Produk::latest()->get();
        }
    } catch (Throwable $e) {
        $produks = collect();
    }

    return view('koleksi', compact('produks'));
})->name('koleksi.index');

Route::get('/produk-detail/{id?}', function ($id = null) {
    $produk = null;
    $produksTerkait = collect();
    try {
        if (Schema::hasTable('produks')) {
            if ($id) {
                $produk = Produk::find($id);
            }
            if (! $produk) {
                $produk = Produk::first();
            }
            $produksTerkait = Produk::latest()->take(4)->get();
        }
    } catch (Throwable $e) {
        $produk = null;
        $produksTerkait = collect();
    }

    return view('produk-detail', compact('produk', 'produksTerkait'));
})->name('produk.detail');

Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');
Route::post('/keranjang/tambah', [CartController::class, 'store'])->name('keranjang.store');
Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('keranjang.update');
Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('keranjang.destroy');
Route::get('/keranjang/count', [CartController::class, 'count'])->name('keranjang.count');
Route::post('/keranjang/checkout', [CartController::class, 'checkout'])->name('keranjang.checkout');
Route::get('/checkout', [CartController::class, 'checkoutPage'])->name('checkout.index');
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.store');

Route::get('/pesanan', function () {
    $orders = collect();
    try {
        if (Schema::hasTable('orders')) {
            $query = Order::with('items.produk')->latest();
            if (Auth::check()) {
                $userOrders = (clone $query)->where('user_id', Auth::id())->get();
                $orders = $userOrders->isNotEmpty() ? $userOrders : $query->take(5)->get();
            } else {
                $orders = $query->take(5)->get();
            }
        }
    } catch (Throwable $e) {
        $orders = collect();
    }

    return view('pesanan', compact('orders'));
})->name('pesanan.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Midtrans Payment ─────────────────────────────────────────────────────
    // Snap token endpoint (called by JS after order is created)
    Route::post('/payment/token', [PaymentController::class, 'token'])->name('payment.token');

    // Midtrans finish callback (GET, after user pays)
    Route::get('/payment/finish', [PaymentController::class, 'finish'])->name('payment.finish');

    // Halaman pembayaran per-order
    Route::get('/orders/{order}/pay', [OrderPayController::class, 'show'])->name('orders.pay');

    // Sync status dari Midtrans secara manual
    Route::post('/orders/{order}/payment/sync', [PaymentSyncController::class, 'sync'])->name('orders.payment.sync');

    // Ganti metode pembayaran
    Route::post('/orders/{order}/payment/change-method', [PaymentChangeMethodController::class, 'store'])
        ->name('orders.payment.change-method');

    // QR code proxy untuk QRIS
    Route::get('/orders/{order}/payment/qr', [PaymentQrController::class, 'download'])->name('payment.qr');
});

// Midtrans webhook (tidak perlu auth, diverifikasi via signature)
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook')
    ->withoutMiddleware(['App\Http\Middleware\PreventRequestForgery']);

// Route Khusus Admin: Kelola Produk, Kelola Pesanan, & Kelola Ulasan
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('produk', ProdukController::class);

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        // Kelola Ulasan
        Route::get('/ulasans', [UlasanController::class, 'index'])->name('ulasans.index');
        Route::patch('/ulasans/{ulasan}/status', [UlasanController::class, 'updateStatus'])->name('ulasans.update-status');
        Route::delete('/ulasans/{ulasan}', [UlasanController::class, 'destroy'])->name('ulasans.destroy');
        // Kelola Laporan
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    });
});
require __DIR__.'/auth.php';
