<?php

namespace App\Console\Commands\Import;

use App\Models\Post;
use App\Models\Producer;
use App\Models\PostProducer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportProducersFromJikan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-producers-from-jikan {--post_id= : Import for specific post ID} {--limit= : Limit number of posts to process} {--force : Force update existing producers/licensors/studios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import producers, licensors, and studios from Jikan API for anime posts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $postId = $this->option('post_id');
        $limit = (int) $this->option('limit') ?: null;
        $force = $this->option('force');

        if ($postId) {
            // Import for specific post
            $post = Post::find($postId);
            if (!$post) {
                $this->error("Post with ID {$postId} not found.");
                return 1;
            }

            if (!$post->mal_id) {
                $this->error("Post {$postId} does not have a MAL ID.");
                return 1;
            }

            $this->processPost($post, $force);
        } else {
            // Import for all posts with MAL ID
            $query = Post::whereNotNull('mal_id');

            if ($limit) {
                $query->limit($limit);
            }

            $posts = $query->get();

            $this->info("Found {$posts->count()} posts with MAL IDs to process.");

            $progressBar = $this->output->createProgressBar($posts->count());
            $progressBar->start();

            foreach ($posts as $post) {
                $this->processPost($post, $force);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();
        }

        $this->info('Import completed successfully.');
        return 0;
    }

    /**
     * Process a single post
     */
    private function processPost(Post $post, bool $force = false): void
    {
        try {
            // Check if already has producers/licensors/studios and not forcing
            if (!$force && $post->postProducers()->exists()) {
                return; // Skip if already has entities
            }

            // Fetch data from Jikan API
            $response = Http::timeout(30)->get("https://api.jikan.moe/v4/anime/{$post->mal_id}/full");

            if (!$response->successful()) {
                $this->error("Failed to fetch data for post {$post->id} (MAL ID: {$post->mal_id}). Status: {$response->status()}");
                return;
            }

            $data = $response->json();

            if (!isset($data['data'])) {
                $this->error("Invalid response format for post {$post->id}");
                return;
            }

            $animeData = $data['data'];

            // Define the types to process
            $typesToProcess = [
                'producers' => 'producer',
                'licensors' => 'licensor',
                'studios' => 'studio'
            ];

            $totalProcessed = 0;

            // Process each type
            foreach ($typesToProcess as $apiKey => $type) {
                if (isset($animeData[$apiKey]) && is_array($animeData[$apiKey])) {
                    $count = $this->processProducersByType($post, $animeData[$apiKey], $type, $force);
                    $totalProcessed += $count;
                    $this->info("Processed {$count} {$type}s for post {$post->id}");
                }
            }

            if ($totalProcessed > 0) {
                $this->info("Total processed: {$totalProcessed} entities for post {$post->id}");
            } else {
                $this->warn("No producer/licensor/studio data found for post {$post->id}");
            }

        } catch (\Exception $e) {
            $this->error("Error processing post {$post->id}: " . $e->getMessage());
        }
    }

    /**
     * Process producers/licensors/studios by type
     */
    private function processProducersByType(Post $post, array $entities, string $type, bool $force = false): int
    {
        if (empty($entities)) {
            return 0;
        }

        // Remove existing entities of this type if forcing
        if ($force) {
            $post->postProducers()->where('type', $type)->delete();
        }

        $processed = 0;

        foreach ($entities as $entityData) {
            try {
                $malId = $entityData['mal_id'] ?? null;

                if (!$malId) {
                    $this->warn("{$type} data missing MAL ID for post {$post->id}");
                    continue;
                }

                // Find or create producer/studio/licensor
                $entity = Producer::firstOrCreate(
                    ['mal_id' => $malId],
                    [
                        'slug' => Str::slug($entityData['name'] ?? 'unknown-' . $type),
                        'titles' => [
                            'en' => $entityData['name'] ?? 'Unknown ' . ucfirst($type),
                        ],
                        'images' => null,
                        'established' => null,
                        'about' => null,
                    ]
                );

                // Attach entity to post with specific type
                PostProducer::attachProducerToPost($post->id, $entity->id, $type);
                $processed++;

            } catch (\Exception $e) {
                $this->error("Error processing {$type} for post {$post->id}: " . $e->getMessage());
            }
        }

        return $processed;
    }
}
