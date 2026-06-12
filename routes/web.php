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

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/admin/dashboard', function () {
    return view('admin');
})->name('admin.dashboard');

Route::get('/user/dashboard', function () {
    return "Ini dashboard user";
})->name('user.dashboard');

