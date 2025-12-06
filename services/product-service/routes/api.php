<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::prefix('api/v1')->group(function () {
    Route::apiResource('products', ProductController::class);
    
    Route::get('products/{id}/images', [ProductController::class, 'getImages']);
    Route::post('products/{id}/images', [ProductController::class, 'uploadImage']);
});

