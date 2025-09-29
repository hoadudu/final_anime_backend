<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimeGroupPost extends Model
{
    // `id`, `group_id`, `post_id`, `position`, `note`
    protected $fillable = ['group_id', 'post_id', 'position', 'note'];
    public $timestamps = false;

    /**
     * Quan hệ với AnimeGroup
     */
    public function group()
    {
        return $this->belongsTo(AnimeGroup::class, 'group_id');
    }

    /**
     * Quan hệ với Post
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
