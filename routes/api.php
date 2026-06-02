<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeBannerController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\OrdersController;

Route::get('/products/search', [ProductApiController::class, 'search']);
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/home-banner', [HomeBannerController::class, 'show']);
Route::get('/checkout/payment-methods', [CheckoutController::class, 'paymentMethods']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);

Route::middleware(['optional.sanctum', 'throttle:cart'])->group(function () {
    // Cart routes
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/add/{product}', [CartController::class, 'store']);
    Route::patch('/cart/update/{product}', [CartController::class, 'update']);
    Route::delete('/cart/delete/{product}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
});

Route::middleware(['optional.sanctum', 'throttle:checkout'])->group(function () {
    Route::get('/checkout/econt-offices', [CheckoutController::class, 'econtOffices']);
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::post('/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping']);
});

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth');
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])
    ->middleware('throttle:6,1');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/orders', [OrdersController::class, 'index']);
});
