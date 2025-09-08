<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPostVideo
 */
class PostVideo extends Model
{
    protected $table = 'anime_post_videos';
    
    protected $fillable = [
        'post_id',
        'title',
        'url',
        'meta',
        'video_type',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public $timestamps = true;

    /**
     * Get the post that owns this video.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Insert video from Jikan API data
     */
    public static function insertFromJikanData($postId, $videoData, $videoType)
    {
        return self::create([
            'post_id' => $postId,
            'title' => $videoData['title'] ?? null,
            'url' => $videoData['url'] ?? null,
            'meta' => $videoData['meta'] ?? null,
            'video_type' => $videoType,
        ]);
    }

    /**
     * Helper method để lấy YouTube ID từ URL
     */
    public function getYoutubeIdAttribute()
    {
        if (!$this->url) {
            return null;
        }

        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $this->url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Helper method để lấy embed URL
     */
    public function getEmbedUrlAttribute()
    {
        $youtubeId = $this->youtube_id;
        if ($youtubeId) {
            return "https://www.youtube.com/embed/{$youtubeId}";
        }

        return null;
    }

    /**
     * Helper method để lấy thumbnail
     */
    public function getThumbnailUrlAttribute()
    {
        $youtubeId = $this->youtube_id;
        if ($youtubeId) {
            return "https://img.youtube.com/vi/{$youtubeId}/maxresdefault.jpg";
        }

        // Check if meta has images
        if (isset($this->meta['images']['image_url'])) {
            return $this->meta['images']['image_url'];
        }

        return null;
    }
}
