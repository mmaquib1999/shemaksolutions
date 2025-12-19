<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\Api\AiProviderKeyController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UsageController;
use App\Http\Controllers\AskKingController;
use App\Http\Controllers\KingController;
use App\Http\Controllers\Api\DashboardStatsController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\QuickTriggerController;
use App\Http\Controllers\VerificationCodeController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard-stats', [DashboardStatsController::class, 'show']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Verification code endpoints
    Route::post('/verify-code', [VerificationCodeController::class, 'verify']);
    Route::post('/verify-code/resend', [VerificationCodeController::class, 'resend']);
});

Route::middleware(['auth:sanctum', 'team.role:owner,admin'])->group(function () {
    Route::get('/provider-keys', [AiProviderKeyController::class, 'index']);
    Route::post('/provider-keys', [AiProviderKeyController::class, 'store']);
    Route::put('/provider-keys/{id}', [AiProviderKeyController::class, 'update']);
    Route::put('/provider-keys/{id}/default', [AiProviderKeyController::class, 'setDefault']);
    Route::delete('/provider-keys/{id}', [AiProviderKeyController::class, 'destroy']);

    // Quick Triggers
    Route::get('/quick-triggers', [QuickTriggerController::class, 'index']);
    Route::post('/quick-triggers/categories', [QuickTriggerController::class, 'storeCategory']);
    Route::delete('/quick-triggers/categories/{category}', [QuickTriggerController::class, 'destroyCategory']);
    Route::post('/quick-triggers/categories/{category}/triggers', [QuickTriggerController::class, 'storeTrigger']);
    Route::delete('/quick-triggers/categories/{category}/triggers/{trigger}', [QuickTriggerController::class, 'destroyTrigger']);
    Route::post('/quick-triggers/reset', [QuickTriggerController::class, 'reset']);
});

Route::middleware(['auth:sanctum', 'team.role:owner'])->group(function () {
    Route::get('/account', [AccountController::class, 'show']);
    Route::put('/account', [AccountController::class, 'update']);
    Route::post('/account/delete', [AccountController::class, 'softDelete']);

    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invitations', [TeamController::class, 'invite']);
    Route::delete('/team/members/{member}', [TeamController::class, 'destroy']);
    Route::get('/usage', [UsageController::class, 'index']);

    Route::get('/subscription', [SubscriptionController::class, 'show']);
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::post('/subscription/portal', [SubscriptionController::class, 'portal']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume']);
    Route::get('/subscription/invoices', [SubscriptionController::class, 'invoices']);
    Route::get('/subscription/invoices/export', [SubscriptionController::class, 'exportInvoices']);
});

Route::get('/team/invitations/{token}', [TeamController::class, 'showInvitation']);
Route::post('/team/invitations/accept', [TeamController::class, 'accept']);


Route::middleware('auth:sanctum')->post('/ask-king', [AskKingController::class, 'ask']);
Route::middleware('auth:sanctum')->get('/ask-king/history', [AskKingController::class, 'history']);
Route::middleware('auth:sanctum')->post('/ask-king/history', [AskKingController::class, 'storeHistory']);



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'email_verified_at' => $user->email_verified_at,
        'team_role' => $user->teamRole(),
        'team_owner_id' => $user->teamOwnerId(),
    ];
});
