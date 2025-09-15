<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Stream;
use App\Models\StreamSubtitle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubtitleUploadService
{
    public function getUploadDirectory(Post $post): string
    {
        return $post->getSubtitleDirectory();
    }

    public function getDirectoryFiles(Post $post): array
    {
        $directory = $this->getUploadDirectory($post);
        
        if (!Storage::disk('public')->exists($directory)) {
            return [];
        }
        
        $files = Storage::disk('public')->files($directory);
        
        return collect($files)->map(function ($file) {
            $fullPath = Storage::disk('public')->path($file);
            return [
                'name' => basename($file),
                'size' => $this->formatBytes(filesize($fullPath)),
                'modified' => date('Y-m-d H:i:s', filemtime($fullPath)),
                'path' => $file,
                'extension' => pathinfo($file, PATHINFO_EXTENSION),
                'is_subtitle' => $this->isSubtitleFile($file),
            ];
        })->toArray();
    }

    public function uploadFiles(array $files, Post $post): array
    {
        $post->ensureSubtitleDirectoryExists();
        $directory = $this->getUploadDirectory($post);
        $uploadedFiles = [];

        // Allowed extensions for subtitle files
        $allowedExtensions = ['srt', 'vtt', 'ass', 'ssa', 'txt'];

        foreach ($files as $file) {
            if (!($file instanceof UploadedFile)) continue;

            $extension = strtolower($file->getClientOriginalExtension());
            
            // Validate extension only (MIME type can be unreliable for subtitle files)
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception("File '{$file->getClientOriginalName()}' is not a valid subtitle file. Allowed types: srt, vtt, ass, ssa, txt.");
            }

            $filename = $file->getClientOriginalName();

            // Ensure unique filename
            $counter = 1;
            $originalFilename = $filename;
            while (Storage::disk('public')->exists($directory . '/' . $filename)) {
                $info = pathinfo($originalFilename);
                $filename = $info['filename'] . "_{$counter}." . $info['extension'];
                $counter++;
            }

            $path = $file->storeAs($directory, $filename, 'public');
            $uploadedFiles[] = [
                'original_name' => $originalFilename,
                'stored_name' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
            ];
        }

        return $uploadedFiles;
    }

    public function scanAndCreateSubtitles(Post $post, array $streamIds = null): array
    {
        $files = $this->getDirectoryFiles($post);
        $subtitleFiles = collect($files)->filter(fn($file) => $file['is_subtitle']);
        
        if ($subtitleFiles->isEmpty()) {
            return ['created' => 0, 'skipped' => 0, 'errors' => []];
        }

        // Get streams to assign subtitles to
        $streams = $streamIds 
            ? Stream::whereIn('id', $streamIds)->get()
            : Stream::whereHas('episode', fn($q) => $q->where('post_id', $post->id))->get();

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($subtitleFiles as $file) {
            try {
                $language = $this->detectLanguageFromFilename($file['name']);
                $type = strtolower($file['extension']);

                foreach ($streams as $stream) {
                    // Check if subtitle already exists
                    if (StreamSubtitle::where('stream_id', $stream->id)
                        ->where('file_path', $file['path'])
                        ->exists()) {
                        $skipped++;
                        continue;
                    }

                    StreamSubtitle::create([
                        'stream_id' => $stream->id,
                        'language' => $language,
                        'type' => $type,
                        'file_path' => $file['path'],
                        'url' => $file['path'], // Keep backward compatibility
                        'source' => 'manual',
                        'is_active' => true,
                        'is_default' => false,
                        'sort_order' => 0,
                    ]);
                    
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = "File {$file['name']}: " . $e->getMessage();
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    public function deleteFile(Post $post, string $filename): bool
    {
        $directory = $this->getUploadDirectory($post);
        $filePath = $directory . '/' . $filename;

        if (Storage::disk('public')->exists($filePath)) {
            // Also delete related subtitle records
            StreamSubtitle::where('file_path', $filePath)->delete();
            
            return Storage::disk('public')->delete($filePath);
        }

        return false;
    }

    public function renameFile(Post $post, string $oldName, string $newName): bool
    {
        $directory = $this->getUploadDirectory($post);
        $oldPath = $directory . '/' . $oldName;
        $newPath = $directory . '/' . $newName;

        if (Storage::disk('public')->exists($oldPath) && 
            !Storage::disk('public')->exists($newPath)) {
            
            $success = Storage::disk('public')->move($oldPath, $newPath);
            
            if ($success) {
                // Update subtitle records
                StreamSubtitle::where('file_path', $oldPath)
                    ->update(['file_path' => $newPath, 'url' => $newPath]);
            }
            
            return $success;
        }

        return false;
    }

    public function createSubtitleRecord(
        Stream $stream, 
        string $filePath, 
        string $language, 
        string $type,
        array $options = []
    ): StreamSubtitle {
        return StreamSubtitle::create([
            'stream_id' => $stream->id,
            'language' => $language,
            'language_name' => $options['language_name'] ?? $this->getLanguageName($language),
            'type' => $type,
            'file_path' => $filePath,
            'url' => $filePath, // Keep backward compatibility
            'source' => $options['source'] ?? 'manual',
            'is_default' => $options['is_default'] ?? false,
            'is_active' => $options['is_active'] ?? true,
            'sort_order' => $options['sort_order'] ?? 0,
            'meta' => array_merge([
                'original_filename' => basename($filePath),
                'uploaded_at' => now()->toISOString(),
            ], $options['meta'] ?? []),
        ]);
    }

    public function deleteSubtitleFile(StreamSubtitle $subtitle): bool
    {
        if ($this->isLocalFile($subtitle->file_path ?? $subtitle->url)) {
            $filePath = $subtitle->file_path ?? $subtitle->url;
            return Storage::disk('public')->delete($filePath);
        }
        
        return true; // External URLs don't need file deletion
    }

    public function isLocalFile(string $url): bool
    {
        return !str_starts_with($url, 'http');
    }

    private function isSubtitleFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, ['srt', 'vtt', 'ass', 'ssa', 'txt']);
    }

    private function detectLanguageFromFilename(string $filename): string
    {
        $patterns = [
            'vi' => '/[._-](vi|viet|vietnamese)[._-]/i',
            'en' => '/[._-](en|eng|english)[._-]/i',
            'ja' => '/[._-](ja|jp|jap|japanese)[._-]/i',
            'ko' => '/[._-](ko|kor|korean)[._-]/i',
            'zh' => '/[._-](zh|cn|chinese|中文)[._-]/i',
            'th' => '/[._-](th|thai)[._-]/i',
        ];

        foreach ($patterns as $lang => $pattern) {
            if (preg_match($pattern, $filename)) {
                return $lang;
            }
        }

        return 'en'; // default
    }

    private function formatBytes($size, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    private function getLanguageName(string $code): string
    {
        return match($code) {
            'vi' => 'Vietnamese',
            'en' => 'English',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
            'th' => 'Thai',
            'fr' => 'French',
            'de' => 'German',
            'es' => 'Spanish',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            default => ucfirst($code),
        };
    }

    // Legacy methods for backward compatibility
    public function uploadSubtitle($file, Post $post, $type = 'subtitle', $language = 'vi', $name = null)
    {
        $uploaded = $this->uploadFiles([$file], $post);
        return $uploaded[0] ?? null;
    }

    public function batchAssignSubtitle(
        string $filePath, 
        array $streamIds, 
        string $language, 
        string $type,
        array $options = []
    ): array {
        $results = [];
        
        foreach ($streamIds as $streamId) {
            $stream = Stream::find($streamId);
            if ($stream) {
                $results[] = $this->createSubtitleRecord(
                    $stream, 
                    $filePath, 
                    $language, 
                    $type, 
                    $options
                );
            }
        }
        
        return $results;
    }

    public function uploadAndAssignToStreams(
        UploadedFile $file,
        Post $post,
        array $streamIds,
        string $language,
        array $options = []
    ): array {
        // Upload file
        $uploaded = $this->uploadFiles([$file], $post);
        $filePath = $uploaded[0]['path'];
        
        // Detect file type from extension
        $type = strtolower($file->getClientOriginalExtension());
        
        // Assign to streams
        return $this->batchAssignSubtitle($filePath, $streamIds, $language, $type, $options);
    }

    public function uploadSubtitles(Post $post, array $files): int
    {
        // Ensure post has subtitle directory
        $this->ensurePostSubtitleDirectory($post);

        // Convert UploadedFile objects to temporary paths if needed
        $filePaths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                // Store temporarily and add to paths
                $tempPath = $file->store('temp-uploads', 'public');
                $filePaths[] = $tempPath;
            } else {
                // Assume it's already a path
                $filePaths[] = $file;
            }
        }

        return UploadSubtitlesAction::uploadFiles($filePaths, $post);
    }

    /**
     * Get subtitle files count for a post
     */
    public function getSubtitleFilesCount(Post $post): int
    {
        $this->ensurePostSubtitleDirectory($post);
        
        $files = $this->getDirectoryFiles($post);
        return collect($files)->filter(fn($file) => $file['is_subtitle'])->count();
    }

    /**
     * Get all subtitle files for a post
     */
    public function getSubtitleFiles(Post $post): array
    {
        $this->ensurePostSubtitleDirectory($post);
        
        $files = $this->getDirectoryFiles($post);
        return collect($files)->filter(fn($file) => $file['is_subtitle'])->toArray();
    }

    /**
     * Delete a subtitle file by filename
     */
    public function deleteSubtitleFileByName(Post $post, string $filename): bool
    {
        return $this->deleteFile($post, $filename);
    }

    /**
     * Ensure post has subtitle directory set and created
     */
    public function ensurePostSubtitleDirectory(Post $post): void
    {
        // Check and update subtitle_directory if not set
        if (empty($post->subtitle_directory)) {
            $post->subtitle_directory = "subtitles/{$post->id}_{$post->slug}";
            $post->save();
        }
        
        // Create directory if it doesn't exist
        $post->ensureSubtitleDirectoryExists();
    }

    /**
     * Bulk upload subtitles for multiple posts
     */
    public function bulkUploadSubtitles(array $postsAndFiles): array
    {
        $results = [];
        
        foreach ($postsAndFiles as $postId => $files) {
            $post = Post::find($postId);
            if ($post) {
                $uploadedCount = $this->uploadSubtitles($post, $files);
                $results[$postId] = [
                    'post' => $post,
                    'uploaded' => $uploadedCount,
                    'success' => $uploadedCount > 0
                ];
            }
        }
        
        return $results;
    }

    /**
     * Get subtitle directory path for a post
     */
    public function getSubtitleDirectoryPath(Post $post): string
    {
        $this->ensurePostSubtitleDirectory($post);
        return $post->subtitle_directory;
    }
}
