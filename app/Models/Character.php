<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCharacter
 */
class Character extends Model
{
    // `id`, `mal_id`, `images`, `name`, `name_kanji`, `nicknames`, `about`, `slug`
    protected $table = 'anime_characters';
    protected $fillable = [
        'mal_id',
        'images',
        'name',
        'name_kanji',
        'nicknames',
        'about',
        'slug',
    ];
    
    protected $casts = [
        'images' => 'array',
        'nicknames' => 'array'
          
    ];

    public $timestamps = false;

}
