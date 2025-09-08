<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperPostMorphable
 */
class PostMorphable extends Model
{
    protected $table = 'anime_post_morphables';
    public $timestamps = true;
    
    protected $fillable = [
        'post_id',
        'morphable_id',
        'morphable_type',
    ];

    /**
     * Get the post that owns this morphable.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Get the morphable model (Genres, etc.)
     */
    public function morphable(): MorphTo
    {
        return $this->morphTo('morphable');
    }

    /**
     * Helper method để tạo liên kết post với genre
     */
    public static function attachGenreToPost($postId, $genreId, $genreType = 'genres')
    {
        return self::firstOrCreate([
            'post_id' => $postId,
            'morphable_id' => $genreId,
            'morphable_type' => Genres::class,
        ]);
    }

    /**
     * Helper method để xóa tất cả genres của post
     */
    public static function detachAllGenresFromPost($postId)
    {
        return self::where('post_id', $postId)
                  ->where('morphable_type', Genres::class)
                  ->delete();
    }
}
