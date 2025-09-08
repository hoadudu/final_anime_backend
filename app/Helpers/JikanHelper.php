<?php

namespace App\Helpers;

use App\Helpers\ConvertHelper;

class JikanHelper
{
    public static function getAnimeData($id)
    {
        // Fetch anime data from Jikan API https://api.jikan.moe/v4/anime/53065/full
        $response = file_get_contents("https://api.jikan.moe/v4/anime/$id/full");
        return json_decode($response, true);
    }

    public static function searchAnime($query)
    {
        // Search for anime using Jikan API
        $response = file_get_contents("https://api.jikan.moe/v4/anime?search=$query");
        return json_decode($response, true);
    }

    /**
     * Parse và chuẩn hóa dữ liệu anime từ Jikan API thành format phù hợp với database
     */
    public static function parseAnimeData($animeData)
    {
        $data = $animeData['data'] ?? [];
        
        return [
            'basic_info' => self::parseBasicInfo($data),
            'titles' => self::parseTitles($data['titles'] ?? []),
            'images' => self::parseImages($data['images'] ?? []),
        ];
    }

    /**
     * Parse thông tin cơ bản của anime
     */
    public static function parseBasicInfo($data)
    {
        return [
            'mal_id' => $data['mal_id'] ?? null,
            'type' => $data['type'] ?? null,
            'source' => $data['source'] ?? null,
            'episodes' => $data['episodes'] ?? null,
            'status' => $data['status'] ?? null,
            'airing' => $data['airing'] ?? false,
            'aired_from' => isset($data['aired']['from']) ? date('Y-m-d', strtotime($data['aired']['from'])) : null,
            'aired_to' => isset($data['aired']['to']) ? date('Y-m-d', strtotime($data['aired']['to'])) : null,
            'duration' => $data['duration'] ?? null,
            'rating' => $data['rating'] ?? null,
            'synopsis' => $data['synopsis'] ?? null,
            'background' => $data['background'] ?? null,
            'season' => $data['season'] ?? null,
            'broadcast' => isset($data['broadcast']) ? ConvertHelper::broadcastToTimestamp($data['broadcast']) : null,
            'external' => $data['external'] ?? null,
            'approved' => true, // Mặc định là đã duyệt
        ];
    }

    /**
     * Parse và chuẩn hóa titles từ Jikan API
     */
    public static function parseTitles($titles)
    {
        // Map type về các giá trị enum của PostTitle
        $typeMap = [
            'Default'     => 'Default',
            'Synonym'     => 'Synonym',
            'Alternative' => 'Alternative',
        ];

        return collect($titles)->map(function ($item) use ($typeMap) {
            $type = $typeMap[$item['type']] ?? 'Official';
            
            // Xác định ngôn ngữ dựa trên type hoặc title content
            $language = self::detectLanguage($item);
            
            return [
                'title' => $item['title'],
                'type' => $type,
                'language' => $language,
                'is_primary' => $item['type'] === 'Default' ? 1 : 0,
            ];
        })->toArray();
    }
    /**
     * Lấy danh sách hình ảnh (pictures) của anime từ Jikan API
     */
    public static function getPictures($id)
    {
        try {
            $response = file_get_contents("https://api.jikan.moe/v4/anime/$id/pictures");
            if ($response === false) {
                throw new \Exception("Failed to fetch pictures from Jikan API for ID: $id");
            }
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from Jikan API for pictures: " . json_last_error_msg());
            }
            return $data;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error fetching pictures from Jikan API: " . $e->getMessage());
            return ['data' => []]; // Return empty data structure to prevent further errors
        }
    }

    /**
     * Parse all images from pictures array returned by Jikan API
     * 
     * @param array $pictures Pictures data from Jikan API
     * @return array Array of formatted image data for storing in the database
     */
    public static function parsePictures($pictures)
    {
        $parsedImages = [];
        
        // Loop through all images in the data array
        foreach ($pictures['data'] ?? [] as $index => $picture) {
            // Process JPG images
            if (isset($picture['jpg']['image_url'])) {
                $parsedImages[] = [
                    'image_url' => $picture['jpg']['image_url'],
                    'alt_text' => 'Gallery image ' . ($index + 1),
                    'image_type' => 'gallery',
                    'language' => 'en',
                    'is_primary' => false,
                ];
            }
            
            // Also process large JPG images if available
            if (isset($picture['jpg']['large_image_url'])) {
                $parsedImages[] = [
                    'image_url' => $picture['jpg']['large_image_url'],
                    'alt_text' => 'Large gallery image ' . ($index + 1),
                    'image_type' => 'gallery',
                    'language' => 'en',
                    'is_primary' => false,
                ];
            }
            
            // Process WebP images if available
            if (isset($picture['webp']['image_url'])) {
                $parsedImages[] = [
                    'image_url' => $picture['webp']['image_url'],
                    'alt_text' => 'WebP gallery image ' . ($index + 1),
                    'image_type' => 'gallery',
                    'language' => 'en',
                    'is_primary' => false,
                ];
            }
        }
        
        return $parsedImages;
    }
    /**
     * Parse images từ Jikan API
     */
    public static function parseImages($images)
    {
        $parsedImages = [];
        
        // Parse JPG images
        if (isset($images['jpg'])) {
            $jpgImages = $images['jpg'];
            
            if (isset($jpgImages['image_url'])) {
                $parsedImages[] = [
                    'image_url' => $jpgImages['image_url'],
                    'alt_text' => 'Main poster image',
                    'image_type' => 'poster',
                    'language' => 'en',
                    'is_primary' => true,
                ];
            }
            
            if (isset($jpgImages['large_image_url'])) {
                $parsedImages[] = [
                    'image_url' => $jpgImages['large_image_url'],
                    'alt_text' => 'Large poster image',
                    'image_type' => 'poster',
                    'language' => 'en',
                    'is_primary' => false,
                ];
            }
        }
        
        // Parse WebP images
        if (isset($images['webp'])) {
            $webpImages = $images['webp'];
            
            if (isset($webpImages['image_url'])) {
                $parsedImages[] = [
                    'image_url' => $webpImages['image_url'],
                    'alt_text' => 'WebP poster image',
                    'image_type' => 'poster',
                    'language' => 'en',
                    'is_primary' => false,
                ];
            }
        }
        
        return $parsedImages;
    }

    /**
     * Detect ngôn ngữ của title dựa trên type và content
     */
    public static function detectLanguage($titleItem)
    {
        $title = $titleItem['title'] ?? '';
        $type = $titleItem['type'] ?? '';
        
        // Nếu là Default thì thường là tiếng Nhật
        if ($type === 'Default') {
            return 'ja';
        }
        
        // Kiểm tra nếu chứa ký tự tiếng Nhật
        if (preg_match('/[\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{4E00}-\x{9FAF}]/u', $title)) {
            return 'ja';
        }
        
        // Kiểm tra nếu là tiếng Việt (có dấu)
        if (preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/i', $title)) {
            return 'vi';
        }
        
        // Mặc định là tiếng Anh
        return 'en';
    }

    /**
     * Tạo slug từ danh sách titles
     */
    public static function generateSlug($titles)
    {
        // Ưu tiên title có is_primary = true
        $primaryTitle = collect($titles)->firstWhere('is_primary', true);
        if ($primaryTitle) {
            return \Illuminate\Support\Str::slug($primaryTitle['title']);
        }
        
        // Nếu không có primary, lấy title đầu tiên
        $firstTitle = collect($titles)->first();
        if ($firstTitle) {
            return \Illuminate\Support\Str::slug($firstTitle['title']);
        }
        
        return null;
    }

    /**
     * Lấy danh sách video của anime từ Jikan API
     */
    public static function getVideos($id)
    {
        try {
            $response = file_get_contents("https://api.jikan.moe/v4/anime/$id/videos");
            if ($response === false) {
                throw new \Exception("Failed to fetch videos from Jikan API for ID: $id");
            }
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from Jikan API for videos: " . json_last_error_msg());
            }
            return $data;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error fetching videos from Jikan API: " . $e->getMessage());
            return ['data' => ['promo' => [], 'episodes' => [], 'music_videos' => []]]; // Return empty data structure
        }
    }

    /**
     * Parse videos từ Jikan API thành format phù hợp với database
     */
    public static function parseVideos($videosData)
    {
        $parsedVideos = [];
        $data = $videosData['data'] ?? [];

        // Parse promo videos (trailers)
        foreach ($data['promo'] ?? [] as $promo) {
            if (isset($promo['trailer'])) {
                $parsedVideos[] = [
                    'title' => $promo['title'] ?? 'Trailer',
                    'url' => $promo['trailer']['url'] ?? null,
                    'video_type' => 'promo',
                    
                ];
            }
        }

        // Parse music videos
        foreach ($data['music_videos'] ?? [] as $musicVideo) {
            if (isset($musicVideo['video'])) {
                $parsedVideos[] = [
                    'title' => $musicVideo['title'] ?? 'Music Video',
                    'url' => $musicVideo['video']['url'] ?? null,
                    'video_type' => 'music_videos',
                    'meta' => $musicVideo['meta'] ?? null,
                ];
            }
        }       
        

        return $parsedVideos;
    }
}
