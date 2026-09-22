<?php

use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProdukController::class, 'index'])->name('home');

Route::resource('produk', ProdukController::class);
