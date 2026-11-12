<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Public endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*');

// Protected endpoints (require Bearer token from Sanctum)
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', [AuthController::class, 'me']);
    Route::patch('/user', [ProfileController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/addresses', [ProfileController::class, 'addresses']);
    Route::post('/addresses', [ProfileController::class, 'storeAddress']);
    Route::patch('/addresses/{address}', [ProfileController::class, 'updateAddress']);
    Route::delete('/addresses/{address}', [ProfileController::class, 'destroyAddress']);

    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/reviews', [OrderController::class, 'submitReview']);
});
