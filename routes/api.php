<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SparepartController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::post('auth/login', [ApiAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [ApiAuthController::class, 'me']);
        Route::post('auth/logout', [ApiAuthController::class, 'logout']);

        Route::middleware('permission:manage-categories')->group(function (): void {
            Route::apiResource('categories', CategoryController::class);
        });

        Route::middleware('permission:manage-spareparts')->group(function (): void {
            Route::apiResource('spareparts', SparepartController::class);
        });

        Route::middleware('permission:manage-suppliers')->group(function (): void {
            Route::apiResource('suppliers', SupplierController::class);
        });

        Route::middleware('permission:manage-customers')->group(function (): void {
            Route::apiResource('customers', CustomerController::class);
        });

        Route::middleware(['permission:manage-purchases', 'role:purchasing|super-admin'])->group(function (): void {
            Route::post('purchases', [PurchaseController::class, 'store']);
            Route::post('purchases/{purchase}/order', [PurchaseController::class, 'order']);
            Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive']);
        });

        Route::middleware('permission:manage-purchases')->group(function (): void {
            Route::get('purchases', [PurchaseController::class, 'index']);
            Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->whereNumber('purchase');
        });

        Route::middleware('permission:manage-sales')->group(function (): void {
            Route::get('sales', [SaleController::class, 'index']);
            Route::get('sales/{sale}', [SaleController::class, 'show'])->whereNumber('sale');
        });

        Route::middleware(['permission:manage-sales', 'role:kasir'])->group(function (): void {
            Route::post('sales', [SaleController::class, 'store']);
        });

        Route::middleware('permission:manage-stock')->group(function (): void {
            Route::post('stock-adjustments', [StockAdjustmentController::class, 'store']);
        });

        Route::middleware('permission:manage-users')->group(function (): void {
            Route::apiResource('users', UserController::class);
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('permissions', [PermissionController::class, 'index']);
        });

        Route::middleware('permission:view-reports')->group(function (): void {
            Route::get('reports/sales', [ReportController::class, 'sales']);
            Route::get('reports/stock', [ReportController::class, 'stock']);
            Route::get('reports/profit', [ReportController::class, 'profit']);
        });
    });
});
