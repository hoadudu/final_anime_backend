<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Post;
use App\Helpers\PostHelper;
use CyrildeWit\EloquentViewable\Support\Period;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Illuminate\Support\Facades\DB;




class FeaturedListController extends Controller
{
    /**
     * Get top $n airing anime with highest view counts
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function top_airing(Request $request): JsonResponse
    {
        $n = 5; // Number of top airing anime to fetch
        $lang = $request->query('lang', 'en'); // Default to 'en'

        // Get top $n airing (unfinished) anime by views
        $topAiring = Post::where('status', '=', 'Ongoing')
            ->where('airing', 0)
            ->orderBy('film_viewed', 'desc')
            ->take($n)
            ->get();

        $data = $topAiring->map(function ($post) use ($lang) {
            $posterImage = $post->images->where('image_type', 'poster')->first();
            $streams_with_sub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'sub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $streams_with_dub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'dub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $sub = $streams_with_sub->isNotEmpty()
                ? $streams_with_sub->count()
                : '0';
            $dub = $streams_with_dub->isNotEmpty()
                ? $streams_with_dub->count()
                : '0';

            $title = (new PostHelper)->getLocalizedTitle($post, $lang);    
            return [
                'id' => $post->id,
                'title' => $title,
                'image' => $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg',
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
    public function most_popular_animes(Request $request): JsonResponse
    {
        $n = 5; // Number of top airing anime to fetch
        $lang = $request->query('lang', 'en'); // Default to 'en'

        // Get top $n airing (unfinished) anime by views        
        $mostPopular = Post::orderByViews('desc', Period::pastDays(7))->take($n)->get();

        $data = $mostPopular->map(function ($post) use ($lang) {
            $posterImage = $post->images->where('image_type', 'poster')->first();
            $streams_with_sub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'sub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $streams_with_dub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'dub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $sub = $streams_with_sub->isNotEmpty()
                ? $streams_with_sub->count()
                : '0';
            $dub = $streams_with_dub->isNotEmpty()
                ? $streams_with_dub->count()
                : '0';

            $title = (new PostHelper)->getLocalizedTitle($post, $lang);    
            return [
                'id' => $post->id,
                'title' => $title,
                'image' => $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg',
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
    
    //latest_completed
    public function latest_completed(Request $request): JsonResponse
    {
        $n = 5; // Number of top airing anime to fetch
        $lang = $request->query('lang', 'en'); // Default to 'en'

        // Get top $n airing (unfinished) anime by views
        $latestCompleted = Post::where('status', '=', 'Completed')
            ->orderBy('updated_at', 'desc')
            ->take($n)
            ->get();

        $data = $latestCompleted->map(function ($post) use ($lang) {
            $posterImage = $post->images->where('image_type', 'poster')->first();
            $streams_with_sub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'sub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $streams_with_dub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'dub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $sub = $streams_with_sub->isNotEmpty()
                ? $streams_with_sub->count()
                : '0';
            $dub = $streams_with_dub->isNotEmpty()
                ? $streams_with_dub->count()
                : '0';

            $title = (new PostHelper)->getLocalizedTitle($post, $lang);    
            return [
                'id' => $post->id,
                'title' => $title,
                'image' => $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg',
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

    public function most_liked_animes(Request $request): JsonResponse
    {
        $n = 5; // Number of top anime to fetch
        $lang = $request->query('lang', 'en'); // Default to 'en'

        $likeType = ReactionType::where('name', 'Like')->firstOrFail();

        
        $likeType = ReactionType::where('name', 'Like')->firstOrFail();

        $mostLiked = Post::join('love_reactions', 'anime_posts.love_reactant_id', '=', 'love_reactions.reactant_id')
            ->where('love_reactions.reaction_type_id', $likeType->id)
            ->select('anime_posts.*', DB::raw('COUNT(love_reactions.id) as likes_count'))
            ->groupBy('anime_posts.id')
            ->orderByDesc('likes_count')
            ->limit($n)
            ->get();
    

        $data = $mostLiked->map(function ($post) use ($lang) {
            $posterImage = $post->images->where('image_type', 'poster')->first();
            $streams_with_sub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'sub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $streams_with_dub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'dub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $sub = $streams_with_sub->isNotEmpty()
                ? $streams_with_sub->count()
                : '0';
            $dub = $streams_with_dub->isNotEmpty()
                ? $streams_with_dub->count()
                : '0';

            $title = (new PostHelper)->getLocalizedTitle($post, $lang);    
            return [
                'id' => $post->id,
                'title' => $title,
                'image' => $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg',
                'sub' => $sub,
                'dub' => $dub,
                'type' => $post->type,
                'slug' => $post->slug,
                'likes_count' => $post->reactions_count ?? 0,
            ];
        });

        return response()->json([
            'data' => $data
        ]);

        
        
        $data = $mostLiked->map(function ($post) use ($lang) {
            $posterImage = $post->images->where('image_type', 'poster')->first();
            $streams_with_sub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'sub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $streams_with_dub = $post->episodeList->map(function ($episode) {
                return $episode->activeStreams->where('language', 'dub')->first(); // chỉ lấy stream đầu tiên
            })->filter(); // loại bỏ null nếu episode không có stream

            $sub = $streams_with_sub->isNotEmpty()
                ? $streams_with_sub->count()
                : '0';
            $dub = $streams_with_dub->isNotEmpty()
                ? $streams_with_dub->count()
                : '0';

            $title = (new PostHelper)->getLocalizedTitle($post, $lang);    
            return [
                'id' => $post->id,
                'title' => $title,
                'image' => $posterImage ? $posterImage->image_url : 'https://cdn.noitatnemucod.net/thumbnail/300x400/100/default.jpg',
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
    

        
    
}
