<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use SoftDeletes;
    protected $table = 'anime_genres';
    public $timestamps = false;
    protected $fillable = [
        'mal_id',
        'name',
        'name_vn',
        'slug',
        'description',
        'type',
    ];

    /**
     * Accessor for category (alias for type)
     */
    public function getCategoryAttribute()
    {
        return $this->type;
    }

    /**
     * Get localized name
     */
    public function getLocalizedName($lang = 'en')
    {
        return $lang === 'vi' ? $this->name_vn : $this->name;
    }

    /**
     * Scope theo type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Lấy danh sách genres
     */
    public static function getGenres()
    {
        return self::ofType('genres')->get();
    }

    /**
     * Lấy danh sách explicit genres
     */
    public static function getExplicitGenres()
    {
        return self::ofType('explicit_genres')->get();
    }

    /**
     * Lấy danh sách themes
     */
    public static function getThemes()
    {
        return self::ofType('themes')->get();
    }

    /**
     * Get the posts that use this genre through PostMorphable
     */
    public function posts()
    {
        return $this->morphMany(PostMorphable::class, 'morphable');
    }

    /**
     * Get the count of posts using this genre
     */
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }
}
