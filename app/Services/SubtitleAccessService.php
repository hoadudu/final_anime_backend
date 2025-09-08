<?php

namespace App\Services;

use App\Models\StreamSubtitle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubtitleAccessService
{
    public function getSubtitleUrl(StreamSubtitle $subtitle, int $ttl = 3600): string
    {
        // Check if it's already a public URL
        if (str_starts_with($subtitle->url, 'http')) {
            return $subtitle->url;
        }

        // For local files, we'll use our serve endpoint instead of temporary URL
        // because temporary URLs might not work properly with video players
        return route('api.subtitles.serve', $subtitle->id);
    }

    public function getTemporaryUrl(StreamSubtitle $subtitle, int $ttl = 3600): string
    {
        if (str_starts_with($subtitle->url, 'http')) {
            return $subtitle->url;
        }

        // For local storage, we'll use our serve endpoint
        // since local disk doesn't support temporaryUrl by default
        return route('api.subtitles.serve', $subtitle->id);
    }

    public function getSubtitleContent(StreamSubtitle $subtitle): ?string
    {
        $cacheKey = "subtitle_content_{$subtitle->id}_{$subtitle->updated_at->timestamp}";
        
        return Cache::remember($cacheKey, 3600, function () use ($subtitle) {
            if (str_starts_with($subtitle->url, 'http')) {
                // External URL - fetch content with error handling
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 30,
                            'user_agent' => 'Mozilla/5.0 (compatible; AnimeSubtitleFetcher/1.0)',
                        ]
                    ]);
                    return file_get_contents($subtitle->url, false, $context);
                } catch (\Exception $e) {
                    Log::error("Failed to fetch external subtitle: {$subtitle->url}", ['error' => $e->getMessage()]);
                    return null;
                }
            }
            
            // Local file - read from storage
            try {
                if (Storage::disk('subtitles')->exists($subtitle->url)) {
                    return Storage::disk('subtitles')->get($subtitle->url);
                }
            } catch (\Exception $e) {
                Log::error("Failed to read local subtitle: {$subtitle->url}", ['error' => $e->getMessage()]);
            }
            
            return null;
        });
    }

    public function serveSubtitleResponse(StreamSubtitle $subtitle): Response|StreamedResponse
    {
        $content = $this->getSubtitleContent($subtitle);
        
        if (!$content) {
            return response('Subtitle not found', 404);
        }
        
        $headers = [
            'Content-Type' => $subtitle->content_type,
            'Content-Disposition' => 'inline; filename="' . $this->getFilename($subtitle) . '"',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ];

        return response($content, 200, $headers);
    }

    public function downloadSubtitleResponse(StreamSubtitle $subtitle): Response|StreamedResponse
    {
        $content = $this->getSubtitleContent($subtitle);
        
        if (!$content) {
            return response('Subtitle not found', 404);
        }
        
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $this->getFilename($subtitle) . '"',
            'Content-Length' => strlen($content),
        ];

        return response($content, 200, $headers);
    }

    public function streamSubtitleResponse(StreamSubtitle $subtitle): StreamedResponse
    {
        if (str_starts_with($subtitle->url, 'http')) {
            // Stream external URL
            return response()->stream(function () use ($subtitle) {
                $handle = fopen($subtitle->url, 'rb');
                if ($handle) {
                    while (!feof($handle)) {
                        echo fread($handle, 8192);
                        flush();
                    }
                    fclose($handle);
                }
            }, 200, [
                'Content-Type' => $subtitle->content_type,
                'Cache-Control' => 'public, max-age=3600',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        // Stream local file
        return response()->stream(function () use ($subtitle) {
            $stream = Storage::disk('subtitles')->readStream($subtitle->url);
            if ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $subtitle->content_type,
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function clearCache(StreamSubtitle $subtitle): void
    {
        $pattern = "subtitle_content_{$subtitle->id}_*";
        
        // Clear specific subtitle cache
        Cache::forget("subtitle_content_{$subtitle->id}_{$subtitle->updated_at->timestamp}");
        
        // Clear pattern-based cache if needed (simplified approach)
        // You can implement Redis-specific pattern clearing if needed
    }

    public function getFileStats(StreamSubtitle $subtitle): array
    {
        if (str_starts_with($subtitle->url, 'http')) {
            return [
                'type' => 'external',
                'url' => $subtitle->url,
                'accessible' => $this->checkExternalUrl($subtitle->url),
            ];
        }

        $exists = Storage::disk('subtitles')->exists($subtitle->url);
        
        return [
            'type' => 'local',
            'exists' => $exists,
            'path' => $subtitle->url,
            'size' => $exists ? Storage::disk('subtitles')->size($subtitle->url) : 0,
            'last_modified' => $exists ? Storage::disk('subtitles')->lastModified($subtitle->url) : null,
            'readable' => $exists && Storage::disk('subtitles')->get($subtitle->url) !== false,
        ];
    }

    private function getFilename(StreamSubtitle $subtitle): string
    {
        $basename = basename($subtitle->url);
        
        if (empty($basename) || $basename === $subtitle->url) {
            // Generate filename if not available
            $stream = $subtitle->stream;
            $episode = $stream?->episode;
            $post = $episode?->post;
            
            $parts = [];
            if ($post) $parts[] = Str::slug($post->title);
            if ($episode) $parts[] = "ep{$episode->episode_number}";
            $parts[] = $subtitle->language;
            
            return implode('_', $parts) . '.' . $subtitle->type;
        }
        
        return $basename;
    }

    private function checkExternalUrl(string $url): bool
    {
        try {
            $headers = get_headers($url, 1);
            return $headers !== false && strpos($headers[0], '200') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
