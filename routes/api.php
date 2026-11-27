<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\RajaOngkirController;
use Illuminate\Support\Facades\Route;

// Public endpoints
Route::get('/health', fn (): array => ['ok' => true]);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'google']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/reviews', [ProductController::class, 'reviews']);
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*');

// RajaOngkir region & postal code endpoints for address picker
Route::prefix('rajaongkir')->group(function (): void {
    Route::get('/provinces', [RajaOngkirController::class, 'provinces']);
    Route::get('/cities', [RajaOngkirController::class, 'cities']);
    Route::get('/districts', [RajaOngkirController::class, 'districts']);
    Route::get('/postal-codes', [RajaOngkirController::class, 'postalCodes']);
});

// Geocoding endpoints for address map picker
Route::get('/alamat/geocode', [AlamatController::class, 'geocode']);
Route::get('/alamat/reverse-geocode', [AlamatController::class, 'reverseGeocode']);

// Protected endpoints (require Bearer token from Sanctum)
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/user', [ProfileController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/addresses', [ProfileController::class, 'addresses']);
    Route::post('/addresses', [ProfileController::class, 'storeAddress']);
    Route::match(['put', 'patch'], '/addresses/{address}', [ProfileController::class, 'updateAddress']);
    Route::delete('/addresses/{address}', [ProfileController::class, 'destroyAddress']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::post('/shipping/costs', [ShippingController::class, 'costs']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::get('/orders/{order}/payment', [OrderController::class, 'payment']);
    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/reviews', [OrderController::class, 'submitReview']);
});
