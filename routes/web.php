<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::post('/categories', [ProductController::class, 'getCategories'])->name('products.getCategories');
Route::post('/send-to-shopify/{productId}', [ProductController::class, 'sendToShopify'])->name('products.sendToShopify');