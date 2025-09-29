<?php

namespace App\Helpers\Import;

use App\Models\Post;
use App\Models\PostTitle;
use App\Models\PostImage;
use App\Models\PostVideo;
use App\Models\Genre;
use App\Models\Producer;
use App\Models\PostProducer;
use App\Models\Character;
use App\Models\Episode;
use App\Models\PostCharacter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Database operations for import processes
 */
class DatabaseService
{
    /**
     * Create or update anime post
     */
    public function createOrUpdateAnime(array $basicInfo, array $titles, array $images, array $videos): Post
    {
        DB::beginTransaction();
        
        try {
            // Create or update main post
            $post = Post::updateOrCreate(
                ['mal_id' => $basicInfo['mal_id']],
                $basicInfo
            );

            // Handle titles
            $this->syncTitles($post, $titles);
            
            // Handle images
            $this->syncImages($post, $images);
            
            // Handle videos
            $this->syncVideos($post, $videos);

            DB::commit();
            
            Log::info("Successfully created/updated anime", [
                'post_id' => $post->id,
                'mal_id' => $post->mal_id
            ]);

            return $post;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to create/update anime", [
                'mal_id' => $basicInfo['mal_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Sync titles for a post and update main title
     */
    public function syncTitles(Post $post, array $titles): void
    {
        // Delete existing titles
        PostTitle::where('post_id', $post->id)->delete();
        
        $primaryTitle = null;
        
        // Create new titles
        foreach ($titles as $titleData) {
            $postTitle = PostTitle::create([
                'post_id' => $post->id,
                'type' => $titleData['type'],
                'title' => $titleData['title'],
                'language' => $titleData['language'] ?? null,
                'is_primary' => $titleData['type'] === 'Default',
            ]);
            
            // Set primary title (prefer Default, then Official)
            if ($titleData['type'] === 'Default' || 
                ($titleData['type'] === 'Official' && !$primaryTitle)) {
                $primaryTitle = $titleData['title'];
            }
        }
        
        // Update post's main title field
        if ($primaryTitle) {
            $post->update(['title' => $primaryTitle]);
        }
    }

    /**
     * Sync images for a post
     */
    public function syncImages(Post $post, array $images): void
    {
        // Delete existing images
        PostImage::where('post_id', $post->id)->delete();
        
        // Create new images
        foreach ($images as $imageData) {
            PostImage::create([
                'post_id' => $post->id,
                'image_type' => $imageData['image_type'],
                'image_url' => $imageData['image_url'],
                'alt_text' => $imageData['alt_text'] ?? null,
                'language' => $imageData['language'] ?? null,
                'is_primary' => $imageData['is_primary'] ?? false,
            ]);
        }
    }

    /**
     * Sync videos for a post
     */
    public function syncVideos(Post $post, array $videos): void
    {
        // Delete existing videos
        PostVideo::where('post_id', $post->id)->delete();
        
        // Create new videos
        foreach ($videos as $videoData) {
            PostVideo::create([
                'post_id' => $post->id,
                'video_type' => $videoData['type'],
                'url' => $videoData['url'],
                'meta' => [
                    'embed_url' => $videoData['embed_url'] ?? null,
                    'youtube_id' => $videoData['youtube_id'] ?? null,
                ],
            ]);
        }
    }

    /**
     * Create or update genres and link to post
     */
    public function syncGenres(Post $post, array $genresData): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'linked' => 0];
        
        // Get existing genre relationships
        $existingGenreIds = $post->genres()->pluck('genres.id')->toArray();
        $newGenreIds = [];

        foreach ($genresData as $genreData) {
            $genre = Genre::updateOrCreate(
                ['mal_id' => $genreData['mal_id']],
                [
                    'name' => $genreData['name'],
                    'type' => $genreData['type'],
                ]
            );

            if ($genre->wasRecentlyCreated) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }

            $newGenreIds[] = $genre->id;
        }

        // Sync the relationship
        $post->genres()->sync($newGenreIds);
        $stats['linked'] = count($newGenreIds);

        return $stats;
    }

    /**
     * Create or update producers and link to post
     */
    public function syncProducers(Post $post, array $producersData): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'linked' => 0];
        
        // Delete existing producer relationships
        PostProducer::where('post_id', $post->id)->delete();
        
        foreach ($producersData as $producerData) {
            $producer = Producer::updateOrCreate(
                ['mal_id' => $producerData['mal_id']],
                [
                    'slug' => \Illuminate\Support\Str::slug($producerData['name']),
                    'titles' => ['en' => $producerData['name']],
                ]
            );

            if ($producer->wasRecentlyCreated) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }

            // Create the pivot relationship with type
            PostProducer::create([
                'post_id' => $post->id,
                'producer_id' => $producer->id,
                'type' => $producerData['type'],
            ]);
            
            $stats['linked']++;
        }

        return $stats;
    }

    /**
     * Create or update characters and link to post
     */
    public function syncCharacters(Post $post, array $charactersData): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'linked' => 0];
        
        // Remove existing character relationships
        PostCharacter::where('post_id', $post->id)->delete();

        foreach ($charactersData as $characterData) {
            $charInfo = $characterData['character'];
            
            $character = Character::updateOrCreate(
                ['mal_id' => $charInfo['mal_id']],
                [
                    'name' => $charInfo['name'],
                    'url' => $charInfo['url'] ?? null,
                    'images' => $charInfo['images'] ?? [],
                ]
            );

            if ($character->wasRecentlyCreated) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }

            // Create relationship
            PostCharacter::create([
                'post_id' => $post->id,
                'character_id' => $character->id,
                'role' => $characterData['role'],
                'voice_actors' => $characterData['voice_actors'] ?? [],
            ]);

            $stats['linked']++;
        }

        return $stats;
    }

    /**
     * Create or update episodes for a post
     */
    public function syncEpisodes(Post $post, array $episodesData): array
    {
        $stats = ['created' => 0, 'updated' => 0];

        foreach ($episodesData as $episodeData) {
            $episode = Episode::updateOrCreate(
                [
                    'post_id' => $post->id,
                    'episode_number' => $episodeData['number']
                ],
                [
                    'mal_id' => $episodeData['mal_id'],
                    'titles' => [
                        'default' => $episodeData['title'],
                        'japanese' => $episodeData['title_japanese'],
                        'romanji' => $episodeData['title_romanji'],
                    ],
                    'release_date' => $episodeData['aired'],
                    'description' => $episodeData['synopsis'] ?? null,
                    'type' => $episodeData['filler'] ? 'filler' : ($episodeData['recap'] ? 'recap' : 'regular'),
                ]
            );

            if ($episode->wasRecentlyCreated) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }
        }

        return $stats;
    }

    /**
     * Get posts that need processing
     */
    public function getPostsForProcessing(?int $specificId = null, ?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Post::whereNotNull('mal_id')->where('mal_id', '>', 0);

        if ($specificId) {
            $query->where('id', $specificId);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Check if resource exists by MAL ID
     */
    public function resourceExists(string $model, int $malId): bool
    {
        $modelClass = "App\\Models\\{$model}";
        
        if (!class_exists($modelClass)) {
            return false;
        }

        return $modelClass::where('mal_id', $malId)->exists();
    }
}
