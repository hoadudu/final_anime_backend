<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAnimeList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'is_default',
        'visibility',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the anime list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this anime list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(UserAnimeListItem::class, 'list_id');
    }

    /**
     * Get statistics for this list.
     */
    public function getStatsAttribute(): array
    {
        $stats = $this->items()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'watching' => $stats['watching'] ?? 0,
            'completed' => $stats['completed'] ?? 0,
            'on_hold' => $stats['on_hold'] ?? 0,
            'dropped' => $stats['dropped'] ?? 0,
            'plan_to_watch' => $stats['plan_to_watch'] ?? 0,
            'total' => array_sum($stats),
        ];
    }

    /**
     * Get the average score for completed anime in this list.
     */
    public function getAverageScoreAttribute(): ?float
    {
        $averageScore = $this->items()
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->avg('score');

        return $averageScore ? round($averageScore, 2) : null;
    }

    /**
     * Scope to get default lists.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get public lists.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }
}
