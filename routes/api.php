<?php

use App\Http\Controllers\Api\EmailController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\ApiKeyController;
use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\ApiRateLimit;
use Illuminate\Support\Facades\Route;

// Resend-compatible API
Route::middleware([AuthenticateApiKey::class, ApiRateLimit::class])->group(function () {
    // Emails
    Route::post('/emails', [EmailController::class, 'send']);
    Route::post('/emails/batch', [EmailController::class, 'sendBatch']);
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/emails/{id}', [EmailController::class, 'show']);
    Route::patch('/emails/{id}', [EmailController::class, 'update']);
    Route::delete('/emails/{id}', [EmailController::class, 'destroy']);

    // Domains
    Route::get('/domains', [DomainController::class, 'index']);
    Route::post('/domains', [DomainController::class, 'store']);
    Route::get('/domains/{id}', [DomainController::class, 'show']);
    Route::post('/domains/{id}/verify', [DomainController::class, 'verify']);
    Route::delete('/domains/{id}', [DomainController::class, 'destroy']);

    // API Keys
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy']);
});
