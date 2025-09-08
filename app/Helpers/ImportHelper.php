<?php

namespace App\Helpers;

use App\Helpers\JikanHelper;
use App\Models\Post;
use App\Models\PostTitle;
use App\Models\PostImage;
use App\Models\PostVideo;
use Illuminate\Support\Facades\Log;

class ImportHelper
{
    /**
     * Import anime data từ Jikan API và lưu vào database
     */
    public static function importAnimeData($id)
    {
        // Lấy dữ liệu từ Jikan API
        $animeData = JikanHelper::getAnimeData($id);
        
        // Parse dữ liệu thành format phù hợp với database
        $parsedData = JikanHelper::parseAnimeData($animeData);
        
        $basicInfo = $parsedData['basic_info'];
        $titles = $parsedData['titles'];
        $images = $parsedData['images'];
        
        // Tạo slug từ titles
        $slug = JikanHelper::generateSlug($titles);
        $basicInfo['slug'] = $slug;
        
        // Kiểm tra bài viết đã tồn tại chưa
        $mal_id = $basicInfo['mal_id'];
        $existingPost = Post::where('mal_id', $mal_id)->orWhere('slug', $slug)->first();

        if ($existingPost && config('web.update_post_when_exists') === false) {
            throw new \Exception("Bài viết với mal_id $mal_id hoặc slug $slug này đã tồn tại!");
        }

        if ($existingPost && config('web.update_post_when_exists')) {
            // Cập nhật bài viết hiện có
            return self::updateExistingPost($existingPost, $basicInfo, $titles, $images, $animeData);
        } else {
            // Tạo bài viết mới
            return self::createNewPost($basicInfo, $titles, $images, $animeData);
        }
    }
    
    /**
     * Cập nhật bài viết đã tồn tại
     */
    private static function updateExistingPost($post, $basicInfo, $titles, $images, $animeData)
    {
        // Cập nhật thông tin cơ bản
        $post->update($basicInfo);        
        // Xóa và tạo lại titles
        $post->titles()->delete();
        foreach ($titles as $titleData) {
            $post->titles()->create($titleData);
        }
        
        // Xóa tất cả ảnh cũ
        $post->images()->delete();
        
        // Thêm ảnh chính (poster)
        $posterImage = collect($images)->firstWhere('is_primary', true);
        if ($posterImage && !empty($posterImage['image_url'])) {
            PostImage::insertFromUrl(
                $post->id, 
                $posterImage['image_url'], 
                $posterImage['language'] ?? 'en', 
                true, // is_primary
                'poster', // image_type
                $posterImage['alt_text'] ?? 'Main poster image'
            );
        }
        
        // Thêm các ảnh khác từ images array
        foreach ($images as $imageData) {
            if (!($imageData['is_primary'] ?? false) && !empty($imageData['image_url'])) {
                PostImage::insertFromUrl(
                    $post->id,
                    $imageData['image_url'],
                    $imageData['language'] ?? 'en',
                    false,
                    $imageData['image_type'] ?? 'poster',
                    $imageData['alt_text'] ?? 'Additional image'
                );
            }
        }
        
        // Get additional pictures for gallery
        self::importPictures($post);
        
        // Import videos
        self::importVideos($post);
        
        // Import genres and attach them to the post
        $post->attachGenresFromApiData($animeData);
        
        // Import producers, licensors, and studios
        $post->attachProducersFromApiData($animeData);
        
        return $post->load(['titles', 'images', 'videos']);
    }
    
    /**
     * Tạo bài viết mới
     */
    private static function createNewPost($basicInfo, $titles, $images, $animeData)
    {
        // Tạo post mới
        $post = Post::create($basicInfo);
        
        // Tạo titles
        foreach ($titles as $titleData) {
            $post->titles()->create($titleData);
        }
        
        // Thêm ảnh chính (poster)        
        $posterImage = collect($images)->firstWhere('is_primary', true);
        if ($posterImage && !empty($posterImage['image_url'])) {
            try {
                PostImage::insertFromUrl(
                    $post->id, 
                    $posterImage['image_url'], 
                    $posterImage['language'] ?? 'en', 
                    true, // is_primary
                    'poster', // image_type
                    $posterImage['alt_text'] ?? 'Main poster image'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to import poster image: " . $e->getMessage());
            }
        }
        
        // Thêm các ảnh khác từ images array        
        foreach ($images as $imageData) {
            if (!($imageData['is_primary'] ?? false) && !empty($imageData['image_url'])) {
                try {
                    PostImage::insertFromUrl(
                        $post->id,
                        $imageData['image_url'],
                        $imageData['language'] ?? 'en',
                        false,
                        $imageData['image_type'] ?? 'poster',
                        $imageData['alt_text'] ?? 'Additional image'
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to import additional image: " . $e->getMessage());
                }
            }
        }
        
        // Import additional pictures for gallery
        self::importPictures($post);
        
        // Import videos
        self::importVideos($post);
        
        // Import genres and attach them to the post
        $post->attachGenresFromApiData($animeData);
        
        // Import producers, licensors, and studios
        $post->attachProducersFromApiData($animeData);
        
        return $post->load(['titles', 'images', 'videos']);
    }
    
    /**
     * Import pictures for a post from Jikan API
     */
    private static function importPictures($post)
    {
        if (empty($post->mal_id)) {
            return;
        }
        
        // Fetch pictures from Jikan API
        $picturesData = JikanHelper::getPictures($post->mal_id);
        $parsedImages = JikanHelper::parsePictures($picturesData);

        // Debug logging
        \Illuminate\Support\Facades\Log::info("Pictures data for mal_id {$post->mal_id}: " . json_encode($picturesData));
        \Illuminate\Support\Facades\Log::info("Parsed images for mal_id {$post->mal_id}: " . json_encode($parsedImages));
        
        // Add pictures to gallery
        foreach ($parsedImages as $imageData) {
            try {
                // Validate each field before inserting
                if (empty($imageData['image_url'])) {
                    \Illuminate\Support\Facades\Log::warning("Skipping image with empty URL: " . json_encode($imageData));
                    continue;
                }
                
                // Save as screenshot type images (gallery equivalent)
                PostImage::insertFromUrl(
                    $post->id,
                    $imageData['image_url'],
                    $imageData['language'] ?? 'en',
                    $imageData['is_primary'] ?? false,
                    $imageData['image_type'] ?? 'gallery',
                    $imageData['alt_text'] ?? 'Gallery image'
                );
            } catch (\Exception $e) {
                // Log error but continue with other images
                \Illuminate\Support\Facades\Log::error("Failed to import gallery image: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Image data was: " . json_encode($imageData));
            }
        }
    }

    /**
     * Import multiple anime từ danh sách IDs
     */
    public static function importMultipleAnime(array $ids)
    {
        $results = [];
        
        foreach ($ids as $id) {
            try {
                $post = self::importAnimeData($id);
                $results[] = [
                    'id' => $id,
                    'status' => 'success',
                    'post' => $post,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'id' => $id,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
            
            // Thêm delay để tránh spam API
            sleep(1);
        }
        
        return $results;
    }

    /**
     * Import videos for a post from Jikan API
     */
    private static function importVideos($post)
    {
        if (empty($post->mal_id)) {
            return;
        }
        
        // Clear existing videos first
        $post->videos()->delete();
        
        // Fetch videos from Jikan API
        $videosData = JikanHelper::getVideos($post->mal_id);
        $parsedVideos = JikanHelper::parseVideos($videosData);

        // Debug logging
        \Illuminate\Support\Facades\Log::info("Videos data for mal_id {$post->mal_id}: " . json_encode($videosData));
        \Illuminate\Support\Facades\Log::info("Parsed videos for mal_id {$post->mal_id}: " . json_encode($parsedVideos));
        
        // Add videos to database
        foreach ($parsedVideos as $videoData) {
            try {
                // Validate video data before inserting
                if (empty($videoData['url']) && empty($videoData['meta']['youtube_id'])) {
                    \Illuminate\Support\Facades\Log::warning("Skipping video with no URL or YouTube ID: " . json_encode($videoData));
                    continue;
                }
                
                // Save video
                PostVideo::insertFromJikanData(
                    $post->id,
                    $videoData,
                    $videoData['video_type'] ?? 'other'
                );
                
                \Illuminate\Support\Facades\Log::info("Successfully imported video: " . ($videoData['title'] ?? 'Unknown'));
                
            } catch (\Exception $e) {
                // Log error but continue with other videos
                \Illuminate\Support\Facades\Log::error("Failed to import video: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Video data was: " . json_encode($videoData));
            }
        }
    }
}