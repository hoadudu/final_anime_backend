<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable 
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens; 
    // use SoftDeletes;
    

    

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the anime lists for the user.
     */
    public function animeLists()
    {
        return $this->hasMany(UserAnimeList::class);
    }

    /**
     * Get the default anime list for the user.
     */
    public function defaultAnimeList()
    {
        return $this->hasOne(UserAnimeList::class)->where('is_default', true);
    }

    /**
     * Get or create the default anime list for the user.
     */
    public function getOrCreateDefaultAnimeList(): UserAnimeList
    {
        return $this->defaultAnimeList()->firstOrCreate([
            'user_id' => $this->id,
            'is_default' => true,
        ], [
            'name' => 'My List',
            'type' => 'default',
            'visibility' => 'private',
        ]);
    }

    public function canAccessFilament(): bool
    {
        return str_ends_with($this->email, 'admin@vlogphim.net') && $this->hasVerifiedEmail();
    }
}
