<?php

use App\Http\Controllers\Api\RajaOngkirController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Raja Ongkir
    Route::get('rajaongkir/provinsi', [RajaOngkirController::class, 'provinsi']);
    Route::get('rajaongkir/kota/{provinsiId}', [RajaOngkirController::class, 'kota']);
    Route::post('rajaongkir/hitung', [RajaOngkirController::class, 'hitung']);

    // TODO: tambahkan route produk, cart, checkout, order, payment nanti
});