<?php

namespace App\Helpers\Import;

use App\Helpers\Api\JikanApiClient;
use App\Helpers\Import\DataParser;
use App\Helpers\Import\DatabaseService;
use App\Models\Post;
use Illuminate\Support\Facades\Log;
use App\Helpers\JikanHelper;

/**
 * Main service for importing anime data
 */
class AnimeImportService
{
    protected JikanApiClient $apiClient;
    protected DatabaseService $dbService;

    public function __construct()
    {
        $this->apiClient = new JikanApiClient();
        $this->dbService = new DatabaseService();
    }

    /**
     * Import complete anime data by MAL ID
     */
    public function importAnime(int $malId, bool $forceUpdate = false): array
    {
        try {
            // Check if anime already exists
            if (!$forceUpdate && $this->dbService->resourceExists('Post', $malId)) {
                return [
                    'success' => false,
                    'message' => "Anime with MAL ID {$malId} already exists",
                    'post' => Post::where('mal_id', $malId)->first()
                ];
            }

            // Fetch data from API
            $animeData = $this->apiClient->getAnime($malId);

            // use pictures change for images
            $picturesData = (new JikanHelper())->getPictures($malId);

            $animeData['data']['images'] = $picturesData['data'] ?? [];

            // Fetch videos data
            $videosData = $this->apiClient->getAnimeVideos($malId);
            $animeData['data']['videos'] = $videosData['data'] ?? [];
            
            
            // Parse the data
            $parsedData = DataParser::parseAnimeData($animeData);

            
            
            // Create/update in database
            $post = $this->dbService->createOrUpdateAnime(
                $parsedData['basic_info'],
                $parsedData['titles'],
                $parsedData['images'],
                $parsedData['videos']
            );

            // Import related data
            $relatedStats = [
                'genres' => $this->importGenres($post, $malId),
                'producers' => $this->importProducers($post, $malId),
            ];

            return [
                'success' => true,
                'message' => 'Anime imported successfully',
                'post' => $post,
                'related_stats' => $relatedStats
            ];

        } catch (\Exception $e) {
            Log::error("Failed to import anime {$malId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'post' => null
            ];
        }
    }

    /**
     * Import characters for a post
     */
    public function importCharacters(Post $post, bool $forceUpdate = false): array
    {
        try {
            if (!$post->mal_id) {
                throw new \Exception("Post {$post->id} has no MAL ID");
            }

            $charactersData = $this->apiClient->getAnimeCharacters($post->mal_id);
            $characters = DataParser::parseCharacters($charactersData['data'] ?? []);
            
            $stats = $this->dbService->syncCharacters($post, $characters);

            return [
                'success' => true,
                'message' => 'Characters imported successfully',
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error("Failed to import characters for post {$post->id}", [
                'mal_id' => $post->mal_id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'stats' => ['created' => 0, 'updated' => 0, 'linked' => 0]
            ];
        }
    }

    /**
     * Import episodes for a post
     */
    public function importEpisodes(Post $post, bool $forceUpdate = false): array
    {
        try {
            if (!$post->mal_id) {
                throw new \Exception("Post {$post->id} has no MAL ID");
            }

            $episodesData = $this->apiClient->getAllAnimeEpisodes($post->mal_id);
            $episodes = DataParser::parseEpisodes($episodesData);
            
            $stats = $this->dbService->syncEpisodes($post, $episodes);

            return [
                'success' => true,
                'message' => 'Episodes imported successfully',
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error("Failed to import episodes for post {$post->id}", [
                'mal_id' => $post->mal_id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'stats' => ['created' => 0, 'updated' => 0]
            ];
        }
    }

    /**
     * Import genres for a post
     */
    public function importGenres(Post $post, ?int $malId = null): array
    {
        try {
            $malId = $malId ?? $post->mal_id;
            
            if (!$malId) {
                throw new \Exception("No MAL ID available for genres import");
            }

            $genresData = $this->apiClient->getAnimeGenres($malId);
            $allGenres = array_merge(
                $genresData['genres'] ?? [],
                $genresData['themes'] ?? [],
                $genresData['demographics'] ?? []
            );
            
            $parsedGenres = DataParser::parseGenres(['genres' => $allGenres]);
            
            return $this->dbService->syncGenres($post, $parsedGenres);

        } catch (\Exception $e) {
            Log::error("Failed to import genres", [
                'post_id' => $post->id,
                'mal_id' => $malId,
                'error' => $e->getMessage()
            ]);

            return ['created' => 0, 'updated' => 0, 'linked' => 0];
        }
    }

    /**
     * Import producers for a post
     */
    public function importProducers(Post $post, ?int $malId = null): array
    {
        try {
            $malId = $malId ?? $post->mal_id;
            
            if (!$malId) {
                throw new \Exception("No MAL ID available for producers import");
            }

            $producersData = $this->apiClient->getAnimeProducers($malId);
            $allProducers = array_merge(
                $producersData['producers'] ?? [],
                $producersData['studios'] ?? [],
                $producersData['licensors'] ?? []
            );
            
            $parsedProducers = DataParser::parseProducers(['producers' => $allProducers]);
            
            return $this->dbService->syncProducers($post, $parsedProducers);

        } catch (\Exception $e) {
            Log::error("Failed to import producers", [
                'post_id' => $post->id,
                'mal_id' => $malId,
                'error' => $e->getMessage()
            ]);

            return ['created' => 0, 'updated' => 0, 'linked' => 0];
        }
    }

    /**
     * Search anime by query
     */
    public function searchAnime(string $query, int $page = 1): array
    {
        try {
            $results = $this->apiClient->searchAnime($query, $page);
            
            return [
                'success' => true,
                'data' => $results['data'] ?? [],
                'pagination' => $results['pagination'] ?? []
            ];

        } catch (\Exception $e) {
            Log::error("Failed to search anime", [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'pagination' => []
            ];
        }
    }

    /**
     * Get posts that need processing
     */
    public function getPostsForProcessing(?int $specificId = null, ?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->dbService->getPostsForProcessing($specificId, $limit);
    }
}
