<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimeConfig extends Model
{
    // `id`, `config_key`, `config_value`, `description`, `updated_at`
    protected $fillable = ['config_key', 'config_value', 'description'];
    public $timestamps = false;
    protected $table = 'anime_configs';
    protected $casts = [
        'config_value' => 'array',
    ];
}
