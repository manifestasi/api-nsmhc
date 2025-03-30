<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'abilities:admin'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('/all', [UserController::class, 'showAllUser']);
        Route::get('/{user}/detail', [UserController::class, 'showDetailUser']);
        Route::get('/{user}/security', [AuthController::class, 'showDataSecurityUser']);
        Route::put('/{user}/edit', [UserController::class, 'updateUser']);
        Route::put('/{user}/security', [AuthController::class, 'updateDataSecurityUser']);
        Route::get('/open/summary', [UserController::class, 'showUserOpenSummary']);
    });

    Route::get('/content/progress', [ContentController::class, 'showProgress']);
    Route::get('/reaction', [ReactionController::class, 'showUserReaction']);
    Route::get('/question/answer', [QuestionController::class, 'getDataGrafikQuestionAnswer']);

    Route::get('/progress/summary', [ContentController::class, 'countUserCompletedProgress']);
    Route::get('/reaction/summary', [ReactionController::class, 'showReactionSummary']);
});

Route::middleware(['auth:sanctum', 'abilities:user'])->group(function () {
    Route::prefix('profile')->group(function () {
        Route::post('/', [UserController::class, 'updateProfile']);
        Route::post('/child', [UserController::class, 'updateChildren']);
        Route::post('/husband', [UserController::class, 'updateHusband']);
    });
    Route::post('/content/{content}', [ContentController::class, 'storeProgress']);
    Route::post('/reaction', [ReactionController::class, 'storeReaction']);
    Route::post('/track', [UserController::class, 'trackOpen']);
    Route::post('/question/answer', [QuestionController::class, 'storeQuestionAnswer']);
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
