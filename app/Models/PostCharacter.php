<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPostCharacter
 */
class PostCharacter extends Model
{
    protected $table = 'anime_post_characters';
    protected $fillable = [
        'post_id',
        'character_id',
        'role',
    ];
    public $timestamps = false;
    /**
     * Get the post that owns this character relationship.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    /**
     * Get the character.
     */
    public function character()
    {
        return $this->belongsTo(Character::class, 'character_id', 'id');
    }

    /**
     * Helper method to attach character to post
     */
    public static function attachCharacterToPost($postId, $characterId, $role = 'main')
    {
        return self::firstOrCreate([
            'post_id' => $postId,
            'character_id' => $characterId,
            'role' => $role,
        ]);
    }

    /**
     * Helper method to detach all characters from post
     */
    public static function detachAllCharactersFromPost($postId)
    {
        return self::where('post_id', $postId)->delete();
    }

    /**
     * Helper method to detach characters by role from post
     */
    public static function detachCharactersByRoleFromPost($postId, $role)
    {
        return self::where('post_id', $postId)->where('role', $role)->delete();
    }
}
