<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperPostImage
 */
class PostImage extends Model
{
    protected $table = 'anime_post_images';
    
    protected $fillable = [
        'post_id',
        'image_url',
        'alt_text',
        'image_type',
        'language',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the post that owns this image.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Insert image from URL
     */
    public static function insertFromUrl($postId, $url, $language = null, $isPrimary = false, $imageType = 'poster', $altText = null)
    {
        // Validate input parameters to avoid array to string conversion errors
        if (is_array($url)) {
            throw new \InvalidArgumentException('URL cannot be an array');
        }
        if (is_array($language)) {
            throw new \InvalidArgumentException('Language cannot be an array');
        }
        if (is_array($imageType)) {
            throw new \InvalidArgumentException('Image type cannot be an array');
        }
        if (is_array($altText)) {
            throw new \InvalidArgumentException('Alt text cannot be an array');
        }

        return self::create([
                'post_id'    => $postId,
                'image_url'  => $url,
                'language'   => $language,
                'is_primary' => $isPrimary,
                'image_type' => $imageType,
                'alt_text'   => $altText,
        ]);
    }

    
}
