<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/projects', [ProjectApiController::class, 'index']);
    Route::get('/projects/{project}', [ProjectApiController::class, 'show']);
    Route::post('/projects/{project}/reports', [ProjectApiController::class, 'storeReport']);
});