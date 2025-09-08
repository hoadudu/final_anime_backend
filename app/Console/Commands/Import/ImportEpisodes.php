<?php

namespace App\Console\Commands\Import;

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-episodes {--post_id= : Import episodes for specific post} {--force : Force re-import existing episodes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import episodes from Jikan API for posts with MAL IDs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting episode import from Jikan API...');

        // Get posts with MAL IDs
        $query = Post::whereNotNull('mal_id');

        if ($this->option('post_id')) {
            $query->where('id', $this->option('post_id'));
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->warn('No posts found with MAL IDs.');
            return;
        }

        $this->info("Found {$posts->count()} posts with MAL IDs");

        $progressBar = $this->output->createProgressBar($posts->count());
        $progressBar->start();

        $totalEpisodesImported = 0;
        $totalEpisodesUpdated = 0;

        foreach ($posts as $post) {
            try {
                $episodesImported = $this->importEpisodesForPost($post);
                $totalEpisodesImported += $episodesImported['imported'];
                $totalEpisodesUpdated += $episodesImported['updated'];

                $this->newLine();
                $this->info("Post '{$post->title}' (MAL: {$post->mal_id}): {$episodesImported['imported']} imported, {$episodesImported['updated']} updated");

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to import episodes for post '{$post->title}': {$e->getMessage()}");
                Log::error('Episode import failed', [
                    'post_id' => $post->id,
                    'mal_id' => $post->mal_id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Episode import completed!");
        $this->info("Total episodes imported: {$totalEpisodesImported}");
        $this->info("Total episodes updated: {$totalEpisodesUpdated}");
    }

    /**
     * Import episodes for a specific post.
     */
    private function importEpisodesForPost(Post $post): array
    {
        $imported = 0;
        $updated = 0;

        // Call Jikan API to get episodes
        $response = Http::timeout(30)->get("https://api.jikan.moe/v4/anime/{$post->mal_id}/episodes");

        if (!$response->successful()) {
            throw new \Exception("API request failed with status {$response->status()}");
        }

        $data = $response->json();

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new \Exception('Invalid API response format');
        }

        foreach ($data['data'] as $episodeData) {
            try {
                $result = $this->createOrUpdateEpisode($post, $episodeData);
                if ($result === 'created') {
                    $imported++;
                } elseif ($result === 'updated') {
                    $updated++;
                }
            } catch (\Exception $e) {
                $this->warn("Failed to import episode {$episodeData['mal_id']}: {$e->getMessage()}");
                continue;
            }
        }

        return ['imported' => $imported, 'updated' => $updated];
    }

    /**
     * Create or update an episode.
     */
    private function createOrUpdateEpisode(Post $post, array $episodeData): string
    {
        // Determine episode type based on filler/recap flags
        $type = 'regular';
        if ($episodeData['filler']) {
            $type = 'filler';
        } elseif ($episodeData['recap']) {
            $type = 'recap';
        }

        // Prepare titles array
        $titles = [];
        if (!empty($episodeData['title'])) {
            $titles['en'] = $episodeData['title'];
        }
        if (!empty($episodeData['title_japanese'])) {
            $titles['ja'] = $episodeData['title_japanese'];
        }
        if (!empty($episodeData['title_romanji'])) {
            $titles['romanji'] = $episodeData['title_romanji'];
        }

        // Prepare episode data
        $episodeAttributes = [
            'post_id' => $post->id,
            'titles' => $titles,
            'episode_number' => $episodeData['mal_id'], // Using mal_id as episode number
            'absolute_number' => $episodeData['mal_id'], // Same as episode number for now
            'release_date' => $episodeData['aired'] ? \Carbon\Carbon::parse($episodeData['aired'])->format('Y-m-d') : null,
            'description' => null, // API doesn't provide description
            'type' => $type,
            'group' => 1, // Default group
            'sort_number' => $episodeData['mal_id'], // Use mal_id for sorting
        ];

        // Check if episode already exists
        $existingEpisode = Episode::where('post_id', $post->id)
            ->where('episode_number', $episodeData['mal_id'])
            ->first();

        if ($existingEpisode) {
            if ($this->option('force')) {
                $existingEpisode->update($episodeAttributes);
                return 'updated';
            }
            return 'skipped';
        }

        // Create new episode
        Episode::create($episodeAttributes);
        return 'created';
    }
}
