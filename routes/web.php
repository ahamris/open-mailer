<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\MailClientController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\DocsController;
use App\Http\Controllers\Admin\AiSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);

Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);

    // Mail Client
    Route::get('/mail', [MailClientController::class, 'inbox']);
    Route::get('/mail/compose', [MailClientController::class, 'compose']);
    Route::post('/mail/send', [MailClientController::class, 'send']);
    Route::get('/mail/{id}', [MailClientController::class, 'show']);
    Route::post('/mail/{id}/star', [MailClientController::class, 'toggleStar']);
    Route::post('/mail/{id}/trash', [MailClientController::class, 'moveToTrash']);
    Route::post('/mail/{id}/ai-summarize', [MailClientController::class, 'aiSummarize']);
    Route::post('/mail/ai-compose', [MailClientController::class, 'aiCompose']);

    // Workflows
    Route::get('/workflows', [WorkflowController::class, 'index']);
    Route::get('/workflows/create', [WorkflowController::class, 'create']);
    Route::post('/workflows', [WorkflowController::class, 'store']);
    Route::get('/workflows/{id}/edit', [WorkflowController::class, 'edit']);
    Route::put('/workflows/{id}', [WorkflowController::class, 'update']);
    Route::delete('/workflows/{id}', [WorkflowController::class, 'destroy']);
    Route::post('/workflows/{id}/toggle', [WorkflowController::class, 'toggle']);
    Route::get('/workflows/{id}/logs', [WorkflowController::class, 'logs']);

    // AI Settings
    Route::get('/ai-settings', [AiSettingsController::class, 'index']);
    Route::post('/ai-settings', [AiSettingsController::class, 'store']);
    Route::post('/ai-settings/test', [AiSettingsController::class, 'test']);

    // Beheer
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
    Route::post('/api-keys', [ApiKeyController::class, 'store']);
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy']);
    Route::get('/domains', [DomainController::class, 'index']);
    Route::post('/domains', [DomainController::class, 'store']);
    Route::post('/domains/{id}/verify', [DomainController::class, 'verify']);
    Route::delete('/domains/{id}', [DomainController::class, 'destroy']);

    // Docs
    Route::get('/docs/api', [DocsController::class, 'api']);
    Route::get('/docs/guide', [DocsController::class, 'guide']);
    Route::get('/docs/swagger', [DocsController::class, 'swagger']);
});
