<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

Route::prefix('api/v1')->group(function () {
    Route::post('notifications/send', [NotificationController::class, 'send']);
    Route::get('notifications/{id}', [NotificationController::class, 'show']);
    Route::get('notifications/user/{user_id}', [NotificationController::class, 'getUserNotifications']);
});

