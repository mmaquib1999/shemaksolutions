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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/provider-keys', [AiProviderKeyController::class, 'index']);
    Route::post('/provider-keys', [AiProviderKeyController::class, 'store']);
    Route::put('/provider-keys/{id}', [AiProviderKeyController::class, 'update']);
    Route::put('/provider-keys/{id}/default', [AiProviderKeyController::class, 'setDefault']);
    Route::delete('/provider-keys/{id}', [AiProviderKeyController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/account', [AccountController::class, 'show']);
    Route::put('/account', [AccountController::class, 'update']);

    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invitations', [TeamController::class, 'invite']);
    Route::delete('/team/members/{member}', [TeamController::class, 'destroy']);
    Route::get('/usage', [UsageController::class, 'index']);
});

Route::get('/team/invitations/{token}', [TeamController::class, 'showInvitation']);
Route::post('/team/invitations/accept', [TeamController::class, 'accept']);


Route::middleware('auth:sanctum')->post('/ask-king', [AskKingController::class, 'ask']);



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
