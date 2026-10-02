<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\UlasanController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderPayController;
use App\Http\Controllers\PaymentChangeMethodController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentQrController;
use App\Http\Controllers\PaymentSyncController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController as CustomerUlasanController;
use App\Models\Kategori;
use App\Models\Order;
use App\Models\Produk;
use App\Models\Ulasan;
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
    $kategoris = collect();
    try {
        if (Schema::hasTable('produks')) {
            $produks = Produk::latest()->get();
        }
        if (Schema::hasTable('kategoris')) {
            $kategoris = Kategori::withCount('produks')->orderBy('nama_kategori')->get();
        }
    } catch (Throwable $e) {
        $produks = collect();
        $kategoris = collect();
    }

    return view('koleksi', compact('produks', 'kategoris'));
})->name('koleksi.index');

Route::get('/produk-detail/{id?}', function ($id = null) {
    $produk = null;
    $produksTerkait = collect();
    $ulasans = collect();
    $ratingAvg = 0;
    $ratingCount = 0;
    $ratingBars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
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
        if ($produk && Schema::hasTable('ulasans')) {
            $approved = Ulasan::where('produk_id', $produk->id)->where('status', 'Disetujui');
            $ulasans = (clone $approved)->latest()->take(6)->get();
            $ratingCount = (clone $approved)->count();
            $ratingAvg = $ratingCount > 0 ? round((float) (clone $approved)->avg('rating'), 1) : 0;
            foreach ([5, 4, 3, 2, 1] as $star) {
                $ratingBars[$star] = (clone $approved)->where('rating', $star)->count();
            }
        }
    } catch (Throwable $e) {
        $produk = null;
        $produksTerkait = collect();
        $ulasans = collect();
    }

    return view('produk-detail', compact('produk', 'produksTerkait', 'ulasans', 'ratingAvg', 'ratingCount', 'ratingBars'));
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
    $reviewedIds = [];
    $reviewedPairs = [];
    $reviewMap = [];
    try {
        if (Schema::hasTable('orders')) {
            $query = Order::with('items.produk')->latest();
            if (Auth::check()) {
                $userOrders = (clone $query)->where('user_id', Auth::id())->get();
                $orders = $userOrders->isNotEmpty() ? $userOrders : $query->take(5)->get();
                if (Schema::hasTable('ulasans')) {
                    $myReviews = Ulasan::where('user_id', Auth::id())->get(['id', 'order_id', 'produk_id', 'rating', 'comment']);
                    $reviewedIds = $myReviews->pluck('produk_id')->all();
                    $reviewedPairs = $myReviews->whereNotNull('order_id')->groupBy('order_id')
                        ->map(fn ($rows) => $rows->pluck('produk_id')->all())
                        ->all();
                    foreach ($myReviews as $review) {
                        if ($review->order_id) {
                            $reviewMap[$review->order_id][$review->produk_id] = [
                                'id' => $review->id,
                                'rating' => $review->rating,
                                'comment' => $review->comment,
                            ];
                        }
                    }
                }
            } else {
                $orders = $query->take(5)->get();
            }
        }
    } catch (Throwable $e) {
        $orders = collect();
    }

    return view('pesanan', compact('orders', 'reviewedIds', 'reviewedPairs', 'reviewMap'));
})->name('pesanan.index');

Route::get('/pembayaran/{id}', [PaymentController::class, 'show'])->name('pembayaran');
Route::get('/pembayaran/{id}/snap-token', [PaymentController::class, 'getSnapToken'])->name('pembayaran.snap-token');
Route::get('/midtrans/finish', [PaymentController::class, 'finish'])->name('midtrans.finish');

// Midtrans webhook / notification (tidak perlu auth, diverifikasi via signature)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.notification')
    ->withoutMiddleware(['App\Http\Middleware\PreventRequestForgery']);

Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook')
    ->withoutMiddleware(['App\Http\Middleware\PreventRequestForgery']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Midtrans Payment ─────────────────────────────────────────────────────
    // Snap token endpoint (called by JS after order is created)
    Route::post('/payment/token', [PaymentController::class, 'token'])
        ->middleware('throttle:10,1')
        ->name('payment.token');

    // Midtrans finish callback (GET, after user pays)
    Route::get('/payment/finish', [PaymentController::class, 'finish'])->name('payment.finish');

    // Halaman pembayaran per-order
    Route::get('/orders/{order}/pay', [OrderPayController::class, 'show'])->name('orders.pay');

    // Sync status dari Midtrans secara manual
    Route::post('/payment/sync', [PaymentSyncController::class, 'sync'])->name('payment.sync');
    Route::post('/orders/{order}/payment/sync', [PaymentSyncController::class, 'sync'])->name('orders.payment.sync');

    // Ganti metode pembayaran
    Route::post('/payment/change-method', [PaymentChangeMethodController::class, 'change'])->name('payment.change-method');
    Route::post('/orders/{order}/payment/change-method', [PaymentChangeMethodController::class, 'change'])
        ->name('orders.payment.change-method');

    // QR code proxy untuk QRIS
    Route::get('/orders/{order}/payment/qr', [PaymentQrController::class, 'download'])->name('payment.qr');

    // Alamat pelanggan
    Route::get('/alamat', [AlamatController::class, 'index'])->name('alamat.index');
    Route::post('/alamat', [AlamatController::class, 'store'])->name('alamat.store');
    Route::put('/alamat/{alamat}', [AlamatController::class, 'update'])->name('alamat.update');
    Route::patch('/alamat/{alamat}/default', [AlamatController::class, 'setDefault'])->name('alamat.setDefault');
    Route::delete('/alamat/{alamat}', [AlamatController::class, 'destroy'])->name('alamat.destroy');

    // Ulasan pelanggan
    Route::get('/ulasan-saya', [CustomerUlasanController::class, 'index'])->name('ulasan.index');
    Route::post('/ulasan', [CustomerUlasanController::class, 'store'])->name('ulasan.store');
    Route::put('/ulasan/{ulasan}', [CustomerUlasanController::class, 'update'])->name('ulasan.update');
    Route::delete('/ulasan/{ulasan}', [CustomerUlasanController::class, 'destroy'])->name('ulasan.destroy');

    // Konfirmasi / batalkan pesanan oleh pelanggan
    Route::patch('/pesanan/{order}/terima', [PaymentController::class, 'confirmReceived'])->name('pesanan.terima');
    Route::patch('/pesanan/{order}/batal', [PaymentController::class, 'cancelOrder'])->name('pesanan.batal');
});

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
        Route::get('/laporan/export/{format}', [LaporanController::class, 'export'])
            ->whereIn('format', ['pdf', 'excel', 'csv'])
            ->name('laporan.export');
        Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');

        // Kelola Kategori (halaman tunggal + modal, tanpa create/edit/show terpisah)
        Route::resource('kategori', KategoriController::class)->only(['index', 'store', 'update', 'destroy'])->names('kategori');

        // Update Pengiriman
        Route::patch('/orders/{order}/pengiriman', [OrderController::class, 'updatePengiriman'])->name('orders.update-pengiriman');

        // Pengaturan Toko
        Route::get('/pengaturan', [StoreSettingController::class, 'index'])->name('pengaturan.index');
        Route::patch('/pengaturan', [StoreSettingController::class, 'update'])->name('pengaturan.update');
    });
});

require __DIR__.'/auth.php';
