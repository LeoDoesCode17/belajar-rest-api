<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);
Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get('/users/current', [UserController::class, 'get']);
    Route::patch('/users/current', [UserController::class, 'update']);
    Route::delete('/users/logout', [UserController::class, 'logout']);

    Route::post('/contacts', [ContactController::class, 'create']);
    Route::patch('/contacts/{id}', [ContactController::class, 'update']);
    Route::get('/contacts/{id}', [ContactController::class, 'get']);
    Route::delete('/contacts/{id}', [ContactController::class, 'delete']);
    Route::get('/contacts', [ContactController::class, 'search']);
    
    Route::post('/contacts/{contactId}/addresses', [AddressController::class, 'create']);
    Route::put('/contacts/{contactId}/addresses/{addressId}', [AddressController::class, 'update']);
    Route::delete('/contacts/{contactId}/addresses/{addressId}', [AddressController::class, 'delete']);
    Route::get('/contacts/{contactId}/addresses/{addressId}', [AddressController::class, 'show']);
    Route::get('/contacts/{contactId}/addresses', [AddressController::class, 'index']);
});
