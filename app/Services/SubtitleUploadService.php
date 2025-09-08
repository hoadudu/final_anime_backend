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
    public function uploadSubtitle($file, Post $post, $type = 'subtitle', $language = 'vi', $name = null)
    {
        // Create directory name from post
        $directoryName = $post->id . '_' . $post->slug;
        
        // Get original filename
        $originalName = $file->getClientOriginalName();
        
        // Get file extension
        $extension = $file->getClientOriginalExtension();
        
        // Generate name part (without extension)
        $namePart = $name ? $name : pathinfo($originalName, PATHINFO_FILENAME);
        
        // Create MD5 hash of current day/month/year
        $dateHash = md5(date('d_m_Y'));
        
        // Create final filename
        $filename = $dateHash . '_' . $namePart . '.' . $extension;
        
        // Create full directory path
        $directoryPath = $directoryName;
        
        // Store the file using subtitle disk
        $filePath = $file->storeAs($directoryPath, $filename, 'subtitles');
        
        return [
            'file_path' => $filePath,
            'directory' => $directoryPath,
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ];
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
            'url' => $filePath, // Lưu local path
            'source' => $options['source'] ?? 'manual',
            'is_default' => $options['is_default'] ?? false,
            'is_active' => $options['is_active'] ?? true,
            'sort_order' => $options['sort_order'] ?? 0,
            'meta' => array_merge([
                'original_filename' => basename($filePath),
                'file_size' => Storage::disk('subtitles')->size($filePath),
                'uploaded_at' => now()->toISOString(),
            ], $options['meta'] ?? []),
        ]);
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
        $filePath = $this->uploadSubtitle($file, $post);
        
        // Detect file type from extension
        $type = strtolower($file->getClientOriginalExtension());
        
        // Assign to streams
        return $this->batchAssignSubtitle($filePath['file_path'], $streamIds, $language, $type, $options);
    }

    public function deleteSubtitleFile(StreamSubtitle $subtitle): bool
    {
        if ($this->isLocalFile($subtitle->url)) {
            return Storage::disk('subtitles')->delete($subtitle->url);
        }
        
        return true; // External URLs don't need file deletion
    }

    public function isLocalFile(string $url): bool
    {
        return !str_starts_with($url, 'http');
    }

    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters and make filename safe
        $filename = Str::slug($filename, '_');
        return Str::limit($filename, 100, '');
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

    public function getUploadDirectory(Post $post): string
    {
        return "subtitles/{$post->id}_{$post->slug}";
    }

    public function getFileInfo(string $filePath): array
    {
        if (!$this->isLocalFile($filePath)) {
            return [
                'exists' => false,
                'size' => 0,
                'type' => 'external',
                'url' => $filePath,
            ];
        }

        $exists = Storage::disk('subtitles')->exists($filePath);
        return [
            'exists' => $exists,
            'size' => $exists ? Storage::disk('subtitles')->size($filePath) : 0,
            'type' => 'local',
            'last_modified' => $exists ? Storage::disk('subtitles')->lastModified($filePath) : null,
            'url' => $filePath,
        ];
    }
}
