<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OwnerController;
use App\Http\Controllers\Api\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth');
    Route::get('/store/{slug}', [StorefrontController::class, 'show']);

    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/overview', [AdminController::class, 'overview']);

        Route::get('/shops', [AdminController::class, 'shops']);
        Route::post('/shops', [AdminController::class, 'storeShop']);
        Route::put('/shops/{shop}', [AdminController::class, 'updateShop'])->whereNumber('shop');
        Route::delete('/shops/{shop}', [AdminController::class, 'deleteShop'])->whereNumber('shop');
        Route::get('/shops/{shop}/listings', [AdminController::class, 'shopListings'])->whereNumber('shop');
        Route::post('/shops/{shop}/listings', [AdminController::class, 'addShopListing'])->whereNumber('shop');
        Route::put('/shops/{shop}/listings/{product}', [AdminController::class, 'updateShopListing'])->whereNumber(['shop', 'product']);
        Route::delete('/shops/{shop}/listings/{product}', [AdminController::class, 'removeShopListing'])->whereNumber(['shop', 'product']);

        Route::get('/owners', [AdminController::class, 'owners']);
        Route::post('/owners', [AdminController::class, 'storeOwner']);
        Route::put('/owners/{owner}', [AdminController::class, 'updateOwner'])->whereNumber('owner');
        Route::delete('/owners/{owner}', [AdminController::class, 'deleteOwner'])->whereNumber('owner');

        Route::get('/products', [AdminController::class, 'products']);
        Route::post('/products', [AdminController::class, 'storeProduct']);
        Route::put('/products/{product}', [AdminController::class, 'updateProduct'])->whereNumber('product');
        Route::delete('/products/{product}', [AdminController::class, 'deleteProduct'])->whereNumber('product');
    });

    Route::prefix('owner')->middleware(['auth', 'role:owner'])->group(function () {
        Route::get('/dashboard', [OwnerController::class, 'dashboard']);
        Route::get('/catalog', [OwnerController::class, 'catalog']);
        Route::get('/listings', [OwnerController::class, 'listings']);
        Route::post('/listings', [OwnerController::class, 'addListing']);
        Route::post('/products', [OwnerController::class, 'createProduct']);
        Route::put('/listings/{product}', [OwnerController::class, 'updateListing'])->whereNumber('product');
        Route::delete('/listings/{product}', [OwnerController::class, 'removeListing'])->whereNumber('product');
    });
});

Route::view('/', 'app');
Route::get('/{path}', fn () => view('app'))->where('path', '^(?!api(?:/|$)).*');
