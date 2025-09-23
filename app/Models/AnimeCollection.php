<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AnimeCollection extends Model
{
    // `id`, `title`, `slug`, `description`, `position`, `type`, `created_at`, `updated_at`
    protected $table = 'anime_collections';
    public $timestamps = true;
    protected $fillable = [
        'title',
        'description',
        'slug',
        'position',
        'type',
    ];

    /**
     * Get all posts in this collection
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'anime_collection_posts', 'collection_id', 'post_id')
            ->withPivot('backdrop_image', 'position')
            ->orderBy('anime_collection_posts.position');
    }

    /**
     * Get collection posts relationship
     */
    public function collectionPosts()
    {
        return $this->hasMany(AnimeCollectionPost::class, 'collection_id');
    }
}
