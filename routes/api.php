<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReminderController;

Route::get('/reminders', [ReminderController::class, 'index']);
Route::post('/reminders', [ReminderController::class, 'store']);
Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']);
Route::post('/send-fcm-test', [ReminderController::class, 'sendFcmTest']);
Route::post('/register-token', [ReminderController::class, 'registerToken']);
