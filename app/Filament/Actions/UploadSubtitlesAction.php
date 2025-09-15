<?php

namespace App\Filament\Actions;

use App\Models\Post;
use App\Services\SubtitleUploadService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class UploadSubtitlesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'uploadSubtitles';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Upload Subtitles')
            ->modalHeading(fn ($record) => 'Upload Subtitles for ' . $record->title)
            ->modalWidth('md')
            ->icon('heroicon-o-arrow-up-tray')
            ->form([
                Section::make('Upload Subtitle Files')
                    ->description('Select multiple subtitle files to upload. Supported formats: SRT, VTT, ASS, SSA.')
                    ->schema([
                        FileUpload::make('subtitle_files')
                            ->label('Subtitle Files')
                            ->multiple()
                            ->acceptedFileTypes([
                                'application/x-subrip',
                                'text/plain',
                                'application/vnd.video.vtt',
                                'application/x-subrip',                                                
                            ])
                            ->mimeTypeMap([
                                'srt' => 'application/x-subrip',
                                'vtt' => 'application/vnd.video.vtt',
                                'txt' => 'text/plain',
                                'ass' => 'application/x-subrip',
                                'ssa' => 'application/x-subrip',
                            ])                                            
                            ->disk('public')
                            ->directory('temp-uploads')
                            ->preserveFilenames() // This will keep original filenames
                            ->required()
                            ->maxFiles(10)
                            ->helperText('You can upload up to 10 files at once.'),
                    ])
            ])
            ->action(function (array $data, $record) {
                // If record is a Stream, get the associated Post
                $post = $record instanceof \App\Models\Post ? $record : $record->episode->post;
                return static::handleUpload($data, $post);
            });
    }

    /**
     * Handle the subtitle upload process
     * This method can be called from anywhere with Post data
     */
    public static function handleUpload(array $data, Post $record): void
    {
        // Check and update subtitle_directory if not set
        if (empty($record->subtitle_directory)) {
            $record->subtitle_directory = "subtitles/{$record->id}_{$record->slug}";
            $record->save();
        }
        
        // Create directory if it doesn't exist
        $record->ensureSubtitleDirectoryExists();
        
        // Process uploaded files differently to preserve original names
        $uploadService = app(SubtitleUploadService::class);
        $directory = $uploadService->getUploadDirectory($record);
        $uploadedCount = 0;
        
        foreach ($data['subtitle_files'] as $tempPath) {
            // Get the temp file path
            $tempFile = Storage::disk('public')->path($tempPath);
            
            if (!file_exists($tempFile)) {
                continue; // Skip if file doesn't exist
            }

            // With preserveFilenames(), the temp filename should be the original filename
            $originalName = basename($tempPath);
            
            // Get file extension
            $extension = pathinfo($tempFile, PATHINFO_EXTENSION);
            if (!$extension) {
                // Try to detect from content
                $content = file_get_contents($tempFile, false, null, 0, 1000);
                if (strpos($content, '-->') !== false) {
                    $extension = 'srt';
                } else {
                    $extension = 'txt';
                }
                $originalName = pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;
            }
            
            // Ensure unique filename in destination
            $filename = $originalName;
            $counter = 1;
            while (Storage::disk('public')->exists($directory . '/' . $filename)) {
                $info = pathinfo($originalName);
                $filename = $info['filename'] . "_{$counter}." . $info['extension'];
                $counter++;
            }
            
            // Copy file to destination with original name
            $destinationPath = $directory . '/' . $filename;
            if (Storage::disk('public')->put($destinationPath, file_get_contents($tempFile))) {
                $uploadedCount++;
            }
            
            // Clean up temp file
            Storage::disk('public')->delete($tempPath);
        }
        
        // Show success notification
        if ($uploadedCount > 0) {
            Notification::make()
                ->title('Upload thành công!')
                ->body("Đã upload {$uploadedCount} file với tên gốc được bảo toàn.")
                ->success()
                ->send();
        }
    }

    /**
     * Create action for table columns
     */
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? static::getDefaultName());
    }

    /**
     * Create action for use in forms or other contexts
     */
    public static function forPost(Post $post): static
    {
        return static::make()
            ->modalHeading('Upload Subtitles for ' . $post->title);
    }

    /**
     * Upload subtitles directly without UI (for programmatic use)
     */
    public static function uploadFiles(array $filePaths, Post $post): int
    {
        // Prepare data in the format expected by handleUpload
        $data = ['subtitle_files' => $filePaths];
        
        // Call the main upload handler
        static::handleUpload($data, $post);
        
        // Count uploaded files
        $uploadService = app(SubtitleUploadService::class);
        $files = $uploadService->getDirectoryFiles($post);
        return collect($files)->filter(fn($file) => $file['is_subtitle'])->count();
    }
}
