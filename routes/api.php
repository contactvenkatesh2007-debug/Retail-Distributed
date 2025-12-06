<?php

use App\Http\Controllers\ApiGatewayController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Gateway Routes
|--------------------------------------------------------------------------
|
| All microservice requests are routed through the API Gateway
|
*/

Route::prefix('api/v1')->group(function () {
    
    // API Gateway Routes - Product Service
    Route::any('products/{path?}', [ApiGatewayController::class, 'product'])
        ->where('path', '.*')
        ->name('gateway.product');

    // API Gateway Routes - Order Service
    Route::any('orders/{path?}', [ApiGatewayController::class, 'order'])
        ->where('path', '.*')
        ->name('gateway.order');

    // API Gateway Routes - User Service
    Route::any('users/{path?}', [ApiGatewayController::class, 'user'])
        ->where('path', '.*')
        ->name('gateway.user');

    // API Gateway Routes - Payment Service
    Route::any('payments/{path?}', [ApiGatewayController::class, 'payment'])
        ->where('path', '.*')
        ->name('gateway.payment');

    // API Gateway Routes - Inventory Service
    Route::any('inventory/{path?}', [ApiGatewayController::class, 'inventory'])
        ->where('path', '.*')
        ->name('gateway.inventory');

    // API Gateway Routes - Notification Service
    Route::any('notifications/{path?}', [ApiGatewayController::class, 'notification'])
        ->where('path', '.*')
        ->name('gateway.notification');

    // AWS Storage Routes
    Route::prefix('storage')->group(function () {
        Route::post('upload', [StorageController::class, 'upload']);
        Route::get('download/{path}', [StorageController::class, 'download'])
            ->where('path', '.*');
        Route::get('url/{path}', [StorageController::class, 'getUrl'])
            ->where('path', '.*');
        Route::delete('delete/{path}', [StorageController::class, 'delete'])
            ->where('path', '.*');
        Route::get('list', [StorageController::class, 'listFiles']);
    });

    // Health Check
    Route::get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'product' => config('services.microservices.product.base_url'),
                'order' => config('services.microservices.order.base_url'),
                'user' => config('services.microservices.user.base_url'),
                'payment' => config('services.microservices.payment.base_url'),
                'inventory' => config('services.microservices.inventory.base_url'),
                'notification' => config('services.microservices.notification.base_url'),
            ],
        ]);
    })->name('health');
});

