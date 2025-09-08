<?php

namespace App\Console\Commands\Import;

use App\Models\Character;
use App\Models\Post;
use App\Models\PostCharacter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportCharacters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-characters {--post-id= : Import characters for specific post ID} {--limit= : Limit number of posts to process} {--force : Force update existing characters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import characters data from Jikan API for all posts';

    /**
     * Base URL for Jikan API
     */
    private const API_BASE_URL = 'https://api.jikan.moe/v4';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificPostId = $this->option('post-id');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = $this->option('force');

        $this->info('Starting characters import from Jikan API...');

        // Get posts to process
        $query = Post::whereNotNull('mal_id')->where('mal_id', '>', 0);

        if ($specificPostId) {
            $query->where('id', $specificPostId);
            $this->info("Processing specific post ID: {$specificPostId}");
        }

        if ($limit) {
            $query->limit($limit);
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->warn('No posts found with valid mal_id');
            return Command::SUCCESS;
        }

        $this->info("Found {$posts->count()} posts to process");

        $progressBar = $this->output->createProgressBar($posts->count());
        $progressBar->start();

        $totalCharactersImported = 0;
        $totalCharactersUpdated = 0;
        $totalRelationshipsCreated = 0;

        foreach ($posts as $post) {
            try {
                $result = $this->processPostCharacters($post, $force);
                $totalCharactersImported += $result['characters_imported'];
                $totalCharactersUpdated += $result['characters_updated'];
                $totalRelationshipsCreated += $result['relationships_created'];

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("Error processing post {$post->id} (MAL ID: {$post->mal_id}): " . $e->getMessage());
            }

            // Add delay to be respectful to the API
            sleep(1);
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Characters import completed!");
        $this->info("Characters imported: {$totalCharactersImported}");
        $this->info("Characters updated: {$totalCharactersUpdated}");
        $this->info("Relationships created: {$totalRelationshipsCreated}");

        return Command::SUCCESS;
    }

    /**
     * Process characters for a specific post
     */
    private function processPostCharacters(Post $post, bool $force = false): array
    {
        $results = [
            'characters_imported' => 0,
            'characters_updated' => 0,
            'relationships_created' => 0,
        ];

        try {
            // Fetch characters list for this anime
            $charactersResponse = Http::timeout(30)->get(self::API_BASE_URL . "/anime/{$post->mal_id}/characters");

            if (!$charactersResponse->successful()) {
                throw new \Exception("Failed to fetch characters for anime ID {$post->mal_id}: HTTP {$charactersResponse->status()}");
            }

            $charactersData = $charactersResponse->json();

            if (empty($charactersData['data'])) {
                $this->warn("No characters found for anime ID {$post->mal_id}");
                return $results;
            }

            // Clear existing character relationships if force update
            if ($force) {
                PostCharacter::detachAllCharactersFromPost($post->id);
            }

            foreach ($charactersData['data'] as $characterEntry) {
                try {
                    $characterMalId = $characterEntry['character']['mal_id'];
                    $role = $characterEntry['role'] ?? 'other';

                    // Normalize role
                    $normalizedRole = $this->normalizeRole($role);

                    // Check if character already exists
                    $character = Character::where('mal_id', $characterMalId)->first();

                    if (!$character || $force) {
                        // Fetch full character details
                        $characterDetailResponse = Http::timeout(30)->get(self::API_BASE_URL . "/characters/{$characterMalId}/full");

                        if ($characterDetailResponse->successful()) {
                            $characterDetail = $characterDetailResponse->json();

                            if (!empty($characterDetail['data'])) {
                                $characterData = $this->formatCharacterData($characterDetail['data']);

                                if ($character) {
                                    // Update existing character
                                    $character->update($characterData);
                                    $results['characters_updated']++;
                                } else {
                                    // Create new character
                                    $character = Character::create($characterData);
                                    $results['characters_imported']++;
                                }
                            }
                        }

                        // Small delay between character requests
                        usleep(500000); // 0.5 seconds
                    }

                    // Create/update relationship if character exists
                    if ($character) {
                        PostCharacter::firstOrCreate([
                            'post_id' => $post->id,
                            'character_id' => $character->id,
                            'role' => $normalizedRole,
                        ]);
                        $results['relationships_created']++;
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing character {$characterMalId}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return $results;
    }

    /**
     * Normalize role from API to our enum values
     */
    private function normalizeRole(string $role): string
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
     * Format character data from API response
     */
    private function formatCharacterData(array $data): array
    {
        return [
            'mal_id' => $data['mal_id'],
            'images' => [
                'jpg' => [
                    'image_url' => $data['images']['jpg']['image_url'] ?? null,
                ],
                'webp' => [
                    'image_url' => $data['images']['webp']['image_url'] ?? null,
                    'small_image_url' => $data['images']['webp']['small_image_url'] ?? null,
                ],
            ],
            'name' => $data['name'] ?? '',
            'name_kanji' => $data['name_kanji'] ?? null,
            'nicknames' => $data['nicknames'] ?? [],
            'about' => $data['about'] ?? null,
            'slug' => $this->generateCharacterSlug($data['name'] ?? ''),
        ];
    }

    /**
     * Generate slug for character
     */
    private function generateCharacterSlug(string $name): string
    {
        if (empty($name)) {
            return 'unknown-character-' . time();
        }

        return Str::slug($name);
    }
}
