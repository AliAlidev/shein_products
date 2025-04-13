<?php

use App\Http\Controllers\Admin\ApiProductController;
use App\Http\Controllers\Admin\Auth\ApiLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::post('login', [ApiLoginController::class, 'login']);
    Route::middleware('auth:api')->get('get-products', [ApiProductController::class, 'list']);
    Route::middleware('auth:api')->get('get-categories', [ApiProductController::class, 'categories']);
});
