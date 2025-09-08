<?php

namespace App\Console\Commands\Import;

use App\Helpers\Import\AnimeImportService;
use Illuminate\Console\Command;

class ImportGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-genres {--post-id= : Import genres for specific post ID} {--limit= : Limit number of posts to process} {--force : Force update existing genres}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import genres data from Jikan API for all posts';

    protected AnimeImportService $importService;

    public function __construct()
    {
        parent::__construct();
        $this->importService = new AnimeImportService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificPostId = $this->option('post-id');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = $this->option('force');

        $this->info('Starting genres import from Jikan API...');

        // Get posts to process
        $posts = $this->importService->getPostsForProcessing($specificPostId, $limit);

        if ($posts->isEmpty()) {
            $this->warn('No posts found with valid mal_id');
            return Command::SUCCESS;
        }

        $this->info("Found {$posts->count()} posts to process");

        $progressBar = $this->output->createProgressBar($posts->count());
        $progressBar->start();

        $totalGenresImported = 0;
        $totalGenresUpdated = 0;
        $totalRelationshipsCreated = 0;

        foreach ($posts as $post) {
            try {
                $result = $this->importService->importGenres($post);
                
                $totalGenresImported += $result['created'] ?? 0;
                $totalGenresUpdated += $result['updated'] ?? 0;
                $totalRelationshipsCreated += $result['linked'] ?? 0;

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("Error processing post {$post->id} (MAL ID: {$post->mal_id}): " . $e->getMessage());
            }

            // Add delay to be respectful to the API
            sleep(1);
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Genres import completed!");
        $this->info("Genres imported: {$totalGenresImported}");
        $this->info("Genres updated: {$totalGenresUpdated}");
        $this->info("Relationships created: {$totalRelationshipsCreated}");

        return Command::SUCCESS;
    }
}
