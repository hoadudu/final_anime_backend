<?php

namespace App\Http\Controllers\Api\Pages\Home;

use App\Models\Post;
use App\Models\Episode;
use App\Helpers\PostHelper;
use Illuminate\Http\Request;
use App\Models\AnimeCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Helpers\LanguageCodeHelper;
use App\Http\Controllers\Controller;
use CyrildeWit\EloquentViewable\Support\Period;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;

class HomeFrontEndController extends Controller
{
    private const DEFAULT_POSTER_URL = 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg';
    private const DEFAULT_BACKDROP_URL = 'https://cdn.noitatnemucod.net/thumbnail/1366x768/100/db8603d2f4fa78e1c42f6cf829030a18.jpg';
    private const DEFAULT_ITEMS_COUNT = 5;
    private const DEFAULT_LATEST_EPISODES = 24;

    /**
     * Get featured animes from collection
     */
    public function featured_animes(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en'); // Default to 'en'

        // Get the featured collection - get the one with slug 'featured-animes'
        $withRelationships = [
            'collectionPosts.post.genres.morphable',
            'collectionPosts.post.mainCharacters.character'
        ];

        // Include titles relationship if language is Vietnamese
        if ($lang === 'vi') {
            $withRelationships[] = 'collectionPosts.post.titles';
        }

        $featuredCollection = AnimeCollection::with($withRelationships)
            ->where('slug', 'featured-animes')
            ->first();

        if (!$featuredCollection) {
            return response()->json([
                'result' => false,
                'message' => 'No featured collection available'
            ]);
        }

        // Process all collection posts
        $data = $featuredCollection->collectionPosts->map(function ($collectionPost) use ($lang) {
            if (!$collectionPost->post) return null;

            $post = $collectionPost->post;

            // Get appropriate title based on language
            $title = (new PostHelper)->getLocalizedTitle($post, $lang);    

            // Get genres
            $genres = $post->genres->map(function ($morphable) {
                return $morphable->morphable->name ?? '';
            })->filter()->values()->toArray();

            // Get main characters
            $characters = $post->mainCharacters->map(function ($postCharacter) {
                return $postCharacter->character->name ?? '';
            })->filter()->take(3)->implode(', ');

            // Get season info from episodes (simplified logic)
            $season = $post->episodeList->count() > 0 ? '1' : '1'; // Default to season 1, can be improved

            // Get backdrop image - use collectionPost's backdrop_image if available, otherwise try post media
            $backdropImage = $collectionPost->backdrop_image ?? self::DEFAULT_BACKDROP_URL;

            // If no collectionPost backdrop, try to get from post media
            if ($backdropImage === self::DEFAULT_BACKDROP_URL && $post->hasMedia('backdrop')) {
                $media = $post->getFirstMedia('backdrop');
                if ($media) {
                    $backdropImage = $media->getUrl();
                }
            }

            // Format the data according to the specification
            return [
                'title' => $title,
                'season' => $season,
                'backdropImage' => $backdropImage,
                'description' => $post->synopsis ?? '',
                'playLink' => '/anime/' . $post->id,
                'ageRating' => $post->rating ?? 'TV-14',
                'maturityLevel' => $post->rating ?? 'TV-14',
                'spotlight' => (string) $collectionPost->id, // Use collectionPost ID as spotlight identifier
                'billboardId' => (string) $collectionPost->id, // Add collectionPost ID for reference
                'mediaType' => $post->type ?? 'TV',
                'duration' => $post->duration ?? '24m',
                'releaseDate' => $post->aired_from ? date('M j, Y', strtotime($post->aired_from)) : 'Jul 9, 2025',
                'quality' => 'HD',
                'rating' => '9', // You can calculate this from user ratings if available
                'showSidePanel' => true,
                'character' => $characters ?: 'Kaito Ishikawa, Asami Seto, Rina Hidaka',
                'genres' => $genres ?: ['Action', 'Adventure', 'Drama', 'Fantasy', 'Isekai']
            ];
        })->filter()->values();

        return response()->json($data);
    }

    /**
     * Get trending animes from collection
     */
    public function trending_animes(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        // Get collection trending (slug: trending-animes)
        $withRelationships = [
            'collectionPosts.post.genres',
        ];
        
        $trendingCollection = AnimeCollection::with($withRelationships)
            ->where('slug', 'trending-animes')
            ->first();

        if (!$trendingCollection) {
            return response()->json([]);
        }
        
        $data = $trendingCollection->collectionPosts->map(function ($collectionPost, $index) use ($lang) {
            $post = $collectionPost->post;
            if (!$post) return null;

            return [
                'id' => $post->id,
                'title' => (new PostHelper)->getLocalizedTitle($post, $lang),
                'titles' => $this->formatTitles($post->titles),
                'posterUrl' => $post->images->where('image_type', 'poster')->first()->image_url ?? self::DEFAULT_POSTER_URL,
                'rank' => $index + 1,
                'slug' => $post->slug ?? '',
                'description' => strip_tags($post->synopsis ?? ''),
                'year' => $post->aired_from ?? '',
                'status' => $post->status ?? '',
                'genres' => $post->genres->pluck('name')->filter()->values()->toArray(),
            ];
        })->filter()->values();

        return response()->json($data);
    }

    /**
     * Get top airing anime with highest view counts
     */
    public function top_airing(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        $topAiring = Post::where('status', 'Ongoing')
            ->where('airing', 0)
            ->orderBy('film_viewed', 'desc')
            ->take(self::DEFAULT_ITEMS_COUNT)
            ->get();

        return $this->getData($topAiring, $lang);
    }

    /**
     * Get most popular animes based on views in the past 7 days
     */
    public function most_popular_animes(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        $mostPopular = Post::orderByViews('desc', Period::pastDays(7))
            ->take(self::DEFAULT_ITEMS_COUNT)
            ->get();

        return $this->getData($mostPopular, $lang);
    }
    
    /**
     * Get latest completed animes
     */
    public function latest_completed(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        $latestCompleted = Post::where('status', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->take(self::DEFAULT_ITEMS_COUNT)
            ->get();

        return $this->getData($latestCompleted, $lang);
    }

    /**
     * Get most liked animes
     */
    public function most_liked_animes(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        $likeType = ReactionType::where('name', 'Like')->firstOrFail();

        $mostLiked = Post::join('love_reactions', 'anime_posts.love_reactant_id', '=', 'love_reactions.reactant_id')
            ->where('love_reactions.reaction_type_id', $likeType->id)
            ->select('anime_posts.*', DB::raw('COUNT(love_reactions.id) as likes_count'))
            ->groupBy('anime_posts.id')
            ->orderByDesc('likes_count')
            ->limit(self::DEFAULT_ITEMS_COUNT)
            ->get();

        return $this->getData($mostLiked, $lang);
    }

    /** Latest Episodes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function latest_episode_posts(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        // Get 10 posts ordered by their latest episode creation time
        $latestEpisodePosts = Post::where('approved', 1)
            ->whereHas('episodeList', function ($query) {
                $query->whereHas('streams'); // Episode must have at least 1 stream
            })
            ->with(['images', 'titles', 'genres', 'episodeList.activeStreams'])
            ->withMax('episodeList', 'created_at') // Get the latest episode creation time
            ->orderByDesc('episode_list_max_created_at') // Order by latest episode time
            ->take(self::DEFAULT_LATEST_EPISODES)
            ->get();

        return $this->getData($latestEpisodePosts, $lang);
    }


        
    

    /**
     * Format and transform data for API response
     */
    /**
     * Format and transform data for API response
     */
    private function getData($dataDb, $lang): JsonResponse
    {
        $data = $dataDb->map(function ($post) use ($lang) {
            $posterImage = $post->images->where('image_type', 'poster')->first();
            
            $streams_with_sub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'sub')->first();
            })->filter();

            $streams_with_dub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'dub')->first();
            })->filter();

            $sub = $streams_with_sub->isNotEmpty() ? $streams_with_sub->count() : '0';
            $dub = $streams_with_dub->isNotEmpty() ? $streams_with_dub->count() : '0';

            return [
                'id' => $post->id,
                'title' => (new PostHelper)->getLocalizedTitle($post, $lang),
                'titles' => $this->formatTitles($post->titles),
                'description' => strip_tags($post->synopsis ?? ''),
                'genres' => $post->genres->pluck('name')->filter()->values()->toArray(),
                'image' => $posterImage ? $posterImage->image_url : self::DEFAULT_POSTER_URL,
                'sub' => $sub,
                'dub' => $dub,
                'type' => $post->type,
                'slug' => $post->slug,
            ];
        });
        
        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Format titles collection for API response
     */
    private function formatTitles($titles): array
    {
        return $titles->map(function($title) {
            $lang = trim((string) ($title->language ?? ''));
            $name = $lang === '' ? null : LanguageCodeHelper::getName($lang);
            
            return [
                'language' => $name ?: 'Synonyms',
                'title' => $title->title,
            ];
        })->toArray();
    }
}
