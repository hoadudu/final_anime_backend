<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeCollectionPost extends Model
{
    //  `id`, `post_id`, `collection_id`, `backdrop_image`, `position`
    protected $table = 'anime_collection_posts';
    public $timestamps = false;
    protected $fillable = [
        'post_id',
        'collection_id',
        'backdrop_image',
        'position',
    ];

    /**
     * Get the collection that owns this post relationship
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(AnimeCollection::class, 'collection_id');
    }

    /**
     * Get the post
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}