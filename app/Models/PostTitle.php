<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPostTitle
 */
class PostTitle extends Model
{
    
    //     CREATE TABLE `anime_post_titles` (
    //   `id` int NOT NULL,
    //   `post_id` int NOT NULL,
    //   `title` varchar(255) NOT NULL,
    //   `type` enum('Default','Synonym','Official','Alternative') NOT NULL DEFAULT 'Official',
    //   `language` char(2) DEFAULT NULL,
    //   `is_primary` tinyint(1) DEFAULT '0',
    //   `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    //   `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    //  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    protected $table = 'anime_post_titles';
    protected $fillable = [
        'post_id',
        'title',
        'type',
        'language',
        'is_primary',
    ];
    
    /**
     * Get the post that owns this title.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
}
