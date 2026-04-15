<?php

use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\KnightApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token', 'throttle:60,1'])->group(function () {

    // Notifications
    Route::get('/notifications/pending',     [NotificationApiController::class, 'pending']);
    Route::post('/notification/{id}/sent',   [NotificationApiController::class, 'markSent']);

    // Knights
    Route::post('/knight',                          [KnightApiController::class, 'store']);
    Route::get('/knight/{discordid}',               [KnightApiController::class, 'show']);
    Route::put('/knight/{discordid}/roles',         [KnightApiController::class, 'syncRoles']);
    Route::get('/knight/{discordid}/profile',       [KnightApiController::class, 'profile']);
    Route::post('/knight/{discordid}/restore',      [KnightApiController::class, 'restore']);
    Route::post('/knight/{discordid}/reactivate',   [KnightApiController::class, 'reactivate']);

});