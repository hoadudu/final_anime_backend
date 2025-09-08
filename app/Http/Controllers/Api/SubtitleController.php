<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StreamSubtitle;
use App\Models\Stream;
use App\Services\SubtitleAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubtitleController extends Controller
{
    public function __construct(
        private SubtitleAccessService $subtitleService
    ) {}

    /**
     * Get all subtitles for a stream
     */
    public function getStreamSubtitles(Stream $stream): JsonResponse
    {
        $subtitles = $stream->activeSubtitles()->with('stream.episode.post')->get();
        
        return response()->json([
            'success' => true,
            'data' => $subtitles->map(function ($subtitle) {
                return [
                    'id' => $subtitle->id,
                    'language' => $subtitle->language,
                    'language_name' => $subtitle->language_name,
                    'type' => $subtitle->type,
                    'url' => $this->subtitleService->getSubtitleUrl($subtitle),
                    'serve_url' => route('api.subtitles.serve', $subtitle->id),
                    'download_url' => route('api.subtitles.download', $subtitle->id),
                    'is_default' => $subtitle->is_default,
                    'source' => $subtitle->source,
                    'content_type' => $subtitle->content_type,
                    'encoding' => $subtitle->getEncoding(),
                    'offset' => $subtitle->getOffset(),
                    'fps' => $subtitle->getFps(),
                    'display_name' => $subtitle->display_name,
                ];
            })
        ]);
    }

    /**
     * Serve subtitle content directly
     */
    public function serve(StreamSubtitle $subtitle)
    {
        return $this->subtitleService->serveSubtitleResponse($subtitle);
    }

    /**
     * Download subtitle file
     */
    public function download(StreamSubtitle $subtitle)
    {
        return $this->subtitleService->downloadSubtitleResponse($subtitle);
    }

    /**
     * Stream subtitle content (for large files)
     */
    public function stream(StreamSubtitle $subtitle)
    {
        return $this->subtitleService->streamSubtitleResponse($subtitle);
    }

    /**
     * Get subtitle file information
     */
    public function info(StreamSubtitle $subtitle): JsonResponse
    {
        $fileStats = $this->subtitleService->getFileStats($subtitle);
        
        return response()->json([
            'success' => true,
            'data' => [
                'subtitle' => [
                    'id' => $subtitle->id,
                    'language' => $subtitle->language,
                    'language_name' => $subtitle->language_name,
                    'type' => $subtitle->type,
                    'source' => $subtitle->source,
                    'is_default' => $subtitle->is_default,
                    'is_active' => $subtitle->is_active,
                    'created_at' => $subtitle->created_at,
                    'updated_at' => $subtitle->updated_at,
                ],
                'file' => $fileStats,
                'urls' => [
                    'serve' => route('api.subtitles.serve', $subtitle->id),
                    'download' => route('api.subtitles.download', $subtitle->id),
                    'stream' => route('api.subtitles.stream', $subtitle->id),
                ],
            ]
        ]);
    }

    /**
     * Clear subtitle cache
     */
    public function clearCache(StreamSubtitle $subtitle): JsonResponse
    {
        $this->subtitleService->clearCache($subtitle);
        
        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully'
        ]);
    }

    /**
     * Get available subtitle languages for a stream
     */
    public function getLanguages(Stream $stream): JsonResponse
    {
        $languages = $stream->getSubtitleLanguages();
        
        return response()->json([
            'success' => true,
            'data' => $languages
        ]);
    }

    /**
     * Get default subtitle for a stream
     */
    public function getDefault(Stream $stream): JsonResponse
    {
        $defaultSubtitle = $stream->defaultSubtitle();
        
        if (!$defaultSubtitle) {
            return response()->json([
                'success' => false,
                'message' => 'No default subtitle found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $defaultSubtitle->id,
                'language' => $defaultSubtitle->language,
                'language_name' => $defaultSubtitle->language_name,
                'type' => $defaultSubtitle->type,
                'url' => $this->subtitleService->getSubtitleUrl($defaultSubtitle),
                'serve_url' => route('api.subtitles.serve', $defaultSubtitle->id),
                'content_type' => $defaultSubtitle->content_type,
                'display_name' => $defaultSubtitle->display_name,
            ]
        ]);
    }
}
