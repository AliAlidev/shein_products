<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\PriceRuleController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\WebController;
use Illuminate\Support\Facades\Route;


Route::get('login', [LoginController::class, 'showLoginForm']);

// Route::get('/home', [WebController::class, 'index'])->name('home');

Route::get('home', [DashboardController::class, 'index'])->name('dashboard.index');

// Route::resource('product', ProductController::class);
Route::match(['get','post'], 'price-rules', [PriceRuleController::class, 'index'])->name('price_rules');
Route::post('price-rules-create', [PriceRuleController::class, 'create'])->name('price_rules.create');
Route::get('get-categories', [PriceRuleController::class, 'getCategories'])->name('price_rules.get_categories');
Route::get('get-products', [PriceRuleController::class, 'getProducts'])->name('price_rules.get_products');
Route::match(['get','post'], 'price-rule-edit/{id?}', [PriceRuleController::class, 'update'])->name('price_rules.edit');
Route::post('price-rules-delete/{id?}', [PriceRuleController::class, 'delete'])->name('price_rules.delete');

Route::match(['get','post'], 'users', [UserController::class, 'index'])->name('users');
Route::post('/price-rules/{id}/generate-token', [UserController::class, 'generateToken'])->name('users.generate_token');



Route::match(['get','post'], 'products', [ProductController::class, 'index'])->name('products');
Route::get('product-details/{id}', [ProductController::class, 'getProductDetails'])->name('product.details');
Route::post('create', [ProductController::class, 'create'])->name('product.create');
Route::get('product-view-in-app-status/{id}', [ProductController::class, 'changeViewProductOnAppStatus'])->name('product.view_on_app_status');
Route::post('product-edit/{id?}', [ProductController::class, 'editProduct'])->name('product.edit');
Route::post('product-delete/{id?}', [ProductController::class, 'deleteProduct'])->name('product.delete');
Route::post('export', [ProductController::class, 'export'])->name('products.export');
Route::get('sync', [ProductController::class, 'syncProducts'])->name('products.sync');
Route::get('sync-command', [ProductController::class, 'syncProductsCommand'])->name('products.sync.command');
Route::post('export-current', [ProductController::class, 'exportCurrentPage'])->name('products.current.export');

Auth::routes();
