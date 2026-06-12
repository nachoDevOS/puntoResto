<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

    Route::redirect('/', '/pos');

    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('pos/sales', [PosController::class, 'store'])->name('pos.sales.store');

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');

    Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');

    Route::get('reports/sales', [ReportController::class, 'index'])->name('reports.sales');
});
