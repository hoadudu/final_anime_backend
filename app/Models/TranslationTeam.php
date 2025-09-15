<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationTeam extends Model
{
    protected $table = 'anime_translation_teams';

    protected $fillable = [
        'name',
        'home',
        'logo',
    ];
    public $timestamps = false;
}
