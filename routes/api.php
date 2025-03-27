<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', \App\Http\Controllers\Auth\LoginController::class);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', \App\Http\Controllers\Auth\LogoutController::class);
    });
});

Route::middleware(['auth:sanctum'])->prefix('sellers')->group(function () {
    Route::get('/', \App\Http\Controllers\Seller\ListController::class);
    Route::post('/store', \App\Http\Controllers\Seller\StoreController::class);
    Route::get('/{seller}/sales', \App\Http\Controllers\Sales\ListBySeller::class);
});

Route::middleware(['auth:sanctum'])->prefix('sales')->group(function () {
    Route::get('/', \App\Http\Controllers\Sales\ListController::class);
    Route::post('/create', \App\Http\Controllers\Sales\CreateController::class);
});
