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

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [ApiAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [ApiAuthController::class, 'me']);
        Route::post('auth/logout', [ApiAuthController::class, 'logout']);

        Route::middleware('role:super-admin|admin')->group(function (): void {
            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('spareparts', SparepartController::class);
            Route::apiResource('suppliers', SupplierController::class);
            Route::apiResource('customers', CustomerController::class);
        });

        Route::middleware('role:super-admin|admin|purchasing')->group(function (): void {
            Route::get('purchases', [PurchaseController::class, 'index']);
            Route::post('purchases', [PurchaseController::class, 'store']);
            Route::get('purchases/{purchase}', [PurchaseController::class, 'show']);
            Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive']);
        });

        Route::middleware('role:super-admin|admin|kasir')->group(function (): void {
            Route::get('sales', [SaleController::class, 'index']);
            Route::post('sales', [SaleController::class, 'store']);
            Route::get('sales/{sale}', [SaleController::class, 'show']);
        });

        Route::middleware('role:super-admin|admin|gudang')->group(function (): void {
            Route::post('stock-adjustments', [StockAdjustmentController::class, 'store']);
        });

        Route::middleware('permission:manage-users')->group(function (): void {
            Route::apiResource('users', UserController::class);
            Route::get('roles', [RoleController::class, 'index']);
            Route::get('permissions', [PermissionController::class, 'index']);
        });

        Route::middleware('role:super-admin|owner')->group(function (): void {
            Route::get('reports/sales', [ReportController::class, 'sales']);
            Route::get('reports/stock', [ReportController::class, 'stock']);
            Route::get('reports/profit', [ReportController::class, 'profit']);
        });
    });
});
