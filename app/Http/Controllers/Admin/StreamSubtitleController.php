<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stream;
use App\Models\StreamSubtitle;
use App\Services\SubtitleUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StreamSubtitleController extends Controller
{
    protected $uploadService;

    public function __construct(SubtitleUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function scanAndCreate(Stream $stream)
    {
        try {
            $result = $this->uploadService->scanAndCreateSubtitles(
                $stream->episode->post, 
                [$stream->id]
            );

            return response()->json([
                'success' => true,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
                'message' => "Created {$result['created']} subtitle records"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(StreamSubtitle $streamSubtitle)
    {
        try {
            // Delete the file if it's local
            $this->uploadService->deleteSubtitleFile($streamSubtitle);
            
            // Delete the record
            $streamSubtitle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subtitle deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a specific subtitle file to a stream
     */
    public function assignSubtitle(Request $request, Stream $stream)
    {
        $request->validate([
            'filename' => 'required|string',
            'language' => 'string|nullable',
            'is_default' => 'boolean'
        ]);

        try {
            $post = $stream->episode->post;
            $directory = $this->uploadService->getSubtitleDirectoryPath($post);
            $filePath = $directory . '/' . $request->filename;
            
            // Check if file exists
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subtitle file not found'
                ], 404);
            }

            // Detect language and type from filename if not provided
            $language = $request->language ?? $this->detectLanguage($request->filename);
            $type = $this->detectType($request->filename);

            // Check if already assigned
            $existing = $stream->subtitles()->where('file_path', $filePath)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subtitle already assigned to this stream'
                ], 400);
            }

            // Create subtitle record
            $subtitle = StreamSubtitle::create([
                'stream_id' => $stream->id,
                'language' => $language,
                'type' => $type,
                'file_path' => $filePath,
                'is_default' => $request->is_default ?? false,
                'is_active' => true,
                'sort_order' => $stream->subtitles()->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subtitle assigned successfully',
                'subtitle' => $subtitle
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-assign all subtitle files to a stream
     */
    public function scanAndAssignAll(Request $request, Stream $stream)
    {
        try {
            $post = $stream->episode->post;
            $files = $this->uploadService->getDirectoryFiles($post);
            $subtitleFiles = collect($files)->filter(fn($file) => $file['is_subtitle']);

            $assigned = 0;
            $skipped = 0;
            $errors = [];

            foreach ($subtitleFiles as $file) {
                try {
                    // Check if already assigned
                    $filePath = $file['path'];
                    $existing = $stream->subtitles()->where('file_path', $filePath)->first();
                    
                    if ($existing) {
                        $skipped++;
                        continue;
                    }

                    // Detect language and type
                    $language = $this->detectLanguage($file['name']);
                    $type = $this->detectType($file['name']);

                    // Create subtitle record
                    StreamSubtitle::create([
                        'stream_id' => $stream->id,
                        'language' => $language,
                        'type' => $type,
                        'file_path' => $filePath,
                        'is_default' => $assigned === 0, // First subtitle is default
                        'is_active' => true,
                        'sort_order' => $assigned
                    ]);

                    $assigned++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to assign {$file['name']}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Assigned {$assigned} subtitles, skipped {$skipped}",
                'assigned' => $assigned,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect language from filename
     */
    private function detectLanguage(string $filename): string
    {
        $filename = strtolower($filename);
        
        if (strpos($filename, 'vie') !== false || strpos($filename, '.vi.') !== false) {
            return 'vi';
        } elseif (strpos($filename, 'eng') !== false || strpos($filename, '.en.') !== false) {
            return 'en';
        } elseif (strpos($filename, 'jap') !== false || strpos($filename, '.ja.') !== false) {
            return 'ja';
        } elseif (strpos($filename, 'kor') !== false || strpos($filename, '.ko.') !== false) {
            return 'ko';
        } elseif (strpos($filename, 'chi') !== false || strpos($filename, '.zh.') !== false) {
            return 'zh';
        }
        
        return 'vi'; // Default to Vietnamese
    }

    /**
     * Detect subtitle type from file extension
     */
    private function detectType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match($extension) {
            'srt' => 'srt',
            'vtt' => 'vtt',
            'ass' => 'ass',
            'ssa' => 'ssa',
            default => 'srt'
        };
    }
}
