<?php

use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UlasanController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
    });
});
require __DIR__.'/auth.php';
