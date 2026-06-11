<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;

Route::get('/', function () {
    return view('home');
});

Route::get('/product/{slug}', function ($slug) {
    $product = Product::with('items')->where('slug', $slug)->firstOrFail();
    return view('product-detail', compact('product'));
});

Route::get('/tracker', function () {
    return view('tracker');
});

Route::get('/counter', function () {
    return view('demo');
});
