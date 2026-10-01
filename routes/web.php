<?php

use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\UlasanController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController as CustomerUlasanController;
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
            if (Auth::check()) {
                $orders = Order::with('items.produk')->where('user_id', Auth::id())->latest()->get();
            }
        }
    } catch (Throwable $e) {
        $orders = collect();
    }

    return view('pesanan', compact('orders'));
})->name('pesanan.index');

Route::get('/pembayaran/{id}', [PaymentController::class, 'show'])->name('pembayaran');
Route::get('/pembayaran/{id}/snap-token', [PaymentController::class, 'getSnapToken'])->name('pembayaran.snap-token');
Route::post('/midtrans/notification', [PaymentController::class, 'notification'])->name('midtrans.notification');
Route::get('/midtrans/finish', [PaymentController::class, 'finish'])->name('midtrans.finish');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Alamat pelanggan
    Route::get('/alamat', [AlamatController::class, 'index'])->name('alamat.index');
    Route::post('/alamat', [AlamatController::class, 'store'])->name('alamat.store');
    Route::put('/alamat/{alamat}', [AlamatController::class, 'update'])->name('alamat.update');
    Route::patch('/alamat/{alamat}/default', [AlamatController::class, 'setDefault'])->name('alamat.setDefault');
    Route::delete('/alamat/{alamat}', [AlamatController::class, 'destroy'])->name('alamat.destroy');

    // Ulasan pelanggan
    Route::post('/ulasan', [CustomerUlasanController::class, 'store'])->name('ulasan.store');

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
