<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Admin\InventoryItemController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
});

Route::get(
    '/products/{product}/availability',
    [ProductController::class, 'availability']
);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

Route::post('/bookings', [BookingController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
        ]);
    });
    Route::get('/my-bookings', [BookingController::class, 'indexMine']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/logout', LogoutController::class);
});
Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get(
            '/inventory-items',
            [InventoryItemController::class, 'index']
        );
        Route::post(
            '/inventory-items',
            [InventoryItemController::class, 'store']
        );
        Route::patch(
            '/inventory-items/{inventoryItem}/status',
            [InventoryItemController::class, 'updateStatus']
        );
        Route::get(
            '/inventory-items/{inventoryItem}/status-history',
            [InventoryItemController::class, 'statusHistory']
        );

        Route::get(
            '/bookings',
            [AdminBookingController::class, 'index']
        );
        Route::get(
            '/bookings/{booking}',
            [AdminBookingController::class, 'show']
        );
        Route::post(
            '/bookings/{booking}/approve',
            [AdminBookingController::class, 'approve']
        );
        Route::post(
            '/bookings/{booking}/reject',
            [AdminBookingController::class, 'reject']
        );
        Route::post(
            '/bookings/{booking}/issue',
            [AdminBookingController::class, 'issue']
        );
        Route::post(
            '/bookings/{booking}/return-items',
            [AdminBookingController::class, 'returnItems']
        );
    });
