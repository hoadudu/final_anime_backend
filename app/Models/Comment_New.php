<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'parent_id',
        'content',
        'is_approved',
        'is_hidden',
        'likes_count',
        'dislikes_count',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_hidden' => 'boolean',
        'likes_count' => 'integer',
        'dislikes_count' => 'integer',
    ];

    /**
     * Get the user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the anime post this comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('is_hidden', false);
    }

    /**
     * Get all replies including hidden ones (for admin).
     */
    public function allReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the likes/dislikes for this comment.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Get the reports for this comment.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    /**
     * Get user's reaction to this comment.
     */
    public function getUserReaction(?int $userId = null): ?string
    {
        if (!$userId) {
            return null;
        }

        $like = $this->likes()->where('user_id', $userId)->first();
        return $like ? $like->type : null;
    }

    /**
     * Check if user can delete this comment.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->canAccessFilament();
    }

    /**
     * Scope to get only visible comments.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false)->where('is_approved', true);
    }

    /**
     * Scope to get only root comments (not replies).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get comments with user and replies.
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['user', 'replies.user']);
    }

    /**
     * Get formatted created date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get total replies count.
     */
    public function getRepliesCountAttribute(): int
    {
        return $this->replies()->count();
    }

    /**
     * Like this comment by a user.
     */
    public function likeBy(User $user): bool
    {
        $existingLike = $this->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            if ($existingLike->type === 'like') {
                // Unlike
                $existingLike->delete();
                $this->decrement('likes_count');
                return false;
            } else {
                // Change from dislike to like
                $existingLike->update(['type' => 'like']);
                $this->increment('likes_count');
                $this->decrement('dislikes_count');
                return true;
            }
        } else {
            // New like
            $this->likes()->create(['user_id' => $user->id, 'type' => 'like']);
            $this->increment('likes_count');
            return true;
        }
    }

    /**
     * Dislike this comment by a user.
     */
    public function dislikeBy(User $user): bool
    {
        $existingLike = $this->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            if ($existingLike->type === 'dislike') {
                // Remove dislike
                $existingLike->delete();
                $this->decrement('dislikes_count');
                return false;
            } else {
                // Change from like to dislike
                $existingLike->update(['type' => 'dislike']);
                $this->increment('dislikes_count');
                $this->decrement('likes_count');
                return true;
            }
        } else {
            // New dislike
            $this->likes()->create(['user_id' => $user->id, 'type' => 'dislike']);
            $this->increment('dislikes_count');
            return true;
        }
    }
}
