<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refreshToken']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('update-profile', [AuthController::class, 'updateProfile'])->middleware('auth:api');
    Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});


