<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPostProducer
 */
class PostProducer extends Model
{
    protected $table = 'anime_post_producers';
    protected $fillable = [
        'post_id',
        'producer_id',
        'type',
    ];
    public $timestamps = false;
    /**
     * Get the post that owns this producer relationship.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Get the producer.
     */
    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class, 'producer_id', 'id');
    }

    /**
     * Helper method to attach producer to post
     */
    public static function attachProducerToPost($postId, $producerId, $type = 'producer')
    {
        return self::firstOrCreate([
            'post_id' => $postId,
            'producer_id' => $producerId,
            'type' => $type,
        ]);
    }

    /**
     * Helper method to detach all producers from post
     */
    public static function detachAllProducersFromPost($postId)
    {
        return self::where('post_id', $postId)->delete();
    }

    /**
     * Helper method to detach producers by type from post
     */
    public static function detachProducersByTypeFromPost($postId, $type)
    {
        return self::where('post_id', $postId)->where('type', $type)->delete();
    }
}
