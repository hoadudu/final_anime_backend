<?php

namespace App\Http\Controllers\Api\Sidebar;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Helpers\PostHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use CyrildeWit\EloquentViewable\Support\Period;

class TopController extends Controller
{
    /**
     * Get top 10 anime posts by view count for different time periods
     */
    public function index(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');
        $cacheKey = "top-anime-{$lang}";

        $result = Cache::remember($cacheKey, 3600, function () use ($lang) {
            $periods = [
                'day' => Period::pastDays(1),
                'week' => Period::pastDays(7),
                'month' => Period::pastDays(30),
                'year' => Period::pastDays(365),
                'all' => null, // All time - no period filter
            ];

            $result = [];

            foreach ($periods as $periodName => $period) {
                if ($period) {
                    // For specific periods, use orderByViews
                    $posts = Post::with(['episodeList.activeStreams', 'images', 'titles'])
                        ->orderByViews('desc', $period)
                        ->take(10)
                        ->get();
                    
                } 

                $result[$periodName] = $this->formatPosts($posts, $lang);
            }

            return $result;
        });

        return response()->json($result);
    }

    /**
     * Format posts for API response
     */
    private function formatPosts($posts, string $lang): array
    {
        return $posts->map(function ($post, $index) use ($lang) {
            // Get localized title
            $title = (new PostHelper)->getLocalizedTitle($post, $lang);

            // Get English title (primary or first available)
            $titles = (new PostHelper)->formatTitles($post->titles);

            // Get poster image
            $posterImage = $post->images->where('image_type', 'poster')->first();
            $poster = $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg';

            // Count sub and dub episodes
            $subCount = 0;
            $dubCount = 0;

            foreach ($post->episodeList as $episode) {
                $subStreams = $episode->activeStreams->where('language', 'sub');
                $dubStreams = $episode->activeStreams->where('language', 'dub');

                if ($subStreams->isNotEmpty()) {
                    $subCount++;
                }
                if ($dubStreams->isNotEmpty()) {
                    $dubCount++;
                }
            }

            return [
                'id' => (string) $post->id,
                'rank' => $index + 1,
                'title' => $title,
                'titles' => $titles,
                'views' => $post->views_count ?? 0,
                'description' => strip_tags($post->synopsis),
                'slug' => $post->slug,
                'poster' => $poster,
                'subCount' => $subCount,
                'dubCount' => $dubCount,
                'totalEpisodes' => $post->episodes ?? $post->episodeList->count(),
                'type' => $post->type ?? 'TV',
                'status' => $post->status ?? 'Ongoing'
            ];
        })->toArray();
    }
}
