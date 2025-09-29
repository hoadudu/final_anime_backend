<?php

namespace App\Http\Controllers\Api\Pages\Info;

use App\Models\Post;
use App\Helpers\PostHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AnimeInfoController extends Controller
{
    /**
     * Display anime information by slug or ID
     */
    public function index($id, Request $request): JsonResponse
    {
        $identifier = $id;
        $lang = $request->query('lang', 'en');

        if (!$identifier) {
            return response()->json([
                'error' => 'ID or slug parameter is required'
            ], 400);
        }

        $cacheKey = "anime-info-{$identifier}-{$lang}";

        // $data = Cache::remember($cacheKey, 3600, function () use ($identifier, $lang) {
        $data = Cache::remember($cacheKey, 3600, function () use ($identifier, $lang) {
            // Try to find by ID first, then by slug
            $post = is_numeric($identifier)
                ? Post::where('id', $identifier)->first()
                : Post::where('slug', $identifier)->first();

            if (!$post) {
                return null;
            }
            views($post)->record();


            // Load all necessary relationships
            $post->load([
                'titles',
                'images',
                'genres',
                'studios',
                'producers',
                'characters',
                'videos',
                'images',
                'episodeList.activeStreams',
                'animeGroups'
            ]);

            return $this->formatAnimeInfo($post, $lang);
        });

        if (!$data) {
            return response()->json([
                'error' => 'Anime not found'
            ], 404);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Format anime information according to the specified schema
     */
    private function formatAnimeInfo(Post $post, string $lang): array
    {
        // Get localized title
        $title = $this->getLocalizedTitle($post, $lang);

        // Get poster and cover images
        $poster = $this->getImageUrl($post, 'poster');
        $cover = $this->getImageUrl($post, 'cover');

        // Calculate episodes count
        $episodesCount = $this->getEpisodesCount($post);

        // Format aired dates
        $aired = $this->formatAiredDates($post);

        // Get watch URL
        $watchUrl = $this->getWatchUrl($post);



        return [
            'id' => $post->id,
            'title' => $title,
            'titles' => $this->formatTitles($post->titles),
            'poster' => $poster,
            'cover' => $cover,
            'description' => strip_tags($post->description ?? $post->synopsis ?? ''),
            'type' => (new PostHelper())->beautifyAnimeType($post->type) ?? 'TV',
            'status' => $this->formatStatus($post->status),
            'aired' => $aired,
            'year' => $post->aired_from ? $post->aired_from->format('Y') : '',
            'views' => views($post)->count() ?? 0,
            'premiered' => $this->formatPremiered($post),
            'duration' => $post->duration ?? 'Unknown',
            'rating' => $post->rating ?? 'PG-13',
            'quality' => $post->quality ?? 'HD',
            'episodes' => $episodesCount,
            'episodeList' => $post->episodeList->map(function ($episode) {
                return [
                    'id' => $episode->id,
                    'title' => $episode->title ?? "Episode {$episode->episode_number}",
                    'episodeNumber' => $episode->episode_number,
                    'airDate' => $episode->air_date ? $episode->air_date->format('Y-m-d') : 'Unknown',
                    'subCount' => $episode->activeStreams->where('language', 'sub')->count(),
                    'dubCount' => $episode->activeStreams->where('language', 'dub')->count(),

                ];
            })->toArray(),
            'animeGroups' => $post->animeGroups->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'posts' => $group->posts->map(function ($groupedPost) {
                        return [
                            'id' => $groupedPost->id,
                            'title' => $groupedPost->getDisplayTitleAttribute(),                            
                            'position' => $groupedPost->pivot->position ?? 0,
                            'note' => $groupedPost->pivot->note ?? '',
                            'link' => __('routes.info') . '/' . $groupedPost->slug . '-' . $groupedPost->id
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'characters' => $this->formatCharacters($post->characters->take(16)), // Assuming characters are handled elsewhere
            'videos' => $this->formatVideos($post->videos),
            'images' => $this->formatImages($post->images),
            'malScore' => (float) ($post->mal_score ?? 0),
            'genres' => $this->formatGenresAll($post->getGenreAll()),
            'studios' => $this->formatProducerModel($post->getStudios),
            'producers' => $this->formatProducerModel($post->getProducers),
            'watchUrl' => $watchUrl,
            'shareCount' => (int) ($post->share_count ?? 0)
        ];
    }

    /**
     * Get localized title
     */
    private function getLocalizedTitle(Post $post, string $lang): string
    {
        // Try to get title in requested language
        $localizedTitle = $post->titles->where('language', $lang)->first();

        if ($localizedTitle) {
            return $localizedTitle->title;
        }

        // Fallback to English
        $englishTitle = $post->titles->where('language', 'en')->first();
        if ($englishTitle) {
            return $englishTitle->title;
        }

        // Fallback to post title or first available title
        return $post->title ?? $post->titles->first()?->title ?? 'Unknown';
    }

    /**
     * Format titles array
     */
    private function formatTitles($titles): array
    {
        if (!$titles || $titles->isEmpty()) {
            return [
                [
                    'language' => 'en',
                    'title' => 'Unknown'
                ]
            ];
        }

        return $titles->map(function ($title) {
            return [
                'language' => $title->language ?? 'en',
                'title' => $title->title
            ];
        })->toArray();
    }

    /**
     * Get image URL by type
     */
    private function getImageUrl(Post $post, string $type): string
    {
        $image = $post->images->where('image_type', $type)->first();

        if ($image && $image->image_url) {
            return $image->image_url;
        }

        // Fallback URLs
        $fallbacks = [
            'poster' => 'https://via.placeholder.com/300x400?text=No+Poster',
            'cover' => 'https://via.placeholder.com/1920x1080?text=No+Cover'
        ];

        return $fallbacks[$type] ?? '';
    }

    /**
     * Calculate episodes count
     */
    private function getEpisodesCount(Post $post): array
    {
        $episodes = $post->episodeList;
        $total = $episodes->count();

        // Count sub and dub episodes
        $sub = $episodes->filter(function ($episode) {
            return $episode->activeStreams->where('type', 'sub')->count() > 0;
        })->count();

        $dub = $episodes->filter(function ($episode) {
            return $episode->activeStreams->where('type', 'dub')->count() > 0;
        })->count();

        return [
            'total' => $total,
            'sub' => $sub,
            'dub' => $dub
        ];
    }

    /**
     * Format aired dates
     */
    private function formatAiredDates(Post $post): array
    {
        
        return [
            'from' => $post->aired_from ? $post->aired_from->format('Y-m-d') : 'Unknown',
            'to' => $post->aired_to ? $post->aired_to->format('Y-m-d') : 'Unknown'
        ];
    }

    /**
     * Format premiered season
     */
    private function formatPremiered(Post $post): string
    {
        if ($post->premiered) {
            return $post->premiered;
        }

        if ($post->aired_from) {
            $year = $post->aired_from->format('Y');
            $month = (int) $post->aired_from->format('n');

            $season = match (true) {
                $month >= 1 && $month <= 3 => 'Winter',
                $month >= 4 && $month <= 6 => 'Spring',
                $month >= 7 && $month <= 9 => 'Summer',
                $month >= 10 && $month <= 12 => 'Fall',
            };

            return "{$season} {$year}";
        }

        return 'Unknown';
    }

    /**
     * Format status
     */
    private function formatStatus(string $status = null): string
    {
        $statusMap = [
            'completed' => 'Completed',
            'ongoing' => 'Ongoing',
            'upcoming' => 'Upcoming',
            'hiatus' => 'Hiatus',
            'cancelled' => 'Cancelled'
        ];

        return $statusMap[strtolower($status ?? '')] ?? 'Unknown';
    }

    /**
     * Format genres
     */
    private function formatGenresAll($genres): array
    {
        if (!$genres || $genres->isEmpty()) {
            return [
                [
                    'id' => 0,
                    'name' => 'Unknown',
                    'slug' => 'unknown'
                ]
            ];
        }

        return $genres->map(function ($genre) {

            return [
                'id' => $genre->id,
                'name' => $genre->name ?? 'Unknown',
                'link' => '/' . __('routes.genre') . '/' . ($genre->slug ?? 'unknown'),
            ];
        })->toArray();
    }

    /**
     * Format studios
     */
    private function formatStudios($studios): array
    {
        if (!$studios || $studios->isEmpty()) {
            return [
                [
                    'id' => 0,
                    'name' => 'Unknown',
                    'slug' => 'unknown'
                ]
            ];
        }

        return $studios->map(function ($studio) {
            return [
                'id' => $studio->id,
                'name' => $studio->name,
                'slug' => $studio->slug,
                'link' => '/studio/' . $studio->slug
            ];
        })->toArray();
    }

    /**
     * Format Videos
     */
    private function formatVideos($videos): array
    {
        if (!$videos || $videos->isEmpty()) {
            return [];
        }

        return $videos->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title ?? __('d.Unknown'),
                'url' => $video->url ?? __('d.Unknown'),
                'thumbnail' => $video->thumbnail ?? __('d.Unknown')
            ];
        })->toArray();
    }

    /**
     * Format Images
     */
    private function formatImages($images): array
    {
        if (!$images || $images->isEmpty()) {
            return [];
        }
        // return grouped images by type
        return $images->groupBy('image_type')->map(function ($group) {
            return $group->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->image_url,
                    'type' => $image->image_type
                ];
            });
        })->toArray();
    }

    /**
     * Format Characters
     */
    private function formatCharacters($characters): array
    {
        if (!$characters || $characters->isEmpty()) {
            return [];
        }

        return $characters->map(function ($item) {
            $character = $item->character ?? null;
            return [
                'id' => $item->character_id ?? $character?->id ?? 0,
                'role' => $item->role ?? '',
                'name' => $character?->name ?? '',
                'image_url' => $character?->images['jpg']['image_url'] ?? '',
                'slug' => $character?->slug ?? '',
            ];
        })->toArray();
    }


    /**
     * Format producers
     */
    private function formatProducerModel($producers): array
    {

        if (!$producers || $producers->isEmpty()) {
            return [
                [
                    'id' => 0,
                    'titles' => 'Unknown',
                    'slug' => 'unknown'
                ]
            ];
        }

        return $producers->map(function ($producer) {
            return [
                'id' => $producer->id,
                'titles' => $producer->titles,
                'slug' => $producer->slug
            ];
        })->toArray();
    }

    /**
     * Get watch URL
     */
    private function getWatchUrl(Post $post): string
    {
        // Assuming you have a route for watching anime
        //return route('anime.watch', ['slug' => $post->slug]);
        return url('/anime/' . $post->slug . '-' . $post->id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
