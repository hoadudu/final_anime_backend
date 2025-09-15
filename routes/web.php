<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin subtitle management routes (for logged in users)
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Post subtitle management
    Route::post('posts/{post}/upload-subtitles', [App\Http\Controllers\Admin\PostSubtitleController::class, 'uploadFiles'])
        ->name('admin.posts.upload-subtitles');
    Route::delete('posts/{post}/delete-file', [App\Http\Controllers\Admin\PostSubtitleController::class, 'deleteFile'])
        ->name('admin.posts.delete-file');
    Route::post('posts/{post}/rename-file', [App\Http\Controllers\Admin\PostSubtitleController::class, 'renameFile'])
        ->name('admin.posts.rename-file');
    
    // Stream subtitle management
    Route::post('streams/{stream}/scan-and-create', [App\Http\Controllers\Admin\StreamSubtitleController::class, 'scanAndCreate'])
        ->name('admin.streams.scan-and-create');
    
    // Stream subtitle records management
    Route::delete('stream-subtitles/{streamSubtitle}', [App\Http\Controllers\Admin\StreamSubtitleController::class, 'destroy'])
        ->name('admin.stream-subtitles.destroy');

    // File management page
    Route::get('posts/{post}/subtitle-manager', [App\Http\Controllers\Admin\PostSubtitleController::class, 'managerPage'])
        ->name('admin.subtitle.manage');
    
    // Scan subtitles for all streams in post
    Route::post('posts/{post}/scan-subtitles', [App\Http\Controllers\Admin\PostSubtitleController::class, 'scanSubtitles'])
        ->name('admin.posts.scan-subtitles');
});
