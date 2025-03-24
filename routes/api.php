<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {});

Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {});

Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
Route::post('/login-user', [AuthController::class, 'loginUser']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'registerUser']);
