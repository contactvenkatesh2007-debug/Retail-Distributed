<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;

Route::prefix('api/v1')->group(function () {
    Route::post('inventory/check', [InventoryController::class, 'check']);
    Route::post('inventory/deduct', [InventoryController::class, 'deduct']);
    Route::post('inventory/add', [InventoryController::class, 'add']);
    Route::get('inventory/{product_id}', [InventoryController::class, 'show']);
});

