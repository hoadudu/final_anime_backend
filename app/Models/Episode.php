<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    use SoftDeletes;

    protected $table = 'anime_episodes';

    protected $fillable = [
        'post_id',
        'titles',
        'episode_number',
        'absolute_number',
        'thumbnail',
        'release_date',
        'description',
        'type',
        'group',
        'sort_number',
    ];

    protected $casts = [
        'titles' => 'array',
        'release_date' => 'date',
        'episode_number' => 'integer',
        'absolute_number' => 'integer',
        'group' => 'integer',
        'sort_number' => 'integer',
    ];

    protected $attributes = [
        'type' => 'regular',
        'group' => 1,
    ];

    /**
     * Get the post that owns the episode.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Get all streams for this episode.
     */
    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class, 'episode_id', 'id');
    }

    /**
     * Get active streams for this episode.
     */
    public function activeStreams(): HasMany
    {
        return $this->streams()->where('is_active', true)->ordered();
    }

    /**
     * Scope to filter episodes by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter regular episodes.
     */
    public function scopeRegular($query)
    {
        return $query->where('type', 'regular');
    }

    /**
     * Scope to filter filler episodes.
     */
    public function scopeFiller($query)
    {
        return $query->where('type', 'filler');
    }

    /**
     * Scope to filter recap episodes.
     */
    public function scopeRecap($query)
    {
        return $query->where('type', 'recap');
    }

    /**
     * Scope to filter special episodes.
     */
    public function scopeSpecial($query)
    {
        return $query->where('type', 'special');
    }

    /**
     * Scope to filter episodes by group.
     */
    public function scopeInGroup($query, int $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get the episode title in a specific language or fallback to default.
     */
    public function getTitle(string $lang = 'en'): string
    {
        if (!$this->titles) {
            return "Episode {$this->episode_number}";
        }

        return $this->titles[$lang] ?? $this->titles['en'] ?? $this->titles['ja'] ?? "Episode {$this->episode_number}";
    }

    /**
     * Get the main title (first available title).
     */
    public function getMainTitle(): string
    {
        if (!$this->titles) {
            return "Episode {$this->episode_number}";
        }

        return reset($this->titles);
    }

    /**
     * Check if episode is regular type.
     */
    public function isRegular(): bool
    {
        return $this->type === 'regular';
    }

    /**
     * Check if episode is filler type.
     */
    public function isFiller(): bool
    {
        return $this->type === 'filler';
    }

    /**
     * Check if episode is recap type.
     */
    public function isRecap(): bool
    {
        return $this->type === 'recap';
    }

    /**
     * Check if episode is special type.
     */
    public function isSpecial(): bool
    {
        return $this->type === 'special';
    }

    /**
     * Get the display number for the episode.
     */
    public function getDisplayNumber(): string
    {
        if ($this->episode_number) {
            return (string) $this->episode_number;
        }

        return $this->absolute_number ? (string) $this->absolute_number : 'Unknown';
    }

    /**
     * Get the sort key for ordering episodes.
     */
    public function getSortKey(): int
    {
        return $this->sort_number ?? $this->episode_number ?? $this->absolute_number ?? 0;
    }
}
