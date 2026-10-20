<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkerJobController;
use App\Http\Controllers\Api\WorkerLedgerController;
use App\Http\Controllers\Api\WorkerNotificationController;
use App\Http\Controllers\Api\WorkerProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Worker REST API Routes (Direct Mobile Sync & Versioned)
|--------------------------------------------------------------------------
*/

// Group definition for both root /api and /api/v1 prefixes
$workerApiRoutes = function () {
    // Health Check & Server Ping
    Route::get('/ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'Serwizo Backend Online',
            'server_time' => now()->toIso8601String(),
        ]);
    });

    // Public Worker Login & Reference Data
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/brands', [\App\Http\Controllers\Api\BrandApiController::class, 'getBrands']);
    Route::get('/reference-data', [\App\Http\Controllers\Api\BrandApiController::class, 'getReferenceData']);

    // Public Serwizo Website Live Catalog & Cities API
    Route::get('/catalog', [\App\Http\Controllers\Api\CatalogApiController::class, 'getCatalog']);
    Route::get('/categories', [\App\Http\Controllers\Api\CatalogApiController::class, 'getCatalog']);
    Route::get('/cities', [\App\Http\Controllers\Api\CatalogApiController::class, 'getCities']);

    // Authenticated Worker Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Profile & Availability
        Route::get('/worker/profile', [WorkerProfileController::class, 'profile']);
        Route::put('/worker/availability', [WorkerProfileController::class, 'updateAvailability']);

        // Customer Quick Lookup
        Route::get('/customers/lookup', [\App\Http\Controllers\Api\BrandApiController::class, 'lookupCustomer']);

        // Jobs & Dispatch Workflow
        Route::get('/worker/jobs', [WorkerJobController::class, 'index']);
        Route::get('/jobs', [WorkerJobController::class, 'index']);
        Route::post('/worker/jobs/initiate', [WorkerJobController::class, 'initiate']);
        Route::post('/jobs/initiate', [WorkerJobController::class, 'initiate']);
        Route::get('/worker/jobs/{id}', [WorkerJobController::class, 'show']);
        Route::get('/jobs/{id}', [WorkerJobController::class, 'show']);
        Route::post('/jobs/{id}/accept', [WorkerJobController::class, 'accept']);
        Route::post('/worker/jobs/{id}/accept', [WorkerJobController::class, 'accept']);
        Route::post('/jobs/{id}/reject', [WorkerJobController::class, 'reject']);
        Route::post('/worker/jobs/{id}/reject', [WorkerJobController::class, 'reject']);
        Route::post('/jobs/{id}/start', [WorkerJobController::class, 'start']);
        Route::post('/worker/jobs/{id}/start', [WorkerJobController::class, 'start']);
        Route::post('/jobs/{id}/status', [WorkerJobController::class, 'updateProgressStatus']);
        Route::post('/worker/jobs/{id}/status', [WorkerJobController::class, 'updateProgressStatus']);
        Route::post('/jobs/{id}/proof', [WorkerJobController::class, 'uploadProof']);
        Route::post('/worker/jobs/{id}/proof', [WorkerJobController::class, 'uploadProof']);
        Route::post('/jobs/{id}/complete', [WorkerJobController::class, 'completeJob']);
        Route::post('/worker/jobs/{id}/complete', [WorkerJobController::class, 'completeJob']);

        // Financial Ledger
        Route::get('/worker/ledger', [WorkerLedgerController::class, 'ledger']);

        // Notifications
        Route::get('/worker/notifications', [WorkerNotificationController::class, 'index']);
        Route::post('/worker/notifications/{id}/read', [WorkerNotificationController::class, 'markAsRead']);
    });
};

// Register for /api/* directly (matching Android app requests directly)
$workerApiRoutes();

// Register for /api/v1/* (versioned support)
Route::prefix('v1')->group($workerApiRoutes);
