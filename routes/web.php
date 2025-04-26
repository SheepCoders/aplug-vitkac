<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products/categories', [ProductController::class, 'getCategories'])->name('products.getCategories');
Route::post('/products/send-to-shopify/{productId}', [ProductController::class, 'sendToShopify'])->name('products.sendToShopify');