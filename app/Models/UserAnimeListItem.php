<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnimeListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'post_id',
        'status',
        'score',
        'note',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    /**
     * Get the anime list that owns this item.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(UserAnimeList::class, 'list_id');
    }

    /**
     * Get the anime post for this item.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the anime post for this item (alias).
     */
    public function anime(): BelongsTo
    {
        return $this->post();
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get watching items.
     */
    public function scopeWatching($query)
    {
        return $query->where('status', 'watching');
    }

    /**
     * Scope to get completed items.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get items with scores.
     */
    public function scopeWithScore($query)
    {
        return $query->whereNotNull('score');
    }

    /**
     * Get formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'watching' => 'Watching',
            'completed' => 'Completed',
            'on_hold' => 'On Hold',
            'dropped' => 'Dropped',
            'plan_to_watch' => 'Plan to Watch',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Get the score with stars or null.
     */
    public function getScoreDisplayAttribute(): ?string
    {
        if (!$this->score) {
            return null;
        }

        return $this->score . '/10';
    }
}
