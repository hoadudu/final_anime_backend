<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperProducer
 */
class Producer extends Model
{
    // `id`, `mal_id`, `slug`, `titles`, `images`, `established`, `about`
    protected $table = 'anime_producers';
    protected $fillable = [
        'mal_id',
        'slug',
        'titles',
        'images',
        'established',
        'about',
    ];
    
    protected $casts = [
        'titles' => 'array',
        'images' => 'array',        
    ];
}
