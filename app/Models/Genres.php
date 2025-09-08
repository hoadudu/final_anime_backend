<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperGenres
 */
class Genres extends Model
{
    
    use SoftDeletes;
    protected $table = 'anime_genres';
    public $timestamps = false;
    protected $fillable = [
        'mal_id',
        'name',
        'slug',
        'description',
        'type',
    ];

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
     * Lấy danh sách demographics
     */
    public static function getDemographics()
    {
        return self::ofType('demographics')->get();
    }
}
