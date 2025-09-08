<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StreamSubtitle extends Model
{
    protected $fillable = [
        'stream_id',
        'language',
        'language_name', 
        'type',
        'url',
        'source',
        'is_default',
        'is_active',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    // Relationships
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    // Query Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('language');
    }

    // Helper Methods
    public function getDisplayNameAttribute(): string
    {
        $name = $this->language_name ?: $this->language;
        $source = $this->source !== 'manual' ? " ({$this->source})" : '';
        return $name . $source;
    }

    public function getContentTypeAttribute(): string
    {
        return match($this->type) {
            'vtt' => 'text/vtt',
            'srt' => 'application/x-subrip',
            'ass', 'ssa' => 'text/x-ssa',
            'txt' => 'text/plain',
            default => 'text/plain',
        };
    }

    // Metadata helpers
    public function getEncoding(): string
    {
        return $this->meta['encoding'] ?? 'UTF-8';
    }

    public function getOffset(): int
    {
        return $this->meta['offset'] ?? 0; // milliseconds
    }

    public function getFps(): float
    {
        return $this->meta['fps'] ?? 23.976;
    }

    // Boot method for automatic language_name setting
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subtitle) {
            if (empty($subtitle->language_name)) {
                $subtitle->language_name = static::getLanguageName($subtitle->language);
            }
        });

        static::updating(function ($subtitle) {
            if ($subtitle->isDirty('language') && empty($subtitle->language_name)) {
                $subtitle->language_name = static::getLanguageName($subtitle->language);
            }
        });
    }

    // Language name mapping
    protected static function getLanguageName(string $code): string
    {
        return match($code) {
            'vi' => 'Vietnamese',
            'en' => 'English',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
            'th' => 'Thai',
            'fr' => 'French',
            'de' => 'German',
            'es' => 'Spanish',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            default => ucfirst($code),
        };
    }
}
