<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserAnimeList;
use App\Models\UserAnimeListItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserAnimeListController extends Controller
{
    /**
     * Get user's anime list with items.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $list = $user->getOrCreateDefaultAnimeList();

        $query = $list->items()
            ->with(['post:id,title,mal_id,type,episodes,status,images,slug'])
            ->orderBy('updated_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->paginate(20);

        // Transform the items for response
        $transformedItems = $items->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'anime' => [
                    'id' => $item->post->id,
                    'title' => $item->post->title,
                    'display_title' => $item->post->display_title,
                    'mal_id' => $item->post->mal_id,
                    'type' => $item->post->type,
                    'episodes' => $item->post->episodes,
                    'status' => $item->post->status,
                    'images' => $item->post->images,
                    'slug' => $item->post->slug,
                ],
                'status' => $item->status,
                'score' => $item->score,
                'note' => $item->note,
                'updated_at' => $item->updated_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'list_name' => $list->name,
            'total_items' => $list->items()->count(),
            'stats' => $list->stats,
            'items' => $transformedItems,
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * Add or update anime in user's list.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'status' => ['required', Rule::in(['watching', 'completed', 'on_hold', 'dropped', 'plan_to_watch'])],
            'score' => 'nullable|integer|min:1|max:10',
            'note' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $list = $user->getOrCreateDefaultAnimeList();

        // Check if anime already exists in list
        $item = $list->items()->where('post_id', $request->post_id)->first();

        if ($item) {
            // Update existing item
            $item->update($request->only(['status', 'score', 'note']));
        } else {
            // Create new item
            $item = $list->items()->create($request->only(['post_id', 'status', 'score', 'note']));
        }

        // Load the anime post for response
        $item->load('post:id,title,mal_id,type,episodes,status,images,slug');

        return response()->json([
            'message' => $item->wasRecentlyCreated ? 'Anime added to list' : 'Anime updated in list',
            'item' => [
                'id' => $item->id,
                'anime' => [
                    'id' => $item->post->id,
                    'title' => $item->post->title,
                    'display_title' => $item->post->display_title,
                    'mal_id' => $item->post->mal_id,
                    'type' => $item->post->type,
                    'episodes' => $item->post->episodes,
                    'status' => $item->post->status,
                    'images' => $item->post->images,
                    'slug' => $item->post->slug,
                ],
                'status' => $item->status,
                'score' => $item->score,
                'note' => $item->note,
                'updated_at' => $item->updated_at->format('Y-m-d'),
            ],
        ], $item->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Update anime list item.
     */
    public function update(Request $request, UserAnimeListItem $item): JsonResponse
    {
        // Check if the item belongs to the authenticated user
        if ($item->list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => ['sometimes', Rule::in(['watching', 'completed', 'on_hold', 'dropped', 'plan_to_watch'])],
            'score' => 'nullable|integer|min:1|max:10',
            'note' => 'nullable|string|max:1000',
        ]);

        $item->update($request->only(['status', 'score', 'note']));
        $item->load('post:id,title,mal_id,type,episodes,status,images,slug');

        return response()->json([
            'message' => 'Anime updated successfully',
            'item' => [
                'id' => $item->id,
                'anime' => [
                    'id' => $item->post->id,
                    'title' => $item->post->title,
                    'display_title' => $item->post->display_title,
                    'mal_id' => $item->post->mal_id,
                    'type' => $item->post->type,
                    'episodes' => $item->post->episodes,
                    'status' => $item->post->status,
                    'images' => $item->post->images,
                    'slug' => $item->post->slug,
                ],
                'status' => $item->status,
                'score' => $item->score,
                'note' => $item->note,
                'updated_at' => $item->updated_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Remove anime from user's list.
     */
    public function destroy(Request $request, UserAnimeListItem $item): JsonResponse
    {
        // Check if the item belongs to the authenticated user
        if ($item->list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $animeTitle = $item->post->display_title;
        $item->delete();

        return response()->json([
            'message' => "'{$animeTitle}' removed from your list",
        ]);
    }

    /**
     * Get user's anime list statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $list = $user->getOrCreateDefaultAnimeList();

        // Get basic stats
        $stats = $list->stats;

        // Calculate additional stats
        $completedItems = $list->items()->completed()->with('post')->get();
        
        $totalEpisodes = $completedItems->sum(function ($item) {
            return $item->post->episodes ?? 0;
        });

        $averageScore = $list->average_score;

        // Estimate time spent (assuming 24 minutes per episode)
        $timeSpentMinutes = $totalEpisodes * 24;
        $timeSpentDays = round($timeSpentMinutes / (24 * 60), 1);

        // Get top genres from completed anime
        $topGenres = $completedItems
            ->flatMap(function ($item) {
                return $item->post->genres ?? [];
            })
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->keys()
            ->toArray();

        // Count completed this year
        $completedThisYear = $list->items()
            ->completed()
            ->whereYear('updated_at', now()->year)
            ->count();

        return response()->json([
            'total_anime' => $stats['total'],
            'total_episodes' => $totalEpisodes,
            'average_score' => $averageScore,
            'time_spent_days' => $timeSpentDays,
            'status_breakdown' => [
                'watching' => $stats['watching'],
                'completed' => $stats['completed'],
                'on_hold' => $stats['on_hold'],
                'dropped' => $stats['dropped'],
                'plan_to_watch' => $stats['plan_to_watch'],
            ],
            'top_genres' => $topGenres,
            'completed_this_year' => $completedThisYear,
        ]);
    }
}
