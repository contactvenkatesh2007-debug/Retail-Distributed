<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::prefix('api/v1')->group(function () {
    Route::apiResource('orders', OrderController::class);
    
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{id}/ship', [OrderController::class, 'ship']);
});

