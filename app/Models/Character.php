<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($character) {
            if (empty($character->slug) && !empty($character->name)) {
                $character->slug = static::generateUniqueSlug($character->name);
            }
        });
    }

    /**
     * Generate a unique slug for the character
     */
    protected static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
