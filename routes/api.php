<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'blogs'], function () {
    Route::get('/', [\App\Http\Controllers\BlogController::class, 'index']);
    Route::get('/{slug}', [\App\Http\Controllers\BlogController::class, 'show']);
});

Route::get('/banners', [\App\Http\Controllers\BannerController::class, 'index']);

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [\App\Http\Controllers\ProductController::class, 'index']);
    Route::get('/{slug}', [\App\Http\Controllers\ProductController::class, 'show']);
});

Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index']);
