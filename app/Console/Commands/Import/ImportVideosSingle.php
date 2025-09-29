<?php

namespace App\Console\Commands\Import;

use App\Models\Post;
use App\Models\PostVideo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportVideosSingle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-videos-single {post_id : The post ID to import videos for} {--force : Force update existing videos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import videos (promo and music videos) from Jikan API for a specific anime post';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $postId = $this->argument('post_id');
        $force = $this->option('force');

        // Find the post
        $post = Post::find($postId);

        if (!$post) {
            $this->error("Post with ID {$postId} not found.");
            return 1;
        }

        if (!$post->mal_id) {
            $this->error("Post {$postId} does not have a MAL ID.");
            return 1;
        }

        $this->info("Importing videos for post: {$post->title} (MAL ID: {$post->mal_id})");

        try {
            // Fetch videos from Jikan API
            $response = Http::timeout(30)->get("https://api.jikan.moe/v4/anime/{$post->mal_id}/videos");

            if (!$response->successful()) {
                $this->error("Failed to fetch videos from Jikan API. Status: {$response->status()}");
                return 1;
            }

            $data = $response->json();

            if (!isset($data['data'])) {
                $this->error("Invalid response format from Jikan API.");
                return 1;
            }

            $videosData = $data['data'];

            // Process promo videos
            if (isset($videosData['promo']) && is_array($videosData['promo'])) {
                $this->processPromoVideos($post, $videosData['promo'], $force);
            }

            // Process music videos
            if (isset($videosData['music_videos']) && is_array($videosData['music_videos'])) {
                $this->processMusicVideos($post, $videosData['music_videos'], $force);
            }

            $this->info("Successfully imported videos for post {$postId}");

        } catch (\Exception $e) {
            $this->error("Error importing videos: " . $e->getMessage());
            Log::error("ImportVideosSingle error for post {$postId}: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Process promo videos
     */
    private function processPromoVideos(Post $post, array $promoVideos, bool $force = false)
    {
        $this->info("Processing " . count($promoVideos) . " promo videos...");

        foreach ($promoVideos as $promo) {
            if (!isset($promo['trailer'])) {
                continue;
            }

            $trailer = $promo['trailer'];

            // Check if video already exists
            $existingVideo = PostVideo::where('post_id', $post->id)
                ->where('url', $trailer['url'])
                ->first();

            if ($existingVideo && !$force) {
                $this->warn("Promo video already exists: {$promo['title']}");
                continue;
            }

            // Prepare video data
            $videoData = [
                'title' => $promo['title'] ?? 'Promo Video',
                'url' => $trailer['url'] ?? null,
                'meta' => [
                    'youtube_id' => $trailer['youtube_id'] ?? null,
                    'embed_url' => $trailer['embed_url'] ?? null,
                    'images' => $trailer['images'] ?? null,
                    'type' => 'promo'
                ]
            ];

            if ($existingVideo) {
                $existingVideo->update($videoData);
                $this->info("Updated promo video: {$promo['title']}");
            } else {
                PostVideo::insertFromJikanData($post->id, $videoData, 'promo');
                $this->info("Created promo video: {$promo['title']}");
            }
        }
    }

    /**
     * Process music videos
     */
    private function processMusicVideos(Post $post, array $musicVideos, bool $force = false)
    {
        $this->info("Processing " . count($musicVideos) . " music videos...");

        foreach ($musicVideos as $musicVideo) {
            if (!isset($musicVideo['video'])) {
                continue;
            }

            $video = $musicVideo['video'];

            // Check if video already exists
            $existingVideo = PostVideo::where('post_id', $post->id)
                ->where('url', $video['url'])
                ->first();

            if ($existingVideo && !$force) {
                $this->warn("Music video already exists: {$musicVideo['title']}");
                continue;
            }

            // Prepare video data
            $videoData = [
                'title' => $musicVideo['title'] ?? 'Music Video',
                'url' => $video['url'] ?? null,
                'meta' => [
                    'youtube_id' => $video['youtube_id'] ?? null,
                    'embed_url' => $video['embed_url'] ?? null,
                    'images' => $video['images'] ?? null,
                    'type' => 'music_video',
                    'music_meta' => $musicVideo['meta'] ?? null
                ]
            ];

            if ($existingVideo) {
                $existingVideo->update($videoData);
                $this->info("Updated music video: {$musicVideo['title']}");
            } else {
                PostVideo::insertFromJikanData($post->id, $videoData, 'music_video');
                $this->info("Created music video: {$musicVideo['title']}");
            }
        }
    }
}
