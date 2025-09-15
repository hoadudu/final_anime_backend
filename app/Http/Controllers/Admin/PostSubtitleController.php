<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\SubtitleUploadService;
use Illuminate\Http\Request;

class PostSubtitleController extends Controller
{
    protected $uploadService;

    public function __construct(SubtitleUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function uploadFiles(Request $request, Post $post)
    {
        try {
            $files = $request->file('files', []);
            $uploaded = $this->uploadService->uploadFiles($files, $post);

            return response()->json([
                'success' => true,
                'uploaded' => $uploaded,
                'message' => count($uploaded) . ' files uploaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteFile(Request $request, Post $post)
    {
        try {
            $filename = $request->input('filename');
            $success = $this->uploadService->deleteFile($post, $filename);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'File deleted successfully' : 'File not found'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function renameFile(Request $request, Post $post)
    {
        try {
            $oldName = $request->input('old_name');
            $newName = $request->input('new_name');
            $success = $this->uploadService->renameFile($post, $oldName, $newName);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'File renamed successfully' : 'Rename failed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function managerPage(Post $post)
    {
        $files = $this->uploadService->getDirectoryFiles($post);
        $subtitleFiles = collect($files)->filter(fn($file) => $file['is_subtitle']);
        
        return view('admin.subtitle-manager', [
            'post' => $post,
            'files' => $files,
            'subtitleFiles' => $subtitleFiles,
            'directory' => $this->uploadService->getUploadDirectory($post),
        ]);
    }

    public function scanSubtitles(Post $post)
    {
        try {
            $result = $this->uploadService->scanAndCreateSubtitles($post);

            return response()->json([
                'success' => true,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
                'message' => "Created {$result['created']} subtitle records for all streams"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
