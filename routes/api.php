<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClothingItemController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DryCleanController;
use App\Http\Controllers\Api\OutfitController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.')
    ->group(function (): void {
        Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::apiResource('clothing-items', ClothingItemController::class);

            Route::post('clothing-items/{clothing_item}/send-to-dry-clean', [DryCleanController::class, 'sendToDryClean'])
                ->name('clothing-items.send-to-dry-clean');
            Route::post('clothing-items/{clothing_item}/mark-received', [DryCleanController::class, 'markReceived'])
                ->name('clothing-items.mark-received');

            Route::get('dry-clean-logs', [DryCleanController::class, 'index'])
                ->name('dry-clean-logs.index');

            Route::post('outfits/suggest', [OutfitController::class, 'suggest'])
                ->name('outfits.suggest');
            Route::post('outfits/wear', [OutfitController::class, 'wear'])
                ->name('outfits.wear');
            Route::get('outfits/logs', [OutfitController::class, 'logs'])
                ->name('outfits.logs');

            Route::get('dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');
        });
    });
