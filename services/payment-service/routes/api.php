<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::prefix('api/v1')->group(function () {
    Route::post('payments/process', [PaymentController::class, 'process']);
    Route::post('payments/refund', [PaymentController::class, 'refund']);
    Route::get('payments/{id}', [PaymentController::class, 'show']);
});

