<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PembayaranController;
use App\Http\Controllers\Admin\PengirimanController;
use App\Http\Controllers\Admin\StokController;
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
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\UlasanController as CustomerUlasanController;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('dashboard');
    }

    $produks = collect();
    $produkTerbaru = collect();
    $produkRating = collect();
    $bannerProduk = collect();
    $kategoris = collect();
    try {
        $approvedReviews = static fn ($query) => $query->where('status', 'Disetujui');

        if (Schema::hasTable('products') || Schema::hasTable('produks')) {
            $produks = Product::query()
                ->where('status', '!=', 'Diarsipkan')
                ->withAvg(['reviews as avg_rating' => $approvedReviews], 'rating')
                ->withCount(['reviews as approved_reviews_count' => $approvedReviews])
                ->latest()
                ->take(12)
                ->get();
        }
        if (Schema::hasTable('products')) {

            $produkTerbaru = Product::query()
                ->where('status', '!=', 'Diarsipkan')
                ->withAvg(['reviews as avg_rating' => $approvedReviews], 'rating')
                ->latest()
                ->take(2)
                ->get();

            $produkRating = Product::query()
                ->where('status', '!=', 'Diarsipkan')
                ->withAvg(['reviews as avg_rating' => $approvedReviews], 'rating')
                ->withCount(['reviews as approved_reviews_count' => $approvedReviews])
                ->whereHas('reviews', $approvedReviews)
                ->orderByDesc('avg_rating')
                ->orderByDesc('approved_reviews_count')
                ->latest()
                ->take(2)
                ->get();

            $bannerProduk = $produkTerbaru
                ->concat($produkRating)
                ->unique('id')
                ->values();

            if ($bannerProduk->count() < 4) {
                $bannerProduk = $bannerProduk
                    ->concat(Product::query()
                        ->withAvg(['reviews as avg_rating' => $approvedReviews], 'rating')
                        ->whereNotIn('id', $bannerProduk->modelKeys())
                        ->latest()
                        ->take(4 - $bannerProduk->count())
                        ->get())
                    ->unique('id')
                    ->values();
            }
        }
        if (Schema::hasTable('categories') || Schema::hasTable('kategoris')) {
            $kategoris = Category::withCount('produks')->orderBy('nama_kategori')->get();
        }
    } catch (Throwable $e) {
        Log::warning('Error loading homepage data: '.$e->getMessage(), ['exception' => $e]);
        $produks = collect();
        $produkTerbaru = collect();
        $produkRating = collect();
        $bannerProduk = collect();
        $kategoris = collect();
    }

    return view('welcome', compact('produks', 'produkTerbaru', 'produkRating', 'bannerProduk', 'kategoris'));
})->name('home');

$catalogHandler = function () {
    $produks = collect();
    $kategoris = collect();
    try {
        if (Schema::hasTable('products') || Schema::hasTable('produks')) {
            $approvedReviews = static fn ($query) => $query->where('status', 'Disetujui');
            $produks = Product::query()
                ->where('status', '!=', 'Diarsipkan')
                ->withAvg(['reviews as avg_rating' => $approvedReviews], 'rating')
                ->withCount(['reviews as approved_reviews_count' => $approvedReviews])
                ->latest()
                ->get();
        }
        if (Schema::hasTable('categories') || Schema::hasTable('kategoris')) {
            $kategoris = Category::withCount('produks')->orderBy('nama_kategori')->get();
        }
    } catch (Throwable $e) {
        Log::warning('Error loading produk catalog: '.$e->getMessage(), ['exception' => $e]);
        $produks = collect();
        $kategoris = collect();
    }

    return view('koleksi', compact('produks', 'kategoris'));
};

Route::get('/koleksi', $catalogHandler)->name('koleksi.index');
Route::get('/produk', fn () => redirect()->route('koleksi.index', request()->query()));

Route::get('/produk-detail/{id?}', function ($id = null) {
    if (! $id) {
        abort(404);
    }

    $produk = Product::find($id);
    if (! $produk) {
        abort(404);
    }

    $produksTerkait = collect();
    $ulasans = collect();
    $ratingAvg = 0;
    $ratingCount = 0;
    $ratingBars = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    try {
        $produksTerkait = Product::where('id', '!=', $produk->id)->latest()->take(4)->get();

        if (Schema::hasTable('reviews') || Schema::hasTable('ulasans')) {
            $approved = Review::where('produk_id', $produk->id)->where('status', 'Disetujui');
            $ulasans = (clone $approved)->latest()->take(6)->get();
            $ratingCount = (clone $approved)->count();
            $ratingAvg = $ratingCount > 0 ? round((float) (clone $approved)->avg('rating'), 1) : 0;
            foreach ([5, 4, 3, 2, 1] as $star) {
                $ratingBars[$star] = (clone $approved)->where('rating', $star)->count();
            }
        }
    } catch (Throwable $e) {
        Log::warning('Error loading product detail relations: '.$e->getMessage(), ['exception' => $e]);
        $produksTerkait = collect();
        $ulasans = collect();
    }

    return view('produk-detail', compact('produk', 'produksTerkait', 'ulasans', 'ratingAvg', 'ratingCount', 'ratingBars'));
})->name('produk.detail');

Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');
Route::post('/keranjang/tambah', [CartController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('keranjang.store');
Route::patch('/keranjang/{cartItem}', [CartController::class, 'update'])->name('keranjang.update');
Route::delete('/keranjang/{cartItem}', [CartController::class, 'destroy'])->name('keranjang.destroy');
Route::get('/keranjang/count', [CartController::class, 'count'])->name('keranjang.count');
Route::post('/keranjang/checkout', [CartController::class, 'checkout'])
    ->middleware('throttle:15,1')
    ->name('keranjang.checkout');
Route::get('/checkout', [CartController::class, 'checkoutPage'])->name('checkout.index');
Route::post('/checkout', [CartController::class, 'checkout'])
    ->middleware('throttle:15,1')
    ->name('checkout.store');
Route::post('/checkout/process', [CartController::class, 'checkout'])
    ->middleware('throttle:15,1')
    ->name('checkout.process');

// RajaOngkir Ongkos Kirim API
Route::prefix('rajaongkir')->name('rajaongkir.')->group(function () {
    Route::get('/provinces', [RajaOngkirController::class, 'provinces'])->name('provinces');
    Route::get('/cities', [RajaOngkirController::class, 'cities'])->name('cities');
    Route::get('/districts', [RajaOngkirController::class, 'districts'])->name('districts');
    Route::get('/postal-codes', [RajaOngkirController::class, 'postalCodes'])->name('postal-codes');
    Route::get('/destinations', [RajaOngkirController::class, 'destinations'])->name('destinations');
    Route::post('/cost', [RajaOngkirController::class, 'calculateCost'])->name('cost');
});

// Geocoding & Reverse Geocoding Proxy (OpenStreetMap Nominatim)
Route::get('/alamat/geocode', [AlamatController::class, 'geocode'])->name('alamat.geocode');
Route::get('/alamat/reverse-geocode', [AlamatController::class, 'reverseGeocode'])->name('alamat.reverse-geocode');

Route::get('/pesanan', function () {
    $orders = collect();
    $reviewedIds = [];
    $reviewedPairs = [];
    $reviewMap = [];
    try {
        if (Schema::hasTable('orders')) {
            if (Auth::check()) {
                $orders = Order::with(['items.produk', 'payments'])
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->get();

                foreach ($orders as $ord) {
                    if ($ord->isPaymentExpired()) {
                        $ord->markAsExpired();
                    }
                }

                $pesananAktif = Order::where('user_id', Auth::id())
                    ->whereIn('status', [
                        'Menunggu Pembayaran', 'Belum Dibayar', 'Menunggu Konfirmasi',
                        'Diproses', 'Sudah Dibayar', 'Menunggu Verifikasi', 'Dikirim', 'sedang dikirim', 'sedang diproses',
                    ])->count();

                if (Schema::hasTable('reviews') || Schema::hasTable('ulasans')) {
                    $myReviews = Review::where('user_id', Auth::id())->get(['id', 'order_id', 'produk_id', 'rating', 'comment']);
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
                $orders = collect();
                $pesananAktif = 0;
            }
        }
    } catch (Throwable $e) {
        $orders = collect();
        $pesananAktif = 0;
    }

    return view('pesanan', compact('orders', 'reviewedIds', 'reviewedPairs', 'reviewMap', 'pesananAktif'));
})->name('pesanan.index');

Route::get('/pembayaran/{id}', [PaymentController::class, 'show'])->name('pembayaran');
Route::get('/pembayaran/{id}/snap-token', [PaymentController::class, 'getSnapToken'])->name('pembayaran.snap-token');
Route::get('/midtrans/finish', [PaymentController::class, 'finish'])->name('midtrans.finish');

// Midtrans webhook / notification (tidak perlu auth, diverifikasi via signature)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.notification');

Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::prefix('admin')->group(function () {
        Route::resource('produk', ProdukController::class);
        Route::patch('/produk/{produk}/archive', [ProdukController::class, 'toggleArchive'])->name('produk.toggle-archive');
        Route::patch('/produk/{produk}/stok', [ProdukController::class, 'updateStock'])->name('produk.update-stock');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/orders/{order}/reject-cancellation', [OrderController::class, 'rejectCancellation'])->name('orders.reject-cancellation');

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

        // Kelola Stok & Inventaris Sirkular
        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');

        // Kelola Pelanggan & Patron
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');

        // Kelola Pembayaran & Mutasi Gateway Midtrans
        Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('/pembayaran/sync', [PembayaranController::class, 'sync'])->name('pembayaran.sync');

        // Kelola Pengiriman & Logistik RajaOngkir
        Route::get('/pengiriman', [PengirimanController::class, 'index'])->name('pengiriman.index');

        // Update Pengiriman & Sync Pembayaran Per-Order & Massal
        Route::patch('/orders/{order}/pengiriman', [OrderController::class, 'updatePengiriman'])->name('orders.update-pengiriman');
        Route::post('/orders/{order}/sync-payment', [OrderController::class, 'syncPayment'])->name('orders.sync-payment');
        Route::post('/orders/sync-payments', [OrderController::class, 'syncAllPayments'])->name('orders.sync-all-payments');

        // Pengaturan Toko
        Route::get('/pengaturan', [StoreSettingController::class, 'index'])->name('pengaturan.index');
        Route::patch('/pengaturan', [StoreSettingController::class, 'update'])->name('pengaturan.update');
    });
});

require __DIR__.'/auth.php';
