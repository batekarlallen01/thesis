<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailboxSubmissionController;

Route::get('/test', function () {
    return response()->json(['message' => 'API route is working!']);
});

// Example Laravel default Sanctum user route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 🔹 ESP32 & Mailbox routes (NO CSRF)
Route::post('/mailbox-submission', [MailboxSubmissionController::class, 'store']);

Route::middleware(['esp32.token'])->group(function () {
    Route::post('/verify-pin', [MailboxSubmissionController::class, 'verifyPin']);
});
