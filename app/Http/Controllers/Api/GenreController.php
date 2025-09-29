<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = $request->query('lang', 'en'); // Default to 'en'

        $genres = Genre::all()->map(function ($genre) use ($lang) {
            $genre->loadCount('posts');
            return [
                'id' => $genre->id,
                'name' => $genre->getLocalizedName($lang),
                'category' => $genre->category,
                'posts_count' => $genre->getPostsCountAttribute(),            
            ];
        });

        return response()->json($genres);
    }
}
