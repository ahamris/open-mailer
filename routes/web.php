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
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\BroadcastController;
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

    // Templates
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/create', [TemplateController::class, 'create']);
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::get('/templates/{id}/edit', [TemplateController::class, 'edit']);
    Route::put('/templates/{id}', [TemplateController::class, 'update']);
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

    // Contacts & Audiences
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/create', [ContactController::class, 'create']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::get('/contacts/{id}/edit', [ContactController::class, 'edit']);
    Route::put('/contacts/{id}', [ContactController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);
    Route::post('/audiences', [ContactController::class, 'storeAudience']);
    Route::delete('/audiences/{id}', [ContactController::class, 'destroyAudience']);

    // Broadcasts
    Route::get('/broadcasts', [BroadcastController::class, 'index']);
    Route::get('/broadcasts/create', [BroadcastController::class, 'create']);
    Route::post('/broadcasts', [BroadcastController::class, 'store']);
    Route::get('/broadcasts/{id}/edit', [BroadcastController::class, 'edit']);
    Route::put('/broadcasts/{id}', [BroadcastController::class, 'update']);
    Route::post('/broadcasts/{id}/send', [BroadcastController::class, 'send']);
    Route::delete('/broadcasts/{id}', [BroadcastController::class, 'destroy']);

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

    // Settings
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
