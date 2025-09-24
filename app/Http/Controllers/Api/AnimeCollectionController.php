<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\AnimeCollection;
use App\Helpers\LanguageCodeHelper;
class AnimeCollectionController extends Controller
{
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
            $title = $this->getLocalizedTitle($post, $lang);

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
            $backdropImage = $collectionPost->backdrop_image ?? 'https://cdn.noitatnemucod.net/thumbnail/1366x768/100/db8603d2f4fa78e1c42f6cf829030a18.jpg';

            // If no collectionPost backdrop, try to get from post media
            if ($backdropImage === 'https://cdn.noitatnemucod.net/thumbnail/1366x768/100/db8603d2f4fa78e1c42f6cf829030a18.jpg') {
                if ($post->hasMedia('backdrop')) {
                    $media = $post->getFirstMedia('backdrop');
                    if ($media) {
                        $backdropImage = $media->getUrl();
                    }
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

    public function trending_animes(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en');

        
        // Lấy collection trending (slug: trending-animes)
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
                'title' => $this->getLocalizedTitle($post, $lang),
                'titles' => $post->titles->map(function($t) {
                    $lang = trim((string) ($t->language ?? '')); // bảo đảm là string
                    $name = $lang === '' ? null : LanguageCodeHelper::getName($lang);
                    return [
                        'language' => $name ?: 'Synonyms',
                        'title' => $t->title,
                    ];
                })->toArray(),
                'posterUrl' => $post->images->where('image_type', 'poster')->first()->image_url ?? 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg',
                'rank' => $index + 1,
                'slug' => $post->slug ?? '',
                'description' => $post->synopsis ?? '',
                'aired' => $post->aired_from ?? '',
                'status' => $post->status ?? '',
                'genres' => $post->genres->pluck('name')->filter()->values()->toArray(),
            ];
        })->filter()->values();

        return response()->json($data);
    }

    
    /**
     * Get localized title based on language preference
     */
    private function getLocalizedTitle($post, $lang = 'en')
    {
        // If language is Vietnamese, try to find Vietnamese title
        if ($lang === 'vi' && $post->relationLoaded('titles')) {
            $vietnameseTitle = $post->titles->first(function ($title) {
                return $title->language === 'vi';
            });

            if ($vietnameseTitle) {
                return $vietnameseTitle->title;
            }
        }

        // Fallback to default title or display title
        return $post->title ?? $post->getDisplayTitleAttribute() ?? 'Unknown Title';
    }
}
