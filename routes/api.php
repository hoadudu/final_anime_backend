<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\SubtitleController;

use App\Http\Controllers\Api\Pages\Home\HomeFrontEndController;
use App\Http\Controllers\Api\CommentReportController;
use App\Http\Controllers\Api\UserAnimeListController;
use App\Http\Controllers\Api\FrontEndDrawerController;


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

// Genres routes (public access)
Route::get('/genres', [GenreController::class, 'index']);

// Collections routes (public access)
Route::get('/home-page/featured-animes', [HomeFrontEndController::class, 'featured_animes']);
Route::get('/home-page/trending-animes', [HomeFrontEndController::class, 'trending_animes']);

// Featured lists routes (public access)
Route::get('/home-page/top-airing', [HomeFrontEndController::class, 'top_airing']);
Route::get('/home-page/most-popular-animes', [HomeFrontEndController::class, 'most_popular_animes']);
Route::get('/home-page/most-liked-animes', [HomeFrontEndController::class, 'most_liked_animes']);
Route::get('/home-page/latest-completed', [HomeFrontEndController::class, 'latest_completed']);
Route::get('/home-page/latest-episode-posts', [HomeFrontEndController::class, 'latest_episode_posts']);





// Frontend drawer routes (public access)
Route::get('/drawer', [FrontEndDrawerController::class, 'index']);


// APIS FOR INDEX PAGE FRONTEND
