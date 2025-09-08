<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Stream extends Model
{
    protected $table = 'anime_episode_streams';

    protected $fillable = [
        'episode_id',
        'server_name',
        'url',
        'meta',
        'stream_type',
        'quality',
        'language',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'stream_type' => 'direct',
        'quality' => 'auto',
        'language' => 'sub',
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * Get the episode that owns this stream.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id', 'id');
    }

    /**
     * Scope: Only active streams
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by stream type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('stream_type', $type);
    }

    /**
     * Scope: Filter by quality
     */
    public function scopeQuality(Builder $query, string $quality): Builder
    {
        return $query->where('quality', $quality);
    }

    /**
     * Scope: Filter by language
     */
    public function scopeLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /**
     * Scope: Ordered by sort order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Scope: Filter by server name
     */
    public function scopeServer(Builder $query, string $serverName): Builder
    {
        return $query->where('server_name', $serverName);
    }

    /**
     * Get display name for the stream
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        
        if ($this->server_name) {
            $parts[] = $this->server_name;
        }
        
        if ($this->quality !== 'auto') {
            $parts[] = $this->quality;
        }
        
        if ($this->language !== 'sub') {
            $parts[] = ucfirst($this->language);
        }
        
        return implode(' - ', $parts) ?: 'Stream';
    }

    /**
     * Check if stream is direct playable
     */
    public function isDirectPlayable(): bool
    {
        return in_array($this->stream_type, ['direct', 'hls', 'm3u8', 'dash']);
    }

    /**
     * Check if stream is embed
     */
    public function isEmbed(): bool
    {
        return $this->stream_type === 'embed';
    }

    /**
     * Get stream URL with metadata
     */
    public function getStreamUrl(): string
    {
        $url = $this->url;
        
        // Add metadata parameters if available
        if ($this->meta && isset($this->meta['params'])) {
            $params = http_build_query($this->meta['params']);
            $url .= (strpos($url, '?') !== false ? '&' : '?') . $params;
        }
        
        return $url;
    }

    /**
     * Get headers for stream request
     */
    public function getHeaders(): array
    {
        return $this->meta['headers'] ?? [];
    }
}
