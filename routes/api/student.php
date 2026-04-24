<?php

use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\StudentAnswerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::post('attempts/{attempt}/answer', [QuizAttemptController::class, 'answer'])->middleware(['role:student']);
    Route::post('quiz/{quiz}/answer', [StudentAnswerController::class, 'answer'])->middleware(['role:student']);
    Route::get('students/attempts', [QuizAttemptController::class, 'index'])->middleware(['role:student']);
});
