<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\PostCharacter;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Comment;

/**
 * @mixin IdeHelperPost
 */
class Post extends Model implements HasMedia
{
    // * The table associated with the model.
    // `id`, `mal_id`, `title`, `slug`, `type`, `source`, `episodes`, `status`, `airing`, `aired_from`, `aired_to`, `duration`, `rating`, `synopsis`, `background`, `season`, `broadcast`, `approved`, `created_at`, `updated_at`
    use SoftDeletes;
    use InteractsWithMedia;
    protected $table = 'anime_posts';
    protected $fillable = [
        'mal_id',
        'title',
        'slug',
        'subtitle_directory',
        'type',
        'source',
        'episodes',
        'status',
        'airing',
        'aired_from',
        'aired_to',
        'duration',
        'rating',
        'synopsis',
        'background',
        'season',
        'broadcast',
        'external',
        'approved',
        'film_id',
        'facebook_comment',
        'film_tag',
        'film_viewed',
        'film_year',
        'film_country',
        'film_lb',
        'film_type',

            

    ];

    protected $casts = [        
        'external' => 'array',
    ];

    /**
     * Boot the model to auto-sync title from primary title and handle subtitle directory
     */
    protected static function boot()
    {
        parent::boot();
        
        static::created(function ($post) {
            // Set subtitle directory after creating (when ID is available)
            if (empty($post->subtitle_directory)) {
                $post->update([
                    'subtitle_directory' => "subtitles/{$post->id}_{$post->slug}"
                ]);
            }
        });

        static::updating(function ($post) {
            // Handle slug changes - move files automatically
            if ($post->isDirty('slug') && !$post->isDirty('subtitle_directory')) {
                $oldDirectory = $post->getOriginal('subtitle_directory') ?? "subtitles/{$post->id}_{$post->getOriginal('slug')}";
                $newDirectory = "subtitles/{$post->id}_{$post->slug}";
                
                // Move directory if it exists and new directory doesn't exist
                if (Storage::disk('public')->exists($oldDirectory) && 
                    !Storage::disk('public')->exists($newDirectory)) {
                    
                    try {
                        // Create parent directory if needed
                        Storage::disk('public')->makeDirectory(dirname($newDirectory));
                        
                        // Move the entire directory
                        $files = Storage::disk('public')->allFiles($oldDirectory);
                        foreach ($files as $file) {
                            $newFile = str_replace($oldDirectory, $newDirectory, $file);
                            Storage::disk('public')->move($file, $newFile);
                        }
                        
                        // Remove old directory if empty
                        if (empty(Storage::disk('public')->allFiles($oldDirectory))) {
                            Storage::disk('public')->deleteDirectory($oldDirectory);
                        }
                        
                    } catch (\Exception $e) {
                        Log::error("Failed to move subtitle directory for post {$post->id}: " . $e->getMessage());
                    }
                }
                
                $post->subtitle_directory = $newDirectory;
            }
        });
        
        static::saving(function ($post) {
            // Auto-set title from primary title if not set
            if (!$post->title && $post->exists) {
                $post->title = $post->getPrimaryTitleFromRelation();
            }
        });
    }

    /**
     * Get primary title from titles relationship
     */
    public function getPrimaryTitleFromRelation(): ?string
    {
        $primaryTitle = $this->titles()
            ->where('is_primary', true)
            ->first();
            
        if ($primaryTitle) {
            return $primaryTitle->title;
        }
        
        // Fallback to first title if no primary
        $firstTitle = $this->titles()->first();
        return $firstTitle?->title;
    }

    /**
     * Get display title (for backwards compatibility)
     * Returns the main title field directly
     */
    /**
     * Get display title (for backwards compatibility)
     * Returns the main title field directly
     */
    public function getDisplayTitleAttribute(): ?string
    {
        return $this->title ?? $this->getPrimaryTitleFromRelation();
    }

    /**
     * Get all comments for this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get visible comments for this post.
     */
    public function visibleComments(): HasMany
    {
        return $this->hasMany(Comment::class)->visible();
    }

    /**
     * Get comments count for this post.
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->visibleComments()->count();
    }

    /**
     * Get all titles for this post.
     */
    public function titles(): HasMany
    {
        return $this->hasMany(PostTitle::class, 'post_id', 'id');
    }

    /**
     * Get all images for this post.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class, 'post_id', 'id');
    }


    public function videos(): HasMany
    {
        return $this->hasMany(PostVideo::class, 'post_id', 'id');
    }

    /**
     * Get all morphable relationships for this post.
     */
    public function morphables(): HasMany
    {
        return $this->hasMany(PostMorphable::class, 'post_id', 'id');
    }

    /**
     * Get all producer relationships for this post.
     */
    public function postProducers(): HasMany
    {
        return $this->hasMany(PostProducer::class, 'post_id', 'id');
    }

    /**
     * Get all character relationships for this post.
     */
    public function postCharacters(): HasMany
    {
        return $this->hasMany(PostCharacter::class, 'post_id', 'id');
    }

    /**
     * Get characters for this post.
     */
    public function characters()
    {
        return $this->postCharacters()
            ->with('character');
    }

    /**
     * Get all episodes for this post.
     */
    public function episodeList(): HasMany
    {
        return $this->hasMany(Episode::class, 'post_id', 'id');
    }

    /**
     * Get main characters for this post.
     */
    public function mainCharacters()
    {
        return $this->postCharacters()
            ->where('role', 'main')
            ->with('character');
    }

    /**
     * Get supporting characters for this post.
     */
    public function supportingCharacters()
    {
        return $this->postCharacters()
            ->where('role', 'supporting')
            ->with('character');
    }

    /**
     * Get producers for this post.
     */
    public function producers()
    {
        return $this->postProducers()
            ->where('type', 'producer')
            ->with('producer');
    }

    /**
     * Get licensors for this post.
     */
    public function licensors()
    {
        return $this->postProducers()
            ->where('type', 'licensor')
            ->with('producer');
    }

    /**
     * Get studios for this post.
     */
    public function studios()
    {
        return $this->postProducers()
            ->where('type', 'studio')
            ->with('producer');
    }

    /**
     * Get all genres for this post.
     */
    public function genres()
    {
        return $this->morphables()
            ->where('morphable_type', Genres::class)
            ->with('morphable');
    }

    /**
     * Get genres by type
     */
    public function getGenresByType($type = 'genres')
    {
        return $this->genres()
            ->whereHas('morphable', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->get()
            ->pluck('morphable');
    }

    /**
     * Get all genre types as collections
     */
    public function getGenresAttribute()
    {
        return $this->getGenresByType('genres');
    }

    public function getExplicitGenresAttribute()
    {
        return $this->getGenresByType('explicit_genres');
    }

    public function getThemesAttribute()
    {
        return $this->getGenresByType('themes');
    }

    public function getDemographicsAttribute()
    {
        return $this->getGenresByType('demographics');
    }

    /**
     * Attach genres to post from API data
     */
    public function attachGenresFromApiData($apiData)
    {
        
        $apiData = $apiData['data'];
        // Clear existing genres
        PostMorphable::detachAllGenresFromPost($this->id);

        $genreTypes = ['genres', 'explicit_genres', 'themes', 'demographics'];

        foreach ($genreTypes as $type) {
            
            if (isset($apiData[$type]) && is_array($apiData[$type])) {
                foreach ($apiData[$type] as $genreData) {
                    if (isset($genreData['mal_id'])) {
                        // Find genre by mal_id and type
                        
                        $genre = Genres::where('mal_id', $genreData['mal_id'])
                            ->where('type', $type)
                            ->first();

                        if ($genre) {
                            PostMorphable::attachGenreToPost($this->id, $genre->id, $type);
                        }
                    }
                }
            }
        }
    }


    /**
     * Attach producers to post from API data
     */
    public function attachProducersFromApiData($apiData)
    {
        $apiData = $apiData['data'];

        // Clear existing producers
        PostProducer::detachAllProducersFromPost($this->id);

        $producerTypes = ['producers', 'licensors', 'studios'];

        foreach ($producerTypes as $type) {
            if (isset($apiData[$type]) && is_array($apiData[$type])) {
                foreach ($apiData[$type] as $producerData) {
                    if (isset($producerData['mal_id'])) {
                        // Find producer by mal_id
                        $producer = Producer::where('mal_id', $producerData['mal_id'])->first();

                        if (!$producer) {
                            // Create producer if it doesn't exist
                            $producer = Producer::create([
                                'mal_id' => $producerData['mal_id'],
                                'titles' => [
                                    [
                                        'type' => 'Default',
                                        'title' => $producerData['name'] ?? 'Unknown Producer',
                                    ]
                                ],
                                'images' => [
                                    'jpg' => [
                                        'image_url' => $producerData['images']['jpg']['image_url'] ?? null,
                                    ]
                                ],
                                'established' => null,
                                'about' => null,
                                'slug' => \Illuminate\Support\Str::slug($producerData['name'] ?? 'unknown-producer'),
                            ]);
                        }

                        // Attach producer to post
                        $producerType = rtrim($type, 's'); // Remove 's' from plural
                        PostProducer::attachProducerToPost($this->id, $producer->id, $producerType);
                    }
                }
            }
        }
    }


    /**
     * Attach characters to post from API data
     */
    public function attachCharactersFromApiData($apiData)
    {
        $apiData = $apiData['data'];

        // Clear existing character relationships
        PostCharacter::detachAllCharactersFromPost($this->id);

        if (isset($apiData['characters']) && is_array($apiData['characters'])) {
            foreach ($apiData['characters'] as $characterEntry) {
                if (isset($characterEntry['character']['mal_id'])) {
                    $characterMalId = $characterEntry['character']['mal_id'];
                    $role = $characterEntry['role'] ?? 'other';

                    // Normalize role
                    $normalizedRole = $this->normalizeCharacterRole($role);

                    // Find character by mal_id
                    $character = Character::where('mal_id', $characterMalId)->first();

                    if (!$character) {
                        // Create character if it doesn't exist
                        $character = Character::create([
                            'mal_id' => $characterMalId,
                            'images' => [
                                'jpg' => [
                                    'image_url' => $characterEntry['character']['images']['jpg']['image_url'] ?? null,
                                ],
                                'webp' => [
                                    'image_url' => $characterEntry['character']['images']['webp']['image_url'] ?? null,
                                    'small_image_url' => $characterEntry['character']['images']['webp']['small_image_url'] ?? null,
                                ],
                            ],
                            'name' => $characterEntry['character']['name'] ?? '',
                            'name_kanji' => null,
                            'nicknames' => [],
                            'about' => null,
                            'slug' => \Illuminate\Support\Str::slug($characterEntry['character']['name'] ?? 'unknown-character'),
                        ]);
                    }

                    // Attach character to post
                    PostCharacter::attachCharacterToPost($this->id, $character->id, $normalizedRole);
                }
            }
        }
    }

    /**
     * Normalize character role from API
     */
    private function normalizeCharacterRole(string $role): string
    {
        $role = strtolower($role);

        if (str_contains($role, 'main')) {
            return 'main';
        } elseif (str_contains($role, 'supporting') || str_contains($role, 'support')) {
            return 'supporting';
        } elseif (str_contains($role, 'minor')) {
            return 'minor';
        } else {
            return 'other';
        }
    }


    /**
     * Get subtitle directory for this post
     */
    public function getSubtitleDirectory(): string
    {
        return $this->subtitle_directory ?? "subtitles/{$this->id}_{$this->slug}";
    }

    /**
     * Ensure subtitle directory exists
     */
    public function ensureSubtitleDirectoryExists(): void
    {
        $directory = $this->getSubtitleDirectory();
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
    }

    /**
     * Model Post implement HasMedia interface
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('post_poster')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('post_cover')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('post_gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
