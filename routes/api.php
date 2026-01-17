<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

// CATEGORY
Route::apiResource('category', CategoryController::class);

// PRODUCT
Route::apiResource('product', ProductController::class);
Route::apiResource('product-reseller', \App\Http\Controllers\Api\ProductResellerController::class);

// BANNER (read-only)
// BANNER (read-only)
Route::get('/banner', [BannerController::class, 'index']);



// BUYER
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\TransactionController;

Route::post('/buyers', [BuyerController::class, 'store']);
Route::get('/buyers/{id}', [BuyerController::class, 'show']);
Route::put('/buyers/{id}', [BuyerController::class, 'update']);
Route::post('/login', [BuyerController::class, 'login']);
Route::post('/forgot-password', [BuyerController::class, 'forgotPassword']);

// TRANSACTION
Route::post('/transactions', [TransactionController::class, 'store']);
Route::get('/transactions/user/{userId}', [TransactionController::class, 'index']);
Route::post('/transactions/{id}/payment', [TransactionController::class, 'uploadProof']);

// REGIONS
use App\Http\Controllers\Api\RegionController;
Route::get('/provinces', [RegionController::class, 'provinces']);
Route::get('/cities', [RegionController::class, 'cities']);
Route::get('/districts', [RegionController::class, 'districts']);
Route::get('/villages', [RegionController::class, 'villages']);
