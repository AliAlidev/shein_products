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
Route::get('product-details/{id}', [ProductController::class, 'getProductDetails'])->name('product.details');
Route::post('create', [ProductController::class, 'create'])->name('product.create');
Route::get('product-view-in-app-status/{id}', [ProductController::class, 'changeViewProductOnAppStatus'])->name('product.view_on_app_status');
Route::post('product-edit/{id?}', [ProductController::class, 'editProduct'])->name('product.edit');
Route::post('product-delete/{id?}', [ProductController::class, 'deleteProduct'])->name('product.delete');
Route::get('export', [ProductController::class, 'export'])->name('products.export');
Route::get('sync', [ProductController::class, 'syncProducts'])->name('products.sync');
Route::post('export-current', [ProductController::class, 'exportCurrentPage'])->name('products.current.export');

Auth::routes();
