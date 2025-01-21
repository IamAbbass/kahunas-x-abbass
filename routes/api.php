<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;



Route::prefix('v1')->group(function () {
    // Test
    Route::get('/ping', [ApiController::class, 'ping']);

    // Auth
    Route::post('/register', [ApiController::class, 'register']);
    Route::post('/login', [ApiController::class, 'login']);

    // Kahunas api
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/clients', [ApiController::class, 'storeClient']);
        Route::put('/clients/{client}', [ApiController::class, 'updateClient']);
        Route::delete('/clients/{client}', [ApiController::class, 'deleteClient']);

        Route::get('/user-sessions/uncompleted', [ApiController::class, 'uncompletedSessions']);
        Route::post('/user-sessions/{session}/complete', [ApiController::class, 'completeSession']);

        Route::get('/analytics', [ApiController::class, 'analytics']);
    });

});

