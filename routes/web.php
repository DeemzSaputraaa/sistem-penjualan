<?php

use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\PurchaseController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\SparepartController;
use App\Http\Controllers\Web\StockAdjustmentController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/me', function () {
        return request()->user()?->load('roles', 'permissions');
    });

    Route::middleware('permission:manage-categories')->group(function (): void {
        Route::resource('categories', CategoryController::class)->except('show');
    });

    Route::middleware('permission:manage-spareparts')->group(function (): void {
        Route::resource('spareparts', SparepartController::class)->except('show');
    });

    Route::middleware('permission:manage-suppliers')->group(function (): void {
        Route::resource('suppliers', SupplierController::class)->except('show');
    });

    Route::middleware('permission:manage-customers')->group(function (): void {
        Route::resource('customers', CustomerController::class)->except('show');
    });

    Route::middleware('permission:manage-purchases')->group(function (): void {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    });

    Route::middleware(['permission:manage-sales', 'role:kasir'])->group(function (): void {
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
    });

    Route::middleware('permission:manage-sales')->group(function (): void {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show')->whereNumber('sale');
    });

    Route::middleware('permission:manage-stock')->group(function (): void {
        Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });

    Route::middleware('permission:manage-users')->group(function (): void {
        Route::resource('users', UserController::class)->except('show');
    });

    Route::middleware('permission:view-reports')->group(function (): void {
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    });
});
