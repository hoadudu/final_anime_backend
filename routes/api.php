<?php

use App\Http\Controllers\Api\UserAnimeListController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CommentReportController;
use App\Http\Controllers\Api\SubtitleController;
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

// Subtitle routes (public access)
Route::prefix('subtitles')->name('api.subtitles.')->group(function () {
    Route::get('/{subtitle}/serve', [SubtitleController::class, 'serve'])->name('serve');
    Route::get('/{subtitle}/download', [SubtitleController::class, 'download'])->name('download');
    Route::get('/{subtitle}/stream', [SubtitleController::class, 'stream'])->name('stream');
    Route::get('/{subtitle}/info', [SubtitleController::class, 'info'])->name('info');
});

// Stream subtitle routes (public access)
Route::prefix('streams')->group(function () {
    Route::get('/{stream}/subtitles', [SubtitleController::class, 'getStreamSubtitles']);
    Route::get('/{stream}/subtitles/languages', [SubtitleController::class, 'getLanguages']);
    Route::get('/{stream}/subtitles/default', [SubtitleController::class, 'getDefault']);
});

// Admin subtitle management (authenticated)
Route::middleware('auth:sanctum')->prefix('admin/subtitles')->group(function () {
    Route::post('/{subtitle}/clear-cache', [SubtitleController::class, 'clearCache']);
});
