<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimeGroup extends Model
{
    //
    protected $fillable = ['name', 'description'];
    public $timestamps = false;

    /**
     * Quan hệ hasMany với AnimeGroupPost
     */
    public function groupPosts()
    {
        return $this->hasMany(AnimeGroupPost::class, 'group_id');
    }

    /**
     * Quan hệ belongsToMany với Post thông qua AnimeGroupPost
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'anime_group_posts', 'group_id', 'post_id')
                    ->withPivot('position', 'note')
                    ->orderBy('anime_group_posts.position');
    }
}
