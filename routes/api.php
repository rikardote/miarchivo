<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Operator\AuditController;
use App\Http\Controllers\Api\V1\Operator\DashboardController;
use App\Http\Controllers\Api\V1\Operator\DispatchController;
use App\Http\Controllers\Api\V1\Operator\ExpedientController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Auth
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:api-login');

    // Protected Routes (auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth management
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Operator Module
        Route::prefix('operator')->group(function () {
            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index']);

            // Dispatch & Picking
            Route::prefix('dispatch')->group(function () {
                Route::get('/to-extract', [DispatchController::class, 'toExtract']);
                Route::post('/extract', [DispatchController::class, 'extract']);
                Route::post('/extract-bulk', [DispatchController::class, 'extractBulk']);
                Route::get('/to-return', [DispatchController::class, 'toReturn']);
                Route::post('/rearchive', [DispatchController::class, 'rearchive']);
                Route::post('/rearchive-bulk', [DispatchController::class, 'rearchiveBulk']);
            });

            // Physical Shelf / Drawer Audit
            Route::prefix('audit')->group(function () {
                Route::get('/locations', [AuditController::class, 'locations']);
                Route::get('/{locationId}/status', [AuditController::class, 'status']);
                Route::post('/scan', [AuditController::class, 'scan']);
                Route::post('/fix-misplaced', [AuditController::class, 'fixMisplaced']);
                Route::post('/{locationId}/reset', [AuditController::class, 'reset']);
            });

            // Expedients & Scanner
            Route::prefix('expedients')->group(function () {
                Route::get('/', [ExpedientController::class, 'search']);
                Route::get('/lookup/{code}', [ExpedientController::class, 'lookup'])->where('code', '.*');
                Route::post('/{id}/relocate', [ExpedientController::class, 'relocate']);
                Route::post('/{id}/report-lost', [ExpedientController::class, 'reportLost']);
                Route::post('/{id}/report-found', [ExpedientController::class, 'reportFound']);
            });
        });
    });
});
