<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('/all', [UserController::class, 'showAllUser']);
        Route::get('/detail/{user}', [UserController::class, 'showDetailUser']);
    });
});

Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::post('/', [UserController::class, 'updateProfile']);
        Route::post('/child', [UserController::class, 'updateChildren']);
        Route::post('/husband', [UserController::class, 'updateHusband']);
    });
});


// public API
Route::post('/login-admin', [AuthController::class, 'loginAdmin']);
Route::post('/logout-admin', [AuthController::class, 'logoutAdmin']);
Route::post('/login-user', [AuthController::class, 'loginUser']);
Route::post('/logout-user', [AuthController::class, 'logoutUser']);
Route::post('/register', [AuthController::class, 'registerUser']);

Route::prefix('profile')->group(function () {
    Route::get('/', [UserController::class, 'showProfile']);
    Route::get('/child', [UserController::class, 'showChildren']);
    Route::get('/husband', [UserController::class, 'showHusband']);
});
