<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Get comments for an anime post.
     */
    public function index(Request $request, Post $post): JsonResponse
    {
        $perPage = min($request->get('per_page', 10), 50);
        $parentId = $request->get('parent_id');
        
        $query = $post->comments()
            ->visible()
            ->with(['user', 'replies' => function($query) {
                $query->visible()->with('user')->latest();
            }]);
            
        if ($parentId === null || $parentId === 'null') {
            $query->root();
        } else {
            $query->where('parent_id', $parentId);
        }
        
        $comments = $query->latest()->paginate($perPage);
        
        // Add user reactions if authenticated
        if (Auth::check()) {
            $userId = Auth::id();
            foreach ($comments as $comment) {
                $comment->user_reaction = $comment->getUserReaction($userId);
                foreach ($comment->replies as $reply) {
                    $reply->user_reaction = $reply->getUserReaction($userId);
                }
            }
        }
        
        return response()->json([
            'data' => $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                    'likes_count' => $comment->likes_count,
                    'dislikes_count' => $comment->dislikes_count,
                    'user_reaction' => $comment->user_reaction ?? null,
                    'replies_count' => $comment->replies_count,
                    'created_at' => $comment->created_at->toISOString(),
                    'formatted_date' => $comment->formatted_date,
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                            ],
                            'likes_count' => $reply->likes_count,
                            'dislikes_count' => $reply->dislikes_count,
                            'user_reaction' => $reply->user_reaction ?? null,
                            'created_at' => $reply->created_at->toISOString(),
                            'formatted_date' => $reply->formatted_date,
                        ];
                    }),
                ];
            }),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'last_page' => $comments->lastPage(),
            ]
        ]);
    }
    
    /**
     * Store a new comment.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000|min:2',
            'parent_id' => 'nullable|exists:comments,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Rate limiting check (simplified - in production use Redis)
        $recentComments = Comment::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subMinute())
            ->count();
            
        if ($recentComments >= 5) {
            return response()->json([
                'message' => 'Too many comments. Please wait before posting again.'
            ], 429);
        }
        
        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);
        
        $comment->load('user');
        
        return response()->json([
            'message' => 'Comment posted successfully',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ],
                'likes_count' => 0,
                'dislikes_count' => 0,
                'user_reaction' => null,
                'replies_count' => 0,
                'created_at' => $comment->created_at->toISOString(),
                'formatted_date' => $comment->formatted_date,
                'replies' => [],
            ]
        ], 201);
    }
    
    /**
     * Like a comment.
     */
    public function like(Comment $comment): JsonResponse
    {
        if ($comment->is_hidden) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        
        $user = Auth::user();
        $liked = $comment->likeBy($user);
        
        return response()->json([
            'message' => $liked ? 'Comment liked' : 'Like removed',
            'liked' => $liked,
            'likes_count' => $comment->fresh()->likes_count,
            'dislikes_count' => $comment->fresh()->dislikes_count,
        ]);
    }
    
    /**
     * Dislike a comment.
     */
    public function dislike(Comment $comment): JsonResponse
    {
        if ($comment->is_hidden) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        
        $user = Auth::user();
        $disliked = $comment->dislikeBy($user);
        
        return response()->json([
            'message' => $disliked ? 'Comment disliked' : 'Dislike removed',
            'disliked' => $disliked,
            'likes_count' => $comment->fresh()->likes_count,
            'dislikes_count' => $comment->fresh()->dislikes_count,
        ]);
    }
    
    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $user = Auth::user();
        
        if (!$comment->canBeDeletedBy($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $comment->delete();
        
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
