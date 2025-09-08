<?php

use App\Http\Controllers\Api\UserAnimeListController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CommentReportController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->prefix('me')->group(function () {
    // User anime list management
    Route::get('/anime-list', [UserAnimeListController::class, 'index']);
    Route::get('/anime-list/stats', [UserAnimeListController::class, 'stats']);
    Route::post('/anime-list/items', [UserAnimeListController::class, 'store']);
    Route::patch('/anime-list/items/{item}', [UserAnimeListController::class, 'update']);
    Route::delete('/anime-list/items/{item}', [UserAnimeListController::class, 'destroy']);
});

// Comments (public và authenticated)
Route::get('/anime/{post}/comments', [CommentController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    // Comment actions
    Route::post('/anime/{post}/comments', [CommentController::class, 'store']);
    Route::post('/comments/{comment}/like', [CommentController::class, 'like']);
    Route::post('/comments/{comment}/dislike', [CommentController::class, 'dislike']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Comment reports
    Route::post('/comments/{comment}/report', [CommentReportController::class, 'store']);
});
