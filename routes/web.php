<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\admin\PriceRuleController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;


Route::get('login', [LoginController::class, 'showLoginForm']);

// Route::get('home', [DashboardController::class, 'index'])->name('dashboard.index');

Route::middleware('auth')->controller(PriceRuleController::class)->group(function () {
    Route::match(['get', 'post'], 'price-rules', 'index')->name('price_rules');
    Route::post('price-rules-create', 'create')->name('price_rules.create');
    Route::get('get-categories', 'getCategories')->name('price_rules.get_categories');
    Route::get('get-products', 'getProducts')->name('price_rules.get_products');
    Route::match(['get', 'post'], 'price-rule-edit/{id?}', 'update')->name('price_rules.edit');
    Route::post('price-rules-delete/{id?}', 'delete')->name('price_rules.delete');
});

Route::middleware('auth')->controller(UserController::class)->group(function () {
    Route::match(['get', 'post'], 'users', 'index')->name('users');
    Route::post('/price-rules/{id}/generate-token', 'generateToken')->name('users.generate_token');
});

Route::middleware('auth')->controller(ProductController::class)->group(function () {
    Route::get('get-section-types/{channel?}', 'getSectionTypes')->name('products.get_section_types');
    Route::get('get-categories/{channel?}/{section_type?}', 'getCategories')->name('products.categories');
    Route::match(['get', 'post'], 'products', 'index')->name('products');
    Route::get('product-details/{id}', 'getProductDetails')->name('product.details');
    Route::post('create', 'create')->name('product.create');
    Route::get('product-view-in-app-status/{id}', 'changeViewProductOnAppStatus')->name('product.view_on_app_status');
    Route::post('product-edit/{id?}', 'editProduct')->name('product.edit');
    Route::post('product-delete/{id?}', 'deleteProduct')->name('product.delete');
    Route::post('export', 'export')->name('products.export');
    Route::get('sync', 'syncProducts')->name('products.sync');
    Route::get('sync-command', 'syncProductsCommand')->name('products.sync.command');
    Route::post('export-current', 'exportCurrentPage')->name('products.current.export');
});

Route::fallback(function () {
    return redirect()->route('users');
});


Auth::routes();
