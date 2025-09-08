<?php

namespace App\Helpers\Api;

/**
 * Jikan API client for MyAnimeList data
 */
class JikanApiClient extends BaseApiClient
{
    protected function getBaseUrl(): string
    {
        return 'https://api.jikan.moe/v4';
    }

    /**
     * Get anime data by MAL ID
     */
    public function getAnime(int $malId, bool $full = true): array
    {
        $endpoint = $full ? "/anime/{$malId}/full" : "/anime/{$malId}";
        return $this->makeRequest($endpoint);
    }

    /**
     * Search anime by query
     */
    public function searchAnime(string $query, int $page = 1, int $limit = 25): array
    {
        return $this->makeRequest('/anime', [
            'q' => $query,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * Get anime characters
     */
    public function getAnimeCharacters(int $malId): array
    {
        return $this->makeRequest("/anime/{$malId}/characters");
    }

    /**
     * Get anime episodes
     */
    public function getAnimeEpisodes(int $malId, int $page = 1): array
    {
        return $this->makeRequest("/anime/{$malId}/episodes", [
            'page' => $page
        ]);
    }

    /**
     * Get anime producers/studios
     */
    public function getAnimeProducers(int $malId): array
    {
        $anime = $this->getAnime($malId);
        $data = $this->extractData($anime);
        
        return [
            'producers' => $data['producers'] ?? [],
            'studios' => $data['studios'] ?? [],
            'licensors' => $data['licensors'] ?? []
        ];
    }

    /**
     * Get anime genres
     */
    public function getAnimeGenres(int $malId): array
    {
        $anime = $this->getAnime($malId);
        $data = $this->extractData($anime);
        
        return [
            'genres' => $data['genres'] ?? [],
            'themes' => $data['themes'] ?? [],
            'demographics' => $data['demographics'] ?? []
        ];
    }

    /**
     * Get all episodes with pagination handling
     */
    public function getAllAnimeEpisodes(int $malId): array
    {
        $allEpisodes = [];
        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $this->getAnimeEpisodes($malId, $page);
            $episodes = $this->extractData($response);
            
            if (empty($episodes)) {
                break;
            }

            $allEpisodes = array_merge($allEpisodes, $episodes);
            
            $pagination = $this->extractPagination($response);
            $hasMorePages = $pagination['has_next_page'] ?? false;
            $page++;
        }

        return $allEpisodes;
    }
}
