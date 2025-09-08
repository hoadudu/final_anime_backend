<?php

namespace App\Helpers\Import;

use App\Helpers\Api\JikanApiClient;
use App\Models\Post;
use Illuminate\Support\Str;

/**
 * Data parser for converting API responses to database format
 */
class DataParser
{
    /**
     * Parse anime data from Jikan API
     */
    public static function parseAnimeData(array $apiData): array
    {
        $data = $apiData['data'] ?? [];
        
        return [
            'basic_info' => self::parseBasicInfo($data),
            'titles' => self::parseTitles($data['titles'] ?? []),
            'images' => self::parseImages($data['images'] ?? []),
            'videos' => self::parseVideos($data['trailer'] ?? []),
            'genres' => self::parseGenres($data),
            'producers' => self::parseProducers($data),
        ];
    }

    /**
     * Parse basic anime information
     */
    public static function parseBasicInfo(array $data): array
    {
        return [
            'mal_id' => $data['mal_id'] ?? null,
            'title' => $data['title'] ?? null,
            'slug' => self::generateSlug($data['title'] ?? ''),
            'type' => $data['type'] ?? null,
            'source' => $data['source'] ?? null,
            'episodes' => $data['episodes'] ?? null,
            'status' => $data['status'] ?? null,
            'airing' => $data['airing'] ?? false,
            'aired_from' => self::parseDate($data['aired']['from'] ?? null),
            'aired_to' => self::parseDate($data['aired']['to'] ?? null),
            'duration' => $data['duration'] ?? null,
            'rating' => $data['rating'] ?? null,
            'synopsis' => $data['synopsis'] ?? null,
            'background' => $data['background'] ?? null,
            'season' => $data['season'] ?? null,
            'year' => $data['year'] ?? null,
            'broadcast' => null, // Will handle broadcast separately if needed
            'approved' => true,
        ];
    }

    /**
     * Parse titles array
     */
    public static function parseTitles(array $titles): array
    {
        $parsedTitles = [];
        
        foreach ($titles as $title) {
            $titleType = $title['type'] ?? 'Default';
            $titleText = $title['title'] ?? '';
            
            // Map title type and determine language
            [$mappedType, $language] = self::mapTitleTypeAndLanguage($titleType);
            
            $parsedTitles[] = [
                'type' => $mappedType,
                'title' => $titleText,
                'language' => $language,
            ];
        }
        
        return $parsedTitles;
    }

    /**
     * Map API title type to database ENUM values and determine language code
     */
    private static function mapTitleTypeAndLanguage(string $apiType): array
    {
        $typeMap = [
            'Default' => ['Default', null],
            'Synonym' => ['Synonym', null], 
            'Official' => ['Official', 'ja'], // Official titles are typically Japanese
            'Alternative' => ['Alternative', null],
            'Japanese' => ['Official', 'ja'],
            'English' => ['Alternative', 'en'],
            'German' => ['Alternative', 'de'],
            'Spanish' => ['Alternative', 'es'],
            'French' => ['Alternative', 'fr'],
            'Chinese' => ['Alternative', 'zh'],
            'Korean' => ['Alternative', 'ko'],
        ];

        return $typeMap[$apiType] ?? ['Alternative', null];
    }

    /**
     * Parse images array
     */
    public static function parseImages(array $images): array
    {
        $parsedImages = [];
        
        foreach ($images as $type => $imageSet) {
            if (is_array($imageSet)) {
                foreach ($imageSet as $size => $url) {
                    if ($url) {
                        $parsedImages[] = [
                            'type' => $type,
                            'size' => $size,
                            'url' => $url,
                        ];
                    }
                }
            }
        }
        
        return $parsedImages;
    }

    /**
     * Parse video/trailer data
     */
    public static function parseVideos(array $trailer): array
    {
        if (empty($trailer)) {
            return [];
        }

        return [
            [
                'type' => 'trailer',
                'url' => $trailer['url'] ?? '',
                'embed_url' => $trailer['embed_url'] ?? '',
                'youtube_id' => $trailer['youtube_id'] ?? '',
            ]
        ];
    }

    /**
     * Parse genres, themes, and demographics
     */
    public static function parseGenres(array $data): array
    {
        $genres = [];
        
        // Regular genres
        foreach ($data['genres'] ?? [] as $genre) {
            $genres[] = [
                'mal_id' => $genre['mal_id'],
                'name' => $genre['name'],
                'type' => 'genre',
            ];
        }
        
        // Themes
        foreach ($data['themes'] ?? [] as $theme) {
            $genres[] = [
                'mal_id' => $theme['mal_id'],
                'name' => $theme['name'],
                'type' => 'theme',
            ];
        }
        
        // Demographics
        foreach ($data['demographics'] ?? [] as $demographic) {
            $genres[] = [
                'mal_id' => $demographic['mal_id'],
                'name' => $demographic['name'],
                'type' => 'demographic',
            ];
        }
        
        return $genres;
    }

    /**
     * Parse producers, studios, and licensors
     */
    public static function parseProducers(array $data): array
    {
        $producers = [];
        
        // Producers
        foreach ($data['producers'] ?? [] as $producer) {
            $producers[] = [
                'mal_id' => $producer['mal_id'],
                'name' => $producer['name'],
                'type' => 'producer',
                'url' => $producer['url'] ?? null,
            ];
        }
        
        // Studios
        foreach ($data['studios'] ?? [] as $studio) {
            $producers[] = [
                'mal_id' => $studio['mal_id'],
                'name' => $studio['name'],
                'type' => 'studio',
                'url' => $studio['url'] ?? null,
            ];
        }
        
        // Licensors
        foreach ($data['licensors'] ?? [] as $licensor) {
            $producers[] = [
                'mal_id' => $licensor['mal_id'],
                'name' => $licensor['name'],
                'type' => 'licensor',
                'url' => $licensor['url'] ?? null,
            ];
        }
        
        return $producers;
    }

    /**
     * Parse character data
     */
    public static function parseCharacters(array $characters): array
    {
        $parsedCharacters = [];
        
        foreach ($characters as $characterData) {
            $character = $characterData['character'] ?? [];
            $voiceActors = $characterData['voice_actors'] ?? [];
            
            $parsedCharacters[] = [
                'character' => [
                    'mal_id' => $character['mal_id'] ?? null,
                    'name' => $character['name'] ?? '',
                    'url' => $character['url'] ?? null,
                    'images' => $character['images'] ?? [],
                ],
                'role' => $characterData['role'] ?? 'Supporting',
                'voice_actors' => array_map(function ($va) {
                    return [
                        'mal_id' => $va['person']['mal_id'] ?? null,
                        'name' => $va['person']['name'] ?? '',
                        'language' => $va['language'] ?? 'Japanese',
                    ];
                }, $voiceActors)
            ];
        }
        
        return $parsedCharacters;
    }

    /**
     * Parse episode data
     */
    public static function parseEpisodes(array $episodes): array
    {
        return array_map(function ($episode) {
            return [
                'mal_id' => $episode['mal_id'] ?? null,
                'number' => $episode['mal_id'] ?? 0,
                'title' => $episode['title'] ?? '',
                'title_japanese' => $episode['title_japanese'] ?? null,
                'title_romanji' => $episode['title_romanji'] ?? null,
                'aired' => self::parseDate($episode['aired'] ?? null),
                'score' => $episode['score'] ?? null,
                'filler' => $episode['filler'] ?? false,
                'recap' => $episode['recap'] ?? false,
                'forum_url' => $episode['forum_url'] ?? null,
            ];
        }, $episodes);
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Parse date string to Y-m-d format
     */
    public static function parseDate(?string $dateString): ?string
    {
        if (!$dateString) {
            return null;
        }
        
        try {
            return date('Y-m-d', strtotime($dateString));
        } catch (\Exception $e) {
            return null;
        }
    }
}
