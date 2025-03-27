<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Frontend\WebController;
use Illuminate\Support\Facades\Route;


Route::get('login', [LoginController::class, 'showLoginForm']);

// Route::get('/home', [WebController::class, 'index'])->name('home');

Route::get('home', [DashboardController::class, 'index'])->name('dashboard.index');

// Route::resource('product', ProductController::class);
Route::match(['get','post'], 'products', [ProductController::class, 'index'])->name('products');

Auth::routes();
