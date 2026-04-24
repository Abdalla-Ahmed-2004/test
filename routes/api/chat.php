<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::post('chat/send', [ChatController::class, 'send'])->middleware('throttle:30,1');
    Route::get('chat/session', [ChatController::class, 'getSession']);
});
