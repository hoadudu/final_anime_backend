<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\SubtitleUploadService;
use App\Filament\Actions\UploadSubtitlesAction;
use Illuminate\Http\Request;

class SubtitleManagementController extends Controller
{
    protected SubtitleUploadService $subtitleManager;

    public function __construct(SubtitleUploadService $subtitleManager)
    {
        $this->subtitleManager = $subtitleManager;
    }

    /**
     * Example 1: Upload subtitles programmatically
     */
    public function uploadSubtitles(Request $request, Post $post)
    {
        $files = $request->file('subtitle_files', []);
        
        try {
            $uploadedCount = $this->subtitleManager->uploadSubtitles($post, $files);
            
            return response()->json([
                'success' => true,
                'message' => "Uploaded {$uploadedCount} subtitle files",
                'uploaded_count' => $uploadedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Example 2: Get subtitle files for a post
     */
    public function getSubtitles(Post $post)
    {
        $files = $this->subtitleManager->getSubtitleFiles($post);
        $count = $this->subtitleManager->getSubtitleFilesCount($post);
        
        return response()->json([
            'post_id' => $post->id,
            'post_title' => $post->title,
            'subtitle_directory' => $this->subtitleManager->getSubtitleDirectoryPath($post),
            'files_count' => $count,
            'files' => $files
        ]);
    }

    /**
     * Example 3: Delete a subtitle file
     */
    public function deleteSubtitle(Request $request, Post $post)
    {
        $filename = $request->input('filename');
        
        $success = $this->subtitleManager->deleteSubtitleFileByName($post, $filename);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'File deleted successfully' : 'Failed to delete file'
        ]);
    }

    /**
     * Example 4: Bulk upload for multiple posts
     */
    public function bulkUpload(Request $request)
    {
        // Expected format: ['post_id' => [files...], ...]
        $postsAndFiles = $request->input('posts_files', []);
        
        $results = $this->subtitleManager->bulkUploadSubtitles($postsAndFiles);
        
        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Example 5: Show upload form (for demonstration)
     */
    public function showUploadForm(Post $post)
    {
        // In a real Filament context, you would use the action like this:
        // $uploadAction = $this->subtitleManager->getUploadAction($post);
        
        return view('subtitle-upload', [
            'post' => $post,
            'subtitle_count' => $this->subtitleManager->getSubtitleFilesCount($post),
            'subtitle_files' => $this->subtitleManager->getSubtitleFiles($post),
        ]);
    }
}

/**
 * Usage examples:
 *
 * 1. In any controller or service:
 *    $subtitleService = app(SubtitleUploadService::class);
 *    $count = $subtitleService->getSubtitleFilesCount($post);
 *
 * 2. Upload files programmatically:
 *    $subtitleService->uploadSubtitles($post, $uploadedFiles);
 *
 * 3. In Filament Resource/Table:
 *    ->action(UploadSubtitlesAction::make())
 *
 * 4. In Livewire component:
 *    public function mount(Post $post)
 *    {
 *        $this->subtitleCount = app(SubtitleUploadService::class)->getSubtitleFilesCount($post);
 *    }
 *
 * 5. In blade template:
 *    @php
 *        $subtitleService = app(App\Services\SubtitleUploadService::class);
 *        $count = $subtitleService->getSubtitleFilesCount($post);
 *    @endphp
 */
