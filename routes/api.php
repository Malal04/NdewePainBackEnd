<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\adresse\AdresseController;


Route::get('/api/documentation', function () {
    return view('l5-swagger::index');
});

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refreshToken']);
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::get('users', [AuthController::class, 'listUsers'])->middleware('auth:api');
    Route::get('users/{id}', [AuthController::class, 'showUser'])->middleware('auth:api');
    Route::post('users/{id}/account-state', [AuthController::class, 'changeAccountState'])->middleware('auth:api');
    Route::post('update-profile', [AuthController::class, 'updateProfile'])->middleware('auth:api');
    Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:api');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('v1/adresses', [AdresseController::class, 'index']);
    Route::get('v1/adresses/principale', [AdresseController::class, 'getAdressePrincipale']);
    Route::get('v1/adresses/mode-livraison', [AdresseController::class, 'getModeLivraisonActuel']);
    Route::get('v1/adresses/user/{id}', [AdresseController::class, 'listByUser']);
    Route::get('v1/adresses/{id}', [AdresseController::class, 'show']);
    Route::post('v1/adresses', [AdresseController::class, 'store']);
    Route::post('v1/adresses/choisir/{id}', [AdresseController::class, 'choisirAdresse']);
    Route::post('v1/adresses/{id}/principale', [AdresseController::class, 'setAsPrincipale']);
    Route::post('v1/adresses/choisir/retrait', [AdresseController::class, 'choisirRetrait']);
    Route::put('v1/adresses/{id}', [AdresseController::class, 'update']);
    Route::delete('v1/adresses/{id}', [AdresseController::class, 'destroy']);
});
